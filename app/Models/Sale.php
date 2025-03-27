<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Seller;

class Sale extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'seller_id',
        'amount',
        'commission',
        'sale_date'
    ];
    
    protected $casts = [
        'sale_date' => 'date',
        'amount' => 'decimal:2',
        'commission' => 'decimal:2'
    ];
    
    /**
     * Get the seller that owns the sale.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
