<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    private $types = ['goals','shots','penalties'];
    public function definition()
    {
        return [
            'player_id' => random_int(1,1500),
            'name'=>$this->types[$this->faker->numberBetween(0,2)],
            'value'=>$this->faker->numberBetween(-15,1500)
        ];
    }
}
