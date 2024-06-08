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
        'updated_by'
    ];

    public function faults()
{
    return $this->belongsToMany(RevisionFault::class, 'revision_work_order', 'revision_id', 'fault_id')
                ->withPivot('work_order_id', 'status')
                ->distinct();
}


public function workOrders()
{
    return $this->belongsToMany(WorkOrder::class, 'revision_work_order', 'revision_id', 'work_order_id')
                ->withPivot('fault_id', 'status');
}


}
