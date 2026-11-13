<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompletedAdminNotification extends Notification implements ShouldQueue
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
            ->subject('Pesanan '.$this->order->code.' selesai diterima pelanggan')
            ->greeting('Pelanggan mengonfirmasi pesanan diterima')
            ->line('Kode pesanan: '.$this->order->code)
            ->line('Pelanggan: '.$this->order->customer_name.' ('.$this->order->customer_phone.')')
            ->line('Metode pembayaran: '.$this->order->paymentLabel())
            ->line('Total transaksi: Rp '.number_format($this->order->total, 0, ',', '.'))
            ->line('Produk yang dipesan:');

        foreach ($this->order->items as $item) {
            $amount = $item->is_promo_gift
                ? 'Gratis (hadiah promo)'
                : 'Rp '.number_format($item->subtotal, 0, ',', '.');
            $message->line($item->quantity.' x '.$item->productDisplayName().' — '.$amount);
        }

        return $message->action('Lihat pesanan di admin', route('admin.orders.show', $this->order));
    }
}
