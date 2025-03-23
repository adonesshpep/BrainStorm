<?php

namespace Database\Factories;

use App\Models\Puzzle;
use App\Models\Solution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Answer>
 */
class AnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'=>User::factory(),
            'puzzle_id'=>Puzzle::factory(),
            'solution_id'=>Solution::factory(),
            'answer'=>'asdfghjk',
            'iscorrect'=>false
        ];
    }
}
