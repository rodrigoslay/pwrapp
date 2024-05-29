<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'license_plate',
        'client_id',
        'registration_date',
        'photo',
        'brand_id',
        'model',
        'chassis',
        'color',
        'kilometers',
        'created_by',
        'updated_by',
        'status',
    ];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_vehicle')
                    ->withTimestamps();
    }

    /**
     * Get the client that owns the vehicle.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the brand that the vehicle belongs to.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the user who created the vehicle.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the vehicle.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
