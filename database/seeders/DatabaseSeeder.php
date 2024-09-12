<?php

namespace Database\Seeders;

use App\Models\BadWordsFilter;
use App\Models\LinksFilter;
use App\Models\Chat;
use App\Models\ChatAdmins;
use App\Models\Admin;
use App\Models\ChatNewUserRestriction;
use App\Models\NewUserRestriction;
use App\Models\UnusualCharsFilter;
use App\Models\User;
use Database\Factories\ChatFactory;
use Database\Factories\LinksFilterFactory;
use Database\Factories\NewUserRestrictionFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Don't forget to use --env=testing flag to seed and migrate
     * 
     */
    public function run(): void
    {
        // Make relationships between admins and chats in a relationship table
        // each admin related to only one chat
        $admins = Admin::factory(20)->has(
            Chat::factory(1)
                ->has(
                    NewUserRestriction::factory(1),
                )
                ->has(BadWordsFilter::factory(1))
                ->has(UnusualCharsFilter::factory(1))
                ->has(LinksFilter::factory(1))
        )->create();

        $chats = Chat::all();

        // Add at least one more admin to each chat and set some additional columns values to true
        foreach ($admins as $admin) {
            ChatAdmins::create([
                'chat_id' => $chats->random()->id,
                'admin_id' => $admin->id,
                'private_commands_access' => fake()->boolean(),
                'group_commands_access' => fake()->boolean(),
                'my_commands_set' => fake()->boolean(),
            ]);
        }

        // Dont' forget to clear Database before seeding.
    }
}
