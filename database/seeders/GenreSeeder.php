<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get("database/data/genres.json");
        $years = json_decode($json);

        foreach ($years as $key => $value) {
            Genre::create([
                "id" => $value->id,
                "name" => $value->name,
            ]);
        }
    }
}
