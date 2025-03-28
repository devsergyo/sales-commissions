<?php

namespace App\Repositories\Interfaces;

use App\Models\Sale;
use Carbon\Carbon;

interface SaleRepositoryInterface
{
    /**
     * Retorna todas as vendas.
     *
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(array $columns = ['*']);
    
    /**
     * Retorna as vendas por ID do vendedor.
     *
     * @param int $sellerId
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySeller(int $sellerId, array $columns = ['*']);
    
    /**
     * Retorna as vendas por data.
     *
     * @param Carbon $date
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDate(Carbon $date, array $columns = ['*']);
    
    /**
     * Retorna todas as vendas por data.
     *
     * @param Carbon $date
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllByDate(Carbon $date, array $columns = ['*']);
    
    /**
     * Retorna as vendas por ID do vendedor e data.
     *
     * @param int $sellerId
     * @param Carbon $date
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySellerAndDate(int $sellerId, Carbon $date, array $columns = ['*']);
    
    /**
     * Cria uma nova venda.
     *
     * @param array $data
     * @return Sale
     */
    public function create(array $data): Sale;
    
    /**
     * Retorna as vendas em um período específico.
     *
     * @param string $startDate Data inicial no formato Y-m-d
     * @param string $endDate Data final no formato Y-m-d
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByPeriod(string $startDate, string $endDate, array $columns = ['*']);
    
    /**
     * Retorna as vendas por ID do vendedor em um período específico.
     *
     * @param int $sellerId
     * @param string $startDate Data inicial no formato Y-m-d
     * @param string $endDate Data final no formato Y-m-d
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySellerAndPeriod(int $sellerId, string $startDate, string $endDate, array $columns = ['*']);
}
