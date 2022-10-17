<?php

use Illuminate\Database\Seeder;
use App\Harrow_service;
class HarrowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Harrow_service::create([
                'service'  => 'Harrow'
                
            ]);
    }
}
