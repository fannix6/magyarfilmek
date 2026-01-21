<?php

namespace Database\Seeders;

use App\Helpers\CsvReader;
use App\Models\Movie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fileName = 'csv/movies.csv';
        $delimeter = ';';
        $data = CsvReader::csvToArray($fileName, $delimeter);
        Movie::factory()->createMany($data);
    }
}
