<?php

use Illuminate\Database\Seeder;
use App\Price;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
          Price::create([
                'service_type_id'   => '1',
                'price'              => '80000'
            ]);

           Price::create([
                'service_type_id'   => '2',
                'price'              => '70000'
            ]);

            Price::create([
                'service_type_id'   => '3',
                'price'              => '60000'
            ]);

             Price::create([
                'service_type_id'   => '4',
                'price'              => '50000'
            ]);

              Price::create([
                'service_type_id'   => '5',
                'price'              => '40000'
            ]);

               Price::create([
                'service_type_id'   => '6',
                'price'              => '30000'
            ]);

                Price::create([
                'service_type_id'   => '7',
                'price'              => '20000'
            ]);

                  Price::create([
                'service_type_id'   => '8',
                'price'              => '90000'
            ]);


                Price::create([
                'service_type_id'   => '9',
                'price'              => '90000'
            ]);

                   Price::create([
                'service_type_id'   => '10',
                'price'              => '90000'
            ]);

                Price::create([
                'service_type_id'   => '11',
                'price'              => '90000'
            ]);

                Price::create([
                'service_type_id'   => '12',
                'price'              => '90000'
            ]);

                   Price::create([
                'service_type_id'   => '13',
                'price'              => '90000'
            ]);


    }
}
