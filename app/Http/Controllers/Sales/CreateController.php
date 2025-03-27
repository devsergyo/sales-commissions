<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\CreateSaleRequest;
use App\Services\SaleService;

class CreateController extends Controller
{
    protected $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    public function __invoke(CreateSaleRequest $request)
    {
        return $this->saleService->create($request->validated());
    }
}
