<?php

namespace App\Repositories\Eloquent;

use App\Models\Sale;
use App\Repositories\Interfaces\SaleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
     * Retorna todas as vendas.
     *
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(array $columns = ['*'])
    {
        return $this->model->select($columns)->with(['seller:id,first_name,last_name,email'])->get();
    }

    /**
     * Retorna as vendas por ID do vendedor.
     *
     * @param int $sellerId
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySeller(int $sellerId, array $columns = ['*'])
    {
        return $this->model->select($columns)
            ->where('seller_id', $sellerId)
            ->with(['seller:id,first_name,last_name,email'])
            ->get();
    }

    /**
     * Retorna as vendas por data.
     *
     * @param Carbon $date
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDate(Carbon $date, array $columns = ['*'])
    {
        return $this->model->select($columns)
            ->whereDate('sale_date', $date->format('Y-m-d'))
            ->with(['seller:id,first_name,last_name,email'])
            ->get();
    }

    /**
     * Retorna todas as vendas por data.
     *
     * @param Carbon $date
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllByDate(Carbon $date, array $columns = ['*'])
    {
        return $this->model->select($columns)
            ->whereDate('sale_date', $date->format('Y-m-d'))
            ->with(['seller:id,first_name,last_name,email'])
            ->get();
    }

    /**
     * Retorna as vendas por ID do vendedor e data.
     *
     * @param int $sellerId
     * @param Carbon $date
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySellerAndDate(int $sellerId, Carbon $date, array $columns = ['*'])
    {
        return $this->model->select($columns)
            ->where('seller_id', $sellerId)
            ->whereDate('sale_date', $date->format('Y-m-d'))
            ->with(['seller:id,first_name,last_name,email'])
            ->get();
    }

    /**
     * Cria uma nova venda.
     *
     * @param array $data
     * @return Sale
     */
    public function create(array $data): Sale
    {
        return $this->model->create($data);
    }
    
    /**
     * Retorna as vendas em um período específico.
     *
     * @param string $startDate Data inicial no formato Y-m-d
     * @param string $endDate Data final no formato Y-m-d
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByPeriod(string $startDate, string $endDate, array $columns = ['*'])
    {
        return $this->model->select($columns)
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->with(['seller:id,first_name,last_name,email'])
            ->get();
    }
    
    /**
     * Retorna as vendas por ID do vendedor em um período específico.
     *
     * @param int $sellerId
     * @param string $startDate Data inicial no formato Y-m-d
     * @param string $endDate Data final no formato Y-m-d
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySellerAndPeriod(int $sellerId, string $startDate, string $endDate, array $columns = ['*'])
    {
        return $this->model->select($columns)
            ->where('seller_id', $sellerId)
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->with(['seller:id,first_name,last_name,email'])
            ->get();
    }
}
