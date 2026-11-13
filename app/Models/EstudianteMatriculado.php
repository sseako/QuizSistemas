<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EstudianteMatriculado extends Pivot
{
    protected $table = 'estudiante_matriculados'; 
    protected $casts = [
        'nota' => 'float',
    ];

    /** @use HasFactory<\Database\Factories\EstudianteMatriculadoFactory> */
    use HasFactory;
}
