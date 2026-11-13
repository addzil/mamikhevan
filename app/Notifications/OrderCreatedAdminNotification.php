<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private readonly int $total;

    /** @var array<string, string>|null */
    private readonly ?array $paymentDestination;

    public function __construct(public Order $order)
    {
        $this->total = $order->total;
        $this->paymentDestination = $order->payment_destination;
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
            ->subject($this->order->payment_method === Order::PAYMENT_COD
                ? 'Pesanan baru '.$this->order->code.' dengan pembayaran COD'
                : 'Pesanan baru '.$this->order->code.' menunggu pembayaran')
            ->greeting('Pesanan baru dari '.$this->order->customer_name)
            ->line('Kode pesanan: '.$this->order->code)
            ->line('Pelanggan: '.$this->order->customer_name.' ('.$this->order->customer_phone.')')
            ->line('Email pelanggan: '.$this->order->customer_email)
            ->line('Alamat pengiriman: '.$this->order->customer_address)
            ->line('Barang yang dipesan:');

        foreach ($this->order->items as $item) {
            $message->line($item->quantity.' x '.$item->productDisplayName().' ('.$item->subtotal_formatted.')');
        }

        $message->line('Total yang akan dibayar: Rp '.number_format($this->total, 0, ',', '.'));

        if ($this->order->payment_method === Order::PAYMENT_COD) {
            $message->line('Metode: Bayar di Tempat (COD). Pembayaran dilakukan saat pesanan diterima.');
        } elseif (($this->paymentDestination['type'] ?? null) === 'bank') {
            $message->line('Pelanggan diminta transfer ke rekening '.$this->paymentDestination['bank'].': '.$this->paymentDestination['account_number'].' a.n. '.$this->paymentDestination['account_name']);
        } elseif (($this->paymentDestination['type'] ?? null) === 'qris') {
            $message->line('Pelanggan diminta membayar melalui QRIS.');
        } else {
            $message->line('Tujuan pembayaran belum tercatat pada pesanan ini.');
        }

        return $message->action('Lihat pesanan di admin', route('admin.orders.show', $this->order));
    }
}
