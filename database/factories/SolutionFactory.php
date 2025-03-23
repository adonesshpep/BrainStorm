<?php

namespace Database\Factories;

use App\Models\Puzzle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Solution>
 */
class SolutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'puzzle_id'=>Puzzle::factory(),
            'value'=>'iknowwhatiamdoing',
            'iscorrect'=>false
        ];
    }
}
