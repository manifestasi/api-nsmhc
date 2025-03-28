<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $content = [
            'Niat',
            'Intropeksi Diri',
            'Penyesalan',
            'Menerima',
            'Doa',
            'Body Scan',
            'Detoksifikasi',
            'Relaksasi',
            'Berserah Diri'
        ];

        foreach ($content as $c) {
            Content::create([
                'name' => $c
            ]);
        }
    }
}
