<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserChild;
use App\Models\UserHusband;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'email' => 'mizzy12342@gmail.com',
            'name' => 'mizzy',
            'password' => Hash::make('1234567890'),
            'email_verified_at' => now()
        ]);

        UserProfile::create([
            'users_id' => $user->id,
            'age' => 15,
            'no_hp' => '088884884848',
            'last_education' => 'Test123124124',
            'last_job' => 'asdfasdfsdafsdf',
            'address' => 'adsfasdfsdafasdfsdaf'
        ]);

        UserHusband::create([
            'users_id' => $user->id,
            'name' => 'download',
            'age' => 40,
            'last_education' => 'asdfasdf',
            'last_job' => 'asdfsadfsdf'
        ]);

        UserChild::create([
            'name' => 'test1',
            'users_id' => $user->id,
            'age' => 20,
            'last_education' => 'asdfsadfsdaf'
        ]);

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
