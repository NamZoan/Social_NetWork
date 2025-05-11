<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        for ($i = 0; $i < 500; $i++) {
            DB::table('users')->insert([
                'name' => $faker->name,
                'username' => $faker->userName,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->numerify('0#########'),
                'birthday' => $faker->date('Y-m-d'),
                'email_verified_at' => $faker->optional()->dateTimeThisDecade(),
                'password' => bcrypt('password'), // hoặc Hash::make()
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
