<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Domain\Application\Models\Applicant;

class ApplicantFactory extends Factory
{
    protected $model = Applicant::class;
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
            'address'=>$this->faker->address(),
            'intake_id'=>$this->faker->numberBetween(1,1),
            'admission_year'=>$this->faker->numberBetween(2012,2021),
            'index_number'=>$this->faker->unique()->numberBetween(100000,999999),
            'application_number'=>$this->faker->unique()->numberBetween(100000,999999),
            'entry_mode'=>'DIRECT',
            'next_of_kin_id'=>$this->faker->numberBetween(1,50),
            'country_id'=>$this->faker->numberBetween(1,10),
            'region_id'=>$this->faker->numberBetween(1,10),
            'district_id'=>$this->faker->numberBetween(1,10),
            'ward_id'=>$this->faker->numberBetween(1,10),
            'street'=>$this->faker->address(),
            'email'=>$this->faker->email(),
            'phone'=>$this->faker->phoneNumber(),
            'nationality'=>$this->faker->country(),
            'birth_date'=>$this->faker->date($format = 'Y-m-d', $max = 'now'),
            'disability_status_id'=>$this->faker->numberBetween(1,3),
            'user_id'=>$this->faker->numberBetween(1,100)
        ];
    }
}
