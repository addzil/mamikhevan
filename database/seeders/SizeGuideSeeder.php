<?php

namespace Database\Seeders;

use App\Models\SizeGuideEntry;
use App\Models\SizeGuideType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeGuideSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------------
        // 1. Atasan / Kaos
        // ---------------------------------------------------------------
        $atasan = SizeGuideType::updateOrCreate(
            ['slug' => 'atasan-kaos'],
            [
                'name'               => 'Atasan/Kaos',
                'description'        => 'Panduan ukuran untuk baju dan kaos anak.',
                'measurement_fields' => ['chest_cm', 'length_cm', 'sleeve_cm'],
            ]
        );

        $atasan->entries()->delete();

        SizeGuideEntry::insert(array_map(fn ($e) => array_merge($e, [
            'size_guide_type_id' => $atasan->id,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]), [
            ['label' => '0-3M',   'age_range' => '0-3 bulan',   'weight_kg' => '3-6 kg',   'height_cm' => '50-60 cm',   'chest_cm' => '44', 'length_cm' => '33', 'sleeve_cm' => '17', 'sort_order' => 1],
            ['label' => '3-6M',   'age_range' => '3-6 bulan',   'weight_kg' => '6-8 kg',   'height_cm' => '60-67 cm',   'chest_cm' => '46', 'length_cm' => '36', 'sleeve_cm' => '18', 'sort_order' => 2],
            ['label' => '6-12M',  'age_range' => '6-12 bulan',  'weight_kg' => '8-10 kg',  'height_cm' => '67-76 cm',   'chest_cm' => '48', 'length_cm' => '38', 'sleeve_cm' => '20', 'sort_order' => 3],
            ['label' => '1T',     'age_range' => '1 tahun',      'weight_kg' => '10-12 kg', 'height_cm' => '76-84 cm',   'chest_cm' => '50', 'length_cm' => '40', 'sleeve_cm' => '22', 'sort_order' => 4],
            ['label' => '2T',     'age_range' => '2 tahun',      'weight_kg' => '12-14 kg', 'height_cm' => '84-92 cm',   'chest_cm' => '52', 'length_cm' => '42', 'sleeve_cm' => '24', 'sort_order' => 5],
            ['label' => '3T',     'age_range' => '3 tahun',      'weight_kg' => '14-16 kg', 'height_cm' => '92-100 cm',  'chest_cm' => '54', 'length_cm' => '44', 'sleeve_cm' => '26', 'sort_order' => 6],
            ['label' => '4T',     'age_range' => '4 tahun',      'weight_kg' => '16-18 kg', 'height_cm' => '100-108 cm', 'chest_cm' => '56', 'length_cm' => '46', 'sleeve_cm' => '28', 'sort_order' => 7],
            ['label' => '5T',     'age_range' => '5 tahun',      'weight_kg' => '18-20 kg', 'height_cm' => '108-116 cm', 'chest_cm' => '58', 'length_cm' => '48', 'sleeve_cm' => '30', 'sort_order' => 8],
            ['label' => '6T',     'age_range' => '6 tahun',      'weight_kg' => '20-22 kg', 'height_cm' => '116-122 cm', 'chest_cm' => '60', 'length_cm' => '50', 'sleeve_cm' => '32', 'sort_order' => 9],
            ['label' => '7-8Y',   'age_range' => '7-8 tahun',   'weight_kg' => '22-26 kg', 'height_cm' => '122-132 cm', 'chest_cm' => '63', 'length_cm' => '53', 'sleeve_cm' => '35', 'sort_order' => 10],
            ['label' => '9-10Y',  'age_range' => '9-10 tahun',  'weight_kg' => '26-32 kg', 'height_cm' => '132-142 cm', 'chest_cm' => '66', 'length_cm' => '56', 'sleeve_cm' => '38', 'sort_order' => 11],
            ['label' => '11-12Y', 'age_range' => '11-12 tahun', 'weight_kg' => '32-40 kg', 'height_cm' => '142-152 cm', 'chest_cm' => '70', 'length_cm' => '60', 'sleeve_cm' => '42', 'sort_order' => 12],
        ]));

        // ---------------------------------------------------------------
        // 2. Celana / Bawahan
        // ---------------------------------------------------------------
        $celana = SizeGuideType::updateOrCreate(
            ['slug' => 'celana-bawahan'],
            [
                'name'               => 'Celana/Bawahan',
                'description'        => 'Panduan ukuran untuk celana dan bawahan anak.',
                'measurement_fields' => ['waist_cm', 'hip_cm', 'thigh_cm', 'length_cm'],
            ]
        );

        $celana->entries()->delete();

        SizeGuideEntry::insert(array_map(fn ($e) => array_merge($e, [
            'size_guide_type_id' => $celana->id,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]), [
            ['label' => '0-3M',   'age_range' => '0-3 bulan',   'weight_kg' => '3-6 kg',   'height_cm' => '50-60 cm',   'waist_cm' => '44', 'hip_cm' => '46', 'thigh_cm' => '23', 'length_cm' => '28', 'sort_order' => 1],
            ['label' => '3-6M',   'age_range' => '3-6 bulan',   'weight_kg' => '6-8 kg',   'height_cm' => '60-67 cm',   'waist_cm' => '45', 'hip_cm' => '48', 'thigh_cm' => '24', 'length_cm' => '31', 'sort_order' => 2],
            ['label' => '6-12M',  'age_range' => '6-12 bulan',  'weight_kg' => '8-10 kg',  'height_cm' => '67-76 cm',   'waist_cm' => '46', 'hip_cm' => '50', 'thigh_cm' => '25', 'length_cm' => '34', 'sort_order' => 3],
            ['label' => '1T',     'age_range' => '1 tahun',      'weight_kg' => '10-12 kg', 'height_cm' => '76-84 cm',   'waist_cm' => '47', 'hip_cm' => '52', 'thigh_cm' => '26', 'length_cm' => '37', 'sort_order' => 4],
            ['label' => '2T',     'age_range' => '2 tahun',      'weight_kg' => '12-14 kg', 'height_cm' => '84-92 cm',   'waist_cm' => '49', 'hip_cm' => '54', 'thigh_cm' => '27', 'length_cm' => '40', 'sort_order' => 5],
            ['label' => '3T',     'age_range' => '3 tahun',      'weight_kg' => '14-16 kg', 'height_cm' => '92-100 cm',  'waist_cm' => '51', 'hip_cm' => '57', 'thigh_cm' => '28', 'length_cm' => '43', 'sort_order' => 6],
            ['label' => '4T',     'age_range' => '4 tahun',      'weight_kg' => '16-18 kg', 'height_cm' => '100-108 cm', 'waist_cm' => '53', 'hip_cm' => '60', 'thigh_cm' => '30', 'length_cm' => '47', 'sort_order' => 7],
            ['label' => '5T',     'age_range' => '5 tahun',      'weight_kg' => '18-20 kg', 'height_cm' => '108-116 cm', 'waist_cm' => '55', 'hip_cm' => '63', 'thigh_cm' => '31', 'length_cm' => '51', 'sort_order' => 8],
            ['label' => '6T',     'age_range' => '6 tahun',      'weight_kg' => '20-22 kg', 'height_cm' => '116-122 cm', 'waist_cm' => '57', 'hip_cm' => '66', 'thigh_cm' => '33', 'length_cm' => '55', 'sort_order' => 9],
            ['label' => '7-8Y',   'age_range' => '7-8 tahun',   'weight_kg' => '22-26 kg', 'height_cm' => '122-132 cm', 'waist_cm' => '59', 'hip_cm' => '69', 'thigh_cm' => '35', 'length_cm' => '59', 'sort_order' => 10],
            ['label' => '9-10Y',  'age_range' => '9-10 tahun',  'weight_kg' => '26-32 kg', 'height_cm' => '132-142 cm', 'waist_cm' => '62', 'hip_cm' => '73', 'thigh_cm' => '37', 'length_cm' => '63', 'sort_order' => 11],
            ['label' => '11-12Y', 'age_range' => '11-12 tahun', 'weight_kg' => '32-40 kg', 'height_cm' => '142-152 cm', 'waist_cm' => '65', 'hip_cm' => '77', 'thigh_cm' => '40', 'length_cm' => '68', 'sort_order' => 12],
        ]));

        // ---------------------------------------------------------------
        // 3. Dress / Rok
        // ---------------------------------------------------------------
        $dress = SizeGuideType::updateOrCreate(
            ['slug' => 'dress-rok'],
            [
                'name'               => 'Dress/Rok',
                'description'        => 'Panduan ukuran untuk dress dan rok anak.',
                'measurement_fields' => ['chest_cm', 'waist_cm', 'hip_cm', 'length_cm'],
            ]
        );

        $dress->entries()->delete();

        SizeGuideEntry::insert(array_map(fn ($e) => array_merge($e, [
            'size_guide_type_id' => $dress->id,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]), [
            ['label' => '0-3M',   'age_range' => '0-3 bulan',   'weight_kg' => '3-6 kg',   'height_cm' => '50-60 cm',   'chest_cm' => '44', 'waist_cm' => '44', 'hip_cm' => '46', 'length_cm' => '38', 'sort_order' => 1],
            ['label' => '3-6M',   'age_range' => '3-6 bulan',   'weight_kg' => '6-8 kg',   'height_cm' => '60-67 cm',   'chest_cm' => '46', 'waist_cm' => '45', 'hip_cm' => '48', 'length_cm' => '42', 'sort_order' => 2],
            ['label' => '6-12M',  'age_range' => '6-12 bulan',  'weight_kg' => '8-10 kg',  'height_cm' => '67-76 cm',   'chest_cm' => '48', 'waist_cm' => '46', 'hip_cm' => '50', 'length_cm' => '46', 'sort_order' => 3],
            ['label' => '1T',     'age_range' => '1 tahun',      'weight_kg' => '10-12 kg', 'height_cm' => '76-84 cm',   'chest_cm' => '50', 'waist_cm' => '47', 'hip_cm' => '52', 'length_cm' => '50', 'sort_order' => 4],
            ['label' => '2T',     'age_range' => '2 tahun',      'weight_kg' => '12-14 kg', 'height_cm' => '84-92 cm',   'chest_cm' => '52', 'waist_cm' => '49', 'hip_cm' => '54', 'length_cm' => '54', 'sort_order' => 5],
            ['label' => '3T',     'age_range' => '3 tahun',      'weight_kg' => '14-16 kg', 'height_cm' => '92-100 cm',  'chest_cm' => '54', 'waist_cm' => '51', 'hip_cm' => '57', 'length_cm' => '58', 'sort_order' => 6],
            ['label' => '4T',     'age_range' => '4 tahun',      'weight_kg' => '16-18 kg', 'height_cm' => '100-108 cm', 'chest_cm' => '56', 'waist_cm' => '53', 'hip_cm' => '60', 'length_cm' => '63', 'sort_order' => 7],
            ['label' => '5T',     'age_range' => '5 tahun',      'weight_kg' => '18-20 kg', 'height_cm' => '108-116 cm', 'chest_cm' => '58', 'waist_cm' => '55', 'hip_cm' => '63', 'length_cm' => '68', 'sort_order' => 8],
            ['label' => '6T',     'age_range' => '6 tahun',      'weight_kg' => '20-22 kg', 'height_cm' => '116-122 cm', 'chest_cm' => '60', 'waist_cm' => '57', 'hip_cm' => '66', 'length_cm' => '73', 'sort_order' => 9],
            ['label' => '7-8Y',   'age_range' => '7-8 tahun',   'weight_kg' => '22-26 kg', 'height_cm' => '122-132 cm', 'chest_cm' => '63', 'waist_cm' => '59', 'hip_cm' => '69', 'length_cm' => '79', 'sort_order' => 10],
            ['label' => '9-10Y',  'age_range' => '9-10 tahun',  'weight_kg' => '26-32 kg', 'height_cm' => '132-142 cm', 'chest_cm' => '66', 'waist_cm' => '62', 'hip_cm' => '73', 'length_cm' => '85', 'sort_order' => 11],
            ['label' => '11-12Y', 'age_range' => '11-12 tahun', 'weight_kg' => '32-40 kg', 'height_cm' => '142-152 cm', 'chest_cm' => '70', 'waist_cm' => '65', 'hip_cm' => '77', 'length_cm' => '92', 'sort_order' => 12],
        ]));

        // ---------------------------------------------------------------
        // 4. Jaket / Outer
        // ---------------------------------------------------------------
        $jaket = SizeGuideType::updateOrCreate(
            ['slug' => 'jaket-outer'],
            [
                'name'               => 'Jaket/Outer',
                'description'        => 'Panduan ukuran untuk jaket dan pakaian luar anak.',
                'measurement_fields' => ['chest_cm', 'length_cm', 'sleeve_cm'],
            ]
        );

        $jaket->entries()->delete();

        SizeGuideEntry::insert(array_map(fn ($e) => array_merge($e, [
            'size_guide_type_id' => $jaket->id,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]), [
            ['label' => '0-3M',   'age_range' => '0-3 bulan',   'weight_kg' => '3-6 kg',   'height_cm' => '50-60 cm',   'chest_cm' => '46', 'length_cm' => '35', 'sleeve_cm' => '17', 'sort_order' => 1],
            ['label' => '3-6M',   'age_range' => '3-6 bulan',   'weight_kg' => '6-8 kg',   'height_cm' => '60-67 cm',   'chest_cm' => '48', 'length_cm' => '38', 'sleeve_cm' => '18', 'sort_order' => 2],
            ['label' => '6-12M',  'age_range' => '6-12 bulan',  'weight_kg' => '8-10 kg',  'height_cm' => '67-76 cm',   'chest_cm' => '50', 'length_cm' => '40', 'sleeve_cm' => '20', 'sort_order' => 3],
            ['label' => '1T',     'age_range' => '1 tahun',      'weight_kg' => '10-12 kg', 'height_cm' => '76-84 cm',   'chest_cm' => '52', 'length_cm' => '42', 'sleeve_cm' => '22', 'sort_order' => 4],
            ['label' => '2T',     'age_range' => '2 tahun',      'weight_kg' => '12-14 kg', 'height_cm' => '84-92 cm',   'chest_cm' => '54', 'length_cm' => '44', 'sleeve_cm' => '24', 'sort_order' => 5],
            ['label' => '3T',     'age_range' => '3 tahun',      'weight_kg' => '14-16 kg', 'height_cm' => '92-100 cm',  'chest_cm' => '56', 'length_cm' => '46', 'sleeve_cm' => '26', 'sort_order' => 6],
            ['label' => '4T',     'age_range' => '4 tahun',      'weight_kg' => '16-18 kg', 'height_cm' => '100-108 cm', 'chest_cm' => '58', 'length_cm' => '48', 'sleeve_cm' => '28', 'sort_order' => 7],
            ['label' => '5T',     'age_range' => '5 tahun',      'weight_kg' => '18-20 kg', 'height_cm' => '108-116 cm', 'chest_cm' => '60', 'length_cm' => '50', 'sleeve_cm' => '30', 'sort_order' => 8],
            ['label' => '6T',     'age_range' => '6 tahun',      'weight_kg' => '20-22 kg', 'height_cm' => '116-122 cm', 'chest_cm' => '62', 'length_cm' => '52', 'sleeve_cm' => '32', 'sort_order' => 9],
            ['label' => '7-8Y',   'age_range' => '7-8 tahun',   'weight_kg' => '22-26 kg', 'height_cm' => '122-132 cm', 'chest_cm' => '65', 'length_cm' => '55', 'sleeve_cm' => '35', 'sort_order' => 10],
            ['label' => '9-10Y',  'age_range' => '9-10 tahun',  'weight_kg' => '26-32 kg', 'height_cm' => '132-142 cm', 'chest_cm' => '68', 'length_cm' => '58', 'sleeve_cm' => '38', 'sort_order' => 11],
            ['label' => '11-12Y', 'age_range' => '11-12 tahun', 'weight_kg' => '32-40 kg', 'height_cm' => '142-152 cm', 'chest_cm' => '72', 'length_cm' => '62', 'sleeve_cm' => '42', 'sort_order' => 12],
        ]));
    }
}
