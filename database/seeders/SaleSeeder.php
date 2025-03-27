<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sellers = Seller::all();
        
        if ($sellers->isEmpty()) {
            $sellers = Seller::factory(10)->create();
        }
        
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        
        foreach ($sellers as $seller) {
            $numberOfSales = rand(30, 60);

            $salesDates = [];
          
            for ($i = 0; $i < $numberOfSales; $i++) {
                $randomDate = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                )->format('Y-m-d');

                if (!isset($salesDates[$randomDate])) {
                    $salesDates[$randomDate] = 0;
                }
                $salesDates[$randomDate]++;
            }
            
            foreach ($salesDates as $date => $count) {
                for ($i = 0; $i < $count; $i++) {
                    Sale::factory()->create([
                        'seller_id' => $seller->id,
                        'sale_date' => $date
                    ]);
                }
            }
        }
    }
}
