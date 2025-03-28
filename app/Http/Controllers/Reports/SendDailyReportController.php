<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\SendDailyReportRequest;
use App\Models\Seller;
use App\Services\DailyReportService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class SendDailyReportController extends Controller
{
    use ApiResponseTrait;

    protected DailyReportService $dailyReportService;

    public function __construct(DailyReportService $dailyReportService)
    {
        $this->dailyReportService = $dailyReportService;
    }

    /**
     * Processa a solicitação de envio de relatório de vendas.
     *
     * @param SendDailyReportRequest $request
     * @param int|null $seller
     * @return JsonResponse
     */
    public function __invoke(SendDailyReportRequest $request, int $seller = null): JsonResponse
    {
        try {
            $result = $this->dailyReportService->processReportRequest($request->validated(['date']), $seller);

            if ($result['success']) {
                return $this->successResponse(
                    $result['data'],
                    $result['message']
                );
            }

            return $this->errorResponse(
                $result['message'],
                500,
                $result['error']
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erro ao enviar relatórios',
                500,
                $e->getMessage()
            );
        }
    }
}
