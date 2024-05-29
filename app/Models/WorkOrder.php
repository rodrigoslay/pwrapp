<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'client_id',
        'created_by',
        'executive_id',
        'status',
        'subtotal',
        'tax',
        'total',
        'review',
        'entry_mileage',
        'exit_mileage',
        'revisiones',
    ];

    public function services()
{
    return $this->belongsToMany(Service::class, 'service_work_order', 'work_order_id', 'service_id')
                ->withPivot('mechanic_id', 'status')
                ->with(['mechanic']);
}

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_work_order', 'work_order_id', 'product_id')
                    ->withPivot('quantity', 'status'); // Asegúrate de incluir 'quantity' y 'status' si están en la tabla pivot
    }

    public function mechanics()
{
    return $this->belongsToMany(User::class, 'mechanic_work_order', 'work_order_id', 'user_id')
                ->withPivot('service_id')
                ->select('users.id', 'users.name'); // Especifica las columnas que necesitas
}

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function incidents()
{
    return $this->belongsToMany(Incident::class, 'incident_work_order', 'work_order_id', 'incident_id')
                ->withPivot('observation', 'approved', 'reported_by', 'approved_by')
                ->with(['mechanic' => function($query) {
                    $query->select('id', 'name');
                }]);
}

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function executive()
    {
        return $this->belongsTo(User::class, 'executive_id');
    }
}
