<?php

use Illuminate\Database\Seeder;
use App\Wheat_farm_type;
class WheatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Wheat_farm_type::create([
                'farm_type'  => 'Wheat'
                
            ]);
    }
}
