<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\Region;

class RegionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [

            ['name' => 'ARUSHA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DAR ES SALAAM', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DODOMA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'GEITA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IRINGA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KAGERA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KASKAZINI PEMBA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KASKAZINI UNGUJA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KATAVI', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KIGOMA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KILIMANJARO', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KUSINI PEMBA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KUSINI UNGUJA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LINDI', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MAGHARIBI', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MANYARA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MARA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MBEYA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MJINI', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MOROGORO', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MTWARA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MWANZA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NJOMBE', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PWANI', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'RUKWA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'RUVUMA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SHINYANGA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SIMIYU', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SINGIDA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SONGWE', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TABORA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TANGA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ];

        Region::insert($data);
    }
}
