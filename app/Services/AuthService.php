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
                'token' => $token
            ], 'Login realizado com sucesso');
        } catch (\Exception $exception) {
            dd($exception);
            return $this->errorResponse('Erro ao realizar login', $exception->getCode(), $exception->errors());
        }
    }
}
