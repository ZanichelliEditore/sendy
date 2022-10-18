<?php

namespace Database\Factories;

use App\Models\FailedJob;
use Illuminate\Support\Str;
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
            'connection' => Str::random(),
            'queue' => Str::random(),
            'payload' => Str::random(),
            'exception' => Str::random(),
            'failed_at' => $this->faker->unixTime()
        ];
    }
}
