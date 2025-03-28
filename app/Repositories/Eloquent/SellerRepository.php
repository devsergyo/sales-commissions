<?php

namespace App\Repositories\Eloquent;

use App\Models\Seller;
use App\Repositories\Interfaces\SellerRepositoryInterface;

/**
 * Repositório para gerenciar vendedores
 */
class SellerRepository implements SellerRepositoryInterface
{
    /**
     * @var Seller
     */
    protected $model;

    /**
     * SellerRepository constructor.
     *
     * @param Seller $model
     */
    public function __construct(Seller $model)
    {
        $this->model = $model;
    }

    /**
     * Retorna todos os vendedores
     * 
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(array $columns = ['*'])
    {
        return $this->model->select($columns)->get();
    }

    /**
     * Encontra um vendedor pelo ID
     * 
     * @param int $id
     * @param array $columns Colunas a serem selecionadas
     * @return Seller|null
     */
    public function find($id, array $columns = ['*'])
    {
        return $this->model->select($columns)->find($id);
    }

    /**
     * Encontra um vendedor pelo e-mail
     * 
     * @param string $email
     * @param array $columns Colunas a serem selecionadas
     * @return Seller|null
     */
    public function findByEmail($email, array $columns = ['*'])
    {
        return $this->model->select($columns)
            ->where('email', $email)
            ->first();
    }

    /**
     * Cria um novo vendedor
     * 
     * @param array $data
     * @return Seller
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Atualiza um vendedor existente
     * 
     * @param int $id
     * @param array $data
     * @return Seller|null
     */
    public function update($id, array $data)
    {
        $seller = $this->find($id);
        if ($seller) {
            $seller->update($data);
            return $seller;
        }
        return null;
    }

    /**
     * Remove um vendedor
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $seller = $this->find($id);
        if ($seller) {
            return $seller->delete();
        }
        return false;
    }
    
    /**
     * Retorna os vendedores com vendas no período especificado
     * 
     * @param string $startDate Data inicial no formato Y-m-d
     * @param string $endDate Data final no formato Y-m-d
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSellersWithSalesInPeriod($startDate, $endDate)
    {
        return $this->model->select('sellers.*')
            ->join('sales', 'sellers.id', '=', 'sales.seller_id')
            ->whereDate('sales.sale_date', '>=', $startDate)
            ->whereDate('sales.sale_date', '<=', $endDate)
            ->groupBy('sellers.id')
            ->get();
    }
}
