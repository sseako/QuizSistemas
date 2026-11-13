<?php

use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;

mutates(Grupo::class);

test('CP-07 Un grupo sin docente no se abre aunque tenga cupo completo', function () {
    $curso = Curso::factory()->create([
    'sigla' => 'IF6100',
    'nombreCurso' => 'Análisis y Diseño de Sistemas',
    'creditos' => 4]);

    $grupo = Grupo::factory()->create([
    'curso_id' => $curso->id,
    'numeroCiclo' => 2,
    'ano' => 2026,
    'numeroGrupo' => 1,
    'abierto' => false]);
    
    $estudiantes = Estudiante::factory()->count(20)->create();
    $grupo->estudiantes()->attach($estudiantes->pluck('id'));

    expect($grupo->puedeAbrirse())->toBeFalse();
    expect(fn() => $grupo->abrir())->toThrow(DomainException::class);

    $this->assertDatabaseHas('grupos', [
        'id' => $grupo->id,
        'abierto' => false
    ]);
});

test('CP-08 Un estudiante queda ligado a su profesor consejero', function () {
    $docente = Docente::factory()->create([
        'codEmpleado' => 1001,
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas']);

    $estudiante = Estudiante::factory()->create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
        'docente_id' => $docente->id
    ]);

    expect($estudiante->consejero->codEmpleado)->toBe(1001);
    
    $docenteRecargado = $docente->fresh();
    expect($docenteRecargado->estudiantes)->toHaveCount(1);
    expect($docenteRecargado->estudiantes->first()->carne)->toBe('C12345');

    $this->assertDatabaseHas('estudiantes', [
        'carne' => 'C12345',
        'docente_id' => $docente->id
    ]);
});

test('CP-09 Cambiar de consejero reemplaza al anterior, no lo suma', function () {
    $docente1001 = Docente::factory()->create([
        'codEmpleado' => 1001,
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas']);

    $docente1002 = Docente::factory()->create([
        'codEmpleado' => 1002,
        'nombreEmpleado' => 'Luis',
        'apellidosEmpleado' => 'Rojas Solano']);
    
    $estudiante = Estudiante::factory()->create([
        'carne' => 'C12345',
        'docente_id' => $docente1001->id]);

    $estudiante->docente_id = $docente1002->id;
    $estudiante->save();

    expect($estudiante->fresh()->consejero->codEmpleado)->toBe(1002);
    expect($docente1001->fresh()->estudiantes)->toHaveCount(0);
    expect($docente1002->fresh()->estudiantes)->toHaveCount(1);

    $this->assertDatabaseHas('estudiantes', [
        'id' => $estudiante->id,
        'docente_id' => $docente1002->id
    ]);

    $this->assertDatabaseMissing('estudiantes', [
        'id' => $estudiante->id,
        'docente_id' => $docente1001->id
    ]);
});

test('CP-10 Al matricular, la nota queda vacía', function () {
    $curso = Curso::factory()->create([
        'sigla' => 'IF6100']);
    $grupo = Grupo::factory()->create([
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1]);

    $estudiante = Estudiante::factory()->create([
        'carne' => 'C12345']);

    $grupo->estudiantes()->attach($estudiante->id);

    $pivot = $grupo->fresh()->estudiantes->first()->pivot;
    expect($pivot)->toBeInstanceOf(EstudianteMatriculado::class);
    expect($pivot->nota)->toBeNull();

    $this->assertDatabaseCount('estudiante_matriculados', 1);
    $this->assertDatabaseHas('estudiante_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $estudiante->id,
        'nota' => null
    ]);
});

test('CP-11 Registrar la nota al finalizar el curso', function () {
    $curso = Curso::factory()->create([
        'sigla' => 'IF6100']);
    $grupo = Grupo::factory()->create([
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1]);

    $estudiante = Estudiante::factory()->create([
        'carne' => 'C12345']);

    $grupo->estudiantes()->attach($estudiante->id);

    $grupo->registrarNota($estudiante, 8.5);

    $matricula = EstudianteMatriculado::where('grupo_id', $grupo->id)->where('estudiante_id', $estudiante->id)->first();

    expect($matricula->nota)->toBe(8.5);
    expect(is_float($matricula->nota))->toBeTrue();

    $this->assertDatabaseCount('estudiante_matriculados', 1);
    $this->assertDatabaseHas('estudiante_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $estudiante->id,
        'nota' => 8.5
    ]);
});

test('CP-12 Rechazar notas menores que 0 o mayores que 10', function (float $valor) {
    $curso = Curso::factory()->create([
        'sigla' => 'IF6100']);
    $grupo = Grupo::factory()->create([
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1]);
    $estudiante = Estudiante::factory()->create([
        'carne' => 'C12345']);
    $grupo->estudiantes()->attach($estudiante->id);

    expect(fn() => $grupo->registrarNota($estudiante, $valor))
        ->toThrow(InvalidArgumentException::class);

    $this->assertDatabaseHas('estudiante_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $estudiante->id,
        'nota' => null
    ]);
})->with([-1.0, 10.5]); 

test('CP-13 Un estudiante no se matricula dos veces en el mismo grupo', function () {
    $curso = Curso::factory()->create([
        'sigla' => 'IF6100']);
    $grupo = Grupo::factory()->create([
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1]);
    $estudiante = Estudiante::factory()->create([
        'carne' => 'C12345']);
    $grupo->estudiantes()->attach($estudiante->id);

    expect(fn() => $grupo->estudiantes()->attach($estudiante->id))
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount('estudiante_matriculados', 1);
});
