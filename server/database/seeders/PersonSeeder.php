<?php

namespace Database\Seeders;

use App\Helpers\CsvReader;
use App\Models\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fileName = 'csv/people.csv';
        Person::query()->delete();
        $delimeter = ';';
        $data = CsvReader::csvToArray($fileName, $delimeter);
        Person::factory()->createMany($data);
    }
}
