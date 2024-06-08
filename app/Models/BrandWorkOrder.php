<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandWorkOrder extends Model
{
    use HasFactory;

    protected $fillable = ['work_order_id', 'brand_id', 'car_model_id', 'year'];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function carModel()
    {
        return $this->belongsTo(CarModel::class);
    }
}
