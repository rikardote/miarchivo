<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Recursos Humanos', 'code' => 'RH'],
            ['name' => 'Jurídico', 'code' => 'JUR'],
            ['name' => 'Finanzas', 'code' => 'FIN'],
            ['name' => 'Compras', 'code' => 'COM'],
            ['name' => 'Dirección', 'code' => 'DIR'],
            ['name' => 'Médica', 'code' => 'MED'],
            ['name' => 'Administrativa', 'code' => 'ADM'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(['code' => $department['code']], $department);
        }
    }
}
