<?php

use Illuminate\Database\Seeder;
use App\Seeds_service;

class SeedServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Seeds_service::create([
                'service'  => 'Seed'
                
            ]);
    }
}
