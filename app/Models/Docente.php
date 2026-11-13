<?php

namespace App\Models;

use Database\Factories\DocenteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    protected $guarded = [];

    /** @use HasFactory<DocenteFactory> */
    use HasFactory;

    public function estudiantesConsejero(): HasMany
    {
        return $this->estudiantes();
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'docente_id');
    }

    public function cursoImpartidos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class);
    }
}
