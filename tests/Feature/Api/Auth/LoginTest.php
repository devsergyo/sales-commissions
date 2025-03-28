<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se a API de login retorna o formato de resposta correto com token.
     *
     * @return void
     */
    public function test_login_returns_token_with_correct_format()
    {
        // Criar um usuário
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // Preparar dados de login
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        // Fazer a requisição para a API
        $response = $this->postJson('/api/auth/login', $loginData);

        // Verificar se a resposta é 200 OK
        $response->assertStatus(200);

        // Verificar se a resposta tem a estrutura correta
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token'
            ]
        ]);

        // Verificar se a resposta contém os valores esperados
        $response->assertJson([
            'success' => true,
            'message' => 'Login realizado com sucesso'
        ]);

        // Verificar se o token não está vazio
        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Testa se a API de login retorna erro com credenciais inválidas.
     *
     * @return void
     */
    public function test_login_fails_with_invalid_credentials()
    {
        // Criar um usuário
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // Preparar dados de login inválidos
        $invalidLoginData = [
            'email' => 'test@example.com',
            'password' => 'wrong_password'
        ];

        // Fazer a requisição para a API
        $response = $this->postJson('/api/auth/login', $invalidLoginData);

        // Verificar se a resposta é 401 Unauthorized
        $response->assertStatus(401);

        // Verificar se a resposta tem a estrutura correta
        $response->assertJsonStructure([
            'success',
            'message'
        ]);

        // Verificar se a resposta contém os valores esperados
        $response->assertJson([
            'success' => false
        ]);
    }

    /**
     * Testa se a API de login valida os campos obrigatórios.
     *
     * @return void
     */
    public function test_login_validates_required_fields()
    {
        // Fazer a requisição para a API com campos faltando
        $response = $this->postJson('/api/auth/login', []);

        // Verificar se a resposta é 422 Unprocessable Entity
        $response->assertStatus(422);

        // Verificar se a resposta contém erros de validação
        $response->assertJsonValidationErrors(['email', 'password']);
    }
}
