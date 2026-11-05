<?php

namespace App\Models;

use Database\Factories\GrupoFactory;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grupo extends Model
{
    protected $guarded = [];

    protected $casts = [
        'abierto' => 'boolean', ];

    /** @use HasFactory<GrupoFactory> */
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
        return $this->belongsToMany(Estudiante::class, 'estudiante_matriculados')->withPivot('nota');

    }

    public function asignarDocente($codEmpleado): void
    {
        if ($this->docentes()->count() >= 2) {
            throw new DomainException;
        }
        $this->docentes()->attach($codEmpleado);
    }

    public function puedeAbrirse(): bool
    {
        $tieneDocentes = $this->docentes()->count() >= 1;
        $tieneCupoMinimo = $this->estudiante()->count() >= 15;

        return $tieneDocentes && $tieneCupoMinimo;
    }

    public function abrir(): void
    {
        if (! $this->puedeAbrirse()) {
            throw new DomainException;
        }
        $this->abierto = true;
        $this->save();
    }
}
