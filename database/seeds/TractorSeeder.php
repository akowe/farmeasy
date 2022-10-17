<?php

use Illuminate\Database\Seeder;
use App\Tractor_service;
class TractorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Tractor_service::create([
                'service'  => 'Tractor'
                
            ]);
    }
}
