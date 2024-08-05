<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatAdmins;
use App\Models\NewUserRestriction;
use App\Models\Admin;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SimpleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Don't forget to use --env=testing flag to seed and migrate
     */
    public function run(int $adminsCount = 1, int $chatsCount = 5): void
    {
        // Make relationships between admins and chats in a relationship table
        // each admin related to only one chat
        // don't forget to use --env=testing flag to seed and migrate
        // php artisan db::seed --env=testing --class=SimpleSeeder
        Admin::factory($adminsCount)->has(
            Chat::factory($chatsCount)
                ->has(
                    NewUserRestriction::factory(1)
                )
        )->create();
    }
}
