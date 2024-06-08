<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionWorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'revision_id',
        'fault_id',
        'work_order_id',
        'status',
    ];

    public function revision()
    {
        return $this->belongsTo(Revision::class);
    }

    public function fault()
    {
        return $this->belongsTo(RevisionFault::class);
    }
}

