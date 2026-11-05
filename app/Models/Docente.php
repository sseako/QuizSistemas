<?php

namespace App\Models;

use Database\Factories\DocenteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Docente extends Model
{
    protected $guarded = [];

    /** @use HasFactory<DocenteFactory> */
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
