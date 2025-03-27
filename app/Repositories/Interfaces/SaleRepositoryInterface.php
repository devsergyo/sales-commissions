<?php

namespace App\Repositories\Interfaces;

use App\Models\Sale;

interface SaleRepositoryInterface
{
    /**
     * Create a new sale.
     *
     * @param array $data
     * @return Sale
     */
    public function create(array $data): Sale;
}
