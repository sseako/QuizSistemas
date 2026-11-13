<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;


test('Caso 1: Registrar un grupo con toda su información y su curso', function () {
    $curso = Curso::create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $grupo = Grupo::create([
        'curso_id' => $curso->id,
        'numeroCiclo' => '2',
        'ano' => '2026',
        'numeroGrupo' => '1',
        'abierto' => true,
    ]);

    $grupoRecuperado = Grupo::find($grupo->id);

    expect($grupoRecuperado->numeroCiclo)->toBe(2);
    expect($grupoRecuperado->ano)->toBe(2026);
    expect($grupoRecuperado->numeroGrupo)->toBe(1);
    expect($grupoRecuperado->abierto)->toBeTrue();
    expect($grupoRecuperado->curso->sigla)->toBe('IF6100');
    expect($grupoRecuperado->curso->creditos)->toBe(4);

});

test('Caso 2: Un curso tiene varios grupos y se distinguen los abiertos', function () {
    $curso = Curso::create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    Grupo::create([
        'curso_id' => $curso->id,
        'numeroCiclo' => '2',
        'ano' => '2026',
        'numeroGrupo' => '1',
        'abierto' => false,
    ]);

    Grupo::create([
        'curso_id' => $curso->id,
        'numeroCiclo' => '2',
        'ano' => '2026',
        'numeroGrupo' => '2',
        'abierto' => true,
    ]);

    Grupo::create([
        'curso_id' => $curso->id,
        'numeroCiclo' => '2',
        'ano' => '2026',
        'numeroGrupo' => '3',
        'abierto' => true,
    ]);

    $grupos = $curso->grupos;
    $gruposAbiertos = $grupos->where('abierto', true);

    expect($grupos)->toHaveCount(3);
    expect($gruposAbiertos)->toHaveCount(2);
    expect($gruposAbiertos->pluck('numeroGrupo')->toArray())->toContain(2, 3);
});

test('Caso 3: Un grupo acepta dos docentes', function () {
    $curso = Curso::create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $grupo = Grupo::create([
        'curso_id' => $curso->id,
        'numeroCiclo' => '2',
        'ano' => '2026',
        'numeroGrupo' => '1',
        'abierto' => false,
    ]);

    $docente1001 = Docente::create([
        'codEmpleado' => '1001',
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas',
    ]);

    $docente1002 = Docente::create([
        'codEmpleado' => '1002',
        'nombreEmpleado' => 'Luis',
        'apellidosEmpleado' => 'Rojas Solano',
    ]);

    $grupo->asignarDocente($docente1001->id);
    $grupo->asignarDocente($docente1002->id);

    expect($grupo->fresh()->docentes)->toHaveCount(2);
    expect($grupo->fresh()->docentes->pluck('codEmpleado')->toArray())->toContain(1001, 1002);
});

test('Caso 4: Un grupo rechaza un tercer docente', function () {
    $curso = Curso::create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $grupo = Grupo::create([
        'curso_id' => $curso->id,
        'numeroCiclo' => '2',
        'ano' => '2026',
        'numeroGrupo' => '1',
        'abierto' => false,
    ]);

    $docente1001 = Docente::create([
        'codEmpleado' => '1001',
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas',
    ]);

    $docente1002 = Docente::create([
        'codEmpleado' => '1002',
        'nombreEmpleado' => 'Luis',
        'apellidosEmpleado' => 'Rojas Solano',
    ]);

    $docente1003 = Docente::create([
        'codEmpleado' => '1003',
        'nombreEmpleado' => 'Carla',
        'apellidosEmpleado' => 'Jimenez Araya',
    ]);

    $grupo->asignarDocente($docente1001->id);
    $grupo->asignarDocente($docente1002->id);

    expect(fn () => $grupo->asignarDocente($docente1003->id))->toThrow(DomainException::class);

    expect($grupo->fresh()->docentes)->toHaveCount(2);
});

test('Caso 5: Un grupo con 14 estudiantes no puede abrirse', function () {
    $curso = Curso::create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $grupo = Grupo::create([
        'curso_id' => $curso->id,
        'numeroCiclo' => '2',
        'ano' => '2026',
        'numeroGrupo' => '1',
        'abierto' => false,
    ]);

    $docente1001 = Docente::create([
        'codEmpleado' => '1001',
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas',
    ]);

    $grupo->asignarDocente($docente1001->id);

    $estudiantes = Estudiante::factory()->count(14)->create([
        'docente_id' => $docente1001->id,
    ]);

    $grupo->estudiante()->attach($estudiantes->pluck('id'), ['nota' => 0]);
    expect($grupo->puedeAbrirse())->toBeFalse();
    expect(fn () => $grupo->abrir())->toThrow(DomainException::class);
    expect($grupo->fresh()->abierto)->toBeFalse();
});

test('Caso 6: Un grupo con 15 estudiantes y un docente se abre', function () {
    $curso = Curso::create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $grupo = Grupo::create([
        'curso_id' => $curso->id,
        'numeroCiclo' => '2',
        'ano' => '2026',
        'numeroGrupo' => '1',
        'abierto' => false,
    ]);

    $docente1001 = Docente::create([
        'codEmpleado' => '1001',
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas',
    ]);

    $grupo->asignarDocente($docente1001->id);

    $estudiantes = Estudiante::factory()->count(15)->create([
        'docente_id' => $docente1001->id,
    ]);

    $grupo->estudiante()->attach($estudiantes->pluck('id'), ['nota' => 0]);

    $puedeAbrirse = $grupo->puedeAbrirse();
    $grupo->abrir();

    expect($puedeAbrirse)->toBeTrue();
    expect($grupo->fresh()->abierto)->toBeTrue();
});
