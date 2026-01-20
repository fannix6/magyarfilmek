<?php

namespace Database\Seeders;

use App\Helpers\CsvReader;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fileName = 'csv/roles.csv';
        $delimeter = ';';
        $data = CsvReader::csvToArray($fileName, $delimeter);
        Role::factory()->createMany($data);
    }
}
