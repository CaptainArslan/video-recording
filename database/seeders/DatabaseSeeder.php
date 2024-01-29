<?php

namespace Database\Seeders;

use App\Models\Setting;
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
        $this->call([
            PlanSeeder::class,
            UserSeeder::class,
            RecordingSeeder::class,
        ]);
        Setting::insert([
            [
                'user_id' => 1,
                'key' => 'crm_client_id',
                'value' => '65b39018a18c4da3c9e0e0aa-lruj654l',
            ],
            [
                'user_id' => 1,
                'key' => 'crm_client_secret',
                'value' => '38c599e8-2b63-430f-95fd-e767ac9767a9',
            ],
            [
                'user_id' => 1,
                'key' => 'company_logo',
                'value' => 'uploads/logos\company_logo_1706270261.jpg',
            ],
        ]);
    }
}
