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
        'created_by',
        'updated_by'
    ];

    public function revisions()
    {
        return $this->belongsToMany(Revision::class, 'revision_work_order', 'fault_id', 'revision_id')
                    ->withPivot('work_order_id', 'status');
    }

}
