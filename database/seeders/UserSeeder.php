<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        DB::table('users')->insert([
            'name' => 'Michael Labuschke',
            'email' => 'm.labuschke@golfpark-hufeisensee.de',
            'password' => Hash::make('suneebael4Feixaiquoo'),
            'telegram_id' =>  464615461,
            'arbeitszeit_admin' => 1,
            'arbeitszeit_teamleader' => 0,
        ]);
        DB::table('users')->insert([
            'name' => 'Kerstin Dörfer',
            'email' => 'k.doerfer@golfpark-hufeisensee.de',
            'password' => Hash::make('suneebael4Feixaiquoo'),
            'telegram_id' =>  1342350461,
            'arbeitszeit_admin' => 1,
            'arbeitszeit_teamleader' => 0,
        ]);
    }
}
