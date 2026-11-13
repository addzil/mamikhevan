<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification implements ShouldQueue
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
            ->subject('Pesanan '.$this->order->code.' dibatalkan')
            ->greeting('Halo, '.$this->order->customer_name.'!')
            ->line('Pesanan '.$this->order->code.' telah dibatalkan oleh admin.')
            ->line('Rincian produk:');

        foreach ($this->order->items as $item) {
            $amount = $item->is_promo_gift
                ? 'Gratis (hadiah promo)'
                : 'Rp '.number_format($item->subtotal, 0, ',', '.');
            $message->line($item->quantity.' x '.$item->productDisplayName().' — '.$amount);
        }

        $message->line('Metode pembayaran: '.$this->order->paymentLabel())
            ->line('Total pesanan: Rp '.number_format($this->order->total, 0, ',', '.'))
            ->line('Stok produk pesanan telah dikembalikan ke persediaan.');

        if ($this->order->payment_method === Order::PAYMENT_TRANSFER) {
            $message->line('Jika kamu sudah melakukan transfer, silakan hubungi admin untuk bantuan pengembalian dana.');
        }

        return $message
            ->action('Lacak pesanan', route('orders.track'))
            ->line('Gunakan kode pesanan '.$this->order->code.' dan nomor telepon saat checkout untuk melihat detail pesanan.');
    }
}
