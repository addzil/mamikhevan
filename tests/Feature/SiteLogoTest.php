<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_a_logo_visible_in_the_public_header_footer_and_browser_tab(): void
    {
        Storage::fake('public');
        $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

        $this->actingAs($administrator)
            ->get(route('admin.site-settings.edit'))
            ->assertOk()
            ->assertSee('Unggah logo baru');

        $this->put(route('admin.site-settings.update'), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect();

        $logoPath = SiteSetting::current()->logo_path;
        $this->assertStringStartsWith('branding/', $logoPath);
        Storage::disk('public')->assertExists($logoPath);

        $logoUrl = Storage::disk('public')->url($logoPath);
        $response = $this->get(route('home'))->assertOk();
        $this->assertSame(3, substr_count($response->getContent(), $logoUrl));
        $this->get(route('admin.dashboard'))->assertOk()->assertSee($logoUrl);
        $this->get(route('profile.edit'))->assertOk()->assertSee($logoUrl);
        Auth::logout();
        $this->get(route('admin.login'))->assertOk()->assertSee($logoUrl);
        $this->get('/storage/'.$logoPath)->assertOk()->assertHeader('Content-Type', 'image/png');
    }

    public function test_replacing_and_removing_a_logo_updates_the_public_site_and_deletes_old_files(): void
    {
        Storage::fake('public');
        $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $this->actingAs($administrator);

        $this->put(route('admin.site-settings.update'), [
            'logo' => UploadedFile::fake()->image('first.png'),
        ])->assertRedirect();
        $firstPath = SiteSetting::current()->logo_path;

        $this->put(route('admin.site-settings.update'), [
            'logo' => UploadedFile::fake()->image('second.png'),
        ])->assertRedirect();
        $secondPath = SiteSetting::current()->logo_path;
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);

        $this->put(route('admin.site-settings.update'), ['remove_logo' => true])->assertRedirect();
        $this->assertNull(SiteSetting::current()->fresh()->logo_path);
        Storage::disk('public')->assertMissing($secondPath);
        $this->get(route('home'))->assertOk()->assertSee('Mamikhevan');
    }

    public function test_invalid_logo_and_operator_access_are_rejected(): void
    {
        Storage::fake('public');
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $this->actingAs($operator)->get(route('admin.site-settings.edit'))->assertForbidden();
        $this->put(route('admin.site-settings.update'), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertForbidden();

        $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $this->actingAs($administrator)->put(route('admin.site-settings.update'), [
            'logo' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain'),
        ])->assertSessionHasErrors('logo');

        $this->assertDatabaseCount('site_settings', 0);
    }
}
