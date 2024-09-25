<?php

namespace Database\Seeders;

use App\Models\BadWordsFilter;
use App\Models\Chat;
use App\Models\LinksFilter;
use App\Models\ChatAdmins;
use App\Models\NewUserRestriction;
use App\Models\Admin;
use App\Models\User;
use App\Models\UnusualCharsFilter;
use Illuminate\Database\Seeder;

class SimpleSeeder extends Seeder
{
    /**
     * Seed admin with multiple chats or a few admins with different chats attached
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
                    NewUserRestriction::factory(1),
                )
                ->has(BadWordsFilter::factory(1))
                ->has(UnusualCharsFilter::factory(1))
                ->has(LinksFilter::factory(1))
        )->create();
    }


    /**
     * Create a few admins and attach them to  the same chat
     * @param int $adminsCount
     * @param int $chatsCount
     * @return void
     */
    public function attachAdmins(int $adminsCount): void
    {
        $chat = Chat::factory(1)
            ->has(NewUserRestriction::factory(1))
            ->has(BadWordsFilter::factory(1))
            ->has(UnusualCharsFilter::factory(1))
            ->has(LinksFilter::factory(1))
            ->create();

        $admins = Admin::factory($adminsCount)->create();

        foreach ($admins as $admin) {
            $admin->chats()->attach($chat->first()->id);
        }
    }
}
