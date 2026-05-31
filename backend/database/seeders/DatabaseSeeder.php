<?php

namespace Database\Seeders;

use App\Models\FeeType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        FeeType::query()->updateOrCreate(
            ['code' => 'security'],
            ['name' => 'Satpam', 'amount' => 100000, 'is_active' => true]
        );
        FeeType::query()->updateOrCreate(
            ['code' => 'cleaning'],
            ['name' => 'Kebersihan', 'amount' => 15000, 'is_active' => true]
        );
    }
}
