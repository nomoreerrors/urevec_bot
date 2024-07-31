<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Illuminate\Database\Eloquent\Model>
>
 */
class ChatFactory extends Factory
{
    private static int $increment = 111111111;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // $adminId = fake()->numberBetween(1001, 1015);
        // $secondaryAdminId = fake()->numberBetween(1001, 1015);
        // $thirdAdminId = fake()->numberBetween(1001, 1015);

        // $adminsIds = [$adminId, $secondaryAdminId, $thirdAdminId];

        return [
            'chat_id' => fake()->numberBetween(100001, 500000),
            'chat_title' => fake()->sentence(),
            // 'chat_admins' => $adminsIds,
            // "private_commands_access" => $adminsIds,
            // 'group_commands_access' => "admins",
            // 'my_commands_set' => 1
        ];
    }
}
