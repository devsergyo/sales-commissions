<?php

namespace App\Services;

use App\Mail\AdminDailySalesReport;
use App\Mail\DailySalesReport;
use App\Repositories\Interfaces\SaleRepositoryInterface;
use App\Repositories\Interfaces\SellerRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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

        $sellers = $this->sellerRepository->all();
        $results['total_sellers'] = count($sellers);
        
        foreach ($sellers as $seller) {
            $result = $this->sendDailyReportToSeller($seller->id, $reportDate);
            
            if (!$result['has_sales']) {
                $results['sellers_without_sales']++;
                continue;
            }
            
            if ($result['success']) {
                $results['total_reports_sent']++;
            } else {
                $results['errors'][] = [
                    'seller_id' => $seller->id,
                    'email' => $seller->email,
                    'error' => $result['error']
                ];
            }
        }
        
        $this->sendDailyReportToAdmin($reportDate);
        
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
        
        // Obter as vendas do vendedor para a data do relatório
        $sales = $this->saleRepository->getBySellerAndDate($sellerId, $reportDate);
        
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
            Mail::to($seller->email)->send(new DailySalesReport($seller, $salesData, $formattedDate));
            $result['success'] = true;
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }
        
        return $result;
    }

    /**
     * Envia um e-mail para o administrador contendo a soma de todas as vendas efetuadas no dia.
     *
     * @param Carbon|null $date
     * @return array
     */
    public function sendDailyReportToAdmin(?Carbon $date = null): array
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
            // Obter todas as vendas da data do relatório
            $allSales = $this->saleRepository->getAllByDate($reportDate);
            
            // Calcular totais
            $totalSales = $allSales->count();
            $totalAmount = $allSales->sum('amount');
            $totalCommission = $allSales->sum('commission');
            
            // Obter todos os vendedores
            $sellers = $this->sellerRepository->all();
            $totalSellers = count($sellers);
            
            // Calcular vendedores com vendas
            $sellerIds = $allSales->pluck('seller_id')->unique()->count();
            
            // Obter top 5 vendedores
            $topSellers = [];
            $salesBySeller = $allSales->groupBy('seller_id');
            
            foreach ($salesBySeller as $sellerId => $sales) {
                $seller = $this->sellerRepository->find($sellerId);
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
            
            // Obter o e-mail do administrador a partir do primeiro usuário do sistema
            // Assumindo que o primeiro usuário é o administrador
            $adminEmail = 'admin@example.com';
            
            try {
                $users = $this->userRepository->all();
                if ($users && $users->isNotEmpty()) {
                    $adminEmail = $users->first()->email;
                }
            } catch (\Exception $e) {
                Log::warning('Não foi possível obter o e-mail do administrador: ' . $e->getMessage());
            }
            
            Mail::to($adminEmail)->send(new AdminDailySalesReport($salesData, $formattedDate));
            $result['success'] = true;
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            Log::error('Erro ao gerar ou enviar relatório para o administrador: ' . $e->getMessage());
        }
        
        return $result;
    }
}
