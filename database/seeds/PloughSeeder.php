<?php

use Illuminate\Database\Seeder;
use App\Plough_service;
class PloughSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Plough_service::create([
                'service'  => 'Plough'
                
            ]);
    }
}
