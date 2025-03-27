<?php

namespace App\Services;

use App\Repositories\Interfaces\SellerRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;

class SellerService
{
    use ApiResponseTrait;

    protected SellerRepositoryInterface $sellerRepository;

    public function __construct(SellerRepositoryInterface $sellerRepository)
    {
        $this->sellerRepository = $sellerRepository;
    }

    public function all()
    {
        try {
            $sellers = $this->sellerRepository->all();

            return $this->successResponse([
                'sellers' => $sellers
            ], 'Vendedores listados com sucesso.');
        } catch (\Exception $e) {
            return $this->errorResponse('Erro ao listar vendedores',  500, $e->getMessage());
        }
    }

    public function store(array $data)
    {
        try {
            DB::beginTransaction();

            $seller = $this->sellerRepository->create($data);

            DB::commit();

            return $this->successResponse([
                'seller' => $seller
            ], 'Vendedor cadastrado com sucesso.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Erro ao cadastrar vendedor', $e->getCode(), $e->errors());
        }
    }

}
