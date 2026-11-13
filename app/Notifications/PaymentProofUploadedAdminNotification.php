<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentProofUploadedAdminNotification extends Notification implements ShouldQueue
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
            ->subject('Bukti pembayaran diterima untuk pesanan '.$this->order->code)
            ->greeting('Bukti pembayaran pelanggan telah diunggah')
            ->line('Kode pesanan: '.$this->order->code)
            ->line('Pelanggan: '.$this->order->customer_name.' ('.$this->order->customer_phone.')')
            ->line('Email pelanggan: '.($this->order->customer_email ?: 'Tidak tersedia'))
            ->line('Metode pembayaran: '.$this->paymentDetails())
            ->line('Total pesanan: Rp '.number_format($this->order->total, 0, ',', '.'))
            ->line('Produk yang dipesan:');

        foreach ($this->order->items as $item) {
            $amount = $item->is_promo_gift
                ? 'Gratis (hadiah promo)'
                : 'Rp '.number_format($item->subtotal, 0, ',', '.');
            $message->line($item->quantity.' x '.$item->productDisplayName().' — '.$amount);
        }

        $message->line('Bukti diunggah: '.$this->order->payment_proof_uploaded_at?->format('d/m/Y H:i').' WIB')
            ->line('Periksa bukti dan pastikan dana telah diterima sebelum mengonfirmasi pesanan.');

        return $message->action('Lihat pesanan dan bukti pembayaran', route('admin.orders.show', $this->order));
    }

    private function paymentDetails(): string
    {
        $destination = $this->order->payment_destination;

        if (($destination['type'] ?? null) === 'bank') {
            return 'Transfer Bank '.$destination['bank'].' '.$destination['account_number'].' a.n. '.$destination['account_name'];
        }

        if (($destination['type'] ?? null) === 'qris') {
            return 'QRIS';
        }

        return $this->order->paymentLabel();
    }
}
