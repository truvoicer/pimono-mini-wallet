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

class TransactionReceived implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Transaction $transaction,
    ) {
        //
    }
    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        /** @var Setting|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null $settings */
        $settings = Setting::with('currency')->first();
        if (! $settings instanceof Setting) {
            $currencySymbol = '£';
        } else {
            $currencySymbol = $settings->currency->symbol ?? '£';
        }
        return [
            'message' => sprintf(
                'You have received a new transaction of amount %s%s from user %s (%s).',
                $currencySymbol,
                number_format($this->transaction->amount, 2),
                $this->transaction->sender->name,
                $this->transaction->sender->email,
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
            new PrivateChannel('transactions.user.' . $this->transaction->receiver_id),
        ];
    }
}
