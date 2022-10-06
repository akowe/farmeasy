<?php

use Illuminate\Database\Seeder;
use\App\ServiceType;

class serviceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         ServiceType::create([
                'service'     => 'Tractor'
            ]);

         ServiceType::create([
                'service'     => 'Plower'
            ]);

         ServiceType::create([
                'service'     => 'Planter'
            ]);

         ServiceType::create([
                'service'     => 'Seed'
            ]);

         ServiceType::create([
                'service'     => 'Pesticide'
            ]);

         ServiceType::create([
                'service'     => 'Fertilizer'
            ]);

         ServiceType::create([
                'service'     => 'Processor'
            ]);

          ServiceType::create([
                'service'     => 'Harvester'
            ]);
    }
}
