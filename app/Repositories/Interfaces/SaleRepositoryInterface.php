<?php

namespace App\Repositories\Interfaces;

use App\Models\Sale;
use Carbon\Carbon;

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
     * Get sales by date.
     *
     * @param Carbon $date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDate(Carbon $date);
    
    /**
     * Get all sales by date.
     *
     * @param Carbon $date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllByDate(Carbon $date);
    
    /**
     * Get sales by seller ID and date.
     *
     * @param int $sellerId
     * @param Carbon $date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySellerAndDate(int $sellerId, Carbon $date);
    
    /**
     * Create a new sale.
     *
     * @param array $data
     * @return Sale
     */
    public function create(array $data): Sale;
}
