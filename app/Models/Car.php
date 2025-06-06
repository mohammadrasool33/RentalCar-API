<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Car extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'brand',
        'model',
        'description',
        'year',
        'price_per_day',
        'price_per_week',
        'price_per_month',
        'current_mileage',
        'is_available'
    ];

    // For JSON formatting
    protected $casts = [
        'is_available' => 'boolean',
        'current_mileage' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // For API compatibility with the JSON structure
    protected $appends = ['pricePerDay', 'pricePerWeek', 'pricePerMonth', 'imageUrl', 'isAvailable', 'createdAt', 'updatedAt'];

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }
    
    public function serviceHistory(): HasMany
    {
        return $this->hasMany(ServiceHistory::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }
    
    // API compatibility accessors
    public function getPricePerDayAttribute()
    {
        return $this->attributes['price_per_day'] ?? 0;
    }
    
    public function getPricePerWeekAttribute()
    {
        return $this->attributes['price_per_week'] ?? 0;
    }
    
    public function getPricePerMonthAttribute()
    {
        return $this->attributes['price_per_month'] ?? 0;
    }
    
    public function getImageUrlAttribute()
    {
        $image = $this->images()->first();
        return $image ? url('storage/' . $image->path) : null;
    }
    
    public function getIsAvailableAttribute()
    {
        return $this->attributes['is_available'] ?? true;
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
