<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AiProviderKey extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'model',
        'name',
        'api_key',
        'is_default',
        'total_queries',
    ];

    protected $attributes = [
        'total_queries' => 0,
    ];

    public function getApiKeyAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'is_default' => 'boolean',
        'total_queries' => 'integer',
    ];

    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = Crypt::encryptString($value);
    }
}
