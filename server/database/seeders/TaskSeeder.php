<?php

namespace Database\Seeders;

use App\Helpers\CsvReader;
use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fileName = 'csv/tasks.csv';
        $delimeter = ';';
        $data = CsvReader::csvToArray($fileName,$delimeter);
        Task::factory()->createMany($data);
    }
}
