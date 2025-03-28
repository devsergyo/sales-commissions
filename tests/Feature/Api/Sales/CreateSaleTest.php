<?php

namespace Tests\Feature\Api\Sales;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateSaleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se a API cria uma venda corretamente.
     *
     * @return void
     */
    public function test_can_create_sale()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Criar um vendedor
        $seller = Seller::factory()->create();

        // Dados da venda
        $saleData = [
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'sale_date' => now()->format('Y-m-d')
        ];

        // Fazer a requisição para a API
        $response = $this->postJson('/api/sales/store', $saleData);

        // Verificar se a resposta é 201 Created
        $response->assertStatus(201);

        // Verificar se a venda foi criada no banco de dados
        $this->assertDatabaseHas('sales', [
            'seller_id' => $seller->id,
            'amount' => 1000.00
        ]);

        // Verificar se a comissão foi calculada corretamente (8.5%)
        $this->assertDatabaseHas('sales', [
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'commission' => 85.00 // 8.5% de 1000.00
        ]);
    }

    /**
     * Testa se a API retorna 401 quando o usuário não está autenticado.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_create_sale()
    {
        // Criar um vendedor
        $seller = Seller::factory()->create();

        // Dados da venda
        $saleData = [
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'sale_date' => now()->format('Y-m-d')
        ];

        // Fazer a requisição para a API sem autenticação
        $response = $this->postJson('/api/sales/store', $saleData);

        // Verificar se a resposta é 401 Unauthorized
        $response->assertStatus(401);

        // Verificar se a venda não foi criada no banco de dados
        $this->assertDatabaseMissing('sales', [
            'seller_id' => $seller->id,
            'amount' => 1000.00
        ]);
    }

    /**
     * Testa se a API valida os dados corretamente.
     *
     * @return void
     */
    public function test_validation_works_correctly()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Dados inválidos (faltando campos obrigatórios)
        $invalidData = [
            'seller_id' => 999 // ID de vendedor que não existe
        ];

        // Fazer a requisição para a API
        $response = $this->postJson('/api/sales/store', $invalidData);

        // Verificar se a resposta é 422 Unprocessable Entity
        $response->assertStatus(422);

        // Verificar se a resposta contém erros de validação
        $response->assertJsonValidationErrors(['seller_id', 'amount', 'sale_date']);

        // Verificar se a venda não foi criada no banco de dados
        $this->assertDatabaseCount('sales', 0);
    }

    /**
     * Testa se a API valida o valor mínimo da venda.
     *
     * @return void
     */
    public function test_sale_amount_must_be_positive()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Criar um vendedor
        $seller = Seller::factory()->create();

        // Dados da venda com valor zero
        $invalidData = [
            'seller_id' => $seller->id,
            'amount' => 0,
            'sale_date' => now()->format('Y-m-d')
        ];

        // Fazer a requisição para a API
        $response = $this->postJson('/api/sales/store', $invalidData);

        // Verificar se a resposta é 422 Unprocessable Entity
        $response->assertStatus(422);

        // Verificar se a resposta contém erro de validação para o campo amount
        $response->assertJsonValidationErrors(['amount']);

        // Verificar se a venda não foi criada no banco de dados
        $this->assertDatabaseCount('sales', 0);
    }
}
