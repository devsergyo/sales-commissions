<?php

namespace Tests\Feature\Api\Reports;

use App\Jobs\SendEmailJob;
use App\Models\Sale;
use App\Models\Seller;
use App\Models\User;
use App\Services\DailyReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class SendDailyReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $dailyReportServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake the queue so jobs are not actually dispatched
        Queue::fake();
        Bus::fake();

        // Mock do serviço de relatórios
        $this->dailyReportServiceMock = Mockery::mock(DailyReportService::class);
        $this->app->instance(DailyReportService::class, $this->dailyReportServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Testa se a API envia o relatório diário para um vendedor específico.
     *
     * @return void
     */
    public function test_can_send_daily_report_to_specific_seller()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Criar um vendedor
        $seller = Seller::factory()->create();

        // Criar algumas vendas para o vendedor
        Sale::factory()->count(3)->create([
            'seller_id' => $seller->id,
            'created_at' => Carbon::today()
        ]);

        // Configurar o mock para retornar sucesso
        $this->dailyReportServiceMock
            ->shouldReceive('processReportRequest')
            ->once()
            ->with([], $seller->id)
            ->andReturn([
                'success' => true,
                'data' => [
                    'seller_id' => $seller->id,
                    'date' => Carbon::today()->format('d/m/Y'),
                    'has_sales' => true,
                    'success' => true
                ],
                'message' => 'Relatório enviado com sucesso.'
            ]);

        // Fazer a requisição para a API
        $response = $this->postJson("/api/reports/{$seller->id}/send-daily-report");

        // Verificar se a resposta é 200 OK
        $response->assertStatus(200);

        // Verificar se a resposta tem a estrutura correta
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        // Verificar se a resposta contém os valores esperados
        $response->assertJson([
            'success' => true
        ]);
    }

    /**
     * Testa se a API retorna erro quando o vendedor não existe.
     *
     * @return void
     */
    public function test_returns_error_when_seller_not_found()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // ID de um vendedor que não existe
        $nonExistentSellerId = 9999;

        // Configurar o mock para retornar erro
        $this->dailyReportServiceMock
            ->shouldReceive('processReportRequest')
            ->once()
            ->with([], $nonExistentSellerId)
            ->andReturn([
                'success' => false,
                'message' => 'Vendedor não encontrado',
                'error' => 'Vendedor com ID ' . $nonExistentSellerId . ' não existe.'
            ]);

        // Fazer a requisição para a API
        $response = $this->postJson("/api/reports/{$nonExistentSellerId}/send-daily-report");

        // Verificar se a resposta é um erro
        $response->assertStatus(500);

        // Verificar se a resposta contém a mensagem de erro correta
        $response->assertJson([
            'success' => false,
            'message' => 'Vendedor não encontrado'
        ]);
    }

    /**
     * Testa se a API envia relatórios para todos os vendedores.
     *
     * @return void
     */
    public function test_can_send_daily_reports_to_all_sellers()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Criar alguns vendedores
        $sellers = Seller::factory()->count(3)->create();

        // Criar algumas vendas para cada vendedor
        foreach ($sellers as $seller) {
            Sale::factory()->count(2)->create([
                'seller_id' => $seller->id,
                'created_at' => Carbon::today()
            ]);
        }

        // Configurar o mock para retornar sucesso
        $this->dailyReportServiceMock
            ->shouldReceive('processReportRequest')
            ->once()
            ->with([], null)
            ->andReturn([
                'success' => true,
                'data' => [
                    'date' => Carbon::today()->format('Y-m-d'),
                    'reports_sent' => 3,
                    'total_sellers' => 3,
                    'sellers_without_sales' => 0,
                    'errors' => 0
                ],
                'message' => 'Relatórios enviados com sucesso.'
            ]);

        // Fazer a requisição para a API
        $response = $this->postJson("/api/reports/send-daily-reports");

        // Verificar se a resposta é 200 OK
        $response->assertStatus(200);

        // Verificar se a resposta tem a estrutura correta
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        // Verificar se a resposta contém os valores esperados
        $response->assertJson([
            'success' => true
        ]);
    }

    /**
     * Testa se a API retorna 401 quando o usuário não está autenticado.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_send_reports()
    {
        // Criar um vendedor
        $seller = Seller::factory()->create();

        // Fazer a requisição para a API sem autenticação
        $response = $this->postJson("/api/reports/{$seller->id}/send-daily-report");

        // Verificar se a resposta é 401 Unauthorized
        $response->assertStatus(401);
    }

    /**
     * Testa se a API aceita uma data específica para o relatório.
     *
     * @return void
     */
    public function test_can_specify_date_for_report()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Criar um vendedor
        $seller = Seller::factory()->create();

        // Data para o relatório (ontem)
        $reportDate = Carbon::yesterday()->format('Y-m-d');

        // Criar algumas vendas para o vendedor na data especificada
        Sale::factory()->count(2)->create([
            'seller_id' => $seller->id,
            'created_at' => Carbon::yesterday()
        ]);

        // Configurar o mock para retornar sucesso
        // O controller envia o request->validated(['date']) como primeiro parâmetro
        $this->dailyReportServiceMock
            ->shouldReceive('processReportRequest')
            ->once()
            ->with($reportDate, $seller->id)
            ->andReturn([
                'success' => true,
                'data' => [
                    'seller_id' => $seller->id,
                    'seller_name' => $seller->first_name . ' ' . $seller->last_name,
                    'date' => Carbon::yesterday()->format('d/m/Y'),
                    'has_sales' => true,
                    'success' => true,
                    'error' => null
                ],
                'message' => 'Relatório enviado com sucesso.'
            ]);

        // Fazer a requisição para a API com a data especificada
        $response = $this->postJson("/api/reports/{$seller->id}/send-daily-report", [
            'date' => $reportDate
        ]);

        // Verificar se a resposta é 200 OK
        $response->assertStatus(200);

        // Verificar se a resposta tem a estrutura correta
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        // Verificar se a resposta contém os valores esperados
        $response->assertJson([
            'success' => true
        ]);
    }
}
