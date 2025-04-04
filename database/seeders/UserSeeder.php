<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Reaction;
use App\Models\User;
use App\Models\UserChild;
use App\Models\UserHusband;
use App\Models\UserProfile;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();

        $user = User::create([
            'email' => 'mizzy12342@gmail.com',
            'name' => 'mizzy',
            'password' => Hash::make('1234567890'),
            'email_verified_at' => now()
        ]);

        User::factory(20)->create();

        $user = User::all();

        $reaction = Reaction::all();
        $content = Content::all();

        $totalReaction = $reaction->count();
        $totalContent = $content->count();

        $totalCompleteContent = 15;

        foreach ($user as $index => $u) {
            UserProfile::create([
                'users_id' => $u->id,
                'age' => 15,
                'no_hp' => '088884884848' . $index,
                'last_education' => 'Test123124124',
                'last_job' => 'asdfasdfsdafsdf',
                'address' => 'adsfasdfsdafasdfsdaf'
            ]);

            UserChild::create([
                'name' => 'test' . $index,
                'users_id' => $u->id,
                'age' => 20,
                'last_education' => 'asdfsadfsdaf'
            ]);

            $randomNumberReaction = $faker->numberBetween(0, $totalReaction - 1);
            foreach ($reaction as $index => $r) {
                if ($randomNumberReaction == $index)
                    $r->users()->attach($u->id);
            }

            $randomNumberContent = $faker->numberBetween(0, $totalContent - 1);

            if ($totalCompleteContent !== 0) {
                foreach ($content as $index => $c) {
                    $c->users()->attach($u->id);
                }
                $totalCompleteContent--;
            } else {
                foreach ($content as $index => $c) {

                    if ($randomNumberContent == $index) {
                        break;
                    }
                    $c->users()->attach($u->id);
                }
            }
        }

        // $child = [
        //     [
        //         'name' => 'test1',
        //         'users_id' => $user->id,
        //         'age' => 20,
        //         'last_education' => 'asdfsadfsdaf'
        //     ],
        //     [
        //         'name' => 'test2',
        //         'users_id' => $user->id,
        //         'age' => 20,
        //         'last_education' => 'asdfsadfsdaf'
        //     ],
        // ];

        // foreach ($child as $c) {
        //     UserChild::create($c);
        // }
    }
}
