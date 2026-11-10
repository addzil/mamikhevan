<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::query()->exists()) {
            return;
        }

        $orders = [
            [
                'code' => 'KK-DEMO-0001',
                'customer_name' => 'Rina Kusuma',
                'customer_phone' => '081234567801',
                'customer_email' => 'rina@example.com',
                'customer_address' => 'Jl. Melati No. 12, Kel. Sukajadi, Kec. Sukajadi, Bandung 40162',
                'note' => 'Tolong dibungkus kado ya, kak.',
                'payment_method' => Order::PAYMENT_TRANSFER,
                'status' => Order::STATUS_PENDING,
                'shipping_cost' => 20000,
                'items' => [
                    ['slug' => 'kaos-little-dino', 'quantity' => 2, 'size' => '3T'],
                    ['slug' => 'topi-bucket-sunny', 'quantity' => 1, 'size' => 'M'],
                ],
            ],
            [
                'code' => 'KK-DEMO-0002',
                'customer_name' => 'Budi Santoso',
                'customer_phone' => '081234567802',
                'customer_email' => 'budi@example.com',
                'customer_address' => 'Jl. Kenanga Raya No. 8, Kel. Cipete, Jakarta Selatan 12410',
                'note' => null,
                'payment_method' => Order::PAYMENT_COD,
                'status' => Order::STATUS_PROCESSING,
                'shipping_cost' => 20000,
                'items' => [
                    ['slug' => 'dress-bunga-cherry', 'quantity' => 1, 'size' => '4T'],
                    ['slug' => 'onesie-cloud-soft', 'quantity' => 1, 'size' => '3-6 bln'],
                ],
            ],
            [
                'code' => 'KK-DEMO-0003',
                'customer_name' => 'Sari Ayu',
                'customer_phone' => '081234567803',
                'customer_email' => null,
                'customer_address' => 'Jl. Pahlawan No. 45, Kel. Kepanjen, Kec. Kepanjen, Malang 65163',
                'note' => 'Kirim setelah jam 15.00.',
                'payment_method' => Order::PAYMENT_TRANSFER,
                'status' => Order::STATUS_COMPLETED,
                'shipping_cost' => 0,
                'items' => [
                    ['slug' => 'jaket-denim-ceria', 'quantity' => 1, 'size' => '5T'],
                    ['slug' => 'celana-jogger-mini', 'quantity' => 1, 'size' => '4T'],
                    ['slug' => 'sneakers-mini-star', 'quantity' => 1, 'size' => '24'],
                ],
            ],
            [
                'code' => 'KK-DEMO-0004',
                'customer_name' => 'Dewi Lestari',
                'customer_phone' => '081234567804',
                'customer_email' => 'dewi@example.com',
                'customer_address' => 'Jl. Slamet Riyadi No. 101, Kel. Sriwedari, Surakarta 57141',
                'note' => null,
                'payment_method' => Order::PAYMENT_TRANSFER,
                'status' => Order::STATUS_CONFIRMED,
                'shipping_cost' => 20000,
                'items' => [
                    ['slug' => 'setelan-rainbow', 'quantity' => 1, 'size' => '3T'],
                ],
            ],
            [
                'code' => 'KK-DEMO-0005',
                'customer_name' => 'Andi Pratama',
                'customer_phone' => '081234567805',
                'customer_email' => null,
                'customer_address' => 'Jl. Gatot Subroto No. 77, Kel. Kuta, Badung, Bali 80361',
                'note' => 'Batal karena ganti ukuran.',
                'payment_method' => Order::PAYMENT_COD,
                'status' => Order::STATUS_CANCELLED,
                'shipping_cost' => 20000,
                'items' => [
                    ['slug' => 'rok-tutu-pink', 'quantity' => 1, 'size' => '4T'],
                ],
            ],
        ];

        foreach ($orders as $data) {
            $items = collect($data['items'])->map(function (array $item) {
                $product = Product::query()->where('slug', $item['slug'])->firstOrFail();

                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'size' => $item['size'],
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $product->price * $item['quantity'],
                ];
            });

            $subtotal = $items->sum('subtotal');

            $order = Order::create([
                'code' => $data['code'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'],
                'customer_address' => $data['customer_address'],
                'note' => $data['note'],
                'payment_method' => $data['payment_method'],
                'subtotal' => $subtotal,
                'shipping_cost' => $data['shipping_cost'],
                'total' => $subtotal + $data['shipping_cost'],
                'status' => $data['status'],
                'created_at' => now()->subDays(rand(1, 20)),
                'updated_at' => now(),
            ]);

            $order->items()->createMany($items->all());
        }
    }
}
