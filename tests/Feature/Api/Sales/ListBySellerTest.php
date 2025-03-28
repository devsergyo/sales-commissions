<?php

namespace Tests\Feature\Api\Sales;

use App\Models\Sale;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListBySellerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se a API retorna as vendas de um vendedor corretamente.
     *
     * @return void
     */
    public function test_can_list_sales_by_seller()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Criar um vendedor
        $seller = Seller::factory()->create();

        // Criar algumas vendas para o vendedor
        $sales = Sale::factory()->count(3)->create([
            'seller_id' => $seller->id
        ]);

        // Fazer a requisição para a API
        $response = $this->getJson("/api/sales/{$seller->id}/sales");

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
            'message' => 'Vendas do vendedor listadas com sucesso.'
        ]);

        // Verificar se todas as vendas estão na resposta
        foreach ($sales as $sale) {
            $response->assertJsonFragment([
                'id' => $sale->id,
                'seller_id' => $seller->id,
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
        // Criar um vendedor
        $seller = Seller::factory()->create();

        // Criar algumas vendas para o vendedor
        Sale::factory()->count(3)->create([
            'seller_id' => $seller->id
        ]);

        // Fazer a requisição para a API sem autenticação
        $response = $this->getJson("/api/sales/{$seller->id}/sales");

        // Verificar se a resposta é 401 Unauthorized
        $response->assertStatus(401);
    }

    /**
     * Testa se a API retorna uma lista vazia quando o vendedor não tem vendas.
     *
     * @return void
     */
    public function test_returns_empty_list_when_seller_has_no_sales()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Criar um vendedor sem vendas
        $seller = Seller::factory()->create();

        // Fazer a requisição para a API
        $response = $this->getJson("/api/sales/{$seller->id}/sales");

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
            'message' => 'Vendas do vendedor listadas com sucesso.',
            'data' => [
                'sales' => []
            ]
        ]);
    }
}
