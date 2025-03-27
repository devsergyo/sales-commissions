<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * Retorna uma resposta de sucesso padronizada.
     */
    protected function successResponse($data, string $message = 'Operação realizada com sucesso', int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Retorna uma resposta de erro padronizada.
     */
    protected function errorResponse(string $message, int $statusCode = 400, $errors = [])
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}
