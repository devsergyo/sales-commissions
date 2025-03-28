<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se a API de logout retorna o formato de resposta correto.
     *
     * @return void
     */
    public function test_logout_returns_correct_response_format()
    {
        // Criar um usuário
        $user = User::factory()->create();
        
        // Autenticar o usuário
        Sanctum::actingAs($user);

        // Fazer a requisição para a API
        $response = $this->postJson('/api/auth/logout');

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
            'success' => true,
            'message' => 'Logout realizado com sucesso',
            'data' => []
        ]);
    }

    /**
     * Testa se a API de logout requer autenticação.
     *
     * @return void
     */
    public function test_logout_requires_authentication()
    {
        // Fazer a requisição para a API sem autenticação
        $response = $this->postJson('/api/auth/logout');

        // Verificar se a resposta é 401 Unauthorized
        $response->assertStatus(401);

        // Verificar se a resposta tem a estrutura correta
        $response->assertJsonStructure([
            'message'
        ]);
    }

    /**
     * Testa se o usuário não pode acessar rotas protegidas após o logout.
     *
     * @return void
     */
    public function test_user_cannot_access_protected_routes_after_logout()
    {
        // Criar um usuário e autenticar
        $user = User::factory()->create();
        
        // Autenticar o usuário
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // Verificar que o usuário pode acessar uma rota protegida
        $beforeResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
                               ->getJson('/api/sellers');
        $beforeResponse->assertStatus(200);
        
        // Fazer logout
        $this->withHeader('Authorization', 'Bearer ' . $token)
             ->postJson('/api/auth/logout');
             
        // Criar um novo cliente HTTP para simular uma nova sessão
        $this->refreshApplication();
        
        // Tentar acessar a rota protegida novamente
        $afterResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
                              ->getJson('/api/sellers');
                              
        // A resposta deve ser 401 Unauthorized
        $afterResponse->assertStatus(401);
    }
}
