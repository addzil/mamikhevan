<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class OrderPaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private readonly int $subtotal;

    private readonly int $shippingCost;

    private readonly int $promotionDiscount;

    private readonly int $shippingDiscount;

    private readonly ?string $promotionName;

    private readonly int $total;

    private readonly ?int $paymentUniqueCode;

    private readonly string $paymentMethod;

    /** @var array<string, string>|null */
    private readonly ?array $paymentDestination;

    public function __construct(public Order $order)
    {
        $this->subtotal = $order->subtotal;
        $this->shippingCost = $order->shipping_cost;
        $this->promotionDiscount = $order->promotion_discount ?? 0;
        $this->shippingDiscount = $order->shipping_discount ?? 0;
        $this->promotionName = $order->promotion_name;
        $this->total = $order->total;
        $this->paymentUniqueCode = $order->payment_unique_code;
        $this->paymentMethod = $order->payment_method;
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
            ->subject('Pesanan '.$this->order->code.' berhasil dibuat')
            ->greeting('Halo, '.$this->order->customer_name.'!')
            ->line('Pesanan '.$this->order->code.' sudah kami terima.')
            ->line('Alamat pengiriman: '.$this->order->customer_address)
            ->line('Kurir: '.$this->order->shippingLabel())
            ->line('Barang yang dipesan:');

        foreach ($this->order->items as $item) {
            $message->line($item->quantity.' × '.$item->productDisplayName().' — '.($item->is_promo_gift ? 'Gratis (hadiah promo)' : $item->subtotal_formatted));
        }

        $message->line('Subtotal: '.$this->rupiah($this->subtotal))
            ->line('Ongkos kirim: '.($this->shippingCost === 0 ? 'Gratis' : $this->rupiah($this->shippingCost)));

        if ($this->promotionName !== null) {
            $message->line('Promo: '.$this->promotionName);
        }

        if (filled($this->order->voucher_code)) {
            $message->line('Kode voucher: '.$this->order->voucher_code);
        }

        if ($this->promotionDiscount > 0) {
            $message->line('Diskon produk: -'.$this->rupiah($this->promotionDiscount));
        }

        if ($this->shippingDiscount > 0) {
            $message->line('Diskon ongkir: -'.$this->rupiah($this->shippingDiscount));
        }

        if ($this->paymentMethod === Order::PAYMENT_TRANSFER) {
            $this->addTransferDetails($message);
        } else {
            $message->line('Total pembayaran COD: '.$this->rupiah($this->total))
                ->line('Pembayaran dilakukan saat pesanan diterima.');
        }

        return $message
            ->action('Lacak dan lihat detail pesanan', route('orders.track'))
            ->line('Gunakan kode pesanan '.$this->order->code.' dan nomor telepon saat checkout.');
    }

    private function addTransferDetails(MailMessage $message): void
    {
        $message->line('Total yang perlu ditransfer: '.$this->rupiah($this->total));

        if ($this->paymentUniqueCode) {
            $message->line('Kode unik transfer: '.$this->rupiah($this->paymentUniqueCode));
        }

        if (($this->paymentDestination['type'] ?? null) === 'bank') {
            $message->line('Rekening '.$this->paymentDestination['bank'].': '.$this->paymentDestination['account_number'].' a.n. '.$this->paymentDestination['account_name']);
        } elseif (($this->paymentDestination['type'] ?? null) === 'qris' && Storage::disk('public')->exists($this->paymentDestination['image'])) {
            $message->line('QRIS pembayaran terlampir pada email ini.')
                ->attach(Attachment::fromStorageDisk('public', $this->paymentDestination['image'])
                    ->as('QRIS-Mamikhevan.'.pathinfo($this->paymentDestination['image'], PATHINFO_EXTENSION)));
        } elseif ($this->paymentDestination === null) {
            $message->line('Hubungi admin untuk memastikan tujuan pembayaran pesanan ini.');
        } else {
            $message->line('Gambar QRIS tidak tersedia. Hubungi admin sebelum membayar.');
        }
    }

    private function rupiah(int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
