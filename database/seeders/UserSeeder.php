<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Admin User
        User::factory()->create([
            'name' => 'Max Mustermann',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'arbeitszeit_admin' => 1,
            'arbeitszeit_teamleader' => 0,
        ]);

        // Teamleiter
        User::factory()->create([
            'name' => 'Anna Schmidt',
            'email' => 'teamleiter@example.com',
            'password' => Hash::make('password'),
            'arbeitszeit_admin' => 0,
            'arbeitszeit_teamleader' => 1,
        ]);

        // Weitere Mitarbeiter
        User::factory(10)->create([
            'arbeitszeit_admin' => 0,
            'arbeitszeit_teamleader' => 0,
        ]);
    }
}
