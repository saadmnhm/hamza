<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The demo seeder creates the admin account (test@example.com / password),
     * the company configuration, the article catalogue, a team of users and a
     * large set of realistic clients, demandes, documents and HR records.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(DemoDataSeeder::class);
    }
}
