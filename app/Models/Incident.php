<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'created_by',
        'updated_by',
    ];

    public function workOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'incident_work_order')
                    ->withPivot('reported_by', 'approved', 'approved_by', 'observation')
                    ->withTimestamps();
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
};
