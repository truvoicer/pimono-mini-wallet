<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $timezone
 * @property int $currency_id
 * * @property-read Currency|null $currency
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'timezone',
        'currency_id',
    ];

    public $timestamps = false;

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
