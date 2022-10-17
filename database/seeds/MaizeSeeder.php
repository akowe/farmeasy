<?php

use Illuminate\Database\Seeder;
use App\Maize_farm_type;
class MaizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
           Maize_farm_type::create([
                'farm_type'  => 'Maize'
                
            ]);
    }
}
