<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionFault extends Model
{
    use HasFactory;

    protected $fillable = [
        'revision_id',
        'fallo',
        'solucion',
        'recomendacion',
        'created_by',
        'updated_by',
    ];

    public function revision()
    {
        return $this->belongsTo(Revision::class, 'revision_id');
    }

    /*public function workOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'revision_work_order', 'fault_id', 'work_order_id')
                    ->withPivot('revision_id', 'status')
                    ->withTimestamps();
    }*/
}
