<?php

namespace App\Services\Transaction;

use App\Events\TransactionReceived;
use App\Events\TransactionSent;
use App\Exceptions\TransactionException;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\DB;

class TransactionService
{

    private float $commissionFeePercentage;

    public function __construct(
        private TransactionRepository $transactionRepository
    ){
        $this->commissionFeePercentage = config('wallet.commission_fee_percentage');
    }

    private function calculateCommissionFee(float $amount): float
    {
        return $amount * ($this->commissionFeePercentage / 100);
    }

    /**
     * Stores a new transaction, ensuring atomic balance update using database transactions and pessimistic locking.
     *
     * @param \App\Models\User $user The authenticated sender user.
     * @param array $data
     * @return Transaction
     * @throws TransactionException
     */
    public function storeTransaction(User $user, array $data): Transaction
    {
        if (empty($data['receiver_id'])) {
            throw new TransactionException('Receiver ID is required.');
        }

        $receiverUser = User::find($data['receiver_id']);

        if (!$receiverUser) {
            throw new TransactionException('Receiver user not found.');
        }

        if ($user->id === $receiverUser->id) {
            throw new TransactionException('You cannot send a transaction to yourself.');
        }

        if (empty($data['amount']) || $data['amount'] <= 0) {
            throw new TransactionException('Invalid transaction amount.');
        }

        $commissionFee = $this->calculateCommissionFee($data['amount']);

        $totalAmount = $data['amount'] + $commissionFee;
        $data['commission_fee'] = $commissionFee;

        $transaction = DB::transaction(function () use ($user, $receiverUser, $totalAmount, $data) {

            // Get the IDs of the users
            $senderId = $user->id;
            $receiverId = $receiverUser->id;

            // Determine the order to lock (lower ID first)
            $userA = ($senderId < $receiverId) ? $user : $receiverUser;
            $userB = ($senderId < $receiverId) ? $receiverUser : $user;

            // Fetch and lock user models based on the determined order
            $lockedUserA = User::lockForUpdate()->find($userA->id);
            $lockedUserB = User::lockForUpdate()->find($userB->id);

            // Re-assign locked users to sender/receiver variables
            $lockedSender = ($senderId < $receiverId) ? $lockedUserA : $lockedUserB;
            $lockedReceiver = ($senderId < $receiverId) ? $lockedUserB : $lockedUserA;

            if ($lockedSender->balance < $totalAmount) {
                // Throw an exception to rollback the database transaction
                throw new TransactionException('Insufficient balance.');
            }


            // Modify the balance column directly on the locked row.
            $lockedReceiver->increment('balance', $data['amount']);
            $lockedSender->decrement('balance', $totalAmount);

            //Create the transaction record
            $transaction = $lockedSender->sentTransactions()->create(
                $this->transactionRepository->getDataBuilder()
                ->setData([
                    ...$data,
                    'sender_id' => $lockedSender->id,
                ])
                ->build()
            );

            return $transaction;
        });

        $user->refresh();
        $receiverUser->refresh();

        TransactionSent::dispatch($transaction);
        TransactionReceived::dispatch($transaction);

        return $transaction;
    }
}
