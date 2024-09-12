<?php

namespace Database\Factories;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LinksFilter>
 */
class LinksFilterFactory extends Factory
{
    protected static $increment = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $this->chatsCount = Chat::count();


        $firstElementId = Chat::first()->id;
        $id = $firstElementId + self::$increment;
        self::$increment++;

        // The chat_id column is not nessessary to make relashionships, but it's
        // more convenient and human readable when looking at the database,
        // so I made that the fake chat_id is the same as the id of the chats table's chat_id
        // $lol = Chat::find($id)->get("chat_id");
        // $chatId = Chat::where("id", $id)->value("chat_id");
        $chatId = Chat::where("id", $id)->value("id");

        return [
            'chat_id' => $chatId,
            'enabled' => $this->faker->boolean(),
            'delete_user' => $this->faker->boolean(),
            'restrict_user' => $this->faker->boolean(),
            'delete_message' => $this->faker->boolean(),
            'can_send_messages' => $this->faker->boolean(),
            'can_send_media' => $this->faker->boolean(),
            'restriction_time' => $this->faker->numberBetween(0, 4),
        ];
    }
}
