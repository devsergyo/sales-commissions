<?php

namespace App\Services;

use App\Repositories\Interfaces\SaleRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SaleService
{
    use ApiResponseTrait;

    /**
     * Commission rate for sales (8.5%).
     */
    const COMMISSION_RATE = 0.085;

    /**
     * Tempo de cache em minutos
     */
    const CACHE_TIME = 60;

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
            // Usar cache para melhorar o desempenho
            $cacheKey = 'all_sales';
            $salesWithCommission = Cache::remember($cacheKey, self::CACHE_TIME, function () {
                $sales = $this->saleRepository->all();

                return $sales->map(function ($sale) {
                    $sale->commission = $this->calculateCommission($sale->amount);
                    return $sale;
                });
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
            // Usar cache para melhorar o desempenho
            $cacheKey = 'seller_sales_' . $sellerId;
            $salesWithCommission = Cache::remember($cacheKey, self::CACHE_TIME, function () use ($sellerId) {
                $sales = $this->saleRepository->getBySeller($sellerId);

                return $sales->map(function ($sale) {
                    $sale->commission = $this->calculateCommission($sale->amount);
                    return $sale;
                });
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(array $data)
    {
        // Calcular a comissão antes de criar a venda
        $data['commission'] = $this->calculateCommission($data['amount']);

        // Usar transação para garantir a integridade dos dados
        DB::beginTransaction();

        try {
            $sale = $this->saleRepository->create($data);

            // Invalidar caches relacionados
            $this->invalidateRelatedCaches($data['seller_id'] ?? null);

            DB::commit();

            return $this->successResponse([
                'sale' => $sale
            ], 'Vendas cadastrada com sucesso com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Erro ao cadastrar a venda.', 500, $e->getMessage());
        }
    }

    /**
     * Invalida os caches relacionados às vendas após uma nova venda ser criada.
     *
     * @param int|null $sellerId
     * @return void
     */
    protected function invalidateRelatedCaches(?int $sellerId = null): void
    {
        // Invalidar cache de todas as vendas
        Cache::forget('all_sales');

        // Se um vendedor específico foi fornecido, invalidar seu cache
        if ($sellerId) {
            Cache::forget('seller_sales_' . $sellerId);
        }
    }
}
