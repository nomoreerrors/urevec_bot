<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatAdmins;
use App\Models\Admin;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Don't forget to use --env=testing flag to seed and migrate
     */
    public function run(): void
    {
        // Make relationships between admins and chats in a relationship table
        // each admin related to only one chat
        $admins = Admin::factory(20)->has(
            Chat::factory(1)
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
    }
}
