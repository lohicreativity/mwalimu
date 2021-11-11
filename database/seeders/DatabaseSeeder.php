<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(UsersTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(RoleUserTableSeeder::class);
        $this->call(LevelsTableSeeder::class);
        $this->call(AwardsTableSeeder::class);
        $this->call(UnitCategoriesTableSeeder::class);
        $this->call(DepartmentsTableSeeder::class);
        $this->call(NTALevelsTableSeeder::class);
        $this->call(ProgramsTableSeeder::class);
        $this->call(IntakesTableSeeder::class);
        $this->call(DesignationsTableSeeder::class);
        $this->call(CountriesTableSeeder::class);
        $this->call(RegionsTableSeeder::class);
        $this->call(DistrictsTableSeeder::class);
        $this->call(WardsTableSeeder::class);
        $this->call(DisabilityStatusesTableSeeder::class);
    }
}
