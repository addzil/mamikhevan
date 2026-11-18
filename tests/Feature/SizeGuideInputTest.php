<?php

namespace Tests\Feature;

use App\Models\SizeGuideType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SizeGuideInputTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->actingAs(User::query()->where('is_admin', true)->firstOrFail());
    }

    public function test_blank_slug_is_generated_from_trimmed_name_when_creating(): void
    {
        $this->post(route('admin.size-guides.store'), $this->guideData([
            'name' => '  Baju Tidur Anak  ',
            'slug' => '',
        ]))->assertRedirect(route('admin.size-guides.index'))->assertSessionHasNoErrors();

        $guide = SizeGuideType::query()->where('slug', 'baju-tidur-anak')->firstOrFail();
        $this->assertSame('Baju Tidur Anak', $guide->name);
    }

    public function test_custom_slug_is_normalized_before_uniqueness_validation(): void
    {
        $this->post(route('admin.size-guides.store'), $this->guideData([
            'name' => 'Panduan Baru',
            'slug' => '  Atasan / Kaos  ',
        ]))->assertSessionHasErrors('slug');

        $this->assertDatabaseMissing('size_guide_types', ['name' => 'Panduan Baru']);
    }

    public function test_blank_slug_on_update_keeps_existing_slug_even_when_name_changes(): void
    {
        $guide = SizeGuideType::query()->where('slug', 'atasan-kaos')->firstOrFail();

        $this->put(route('admin.size-guides.update', $guide), $this->guideData([
            'name' => 'Celana/Bawahan Baru',
            'slug' => '   ',
        ]))->assertRedirect(route('admin.size-guides.index'))->assertSessionHasNoErrors();

        $this->assertSame('atasan-kaos', $guide->fresh()->slug);
        $this->assertSame('Celana/Bawahan Baru', $guide->fresh()->name);
    }

    public function test_update_accepts_its_own_slug_and_normalizes_a_new_one(): void
    {
        $guide = SizeGuideType::query()->where('slug', 'atasan-kaos')->firstOrFail();

        $this->put(route('admin.size-guides.update', $guide), $this->guideData([
            'name' => $guide->name,
            'slug' => 'Atasan Kaos',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('atasan-kaos', $guide->fresh()->slug);

        $this->put(route('admin.size-guides.update', $guide), $this->guideData([
            'name' => $guide->name,
            'slug' => '  Baju & Kaos  ',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('baju-kaos', $guide->fresh()->slug);
    }

    public function test_name_that_cannot_generate_a_slug_requires_a_custom_slug(): void
    {
        $this->post(route('admin.size-guides.store'), $this->guideData([
            'name' => '✨✨',
            'slug' => '',
        ]))->assertSessionHasErrors('slug');
    }

    private function guideData(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Panduan Contoh',
            'slug' => '',
            'measurement_fields' => ['chest_cm'],
            'entries' => [['label' => '1T', 'chest_cm' => '50', 'sort_order' => 0]],
        ], $overrides);
    }
}
