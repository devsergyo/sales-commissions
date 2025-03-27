<?php

namespace App\Repositories\Eloquent;

use App\Models\Seller;
use App\Repositories\Interfaces\SellerRepositoryInterface;

/**
 *
 */
class SellerRepository implements SellerRepositoryInterface
{
    public function all()
    {
        return Seller::all();
    }

    public function find($id)
    {
        return Seller::find($id);
    }

    public function findByEmail($email)
    {
        return Seller::where('email', $email)->first();
    }

    public function create(array $data)
    {
        return Seller::create($data);
    }

    public function update($id, array $data)
    {
        $user = Seller::find($id);
        if ($user) {
            $user->update($data);
            return $user;
        }
        return null;
    }

    public function delete($id)
    {
        $user = Seller::find($id);
        if ($user) {
            return $user->delete();
        }
        return false;
    }
}
