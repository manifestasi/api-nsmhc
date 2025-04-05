<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserAppOpen;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();
        $user = User::first();

        $years = [2025, 2026];
        $months = range(1, 12); // 1 = Januari, 12 = Desember

        foreach ($years as $year) {
            foreach ($months as $month) {
                $randomCount = $faker->numberBetween(10, 30); // Jumlah data acak per bulan

                for ($i = 0; $i < $randomCount; $i++) {
                    $day = $faker->numberBetween(1, Carbon::create($year, $month, 1)->daysInMonth);
                    $randomDate = Carbon::create($year, $month, $day)->setTime(
                        $faker->numberBetween(0, 23),
                        $faker->numberBetween(0, 59),
                        $faker->numberBetween(0, 59)
                    );

                    UserAppOpen::create([
                        'users_id' => $user->id,
                        'created_at' => $randomDate,
                    ]);
                }
            }
        }
    }
}
