<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Transaction\TransactionService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(TransactionService $transactionService): void
    {
        User::all()->each(function (User $sender) {
            $sender->balance = 5000;
            $sender->save();
        });
        User::all()->each(function (User $sender) use ($transactionService) {
            $receivers = User::where('id', '!=', $sender->id)->get();
            foreach ($receivers as $receiver) {
                $transactionService->storeTransaction(
                    $sender,
                    [
                        'receiver_id' => $receiver->id,
                        'amount' => 100,
                    ]
                );
            }

        });
    }
}
