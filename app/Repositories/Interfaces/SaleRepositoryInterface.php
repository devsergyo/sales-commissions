<?php

namespace App\Repositories\Interfaces;

use App\Models\Sale;

interface SaleRepositoryInterface
{
    /**
     * Get all sales.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();
    
    /**
     * Get sales by seller ID.
     *
     * @param int $sellerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySeller(int $sellerId);
    
    /**
     * Create a new sale.
     *
     * @param array $data
     * @return Sale
     */
    public function create(array $data): Sale;
}
