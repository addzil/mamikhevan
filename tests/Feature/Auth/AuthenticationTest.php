<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()->assertSee('Akun Pembeli')->assertSee('Akses staf');
        $this->get(route('admin.login'))->assertOk()->assertSee('Masuk panel admin');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

        $response = $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_buyer_login_opens_their_order_list(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('orders.index', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_cannot_sign_in_through_the_customer_login(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

        $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_customer_cannot_sign_in_through_the_admin_login(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_unverified_buyer_login_opens_email_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('verification.notice', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
