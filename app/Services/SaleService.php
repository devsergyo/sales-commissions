<?php

namespace App\Services;

use App\Models\Sale;
use App\Repositories\Interfaces\SaleRepositoryInterface;

class SaleService
{
    /**
     * @var SaleRepositoryInterface
     */
    protected $saleRepository;

    /**
     * SaleService constructor.
     *
     * @param SaleRepositoryInterface $saleRepository
     */
    public function __construct(SaleRepositoryInterface $saleRepository)
    {
        $this->saleRepository = $saleRepository;
    }

    /**
     * Create a new sale.
     *
     * @param array $data
     * @return Sale
     */
    public function create(array $data): Sale
    {
        return $this->saleRepository->create($data);
    }
}
