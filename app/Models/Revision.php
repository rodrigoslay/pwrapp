<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    public function faults()
    {
        return $this->hasMany(RevisionFault::class, 'revision_id');
    }

    public function workOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'revision_work_order', 'revision_id', 'work_order_id')
                    ->withPivot('fault_id', 'status')
                    ->withTimestamps();
    }
}
