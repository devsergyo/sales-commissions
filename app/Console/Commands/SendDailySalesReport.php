<?php

namespace App\Console\Commands;

use App\Services\DailyReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDailySalesReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:send-daily-report {--date= : Data para gerar o relatório (formato: Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia um e-mail para cada vendedor com o resumo das vendas do dia';

    /**
     * @var DailyReportService
     */
    protected $dailyReportService;

    /**
     * Create a new command instance.
     *
     * @param DailyReportService $dailyReportService
     */
    public function __construct(DailyReportService $dailyReportService)
    {
        parent::__construct();
        $this->dailyReportService = $dailyReportService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Obter a data do relatório (hoje por padrão ou a data especificada)
        $reportDate = $this->option('date') 
            ? Carbon::createFromFormat('Y-m-d', $this->option('date')) 
            : Carbon::today();
            
        $formattedDate = $reportDate->format('d/m/Y');
        
        $this->info("Gerando relatório de vendas para {$formattedDate}...");
        
        // Enviar relatórios utilizando o serviço
        $results = $this->dailyReportService->sendDailyReports($reportDate);
        
        // Exibir resultados
        $this->info("Total de vendedores: {$results['total_sellers']}");
        $this->info("Relatórios enviados: {$results['total_reports_sent']}");
        $this->info("Vendedores sem vendas: {$results['sellers_without_sales']}");
        
        if (!empty($results['errors'])) {
            $this->error("Ocorreram erros ao enviar alguns relatórios:");
            foreach ($results['errors'] as $error) {
                $this->error(" - Vendedor ID {$error['seller_id']} ({$error['email']}): {$error['error']}");
            }
        }
        
        $this->info("Processo concluído.");
        
        return 0;
    }
}
