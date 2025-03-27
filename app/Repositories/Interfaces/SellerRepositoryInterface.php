<?php

namespace App\Repositories\Interfaces;

interface SellerRepositoryInterface
{
    public function all();
    public function find($id);
    public function findByEmail($email);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
