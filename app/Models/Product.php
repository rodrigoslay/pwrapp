<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sku',
        'name',
        'price',
        'discounted_price',
        'inventory',
        'created_by',
        'updated_by',
        'status',
    ];

    public function workOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'product_work_order')
                    ->withPivot('quantity', 'status')
                    ->withTimestamps();
    }
}
