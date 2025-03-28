<?php

namespace Tests\Feature\Api\Seller;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se a API cria um vendedor corretamente.
     *
     * @return void
     */
    public function test_can_create_seller()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Dados do vendedor
        $sellerData = [
            'first_name' => 'Vendedor',
            'last_name' => 'Teste',
            'email' => 'vendedor@teste.com'
        ];

        // Fazer a requisição para a API
        $response = $this->postJson('/api/sellers/store', $sellerData);

        // Verificar se a resposta é 201 Created
        $response->assertStatus(201);

        // Verificar se a resposta tem a estrutura correta
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'seller' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);

        // Verificar se a resposta contém os valores esperados
        $response->assertJson([
            'success' => true,
            'message' => 'Vendedor cadastrado com sucesso.',
            'data' => [
                'seller' => [
                    'first_name' => 'Vendedor',
                    'last_name' => 'Teste',
                    'email' => 'vendedor@teste.com'
                ]
            ]
        ]);

        // Verificar se o vendedor foi criado no banco de dados
        $this->assertDatabaseHas('sellers', [
            'first_name' => 'Vendedor',
            'last_name' => 'Teste',
            'email' => 'vendedor@teste.com'
        ]);
    }

    /**
     * Testa se a API retorna 401 quando o usuário não está autenticado.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_create_seller()
    {
        // Dados do vendedor
        $sellerData = [
            'first_name' => 'Vendedor',
            'last_name' => 'Teste',
            'email' => 'vendedor@teste.com'
        ];

        // Fazer a requisição para a API sem autenticação
        $response = $this->postJson('/api/sellers/store', $sellerData);

        // Verificar se a resposta é 401 Unauthorized
        $response->assertStatus(401);

        // Verificar se o vendedor não foi criado no banco de dados
        $this->assertDatabaseMissing('sellers', [
            'email' => 'vendedor@teste.com'
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

        // Dados inválidos (campos obrigatórios faltando)
        $invalidData = [
            'last_name' => 'Teste'
            // faltando first_name e email
        ];

        // Fazer a requisição para a API
        $response = $this->postJson('/api/sellers/store', $invalidData);

        // Verificar se a resposta é 422 Unprocessable Entity
        $response->assertStatus(422);

        // Verificar se a resposta contém erros de validação
        $response->assertJsonValidationErrors(['first_name', 'email']);

        // Verificar se o vendedor não foi criado no banco de dados
        $this->assertDatabaseMissing('sellers', [
            'last_name' => 'Teste'
        ]);
    }

    /**
     * Testa se a API valida o e-mail único.
     *
     * @return void
     */
    public function test_email_must_be_unique()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Criar um vendedor primeiro
        $response = $this->postJson('/api/sellers/store', [
            'first_name' => 'Vendedor',
            'last_name' => 'Um',
            'email' => 'vendedor@teste.com'
        ]);

        // Tentar criar outro vendedor com o mesmo e-mail
        $duplicateResponse = $this->postJson('/api/sellers/store', [
            'first_name' => 'Vendedor',
            'last_name' => 'Dois',
            'email' => 'vendedor@teste.com' // mesmo e-mail
        ]);

        // Verificar se a resposta é 422 Unprocessable Entity
        $duplicateResponse->assertStatus(422);

        // Verificar se a resposta contém erros de validação para e-mail
        $duplicateResponse->assertJsonValidationErrors(['email']);

        // Verificar que apenas um vendedor com esse e-mail existe
        $this->assertDatabaseCount('sellers', 1);
    }
}
