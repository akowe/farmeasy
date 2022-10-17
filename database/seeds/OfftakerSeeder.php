<?php

use Illuminate\Database\Seeder;
use App\Off_taker_service;
class OfftakerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Off_taker_service::create([
                'service'  => 'Off Taker'
                
            ]);
    }
}
