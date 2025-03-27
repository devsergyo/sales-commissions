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
     * Get all sales.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->model->with('seller')->get();
    }

    /**
     * Get sales by seller ID.
     *
     * @param int $sellerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySeller(int $sellerId)
    {
        return $this->model->where('seller_id', $sellerId)->with('seller')->get();
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
