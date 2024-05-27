<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = ['order_number', 'description', 'status', 'created_by', 'updated_by'];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
