<?php

namespace App\Models;

use Database\Factories\EstudianteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Estudiante extends Model
{
    protected $guarded = [];

    /** @use HasFactory<EstudianteFactory> */
    use HasFactory;

    public function consejero(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }

    public function grupo(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'estudiante_matriculados')->withPivot('nota');
    }
}
