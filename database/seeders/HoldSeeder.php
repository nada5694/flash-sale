<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hold;

class HoldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Hold::create([
            'product_id' => 55820,
            'qty' => 2,
            'used' => false,
            'expires_at' => now()->addMinutes(2),
        ]);
    }
}
