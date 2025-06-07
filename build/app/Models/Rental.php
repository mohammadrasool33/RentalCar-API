<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rental extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'car_id',
        'duration_type',
        'duration_count',
        'price_rate',
        'renter_name',
        'renter_phone',
        'passport_number',
        'pickup_location',
        'return_location',
        'rental_start_date',
        'rental_end_date',
        'return_date',
        'total_price',
        'discount_amount',
        'final_price',
        'additional_charges',
        'final_total',
        'mileage_at_rental',
        'mileage_at_return',
        'is_active',
        'is_paid',
        'comments',
        'pickup_service_check',
        'return_service_check'
    ];
    
    protected $casts = [
        'rental_start_date' => 'datetime',
        'rental_end_date' => 'datetime',
        'return_date' => 'datetime',
        'is_active' => 'boolean',
        'is_paid' => 'boolean',
        'pickup_service_check' => 'array',
        'return_service_check' => 'array'
    ];
    
    // For API compatibility with the JSON structure
    protected $appends = [
        'renterName', 'renterPhone', 'passportNumber', 
        'pickupLocation', 'returnLocation', 'rentalStartDate', 
        'rentalEndDate', 'durationType', 'durationCount', 
        'priceRate', 'totalPrice', 'discountAmount', 
        'finalPrice', 'finalTotal', 'mileageAtRental', 'mileageAtReturn', 
        'isActive', 'isPaid', 'createdAt', 'updatedAt',
        'carDetails'
    ];
    
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
    
    // API compatibility accessors
    public function getRenterNameAttribute()
    {
        return $this->attributes['renter_name'] ?? null;
    }
    
    public function getRenterPhoneAttribute()
    {
        return $this->attributes['renter_phone'] ?? null;
    }
    
    public function getPassportNumberAttribute()
    {
        return $this->attributes['passport_number'] ?? null;
    }
    
    public function getPickupLocationAttribute()
    {
        return $this->attributes['pickup_location'] ?? null;
    }
    
    public function getReturnLocationAttribute()
    {
        return $this->attributes['return_location'] ?? null;
    }
    
    public function getRentalStartDateAttribute($value)
    {
        return isset($this->attributes['rental_start_date']) ? \Carbon\Carbon::parse($this->attributes['rental_start_date'])->toIso8601String() : null;
    }
    
    public function getRentalEndDateAttribute($value)
    {
        return isset($this->attributes['rental_end_date']) ? \Carbon\Carbon::parse($this->attributes['rental_end_date'])->toIso8601String() : null;
    }
    
    public function getDurationTypeAttribute()
    {
        return $this->attributes['duration_type'] ?? null;
    }
    
    public function getDurationCountAttribute()
    {
        return (int)($this->attributes['duration_count'] ?? 0);
    }
    
    public function getPriceRateAttribute()
    {
        return $this->attributes['price_rate'] ?? null;
    }
    
    public function getTotalPriceAttribute($value)
    {
        return (float)($this->attributes['total_price'] ?? 0);
    }
    
    public function getDiscountAmountAttribute()
    {
        return (float)($this->attributes['discount_amount'] ?? 0);
    }
    
    public function getFinalPriceAttribute()
    {
        return (float)($this->attributes['final_price'] ?? 0);
    }
    
    public function getFinalTotalAttribute()
    {
        return (float)($this->attributes['final_total'] ?? 0);
    }
    
    public function getMileageAtRentalAttribute()
    {
        return $this->attributes['mileage_at_rental'] ?? null;
    }
    
    public function getMileageAtReturnAttribute()
    {
        return $this->attributes['mileage_at_return'] ?? null;
    }
    
    public function getIsActiveAttribute()
    {
        return (bool)($this->attributes['is_active'] ?? false);
    }
    
    public function getIsPaidAttribute()
    {
        return (bool)($this->attributes['is_paid'] ?? false);
    }
    
    public function getCarDetailsAttribute()
    {
        $car = $this->car;
        return [
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
