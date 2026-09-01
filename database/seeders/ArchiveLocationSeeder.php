<?php

namespace Database\Seeders;

use App\Models\ArchiveLocation;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class ArchiveLocationSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('code', 'MEX')->orWhere('name', 'like', '%DELEGACION%')->first();

        if (!$branch) {
            $branch = Branch::create([
                'name' => 'RH DELEGACION ESTATAL',
                'code' => 'MEX',
                'is_active' => true,
            ]);
        }

        $locations = [
            // --- GAVETAS / ARCHIVEROS ---
            [
                'branch_id' => $branch->id,
                'location_type' => 'Archivero',
                'archive_name' => 'ARCHIVO ACTIVO',
                'cabinet' => 'G-01',
                'drawer' => '1',
                'alpha_range' => 'A - C',
                'notes' => 'Archivero metálico de 4 gavetas',
                'is_active' => true,
            ],
            [
                'branch_id' => $branch->id,
                'location_type' => 'Archivero',
                'archive_name' => 'ARCHIVO ACTIVO',
                'cabinet' => 'G-01',
                'drawer' => '2',
                'alpha_range' => 'D - G',
                'notes' => 'Archivero metálico de 4 gavetas',
                'is_active' => true,
            ],
            [
                'branch_id' => $branch->id,
                'location_type' => 'Archivero',
                'archive_name' => 'ARCHIVO ACTIVO',
                'cabinet' => 'G-01',
                'drawer' => '3',
                'alpha_range' => 'H - L',
                'notes' => 'Archivero metálico de 4 gavetas',
                'is_active' => true,
            ],
            [
                'branch_id' => $branch->id,
                'location_type' => 'Archivero',
                'archive_name' => 'ARCHIVO ACTIVO',
                'cabinet' => 'G-01',
                'drawer' => '4',
                'alpha_range' => 'M - P',
                'notes' => 'Archivero metálico de 4 gavetas',
                'is_active' => true,
            ],
            [
                'branch_id' => $branch->id,
                'location_type' => 'Archivero',
                'archive_name' => 'ARCHIVO ACTIVO',
                'cabinet' => 'G-02',
                'drawer' => '1',
                'alpha_range' => 'Q - S',
                'notes' => 'Archivero metálico de 4 gavetas',
                'is_active' => true,
            ],
            [
                'branch_id' => $branch->id,
                'location_type' => 'Archivero',
                'archive_name' => 'ARCHIVO ACTIVO',
                'cabinet' => 'G-02',
                'drawer' => '2',
                'alpha_range' => 'T - V',
                'notes' => 'Archivero metálico de 4 gavetas',
                'is_active' => true,
            ],
            [
                'branch_id' => $branch->id,
                'location_type' => 'Archivero',
                'archive_name' => 'ARCHIVO ACTIVO',
                'cabinet' => 'G-02',
                'drawer' => '3',
                'alpha_range' => 'W - Z',
                'notes' => 'Archivero metálico de 4 gavetas',
                'is_active' => true,
            ],
            [
                'branch_id' => $branch->id,
                'location_type' => 'Archivero',
                'archive_name' => 'ARCHIVO ACTIVO',
                'cabinet' => 'G-02',
                'drawer' => '4',
                'alpha_range' => 'DIRECTIVOS',
                'notes' => 'Personal directivo y de confianza',
                'is_active' => true,
            ],
        ];

        foreach ($locations as $loc) {
            ArchiveLocation::updateOrCreate(
                [
                    'branch_id' => $loc['branch_id'],
                    'archive_name' => $loc['archive_name'],
                    'cabinet' => $loc['cabinet'],
                    'drawer' => $loc['drawer'],
                ],
                $loc
            );
        }
    }
}

