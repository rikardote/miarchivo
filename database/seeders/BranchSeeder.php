<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'RH DELEGACION ESTATAL', 'code' => 'MEX', 'address' => 'Mexicali, Baja California'],
            ['name' => 'RH Almancen', 'code' => 'CEN', 'address' => 'Oficinas Centrales'],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['code' => $branch['code']], $branch);
        }
    }
}
