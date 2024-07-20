<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'model',
        'year'
    ];

    /**
     * Get the brand that owns the car model.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
};
