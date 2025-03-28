<?php

namespace Tests\Feature\Api\Seller;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se a API retorna todos os vendedores corretamente.
     *
     * @return void
     */
    public function test_can_list_all_sellers()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Criar alguns vendedores
        $sellers = Seller::factory()->count(3)->create();

        // Fazer a requisição para a API
        $response = $this->getJson('/api/sellers');

        // Verificar se a resposta é 200 OK
        $response->assertStatus(200);

        // Verificar se a resposta tem a estrutura correta
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'sellers' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'created_at',
                        'updated_at',
                        'deleted_at'
                    ]
                ]
            ]
        ]);

        // Verificar se a resposta contém os valores esperados
        $response->assertJson([
            'success' => true,
            'message' => 'Vendedores listados com sucesso.'
        ]);

        // Verificar se todos os vendedores estão na resposta
        foreach ($sellers as $seller) {
            $response->assertJsonFragment([
                'id' => $seller->id,
                'first_name' => $seller->first_name,
                'last_name' => $seller->last_name,
                'email' => $seller->email
            ]);
        }
    }

    /**
     * Testa se a API retorna 401 quando o usuário não está autenticado.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_list_sellers()
    {
        // Criar alguns vendedores
        Seller::factory()->count(3)->create();

        // Fazer a requisição para a API sem autenticação
        $response = $this->getJson('/api/sellers');

        // Verificar se a resposta é 401 Unauthorized
        $response->assertStatus(401);
    }

    /**
     * Testa se a API retorna uma lista vazia quando não há vendedores.
     *
     * @return void
     */
    public function test_returns_empty_list_when_no_sellers()
    {
        // Criar um usuário autenticado
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Fazer a requisição para a API
        $response = $this->getJson('/api/sellers');

        // Verificar se a resposta é 200 OK
        $response->assertStatus(200);

        // Verificar se a resposta tem a estrutura correta
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'sellers'
            ]
        ]);

        // Verificar se a resposta contém uma lista vazia de vendedores
        $response->assertJson([
            'success' => true,
            'message' => 'Vendedores listados com sucesso.',
            'data' => [
                'sellers' => []
            ]
        ]);
    }
}
