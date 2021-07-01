<?php

namespace Database\Factories;

use App\Models\FailedJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class FailedJobFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FailedJob::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'connection' => $this->faker->text(),
            'queue' => $this->faker->text(),
            'payload' => $this->faker->text(),
            'exception' => $this->faker->text(),
            'failed_at' => $this->faker->unixTime()
        ];
    }
}
