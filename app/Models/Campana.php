<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campana extends Model
{
    public function donaciones(): HasMany
    {
        return $this->hasMany(Donacion::class, 'campana_id');
    }
}
