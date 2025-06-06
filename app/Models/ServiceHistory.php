<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceHistory extends Model
{
    use HasFactory;
    
    protected $table = 'service_histories';
    
    protected $fillable = [
        'car_id',
        'date',
        'shop_name',
        'services',
        'notes',
    ];
    
    protected $casts = [
        'date' => 'datetime',
        'services' => 'array',
    ];
    
    // For API compatibility
    protected $appends = ['shopName', 'carDetails', 'createdAt', 'updatedAt'];
    
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
    
    // API compatibility accessors
    public function getShopNameAttribute()
    {
        return $this->attributes['shop_name'] ?? null;
    }
    
    public function getCarDetailsAttribute()
    {
        $car = $this->car;
        return [
            'id' => $car->id,
            'name' => $car->name,
            'brand' => $car->brand,
            'model' => $car->model
        ];
    }
    
    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->toIso8601String();
    }
    
    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->toIso8601String();
    }
} 