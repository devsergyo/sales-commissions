<?php

namespace App\Services;

use App\Models\Sale;
use App\Repositories\Interfaces\SaleRepositoryInterface;
use App\Traits\ApiResponseTrait;

class SaleService
{
    use ApiResponseTrait;
    
    /**
     * Commission rate for sales (8.5%).
     */
    const COMMISSION_RATE = 0.085;
    
    /**
     * @var SaleRepositoryInterface
     */
    protected $saleRepository;

    /**
     * SaleService constructor.
     *
     * @param SaleRepositoryInterface $saleRepository
     */
    public function __construct(SaleRepositoryInterface $saleRepository)
    {
        $this->saleRepository = $saleRepository;
    }

    /**
     * Calculate commission for a sale amount.
     *
     * @param float $amount
     * @return float
     */
    public function calculateCommission(float $amount): float
    {
        return round($amount * self::COMMISSION_RATE, 2);
    }

    /**
     * Get all sales.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        try {
            $sales = $this->saleRepository->all();
            
            $salesWithCommission = $sales->map(function ($sale) {
                $sale->commission = $this->calculateCommission($sale->amount);
                return $sale;
            });

            return $this->successResponse([
                'sales' => $salesWithCommission
            ], 'Vendas listadas com sucesso.');
        } catch (\Exception $e) {
            return $this->errorResponse('Erro ao listar vendas', 500, $e->getMessage());
        }
    }

    /**
     * Get sales by seller ID.
     *
     * @param int $sellerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBySeller(int $sellerId)
    {
        try {
            $sales = $this->saleRepository->getBySeller($sellerId);
            
            $salesWithCommission = $sales->map(function ($sale) {
                $sale->commission = $this->calculateCommission($sale->amount);
                return $sale;
            });

            return $this->successResponse([
                'sales' => $salesWithCommission
            ], 'Vendas do vendedor listadas com sucesso.');
        } catch (\Exception $e) {
            return $this->errorResponse('Erro ao listar vendas do vendedor', 500, $e->getMessage());
        }
    }

    /**
     * Create a new sale.
     *
     * @param array $data
     * @return Sale
     */
    public function create(array $data): Sale
    {
        // Calcular a comissão antes de criar a venda
        $data['commission'] = $this->calculateCommission($data['amount']);
        
        return $this->saleRepository->create($data);
    }
}
