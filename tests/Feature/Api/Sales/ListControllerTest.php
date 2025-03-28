<?php

namespace Tests\Feature\Api\Sales;

use App\Models\Sale;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se a API retorna todas as vendas corretamente.
     *
     * @return void
     */
    public function test_can_list_all_sales()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Criar alguns vendedores
        $sellers = Seller::factory()->count(2)->create();

        // Criar algumas vendas para diferentes vendedores
        $salesSeller1 = Sale::factory()->count(2)->create([
            'seller_id' => $sellers[0]->id
        ]);

        $salesSeller2 = Sale::factory()->count(3)->create([
            'seller_id' => $sellers[1]->id
        ]);

        // Combinar todas as vendas
        $allSales = $salesSeller1->merge($salesSeller2);

        // Fazer a requisição para a API
        $response = $this->getJson('/api/sales');

        // Verificar se a resposta é 200 OK
        $response->assertStatus(200);

        // Verificar se a resposta tem a estrutura correta
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'sales' => [
                    '*' => [
                        'id',
                        'seller_id',
                        'amount',
                        'commission',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]
        ]);

        // Verificar se a resposta contém os valores esperados
        $response->assertJson([
            'success' => true,
            'message' => 'Vendas listadas com sucesso.'
        ]);

        // Verificar se todas as vendas estão na resposta
        foreach ($allSales as $sale) {
            $response->assertJsonFragment([
                'id' => $sale->id,
                'seller_id' => $sale->seller_id,
                'amount' => $sale->amount
            ]);
        }
    }

    /**
     * Testa se a API retorna 401 quando o usuário não está autenticado.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_list_sales()
    {
        // Criar alguns vendedores e vendas
        $seller = Seller::factory()->create();
        Sale::factory()->count(3)->create([
            'seller_id' => $seller->id
        ]);

        // Fazer a requisição para a API sem autenticação
        $response = $this->getJson('/api/sales');

        // Verificar se a resposta é 401 Unauthorized
        $response->assertStatus(401);
    }

    /**
     * Testa se a API retorna uma lista vazia quando não há vendas.
     *
     * @return void
     */
    public function test_returns_empty_list_when_no_sales()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Criar alguns vendedores, mas sem vendas
        Seller::factory()->count(2)->create();

        // Fazer a requisição para a API
        $response = $this->getJson('/api/sales');

        // Verificar se a resposta é 200 OK
        $response->assertStatus(200);

        // Verificar se a resposta tem a estrutura correta
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'sales'
            ]
        ]);

        // Verificar se a resposta contém uma lista vazia de vendas
        $response->assertJson([
            'success' => true,
            'message' => 'Vendas listadas com sucesso.',
            'data' => [
                'sales' => []
            ]
        ]);
    }
}
