<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $question = [
            "Apakah ibu bisa meningkatkan kemampuan komunikasi secara baik dengan anak?",
            "Apakah Ibu sudah mampu meningkatkan kemampuan mengelola emosi negatif yang Ibu rasakan",
            "Apakah Ibu mampu mendoakan anaknya setiap saat?"
        ];

        $answer = [
            'Sudah',
            'Belum'
        ];

        $faker = Factory::create();
        $user = User::all();

        foreach ($question as $q) {
            foreach ($user as $u) {
                $question = Question::create([
                    'question_text' => $q
                ]);

                Answer::create([
                    'questions_id' => $question->id,
                    'users_id' => $u->id,
                    'answer_text' => $answer[$faker->numberBetween(0, 1)]
                ]);
            }
        }
    }
}
