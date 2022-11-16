<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Stat;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Player::factory()->count(1500)->create();
        Stat::factory()->count(10000)->create();
    }
}
