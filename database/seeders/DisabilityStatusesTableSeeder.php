<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\DisabilityStatus;

class DisabilityStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'No Disability', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Physical Disability', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Visual Impairment', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hearing Impairment', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Olfactory and Gustatory Impairment', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Somatosensory Impairment', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Balance Disorder', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Intellectual Disability', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mental Health and Emotional Disability', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Developmental Disability', 'created_at' => now(), 'updated_at' => now()],
        ];

        DisabilityStatus::insert($data);
    }
}
