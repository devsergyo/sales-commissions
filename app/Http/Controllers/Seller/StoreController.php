<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreSellerRequest;
use App\Services\SellerService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected SellerService $sellerService;

    public function __construct(SellerService $sellerService)
    {
        $this->sellerService = $sellerService;
    }

    public function __invoke(StoreSellerRequest $request)
    {
        return $this->sellerService->store($request->validated());
    }
}
