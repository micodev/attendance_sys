<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Settings;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        User::create([
            'first_name'=> 'admin',
            'last_name'=> '',
            'middle_name'=>'',
            'username'=>'admin',
            'email'=>'admin@admin.com',
            'password'=> bcrypt('admin'),
            'email_verified_at'=> now(),
        ]);
        //2023-05-16 07:55:00
        $in = Carbon::now()
        ->setHour('7')
        ->setMinute('55')
        ->setSecond('0');

        $out = Carbon::now()
        ->setHour('13')
        ->setMinute('55')
        ->setSecond('0');

        Settings::create([
            'key' => 'sys_in',
            'value' => $in,
        ]);

        Settings::create([
            'key' => 'sys_out',
            'value' => $out,
        ]);

        Settings::create([
            'key' => 'off_after_in',
            'value' => true,
        ]);
    }
}
