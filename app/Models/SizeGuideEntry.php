<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizeGuideEntry extends Model
{
    use HasFactory;

    public const MEASUREMENT_LABELS = [
        'chest_cm'  => 'Lingkar Dada (LD)',
        'waist_cm'  => 'Lingkar Pinggang (LP)',
        'hip_cm'    => 'Lingkar Pinggul (LPt)',
        'thigh_cm'  => 'Lingkar Paha',
        'length_cm' => 'Panjang',
        'sleeve_cm' => 'Panjang Lengan',
    ];

    protected $fillable = [
        'size_guide_type_id',
        'label',
        'age_range',
        'weight_kg',
        'height_cm',
        'chest_cm',
        'waist_cm',
        'hip_cm',
        'thigh_cm',
        'length_cm',
        'sleeve_cm',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function sizeGuideType(): BelongsTo
    {
        return $this->belongsTo(SizeGuideType::class);
    }
}
