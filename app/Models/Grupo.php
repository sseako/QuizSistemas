<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grupo extends Model
{
    /** @use HasFactory<\Database\Factories\GrupoFactory> */
    use HasFactory;

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
}

public function docentes(): BelongsToMany
{
    return $this->belongsToMany(Docente::class);
}

public function estudiante(): BelongsToMany
{
    return $this->belongsToMany(Estudiante::class)->withPivot('nota');

}
}
