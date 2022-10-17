<?php

use Illuminate\Database\Seeder;
use App\Rice_farm_type;
class RiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Rice_farm_type::create([
                'farm_type'  => 'Rice'
                
            ]);
    }
}
