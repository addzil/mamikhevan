<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SizeGuideRequest;
use App\Models\SizeGuideEntry;
use App\Models\SizeGuideType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SizeGuideController extends Controller
{
    public function index(): View
    {
        $sizeGuides = SizeGuideType::withCount('entries')
            ->orderBy('name')
            ->get();

        return view('admin.size-guides.index', compact('sizeGuides'));
    }

    public function create(): View
    {
        return view('admin.size-guides.create');
    }

    public function store(SizeGuideRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $sizeGuide = SizeGuideType::create([
            'name'               => $data['name'],
            'slug'               => $request->input('slug'),
            'description'        => $data['description'] ?? null,
            'measurement_fields' => $data['measurement_fields'],
        ]);

        $this->syncEntries($sizeGuide, $data['entries']);

        return redirect()->route('admin.size-guides.index')
            ->with('success', 'Panduan ukuran "'.$sizeGuide->name.'" berhasil ditambahkan.');
    }

    public function edit(SizeGuideType $sizeGuide): View
    {
        $sizeGuide->load(['entries' => fn ($q) => $q->orderBy('sort_order')]);

        return view('admin.size-guides.edit', compact('sizeGuide'));
    }

    public function update(SizeGuideRequest $request, SizeGuideType $sizeGuide): RedirectResponse
    {
        $data = $request->validated();

        $sizeGuide->update([
            'name'               => $data['name'],
            'slug'               => $request->input('slug') ?: $sizeGuide->slug,
            'description'        => $data['description'] ?? null,
            'measurement_fields' => $data['measurement_fields'],
        ]);

        $sizeGuide->entries()->delete();
        $this->syncEntries($sizeGuide, $data['entries']);

        return redirect()->route('admin.size-guides.index')
            ->with('success', 'Panduan ukuran "'.$sizeGuide->name.'" berhasil diperbarui.');
    }

    public function destroy(SizeGuideType $sizeGuide): RedirectResponse
    {
        $name = $sizeGuide->name;
        $sizeGuide->delete(); // entries cascade via DB

        return redirect()->route('admin.size-guides.index')
            ->with('success', 'Panduan ukuran "'.$name.'" berhasil dihapus.');
    }

    private function syncEntries(SizeGuideType $sizeGuide, array $entries): void
    {
        $rows = [];
        foreach ($entries as $i => $entry) {
            $rows[] = [
                'size_guide_type_id' => $sizeGuide->id,
                'label'              => $entry['label'],
                'age_range'          => $entry['age_range'] ?? null,
                'weight_kg'          => $entry['weight_kg'] ?? null,
                'height_cm'          => $entry['height_cm'] ?? null,
                'chest_cm'           => $entry['chest_cm'] ?? null,
                'waist_cm'           => $entry['waist_cm'] ?? null,
                'hip_cm'             => $entry['hip_cm'] ?? null,
                'thigh_cm'           => $entry['thigh_cm'] ?? null,
                'length_cm'          => $entry['length_cm'] ?? null,
                'sleeve_cm'          => $entry['sleeve_cm'] ?? null,
                'sort_order'         => (int) ($entry['sort_order'] ?? $i),
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        SizeGuideEntry::insert($rows);
    }
}
