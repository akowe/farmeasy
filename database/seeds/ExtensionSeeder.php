<?php

use Illuminate\Database\Seeder;
use App\Extension_service;
class ExtensionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Extension_service::create([
                'service'  => 'Extension Service'
                
            ]);
    }
}
