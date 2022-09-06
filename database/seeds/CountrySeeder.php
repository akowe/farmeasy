<?php

use Illuminate\Database\Seeder;
use App\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Country::create([
                'id'            => '1',
                'country_code'  => '+234',
                'country'       => 'Nigeria'
                
            ]);

         Country::create([
                'id'            => '2',
                'country_code'  => '+255',
                'country'       => 'Tanzania'
                
            ]);
    }
}
