<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Domain\Registration\Models\Registration;

class RegistrationFactory extends Factory
{
    protected $model = Registration::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'student_id'=>$this->faker->numberBetween(1,100),
            'study_academic_year_id'=>$this->faker->numberBetween(1,2),
            'registration_date'=>now()->format('Y-m-d'),
            'registered_by_staff_id'=>$this->faker->numberBetween(1,100),
            'year_of_study'=>$this->faker->numberBetween(1,3),
            'semester_id'=>$this->faker->numberBetween(1,2),
        ];
    }
}
