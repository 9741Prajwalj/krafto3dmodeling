<?php

namespace Modules\Shipping\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Setup\Entities\City;
use Modules\Setup\Entities\Country;
use Modules\Setup\Entities\State;

class PickupLocation extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'pickup_location' => 'string',
        'name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'address' => 'string',
        'address_2' => 'string',
        'city_id' => 'integer',
        'state_id' => 'integer',
        'country_id' => 'integer',
        'pin_code' => 'integer',
        'status' => 'integer',
        'is_default' => 'integer',
    ];

    protected $fillable = [
        'pickup_location',
        'name',
        'email',
        'phone',
        'address',
        'address_2',
        'city_id',
        'state_id',
        'country_id',
        'pin_code',
        'lat',
        'long',
        'status',
        'is_set',
        'is_default',
        'created_by',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id')->withDefault();
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id')->withDefault();
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id')->withDefault();
    }
}
