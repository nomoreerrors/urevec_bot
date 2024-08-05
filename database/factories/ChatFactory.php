<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Illuminate\Database\Eloquent\Model>
>
 */
class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stop = 0;
        return [
            'chat_id' => fake()->numberBetween(100001, 500000),
            'chat_title' => fake()->sentence(),
        ];
    }
}
