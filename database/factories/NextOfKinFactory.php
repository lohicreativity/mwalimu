<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Domain\Application\Models\NextOfKin;

class NextOfKinFactory extends Factory
{
    protected $model = NextOfKin::class;
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
            'email'=>$this->faker->email(),
            'phone'=>$this->faker->phoneNumber(),
            'nationality'=>$this->faker->country(),
            'country_id'=>$this->faker->numberBetween(1,10),
            'region_id'=>$this->faker->numberBetween(1,10),
            'district_id'=>$this->faker->numberBetween(1,10),
            'ward_id'=>$this->faker->numberBetween(1,10),
            'street'=>$this->faker->address(),
        ];
    }
}
