<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Year extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['released'];

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
    }
}
