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
                'id'          => '1',
                'service'     => 'Tractor'
            ]);

         ServiceType::create([
                'id'          => '2',
                'service'     => 'Plower'
            ]);

         ServiceType::create([
                'id'          => '3',
                'service'     => 'Planter'
            ]);

         ServiceType::create([
                'id'          => '4',
                'service'     => 'Seed'
            ]);

         ServiceType::create([
                'id'          => '5',
                'service'     => 'Pesticide'
            ]);

         ServiceType::create([
                'id'          => '6',
                'service'     => 'Fertilizer'
            ]);

         ServiceType::create([
                'id'          => '7',
                'service'     => 'Processor'
            ]);
    }
}
