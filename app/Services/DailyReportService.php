<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Mail\AdminDailySalesReport;
use App\Mail\DailySalesReport;
use App\Repositories\Interfaces\SaleRepositoryInterface;
use App\Repositories\Interfaces\SellerRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DailyReportService
{
    /**
     * @var SaleRepositoryInterface
     */
    protected $saleRepository;

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * Cache time in minutes
     */
    const CACHE_TIME = 60;

    /**
     * DailyReportService constructor.
     *
     * @param SaleRepositoryInterface $saleRepository
     * @param SellerRepositoryInterface $sellerRepository
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        SaleRepositoryInterface $saleRepository,
        SellerRepositoryInterface $sellerRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->saleRepository = $saleRepository;
        $this->sellerRepository = $sellerRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Processa a requisição de envio de relatório e retorna a resposta formatada.
     * 
     * O parâmetro $dateString é opcional e já foi validado pela Request.
     * Se fornecido, deve estar no formato Y-m-d e não pode ser uma data futura.
     *
     * @param string|null $dateString Data no formato Y-m-d (opcional)
     * @param int|null $sellerId ID do vendedor específico (opcional)
     * @return array
     */
    public function processReportRequest(?string $dateString = null, ?int $sellerId = null): array
    {
        // Definir a data do relatório (hoje ou a data especificada)
        $date = $dateString
            ? Carbon::createFromFormat('Y-m-d', $dateString)
            : Carbon::today();

        // Se um vendedor específico foi fornecido, enviar apenas para ele
        if ($sellerId) {
            $seller = $this->sellerRepository->find($sellerId);
            
            if (!$seller) {
                return [
                    'success' => false,
                    'message' => 'Vendedor não encontrado',
                    'error' => 'Vendedor com ID ' . $sellerId . ' não existe.'
                ];
            }
            
            $result = $this->sendDailyReportToSeller($sellerId, $date);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'data' => $result,
                    'message' => 'Relatório enviado com sucesso.'
                ];
            } 
            
            // Se não tiver vendas, retornar sucesso com mensagem informativa
            if (!$result['has_sales']) {
                return [
                    'success' => true,
                    'data' => $result,
                    'message' => 'Nenhuma venda encontrada para este vendedor na data especificada.'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Erro ao enviar relatório',
                'error' => $result['error']
            ];
        }
        
        // Caso contrário, enviar para todos os vendedores
        $results = $this->sendDailyReports($date);
        
        return [
            'success' => true,
            'data' => [
                'date' => $date->format('Y-m-d'),
                'reports_sent' => $results['total_reports_sent'],
                'total_sellers' => $results['total_sellers'],
                'sellers_without_sales' => $results['sellers_without_sales'],
                'errors' => count($results['errors'])
            ],
            'message' => 'Relatórios enviados com sucesso.'
        ];
    }

    /**
     * Gera e envia relatórios diários para todos os vendedores.
     *
     * @param Carbon|null $date
     * @return array
     */
    public function sendDailyReports(?Carbon $date = null): array
    {
        $reportDate = $date ?? Carbon::today();
        $formattedDate = $reportDate->format('d/m/Y');
        $results = [
            'total_sellers' => 0,
            'total_reports_sent' => 0,
            'sellers_without_sales' => 0,
            'errors' => [],
        ];

        // Usar cache para a lista de vendedores para reduzir consultas ao banco
        $cacheKey = 'sellers_list';
        $sellers = Cache::remember($cacheKey, self::CACHE_TIME, function () {
            return $this->sellerRepository->all();
        });
        
        $results['total_sellers'] = count($sellers);
        
        // Obter todas as vendas da data do relatório de uma vez só
        $allSalesForDate = $this->saleRepository->getByDate($reportDate);
        $salesBySellerMap = $allSalesForDate->groupBy('seller_id');
        
        foreach ($sellers as $seller) {
            $sellerSales = $salesBySellerMap->get($seller->id) ?? collect([]);
            
            if ($sellerSales->isEmpty()) {
                $results['sellers_without_sales']++;
                continue;
            }
            
            $totalSales = $sellerSales->count();
            $totalAmount = $sellerSales->sum('amount');
            $totalCommission = $sellerSales->sum('commission');
            
            $salesData = [
                'total_sales' => $totalSales,
                'total_amount' => $totalAmount,
                'total_commission' => $totalCommission,
                'sales' => $sellerSales
            ];

            try {
                // Criar o e-mail e enviá-lo através do job
                $email = new DailySalesReport($seller, $salesData, $formattedDate);
                SendEmailJob::dispatch($seller->email, $email);
                
                $results['total_reports_sent']++;
            } catch (\Exception $e) {
                Log::error('Erro ao enfileirar e-mail para vendedor: ' . $e->getMessage(), [
                    'seller_id' => $seller->id,
                    'email' => $seller->email,
                    'date' => $formattedDate
                ]);
                
                $results['errors'][] = [
                    'seller_id' => $seller->id,
                    'email' => $seller->email,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        $this->sendDailyReportToAdmin($reportDate, $allSalesForDate, $sellers);
        
        return $results;
    }

    /**
     * Gera e envia um relatório diário para um vendedor específico.
     *
     * @param int $sellerId
     * @param Carbon|null $date
     * @return array
     */
    public function sendDailyReportToSeller(int $sellerId, ?Carbon $date = null): array
    {
        $reportDate = $date ?? Carbon::today();
        $formattedDate = $reportDate->format('d/m/Y');
        
        // Obter o vendedor pelo ID
        $seller = $this->sellerRepository->find($sellerId);
        
        if (!$seller) {
            return [
                'seller_id' => $sellerId,
                'date' => $formattedDate,
                'has_sales' => false,
                'success' => false,
                'error' => 'Vendedor não encontrado.'
            ];
        }
        
        // Usar cache para reduzir consultas repetidas
        $cacheKey = 'seller_sales_' . $sellerId . '_' . $reportDate->format('Y-m-d');
        $sales = Cache::remember($cacheKey, self::CACHE_TIME, function () use ($sellerId, $reportDate) {
            return $this->saleRepository->getBySellerAndDate($sellerId, $reportDate);
        });
        
        $result = [
            'seller_id' => $sellerId,
            'seller_name' => $seller->first_name . ' ' . $seller->last_name,
            'date' => $formattedDate,
            'has_sales' => !$sales->isEmpty(),
            'success' => false,
            'error' => null
        ];
        
        if ($sales->isEmpty()) {
            return $result;
        }
        
        $totalSales = $sales->count();
        $totalAmount = $sales->sum('amount');
        $totalCommission = $sales->sum('commission');
        
        $salesData = [
            'total_sales' => $totalSales,
            'total_amount' => $totalAmount,
            'total_commission' => $totalCommission,
            'sales' => $sales
        ];

        try {
            // Criar o e-mail e enviá-lo através do job
            $email = new DailySalesReport($seller, $salesData, $formattedDate);
            SendEmailJob::dispatch($seller->email, $email);
            
            $result['success'] = true;
        } catch (\Exception $e) {
            Log::error('Erro ao enfileirar e-mail para vendedor: ' . $e->getMessage(), [
                'seller_id' => $sellerId,
                'email' => $seller->email,
                'date' => $formattedDate
            ]);
            
            $result['error'] = $e->getMessage();
        }
        
        return $result;
    }

    /**
     * Envia um e-mail para o administrador contendo a soma de todas as vendas efetuadas no dia.
     *
     * @param Carbon|null $date
     * @param \Illuminate\Support\Collection|null $allSales Coleção de vendas pré-carregada (opcional)
     * @param \Illuminate\Support\Collection|null $sellers Coleção de vendedores pré-carregada (opcional)
     * @return array
     */
    public function sendDailyReportToAdmin(?Carbon $date = null, $allSales = null, $sellers = null): array
    {
        $reportDate = $date ?? Carbon::today();
        $formattedDate = $reportDate->format('d/m/Y');
        
        $result = [
            'date' => $formattedDate,
            'success' => false,
            'error' => null,
            'total_sales' => 0,
            'total_amount' => 0,
            'total_commission' => 0,
            'total_sellers' => 0,
            'sellers_with_sales' => 0
        ];
        
        try {
            // Obter o e-mail do administrador (primeiro usuário)
            $adminUser = $this->userRepository->all()->first();
            
            if (!$adminUser) {
                throw new \Exception('Usuário administrador não encontrado.');
            }
            
            $adminEmail = $adminUser->email;
            
            // Se não foi fornecida uma coleção de vendas, buscar do banco
            if ($allSales === null) {
                // Usar cache para reduzir consultas repetidas
                $cacheKey = 'all_sales_' . $reportDate->format('Y-m-d');
                $allSales = Cache::remember($cacheKey, self::CACHE_TIME, function () use ($reportDate) {
                    return $this->saleRepository->getAllByDate($reportDate);
                });
            }
            
            // Calcular totais
            $totalSales = $allSales->count();
            $totalAmount = $allSales->sum('amount');
            $totalCommission = $allSales->sum('commission');
            
            // Obter todos os vendedores se não foram fornecidos
            if ($sellers === null) {
                $cacheKey = 'sellers_list';
                $sellers = Cache::remember($cacheKey, self::CACHE_TIME, function () {
                    return $this->sellerRepository->all();
                });
            }
            
            $totalSellers = count($sellers);
            
            // Calcular vendedores com vendas
            $sellerIds = $allSales->pluck('seller_id')->unique()->count();
            
            // Obter top 5 vendedores de forma otimizada
            $topSellers = [];
            $salesBySeller = $allSales->groupBy('seller_id');
            $sellerMap = $sellers->keyBy('id');
            
            foreach ($salesBySeller as $sellerId => $sales) {
                $seller = $sellerMap->get($sellerId);
                if (!$seller) continue;
                
                $topSellers[] = [
                    'id' => $sellerId,
                    'name' => $seller->first_name . ' ' . $seller->last_name,
                    'sales_count' => $sales->count(),
                    'total_amount' => $sales->sum('amount'),
                    'total_commission' => $sales->sum('commission')
                ];
            }
            
            // Ordenar por valor total de vendas (decrescente)
            usort($topSellers, function($a, $b) {
                return $b['total_amount'] <=> $a['total_amount'];
            });
            
            // Limitar aos top 5
            $topSellers = array_slice($topSellers, 0, 5);
            
            $salesData = [
                'total_sales' => $totalSales,
                'total_amount' => $totalAmount,
                'total_commission' => $totalCommission,
                'total_sellers' => $totalSellers,
                'sellers_with_sales' => $sellerIds,
                'top_sellers' => $topSellers
            ];
            
            $result['total_sales'] = $totalSales;
            $result['total_amount'] = $totalAmount;
            $result['total_commission'] = $totalCommission;
            $result['total_sellers'] = $totalSellers;
            $result['sellers_with_sales'] = $sellerIds;
            
            // Criar o e-mail e enviá-lo através do job
            $email = new AdminDailySalesReport($salesData, $formattedDate);
            SendEmailJob::dispatch($adminEmail, $email);
            
            $result['success'] = true;
        } catch (\Exception $e) {
            Log::error('Erro ao enfileirar relatório para o administrador: ' . $e->getMessage());
            $result['error'] = $e->getMessage();
        }
        
        return $result;
    }
}
