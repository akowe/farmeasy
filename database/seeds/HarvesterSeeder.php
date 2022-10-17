<?php

use Illuminate\Database\Seeder;
use App\Harvester_service;
class HarvesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Harvester_service::create([
                'service'  => 'Harvester'
                
            ]);
    }
}
