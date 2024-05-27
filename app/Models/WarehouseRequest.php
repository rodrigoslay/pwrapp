<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'work_order_id',
        'quantity',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
