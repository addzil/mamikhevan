<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::saving(function ($model) {
            if (blank($model->slug)) {
                $model->slug = static::uniqueSlug(Str::slug($model->name));
            }
        });
    }

    protected static function uniqueSlug(string $base): string
    {
        $slug = $base ?: Str::random(8);
        $original = $slug;
        $counter = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }
}
