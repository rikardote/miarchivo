<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'RH Mexicali', 'code' => 'MEX', 'address' => 'Mexicali, Baja California'],
            ['name' => 'RH Tijuana', 'code' => 'TIJ', 'address' => 'Tijuana, Baja California'],
            ['name' => 'RH Central', 'code' => 'CEN', 'address' => 'Oficinas Centrales'],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['code' => $branch['code']], $branch);
        }
    }
}
