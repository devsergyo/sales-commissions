<?php

namespace App\Repositories\Eloquent;

use App\Models\Sale;
use App\Repositories\Interfaces\SaleRepositoryInterface;

class SaleRepository implements SaleRepositoryInterface
{
    /**
     * @var Sale
     */
    protected $model;

    /**
     * SaleRepository constructor.
     *
     * @param Sale $model
     */
    public function __construct(Sale $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new sale.
     *
     * @param array $data
     * @return Sale
     */
    public function create(array $data): Sale
    {
        return $this->model->create($data);
    }
}
