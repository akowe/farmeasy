<?php

use Illuminate\Database\Seeder;
use App\Role;

class roleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         Role::create([
                'id'          => '1',
                'role'        => '1',
                'user_type'   => 'superadmin'
            ]);

          Role::create([
                'id'          => '2',
                'role'        => '2',
                'user_type'   => 'admin'
            ]);

           Role::create([
                'id'          => '3',
                'role'        => '3',
                'user_type'   => 'agent'
            ]);

            Role::create([
                'id'          => '4',
                'role'        => '4',
                'user_type'   => 'farmer'
            ]);

             Role::create([
                'id'          => '5',
                'role'        => '5',
                'user_type'   => 'service'
            ]);

    }
}
