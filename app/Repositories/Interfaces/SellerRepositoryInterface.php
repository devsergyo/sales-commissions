<?php

namespace App\Repositories\Interfaces;

interface SellerRepositoryInterface
{
    /**
     * Retorna todos os vendedores
     * 
     * @param array $columns Colunas a serem selecionadas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(array $columns = ['*']);
    
    /**
     * Encontra um vendedor pelo ID
     * 
     * @param int $id
     * @param array $columns Colunas a serem selecionadas
     * @return \App\Models\Seller|null
     */
    public function find($id, array $columns = ['*']);
    
    /**
     * Encontra um vendedor pelo e-mail
     * 
     * @param string $email
     * @param array $columns Colunas a serem selecionadas
     * @return \App\Models\Seller|null
     */
    public function findByEmail($email, array $columns = ['*']);
    
    /**
     * Cria um novo vendedor
     * 
     * @param array $data
     * @return \App\Models\Seller
     */
    public function create(array $data);
    
    /**
     * Atualiza um vendedor existente
     * 
     * @param int $id
     * @param array $data
     * @return \App\Models\Seller|null
     */
    public function update($id, array $data);
    
    /**
     * Remove um vendedor
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id);
    
    /**
     * Retorna os vendedores com vendas no período especificado
     * 
     * @param string $startDate Data inicial no formato Y-m-d
     * @param string $endDate Data final no formato Y-m-d
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSellersWithSalesInPeriod($startDate, $endDate);
}
