<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Domain\Registration\Models\Student;

class StudentFactory extends Factory
{
    protected $model = Student::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name'=>$this->faker->firstName(),
            'middle_name'=>$this->faker->firstName(),
            'surname'=>$this->faker->lastName(),
            'gender'=>'M',
            'campus_program_id'=>$this->faker->numberBetween(1,2),
            'insurance_id'=>$this->faker->numberBetween(1,2),
            'year_of_study'=>$this->faker->numberBetween(1,3),
            'applicant_id'=>$this->faker->numberBetween(1,100),
            'studentship_status_id'=>$this->faker->numberBetween(1,5),
            'academic_status_id'=>$this->faker->numberBetween(1,6),
            'registration_number'=>$this->faker->unique()->numberBetween(1000000,9999999),
            'registration_year'=>$this->faker->numberBetween(2012,2021),
            'email'=>$this->faker->email(),
            'phone'=>$this->faker->phoneNumber(),
            'nationality'=>$this->faker->country(),
            'birth_date'=>$this->faker->date($format = 'Y-m-d', $max = 'now'),
            'disability_status_id'=>$this->faker->numberBetween(1,3),
            'user_id'=>$this->faker->numberBetween(1,100)
        ];
    }
}
