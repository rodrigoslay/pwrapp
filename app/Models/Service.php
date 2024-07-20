<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'discount_applicable',
        'status',
        'created_by',
        'updated_by',
    ];

    public function mechanics()
    {
        return $this->belongsToMany(User::class, 'service_work_order', 'service_id', 'mechanic_id')
                    ->withTimestamps();
    }

    public function workOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'service_work_order')
                    ->withPivot('mechanic_id', 'status')
                    ->withTimestamps();
    }
};
