<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'rut',
        'email',
        'phone',
        'client_group_id',
        'status',
    ];

 
    /**
     * Get the client group that the client belongs to.
     */
    public function clientGroup()
    {
        return $this->belongsTo(ClientGroup::class);
    }
}
