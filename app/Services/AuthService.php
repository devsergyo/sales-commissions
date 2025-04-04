<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    use ApiResponseTrait;

    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login($credentials)
    {
        try {
            if (!Auth::attempt($credentials)) {
                return $this->errorResponse('As credenciais fornecidas estão incorretas.', 401);
            }

            $user = $this->userRepository->findByEmail($credentials['email']);

            if (empty($user->email_verified_at)) {
                return $this->errorResponse(
                    'Email não verificado. Por favor, verifique seu email antes de fazer login.',
                    403
                );
            }

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                "id"=> $user->id,
                "name"=> "{$user->name} {$user->last_name}",
                "email"=> $user->email,
                'token' => $token
            ], 'Login realizado com sucesso');
        } catch (\Exception $exception) {
            return $this->errorResponse('Erro ao realizar login', $exception->getCode(), $exception->errors());
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Usuário não autenticado.', 401);
            }

            $request->user()->currentAccessToken()->delete();
            return $this->successResponse([], 'Logout realizado com sucesso');
        } catch (\Exception $exception) {
            return $this->errorResponse('Erro ao realizar logout', $exception->getCode(), $exception->errors());
        }
    }


}
