<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Docente extends Model
{
    /** @use HasFactory<\Database\Factories\DocenteFactory> */
    use HasFactory;

    public function estudiantesConsejero(): HasMany
    {
        return $this->hasMany(Estudiante::class);
    }

    public function cursoImpartidos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class);
    }
}

