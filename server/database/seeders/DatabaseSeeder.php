<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        DB::statement('DELETE FROM reviews');
        DB::statement('DELETE FROM tasks');
        DB::statement('DELETE FROM movies');
        DB::statement('DELETE FROM people');
        DB::statement('DELETE FROM roles');
        DB::statement('DELETE FROM users');



        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            PersonSeeder::class,
            MovieSeeder::class,
            TaskSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
