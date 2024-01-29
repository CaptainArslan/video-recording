<?php

namespace Database\Seeders;

use App\Models\Recording;
use Illuminate\Database\Seeder;

class RecordingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Recording::factory(100)->create();
    }
}
