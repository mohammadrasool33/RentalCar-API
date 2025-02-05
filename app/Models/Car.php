<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{

    protected $fillable = [
        'name', 'model', 'daily_price', 'weekly_price', 'monthly_price','brand','year','is_available'
    ];
    public function rentals():HasMany
    {
        return $this->hasMany(Rental::class);
    }
    public function images(){
        return $this->hasMany(Image::class);
    }
}
