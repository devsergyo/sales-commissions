<?php

namespace App\Console\Commands;

use App\Services\DailyReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAdminDailySalesReport extends Command
{
    /**
     * O nome e a assinatura do comando no console.
     *
     * @var string
     */
    protected $signature = 'sales:send-admin-report {--date= : Data no formato Y-m-d}';

    /**
     * A descrição do comando no console.
     *
     * @var string
     */
    protected $description = 'Envia um relatório diário de vendas para o administrador';

    /**
     * @var DailyReportService
     */
    protected $dailyReportService;

    /**
     * Cria uma nova instância do comando.
     *
     * @param DailyReportService $dailyReportService
     * @return void
     */
    public function __construct(DailyReportService $dailyReportService)
    {
        parent::__construct();
        $this->dailyReportService = $dailyReportService;
    }

    /**
     * Executa o comando no console.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Obter a data do relatório
            $dateOption = $this->option('date');
            $reportDate = $dateOption ? Carbon::createFromFormat('Y-m-d', $dateOption) : null;
            
            // Formatar a data para exibição
            $displayDate = $reportDate ? $reportDate->format('d/m/Y') : Carbon::today()->format('d/m/Y');
            
            $this->info("Gerando relatório de vendas para o administrador - {$displayDate}...");
            
            // Enviar o relatório
            try {
                $result = $this->dailyReportService->sendDailyReportToAdmin($reportDate);
                
                if ($result['success']) {
                    $this->info("Relatório enviado com sucesso para o administrador!");
                    $this->info("Total de vendas: {$result['total_sales']}");
                    $this->info("Valor total: R$ " . number_format($result['total_amount'], 2, ',', '.'));
                    $this->info("Total de comissões: R$ " . number_format($result['total_commission'], 2, ',', '.'));
                } else {
                    $this->error("Erro ao enviar relatório para o administrador: {$result['error']}");
                    Log::error("Erro ao enviar relatório para o administrador: {$result['error']}");
                }
            } catch (\Exception $e) {
                $this->error("Erro ao enviar relatório para o administrador: {$e->getMessage()}");
                Log::error("Erro ao enviar relatório para o administrador: {$e->getMessage()}");
            }
            
            $this->info("Processo concluído.");
            
            return 0; // Sucesso
        } catch (\Exception $e) {
            $this->error("Erro ao executar o comando: {$e->getMessage()}");
            Log::error("Erro ao executar o comando: {$e->getMessage()}");
            
            return 1; // Erro
        }
    }
}
