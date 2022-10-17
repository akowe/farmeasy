<?php

use Illuminate\Database\Seeder;
use App\Boom_sprayer_service;
class BoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Boom_sprayer_service::create([
                'service'  => 'Boom Sprayer'
                
            ]);
    }
}
