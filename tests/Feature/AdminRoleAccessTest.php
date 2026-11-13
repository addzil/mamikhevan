<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_access_only_the_assigned_operational_sections(): void
    {
        $this->seed();
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);

        $this->actingAs($operator);
        foreach ([
            'admin.products.index',
            'admin.categories.index',
            'admin.reviews.index',
            'admin.home-banner.edit',
            'admin.size-guides.index',
            'admin.messages.index',
        ] as $route) {
            $this->get(route($route))->assertOk();
        }

        foreach ([
            'admin.orders.index',
            'admin.promotions.index',
            'admin.payment-settings.edit',
            'admin.users.index',
        ] as $route) {
            $this->get(route($route))->assertForbidden();
        }

        $this->get(route('admin.products.index'))
            ->assertDontSee('Pesanan')
            ->assertDontSee('Pembayaran')
            ->assertDontSee('Manajemen User')
            ->assertDontSee('Promo');
    }

    public function test_management_can_access_business_sections_but_not_user_management(): void
    {
        $this->seed();
        $manager = User::factory()->create(['role' => User::ROLE_MANAGEMENT]);

        $this->actingAs($manager)
            ->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.orders.index'))->assertOk();
        $this->get(route('admin.promotions.index'))->assertOk();
        $this->get(route('admin.payment-settings.edit'))->assertOk();
        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_unknown_role_cannot_access_the_admin_dashboard_even_if_legacy_flag_is_set(): void
    {
        $user = User::factory()->create(['role' => 'unexpected-role']);
        $user->is_admin = true;
        $user->save();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_administrator_can_create_update_and_delete_customer_and_staff_accounts(): void
    {
        $this->seed();
        $administrator = User::query()->where('role', User::ROLE_ADMINISTRATOR)->firstOrFail();
        $this->actingAs($administrator);

        $this->post(route('admin.users.store'), [
            'name' => 'Operator Baru',
            'email' => 'operator.baru@example.com',
            'role' => User::ROLE_OPERATOR,
            'password' => 'operator-secret-123',
            'password_confirmation' => 'operator-secret-123',
        ])->assertRedirect(route('admin.users.index'));

        $operator = User::query()->where('email', 'operator.baru@example.com')->firstOrFail();
        $this->assertSame(User::ROLE_OPERATOR, $operator->role);
        $this->assertTrue($operator->is_admin);
        $this->assertNotNull($operator->email_verified_at);

        $this->put(route('admin.users.update', $operator), [
            'name' => 'Pelanggan Baru',
            'email' => 'operator.baru@example.com',
            'role' => User::ROLE_CUSTOMER,
        ])->assertRedirect(route('admin.users.index'));

        $operator->refresh();
        $this->assertSame(User::ROLE_CUSTOMER, $operator->role);
        $this->assertFalse($operator->is_admin);

        $this->delete(route('admin.users.destroy', $operator))
            ->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $operator->id]);
    }

    public function test_administrator_cannot_remove_or_demote_the_last_administrator(): void
    {
        $this->seed();
        $administrator = User::query()->where('role', User::ROLE_ADMINISTRATOR)->firstOrFail();
        $this->actingAs($administrator);

        $this->put(route('admin.users.update', $administrator), [
            'name' => $administrator->name,
            'email' => $administrator->email,
            'role' => User::ROLE_MANAGEMENT,
        ])->assertSessionHas('error');

        $this->delete(route('admin.users.destroy', $administrator))
            ->assertSessionHas('error');

        $this->assertSame(User::ROLE_ADMINISTRATOR, $administrator->fresh()->role);
    }

    public function test_running_the_admin_seeder_does_not_reset_an_existing_password(): void
    {
        $this->seed();
        $administrator = User::query()->where('role', User::ROLE_ADMINISTRATOR)->firstOrFail();
        $administrator->password = 'changed-password-hash';
        $administrator->save();
        $passwordHash = $administrator->password;

        $this->seed(AdminUserSeeder::class);

        $this->assertSame($passwordHash, $administrator->fresh()->password);
    }
}
