<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tambahkan beberapa kategori
        Category::create(['name' => 'Zumba', 'description' => 'Kelas Zumba']);
        Category::create(['name' => 'Karate', 'description' => 'Kelas Karate']);
        Category::create(['name' => 'Yoga', 'description' => 'Kelas Yoga']);
    }
}
