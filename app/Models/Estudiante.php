<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Estudiante extends Model
{
    /** @use HasFactory<\Database\Factories\EstudianteFactory> */
    use HasFactory;

    public function consejero(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }

    public function grupo(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class)->withPivot('nota');
}

}
