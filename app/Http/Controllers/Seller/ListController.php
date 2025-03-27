<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Services\SellerService;
use Illuminate\Http\Request;

class ListController extends Controller
{
    protected SellerService $sellerService;

    public function __construct(SellerService $sellerService)
    {
        $this->sellerService = $sellerService;
    }

    public function __invoke(Request $request)
    {
        return $this->sellerService->all();
    }
}
