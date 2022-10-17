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
                'service'     => 'Plough'
            ]);

         ServiceType::create([
                'service'     => 'Planter'
            ]);

         ServiceType::create([
                'service'     => 'Seed'
            ]);

         ServiceType::create([
                'service'     => 'Pesticide/Herbicide'
            ]);

         ServiceType::create([
                'service'     => 'Fertilizer'
            ]);

         ServiceType::create([
                'service'     => 'Harrow'
            ]);

          ServiceType::create([
                'service'     => 'Harvester'
            ]);

           ServiceType::create([
                'service'     => 'Ridger'
            ]);

            ServiceType::create([
                'service'     => 'Boom Sprayer'
            ]);

             ServiceType::create([
                'service'     => 'Extension Service'
            ]);

              ServiceType::create([
                'service'     => 'Off Taker'
            ]);


               ServiceType::create([
                'service'     => 'Treasher'
            ]);
    }
}
