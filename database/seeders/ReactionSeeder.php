<?php

namespace Database\Seeders;

use App\Models\Reaction;
use Illuminate\Database\Seeder;

class ReactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reaction = [
            'Pusing',
            'Dada Sesak',
            'Nyeri Dada',
            'Pundak Berat / Sakit',
            'Kesemutan',
            'Mual',
            'Tubuh Gemetar',
            'Lainnya'
        ];

        foreach ($reaction as $r) {
            Reaction::create([
                'name' => $r
            ]);
        }
    }
}
