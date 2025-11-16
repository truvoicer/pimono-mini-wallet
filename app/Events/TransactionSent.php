<?php

namespace App\Events;

use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionSent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;



    public function __construct(
        public Transaction $transaction,
    ) {}

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $settings = Setting::with('currency')->first();
        return [
            'message' => sprintf(
                'You have sent a transaction of amount %s%s to user %s (%s).',
                $settings && $settings->currency ? $settings->currency->symbol : '£',
                number_format($this->transaction->amount, 2),
                $this->transaction->receiver->name,
                $this->transaction->receiver->email,

            ),
            'transaction_id' => $this->transaction->id
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('transactions.user.' . $this->transaction->sender_id),
        ];
    }
}
