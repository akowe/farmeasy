<?php

use Illuminate\Database\Seeder;
use\App\FarmType;

class farmTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         FarmType::create([
                'id'        => '1',
                'farm'      => 'Rice',
                'status'    => 'approve'
                
            ]);

          FarmType::create([
                'id'        => '2',
                'farm'      => 'Wheat',
                'status'    => 'approve'
                
            ]);


           FarmType::create([
                'id'        => '3',
                'farm'      => 'Maize',
                'status'    => 'approve'
                
            ]);

            FarmType::create([
                'id'        => '4',
                'farm'      => 'Others',
                
                
            ]);
    }
}
