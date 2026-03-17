<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movies = Movie::all();
        if ($movies->isEmpty()) {
            return;
        }

        $existingUsers = User::count();
        $targetUsers = 12;
        if ($existingUsers < $targetUsers) {
            User::factory()
                ->count($targetUsers - $existingUsers)
                ->create([
                    'role' => 2,
                    'password' => '123',
                ]);
        }

        $users = User::where('role', 2)->get();
        if ($users->isEmpty()) {
            return;
        }

        $opinions = [
            'Jó volt, tetszett, a hangulat végig működött.',
            'A színészek nagyon jók voltak, különösen a főszereplő.',
            'A történet érdekes, bár a közepe kicsit hosszúra nyúlt.',
            'A zene hangulatos, sokat dobott a jeleneteken.',
            'Nekem közepes, de egyszer mindenképp megéri megnézni.',
            'Nagyon jó film, ajánlom mindenkinek, aki szereti ezt a műfajt.',
            'Nem volt rossz, de semmi extra, pár rész kiszámítható.',
            'A rendezés kiváló, a tempó végig feszes maradt.',
            'Tetszett a vége, jó fordulat volt, nem erre számítottam.',
            'Egyszer nézd meg, megéri, de nem feltétlen újranézős.',
            'A képi világ erős, a díszletek és jelmezek is rendben voltak.',
            'A párbeszédek néhol sántítanak, de összességében élvezhető.',
        ];

        foreach ($movies as $movie) {
            $count = rand(1, 4);
            for ($i = 0; $i < $count; $i++) {
                $user = $users->random();
                Review::create([
                    'score' => rand(1, 10),
                    'opinion' => $opinions[array_rand($opinions)],
                    'movieid' => $movie->id,
                    'userid' => $user->id,
                ]);
            }
        }
    }
}
