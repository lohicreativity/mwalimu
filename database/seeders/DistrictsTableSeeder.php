<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\District;

class DistrictsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
                       ['name' => 'ARUMERU', 'region_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ARUSHA', 'region_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ARUSHA CBD', 'region_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KARATU ', 'region_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LONGIDO ', 'region_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MERU', 'region_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MONDULI ', 'region_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NGORONGORO ', 'region_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ILALA', 'region_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ILALA CBD', 'region_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KIGAMBONI', 'region_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KINONDONI', 'region_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TEMEKE', 'region_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'UBUNGO', 'region_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BAHI', 'region_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CHAMWINO', 'region_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CHEMBA', 'region_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DODOMA', 'region_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DODOMA CBD', 'region_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KONDOA ', 'region_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KONGWA', 'region_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MPWAPWA', 'region_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BUKOMBE', 'region_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CHATO', 'region_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'GEITA', 'region_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MBOGWE', 'region_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NYANGHWALE', 'region_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IRINGA', 'region_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IRINGA CBD', 'region_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KILOLO', 'region_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MUFINDI', 'region_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BIHARAMULO', 'region_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BUKOBA', 'region_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BUKOBA CBD', 'region_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KARAGWE', 'region_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KYERWA', 'region_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MISENYI', 'region_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MULEBA', 'region_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NGARA', 'region_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MICHEWENI', 'region_id' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'WETE', 'region_id' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KASKAZINI A', 'region_id' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KASKAZINI B', 'region_id' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MLELE', 'region_id' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MPANDA', 'region_id' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MPANDA -CBD', 'region_id' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PWANI', 'region_id' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TANGANYIKA', 'region_id' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BUHIGWE', 'region_id'  =>10, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KAKONKO', 'region_id' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KASULU', 'region_id' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KIBONDO', 'region_id' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KIGOMA', 'region_id' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KIGOMA CBD', 'region_id' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'UVINZA', 'region_id' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'HAI', 'region_id' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MOSHI', 'region_id' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MOSHI CBD', 'region_id' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MWANGA', 'region_id' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ROMBO', 'region_id' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SAME', 'region_id' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SIHA', 'region_id' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CHAKE CHAKE', 'region_id' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MKOANI', 'region_id' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KATI', 'region_id' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KUSINI', 'region_id' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KILWA', 'region_id' => 14, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LINDI ', 'region_id' => 14, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LINDI CBD', 'region_id' => 14, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LIWALE', 'region_id' => 14, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NACHINGWEA ', 'region_id' => 14, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'RUANGWA', 'region_id' => 14, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DIMANI', 'region_id' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MFENESINI', 'region_id' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BABATI', 'region_id' => 16, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BABATI CBD', 'region_id' => 16, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'HANANG', 'region_id' => 16, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KITETO', 'region_id' => 16, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MBULU', 'region_id' => 16, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SIMANJIRO', 'region_id' => 16, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BUNDA', 'region_id' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BUTIAMA', 'region_id' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MUSOMA', 'region_id' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MUSOMA CBD', 'region_id' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'RORYA', 'region_id' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SERENGETI', 'region_id' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TARIME', 'region_id' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CHUNYA ', 'region_id' => 18, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KYELA', 'region_id' => 18, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MBARALI ', 'region_id' => 18, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MBEYA', 'region_id' => 18, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MBEYA CBD', 'region_id' => 18, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'RUNGWE ', 'region_id' => 18, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'AMANI', 'region_id' => 19, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MJINI', 'region_id' => 19, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'GAIRO', 'region_id' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KILOMBERO', 'region_id' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KILOSA', 'region_id' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MALINYI', 'region_id' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MOROGORO', 'region_id' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MOROGORO CBD', 'region_id' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MVOMERO ', 'region_id' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ULANGA', 'region_id' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MASASI', 'region_id' => 21, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MTWARA', 'region_id' => 21, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MTWARA CBD', 'region_id' => 21, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NANYUMBU', 'region_id' => 21, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NEWALA', 'region_id' => 21, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TANDAHIMBA', 'region_id' => 21, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BUCHOSA', 'region_id' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ILEMELA', 'region_id' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KWIMBA ', 'region_id' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MAGU', 'region_id' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MISUNGWI', 'region_id' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NYAMAGANA', 'region_id' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SENGEREMA', 'region_id' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'UKEREWE', 'region_id' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LUDEWA', 'region_id' => 23, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MAKETE', 'region_id' => 23, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NJOMBE', 'region_id' => 23, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NJOMBE CBD', 'region_id' => 23, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'WANGINGOMBE', 'region_id' => 23, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BAGAMOYO', 'region_id' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KIBAHA', 'region_id' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KIBAHA CBD', 'region_id' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KIBITI', 'region_id' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KISARAWE', 'region_id' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MAFIA', 'region_id' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MKURANGA', 'region_id' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'RUFIJI', 'region_id' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KALAMBO', 'region_id' => 25, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NKASI', 'region_id' => 25, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SUMBAWANGA', 'region_id' => 25, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SUMBAWANGA CBD', 'region_id' => 25, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MADABA', 'region_id' => 26, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MBINGA ', 'region_id' => 26, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MBINGA CBD', 'region_id' => 26, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NAMTUMBO', 'region_id' => 26, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NYASA', 'region_id' => 26, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SONGEA', 'region_id' => 26, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SONGEA CBD', 'region_id' => 26, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TUNDURU', 'region_id' => 26, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KAHAMA', 'region_id' => 27, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KISHAPU', 'region_id' => 27, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SHINYANGA', 'region_id' => 27, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SHINYANGA CBD', 'region_id' => 27, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BARIADI', 'region_id' => 28, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BUSEGA', 'region_id' => 28, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ITILIMA', 'region_id' => 28, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MASWA', 'region_id' => 28, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MEATU', 'region_id' => 28, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IKUNGI', 'region_id' => 29, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IRAMBA', 'region_id' => 29, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MANYONI', 'region_id' => 29, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MKALAMA', 'region_id' => 29, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SINGIDA', 'region_id' => 29, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SINGIDA CBD', 'region_id' => 29, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ILEJE ', 'region_id' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MBOZI ', 'region_id' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MOMBA', 'region_id' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SONGWE', 'region_id' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IGUNGA', 'region_id' => 31, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KALIUA', 'region_id' => 31, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NZEGA', 'region_id' => 31, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SIKONGE', 'region_id' => 31, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TABORA CBD', 'region_id' => 31, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'URAMBO', 'region_id' => 31, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'UYUI', 'region_id' => 31, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'HANDENI', 'region_id' => 32, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KILINDI', 'region_id' => 32, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KOROGWE', 'region_id' => 32, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KOROGWE CBD', 'region_id' => 32, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LUSHOTO', 'region_id' => 32, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MKINGA', 'region_id' => 32, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MUHEZA', 'region_id' => 32, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PANGANI', 'region_id' => 32, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TANGA', 'region_id' => 32, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TANGA CBD', 'region_id' => 32, 'created_at' => now(), 'updated_at' => now()],
        ];

        District::insert($data);
    }
}
