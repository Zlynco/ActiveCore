<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rooms')->insert([
            ['name' => 'Ruangan A', 'capacity' => 20, 'equipment' => 'Treadmills, Weights'],
            ['name' => 'Ruangan B', 'capacity' => 15, 'equipment' => 'Yoga Mats, Resistance Bands'],
            ['name' => 'Ruangan C', 'capacity' => 25, 'equipment' => 'Bikes, Strength Machines'],
            ['name' => 'Ruangan D', 'capacity' => 10, 'equipment' => 'Dance Floor, Mirrors'],
        ]);
    }
}
