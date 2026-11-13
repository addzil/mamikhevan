<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order)
    {
        $this->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Pesanan '.$this->order->code.' sudah dikirim')
            ->greeting('Halo, '.$this->order->customer_name.'!')
            ->line('Pesanan '.$this->order->code.' sudah diserahkan kepada kurir.')
            ->line('Kurir: '.$this->order->shippingLabel())
            ->line('Nomor resi: '.$this->order->shipping_tracking_number)
            ->line('Barang yang dikirim:');

        foreach ($this->order->items as $item) {
            $message->line($item->quantity.' x '.$item->productDisplayName());
        }

        return $message
            ->action('Lacak pesanan', route('orders.track'))
            ->line('Gunakan kode pesanan '.$this->order->code.' dan nomor telepon saat checkout untuk melihat status terbaru.');
    }
}
