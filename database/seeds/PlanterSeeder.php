<?php

use Illuminate\Database\Seeder;
use App\Planter_service;
class PlanterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Planter_service::create([
                'service'  => 'Planter'
                
            ]);
    }
}
