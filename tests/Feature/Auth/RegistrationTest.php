<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_register_without_admin_privileges(): void
    {
        Notification::fake();
        $this->get(route('register'))->assertOk()->assertSee('Buat akun pembeli');

        $response = $this->post(route('register'), [
            'name' => 'Siti Pembeli',
            'email' => 'siti@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'is_admin' => true,
            'role' => User::ROLE_ADMINISTRATOR,
        ]);

        $response->assertRedirect(route('verification.notice', absolute: false));
        $this->assertAuthenticated();
        $user = User::query()->where('email', 'siti@example.com')->firstOrFail();
        $this->assertFalse($user->is_admin);
        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertFalse($user->hasVerifiedEmail());
        $this->get(route('orders.index'))->assertRedirect(route('verification.notice'));

        Notification::assertSentTo($user, VerifyEmail::class, function (VerifyEmail $notification) use ($user) {
            $verificationUrl = $notification->toMail($user)->actionUrl;
            $this->assertStringStartsWith(config('app.url').'/verify-email/', $verificationUrl);
            $this->get($verificationUrl)
                ->assertRedirect(route('orders.index'));

            return true;
        });

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->get(route('orders.index'))->assertOk();
    }

    public function test_registration_rejects_an_existing_email_with_clear_message(): void
    {
        User::factory()->create(['email' => 'siti@example.com']);

        $this->post(route('register'), [
            'name' => 'Siti Pembeli',
            'email' => 'siti@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertSessionHasErrors([
            'email' => 'Email sudah terdaftar. Silakan masuk atau gunakan tautan lupa kata sandi.',
        ]);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }
}
