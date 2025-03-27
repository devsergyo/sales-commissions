<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Services\SaleService;
use Illuminate\Http\Request;

class ListBySeller extends Controller
{
    protected SaleService $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    public function __invoke(Request $request, Seller $seller)
    {
        return $this->saleService->getBySeller($seller->id);
    }
}
