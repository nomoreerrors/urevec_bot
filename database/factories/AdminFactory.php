<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
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
            'admin_id' => fake()->numberBetween(100000, 500000),
            'is_bot' => fake()->boolean(),
            'first_name' => fake()->firstName(),
            'username' => fake()->userName(),
            'language_code' => fake()->languageCode(),
            'is_premium' => fake()->boolean()
            // $table->boolean('is_bot')->default(0);
            // $table->string("first_name", 100);
            // $table->string("username", 100)->nullable();
            // $table->string("language_code", 10)->nullable();
            // $table->boolean('is_premium')->nullable();
        ];
    }
}
