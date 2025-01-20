<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SetMenu extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'thumbnail',
        'price_per_person',
        'min_spend',
        'status',
        'is_vegan',
        'is_vegetarian',
        'is_halal',
        'is_kosher',
        'is_seated',
        'is_standing',
        'is_canape',
        'is_mixed_dietary',
        'number_of_orders',
        'display_text',
        'created_at'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_vegan' => 'boolean',
        'is_vegetarian' => 'boolean',
        'is_halal' => 'boolean',
        'is_kosher' => 'boolean',
        'is_seated' => 'boolean',
        'is_standing' => 'boolean',
        'is_canape' => 'boolean',
        'is_mixed_dietary' => 'boolean',
        'display_text' => 'boolean',
        'price_per_person' => 'decimal:2',
        'min_spend' => 'decimal:2',
    ];

    public function cuisines(): BelongsToMany
    {
        return $this->belongsToMany(Cuisine::class);
    }
}
