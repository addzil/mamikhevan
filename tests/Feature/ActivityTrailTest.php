<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ActivityTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrator_can_open_the_audit_trail_page(): void
    {
        $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $management = User::factory()->create(['role' => User::ROLE_MANAGEMENT]);
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->get(route('admin.audit-trail.index'))->assertRedirect(route('admin.login'));
        $this->actingAs($administrator)->get(route('admin.audit-trail.index'))->assertOk();
        $this->actingAs($management)->get(route('admin.audit-trail.index'))->assertForbidden();
        $this->actingAs($operator)->get(route('admin.audit-trail.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.audit-trail.index'))->assertForbidden();
    }

    public function test_login_success_failure_and_logout_are_recorded(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        ActivityLog::query()->delete();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $failedLogin = ActivityLog::query()->where('action', 'auth.login.failed')->firstOrFail();
        $this->assertSame($user->email, $failedLogin->properties['identifier']);
        $this->assertNull($failedLogin->actor_user_id);
        $this->assertSame($user->id, $failedLogin->subject_id);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertRedirect();

        $successfulLogin = ActivityLog::query()->where('action', 'auth.login.succeeded')->firstOrFail();
        $this->assertSame($user->id, $successfulLogin->actor_user_id);
        $this->assertSame(User::ROLE_CUSTOMER, $successfulLogin->actor_role);
        $this->assertNotNull($successfulLogin->session_hash);
        $this->assertSame($user->email, $successfulLogin->actor_email);

        $this->post(route('logout'))->assertRedirect('/');
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'auth.logout',
            'actor_user_id' => $user->id,
        ]);
    }

    public function test_model_changes_capture_before_and_after_values_without_secrets(): void
    {
        $this->seed();
        ActivityLog::query()->delete();
        $administrator = User::query()->where('role', User::ROLE_ADMINISTRATOR)->firstOrFail();
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = Product::query()->firstOrFail();
        $oldStock = $product->stock;

        $this->actingAs($administrator);
        $product->decrement('stock', 1);
        $stockLog = ActivityLog::query()->where('action', 'product.updated')->latest('id')->firstOrFail();

        $this->assertSame($administrator->id, $stockLog->actor_user_id);
        $this->assertSame($oldStock, $stockLog->properties['changes']['stock']['before']);
        $this->assertSame($oldStock - 1, $stockLog->properties['changes']['stock']['after']);

        $customer->update(['password' => Hash::make('new-secret')]);
        $passwordLog = ActivityLog::query()->where('action', 'user.updated')->latest('id')->firstOrFail();
        $this->assertSame('[redacted]', $passwordLog->properties['changes']['password']);
        $this->assertStringNotContainsString('new-secret', json_encode($passwordLog->properties));
    }

    public function test_customer_cart_activity_is_linked_to_customer_account(): void
    {
        $this->seed();
        ActivityLog::query()->delete();
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = Product::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($customer)
            ->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2, 'size' => $product->sizeList()[0] ?? null])
            ->assertRedirect();

        $activity = ActivityLog::query()->where('action', 'store.cart.item_added')->firstOrFail();
        $this->assertSame($customer->id, $activity->actor_user_id);
        $this->assertSame($product->id, $activity->subject_id);
        $this->assertSame(2, $activity->properties['quantity']);
        $this->assertNotNull($activity->session_hash);
    }
}
