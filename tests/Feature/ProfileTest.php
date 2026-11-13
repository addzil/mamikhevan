<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        Notification::fake();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'current_password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('verification.notice'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_email_change_requires_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/profile')->patch('/profile', [
            'name' => 'Test User',
            'email' => 'new@example.com',
        ])->assertSessionHasErrors('current_password');

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_email_change_rejects_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/profile')->patch('/profile', [
            'name' => 'Test User',
            'email' => 'new@example.com',
            'current_password' => 'wrong-password',
        ])->assertSessionHasErrors('current_password');

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_profile_cannot_change_admin_role(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'role' => User::ROLE_ADMINISTRATOR,
        ])->assertSessionHasNoErrors();

        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_admin_can_set_and_clear_their_notification_email_without_changing_login_email(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

        $this->actingAs($admin)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Email notifikasi pesanan');

        $this->patch(route('profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'notification_email' => 'pesanan@example.com',
            'current_password' => 'password',
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $admin->refresh();
        $this->assertSame('pesanan@example.com', $admin->notification_email);
        $this->assertNotNull($admin->email_verified_at);

        $this->patch(route('profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'notification_email' => '',
            'current_password' => 'password',
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $this->assertNull($admin->fresh()->notification_email);
    }

    public function test_admin_notification_email_change_requires_password_and_valid_email(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR, 'notification_email' => 'lama@example.com']);

        $this->actingAs($admin)->patch(route('profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'notification_email' => 'baru@example.com',
        ])->assertSessionHasErrors('current_password');

        $this->patch(route('profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'notification_email' => 'bukan-email',
            'current_password' => 'password',
        ])->assertSessionHasErrors('notification_email');

        $this->assertSame('lama@example.com', $admin->fresh()->notification_email);
    }

    public function test_customer_cannot_set_a_notification_email(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('Email notifikasi pesanan');

        $this->patch(route('profile.update'), [
            'name' => $customer->name,
            'email' => $customer->email,
            'notification_email' => 'milik-admin@example.com',
        ])->assertSessionHasErrors('notification_email');

        $this->assertNull($customer->fresh()->notification_email);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jalan Melati Nomor 12, Bandung',
            'payment_method' => Order::PAYMENT_COD,
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'total' => 100000,
            'status' => Order::STATUS_PENDING,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
        $this->assertNull($order->fresh()->user_id);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
