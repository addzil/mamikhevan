<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageTest extends TestCase
{
    public function test_guests_can_read_public_uploads_without_a_symlink(): void
    {
        Storage::fake('public');
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQDJ/pLvAAAAAElFTkSuQmCC');
        Storage::disk('public')->put('products/photo.png', $image);

        $this->get('/storage/products/photo.png')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertStreamedContent($image);
    }

    public function test_missing_public_upload_returns_404(): void
    {
        Storage::fake('public');

        $this->get('/storage/products/missing.png')->assertNotFound();
    }

    public function test_private_files_are_not_accessible_through_public_storage(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        Storage::disk('local')->put('payment-proofs/proof.png', 'private payment proof');

        $this->get('/storage/payment-proofs/proof.png')->assertNotFound();
    }

    public function test_parent_directory_traversal_is_rejected(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        Storage::disk('local')->put('secret.txt', 'private content');

        $this->get('/storage/%2e%2e/local/secret.txt')->assertNotFound();
    }
}
