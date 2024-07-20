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
        'scheduling', // Cambio aquí
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_work_order')
                    ->withPivot('mechanic_id', 'status')
                    ->withTimestamps();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_work_order')
                    ->withPivot('quantity', 'status')
                    ->withTimestamps();
    }

    public function revisions()
    {
        return $this->belongsToMany(Revision::class, 'revision_work_order')
                    ->withPivot('fault_id', 'status')
                    ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function executive()
    {
        return $this->belongsTo(User::class, 'executive_id');
    }

    public function incidents()
    {
        return $this->belongsToMany(Incident::class, 'incident_work_order')
                    ->withPivot('observation', 'reported_by', 'approved', 'approved_by')
                    ->withTimestamps();
    }
}
