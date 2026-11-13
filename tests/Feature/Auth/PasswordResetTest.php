<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status', 'Tautan reset kata sandi telah dikirim ke email Anda.');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $resetUrl = $notification->toMail($user)->actionUrl;
            $this->assertStringStartsWith(config('app.url').'/reset-password/', $resetUrl);
            $response = $this->get($resetUrl);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-secret-password',
                'password_confirmation' => 'new-secret-password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertSessionHas('status', 'Kata sandi berhasil diubah. Silakan masuk.')
                ->assertRedirect(route('login'));

            $this->assertTrue(Hash::check('new-secret-password', $user->fresh()->password));

            return true;
        });
    }

    public function test_unknown_email_shows_a_clear_reset_link_error(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'unknown@example.com'])
            ->assertSessionHasErrors(['email' => 'Akun dengan email tersebut tidak ditemukan.']);

        Notification::assertNothingSent();
    }

    public function test_invalid_reset_token_is_rejected_without_changing_password(): void
    {
        $user = User::factory()->create();

        $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertSessionHasErrors([
            'email' => 'Tautan reset tidak valid atau sudah kedaluwarsa. Minta tautan baru.',
        ]);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
