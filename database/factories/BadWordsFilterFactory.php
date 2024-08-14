<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BadWordsFilter>
 */
class BadWordsFilterFactory extends Factory
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
            'bad_words' => $this->faker->words(10, true),
            'bad_phrases' => $this->faker->sentence(10, true),
            'critical_words' => $this->faker->words(10, true),
            'critical_phrases' => $this->faker->sentence(10, true),
        ];
    }
}
