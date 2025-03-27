<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\Seller;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obter todos os vendedores
        $sellers = Seller::all();
        
        // Se não houver vendedores, criar alguns
        if ($sellers->isEmpty()) {
            $sellers = Seller::factory(10)->create();
        }
        
        // Criar 5 vendas para cada vendedor
        foreach ($sellers as $seller) {
            Sale::factory(5)->create([
                'seller_id' => $seller->id
            ]);
        }
    }
}
