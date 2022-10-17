<?php

use Illuminate\Database\Seeder;
use App\Fertilizer_service;
class FertilizerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Fertilizer_service::create([
                'service'  => 'Fertilizer'
                
            ]);
    }
}
