<?php

use Illuminate\Database\Seeder;
use App\Location;

class locationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Location::create([
                'id'        => '1',
                'location'  => 'Kaduna'
                
            ]);

         Location::create([
                'id'        => '2',
                'location'  => 'Lagos'
                
            ]);

         Location::create([
                'id'        => '3',
                'location'  => 'Niger'
                
            ]);

         Location::create([
                'id'        => '4',
                'location'  => 'Taraba'
                
            ]);
    }
}
