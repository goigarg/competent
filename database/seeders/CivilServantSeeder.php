<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CivilServantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primary Austrian Civil Servant Officer
        User::firstOrCreate(
            ['email' => 'beamter@bka.gv.at'],
            [
                'name' => 'Mag. Stefan Gruber (BKA Bürgerservice)',
                'password' => Hash::make('Bundeskanzleramt2030!'),
                'email_verified_at' => now(),
            ]
        );

        // Simple fallback officer account for quick testing
        User::firstOrCreate(
            ['email' => 'officer@bka.gv.at'],
            [
                'name' => 'Amtsrätin Maria Huber',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
