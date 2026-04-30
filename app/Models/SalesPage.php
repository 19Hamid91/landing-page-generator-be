<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'product_description',
        'target_audience',
        'price',
        'features',
        'usp',
        'ai_output',
        'template_name',
        'images',
        'language',
        'currency',
    ];

    /**
     * Cast JSON columns to arrays automatically.
     */
    protected $casts = [
        'features'   => 'array',
        'usp'        => 'array',
        'images'     => 'array',
        'ai_output'  => 'array',
        'price'      => 'decimal:2',
    ];

    /**
     * Belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
