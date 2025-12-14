<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickTrigger extends Model
{
    use HasFactory;

    protected $fillable = [
        'quick_trigger_category_id',
        'emoji',
        'action',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(QuickTriggerCategory::class, 'quick_trigger_category_id');
    }
}
