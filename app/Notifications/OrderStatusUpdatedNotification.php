<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public string $status)
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
        $message = match ($this->status) {
            Order::STATUS_CONFIRMED => (new MailMessage)
                ->subject('Pesanan '.$this->order->code.' telah dikonfirmasi')
                ->greeting('Pesananmu telah dikonfirmasi')
                ->line('Admin telah mengonfirmasi pesanan '.$this->order->code.'. Pesanan akan segera diproses.'),
            Order::STATUS_PROCESSING => (new MailMessage)
                ->subject('Pesanan '.$this->order->code.' sedang disiapkan')
                ->greeting('Pesananmu sedang disiapkan')
                ->line('Pesanan '.$this->order->code.' sedang disiapkan untuk dikirim.'),
            Order::STATUS_PENDING => (new MailMessage)
                ->subject('Pesanan '.$this->order->code.' diaktifkan kembali')
                ->greeting('Pesananmu diaktifkan kembali')
                ->line('Admin mengaktifkan kembali pesanan '.$this->order->code.'. Pesanan menunggu konfirmasi.'),
            default => (new MailMessage)
                ->subject('Pembaruan pesanan '.$this->order->code)
                ->greeting('Ada pembaruan untuk pesananmu')
                ->line('Status pesanan '.$this->order->code.' berubah menjadi '.$this->order->statusLabel().'.'),
        };

        if ($this->status === Order::STATUS_PENDING) {
            $message->line('Total pesanan: Rp '.number_format($this->order->total, 0, ',', '.'));
        }

        foreach ($this->order->items as $item) {
            $amount = $item->is_promo_gift
                ? 'Gratis (hadiah promo)'
                : 'Rp '.number_format($item->subtotal, 0, ',', '.');
            $message->line($item->quantity.' x '.$item->productDisplayName().' — '.$amount);
        }

        return $message
            ->action('Lacak pesanan', route('orders.track'))
            ->line('Gunakan kode pesanan '.$this->order->code.' dan nomor telepon saat checkout untuk melihat detail pesanan.');
    }
}
