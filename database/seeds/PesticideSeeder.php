<?php

use Illuminate\Database\Seeder;
use App\Pesticide_herbicide_service;
class PesticideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Pesticide_herbicide_service::create([
                'service'  => 'Pesticide/Herbicide'
                
            ]);
    }
}
