<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Domain\HumanResources\Models\Staff;

class StaffFactory extends Factory
{
    protected $model = Staff::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title'=>$this->faker->title($gender = 'male'|'female'),
            'first_name'=>$this->faker->firstName(),
            'middle_name'=>$this->faker->firstName(),
            'surname'=>$this->faker->lastName(),
            'gender'=>'M',
            'address'=>$this->faker->address(),
            'nin'=>$this->faker->numberBetween(10000000,99999999),
            'pf_number'=>$this->faker->numberBetween(100000,999999),
            'check_number'=>$this->faker->numberBetween(100000,999999),
            'vote_number'=>$this->faker->numberBetween(100000,999999),
            'campus_id'=>$this->faker->numberBetween(1,1),
            'designation_id'=>$this->faker->numberBetween(1,3),
            'email'=>$this->faker->email(),
            'phone'=>$this->faker->phoneNumber(),
            'country_id'=>$this->faker->numberBetween(1,10),
            'region_id'=>$this->faker->numberBetween(1,10),
            'district_id'=>$this->faker->numberBetween(1,10),
            'ward_id'=>$this->faker->numberBetween(1,10),
            'street'=>$this->faker->address(),
            'birth_date'=>$this->faker->date($format = 'Y-m-d', $max = 'now'),
            'disability_status_id'=>$this->faker->numberBetween(1,3),
            'user_id'=>$this->faker->numberBetween(1,100),
            'schedule'=>'FULLTIME',
            'category'=>'ACADEMIC',
            'marital_status'=>'SINGLE',
        ];
    }
}
