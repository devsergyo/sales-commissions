<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Services\SaleService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Gerar um valor aleatório para a venda entre R$ 50 e R$ 5.000
        $amount = fake()->randomFloat(2, 50, 5000);
        
        // Calcular a comissão com base na taxa definida no serviço
        $commission = round($amount * SaleService::COMMISSION_RATE, 2);
        
        // Gerar um horário aleatório para a venda (entre 8h e 18h)
        $hour = rand(8, 18);
        $minute = rand(0, 59);
        $second = rand(0, 59);
        
        // Obter a data da venda (será sobrescrita pelo seeder)
        $saleDate = fake()->dateTimeBetween('-30 days', 'now');
        
        // Definir o horário da venda
        $saleDateTime = Carbon::instance($saleDate)->setTime($hour, $minute, $second);
        
        return [
            'seller_id' => Seller::factory(),
            'amount' => $amount,
            'commission' => $commission,
            'sale_date' => $saleDateTime,
        ];
    }
}
