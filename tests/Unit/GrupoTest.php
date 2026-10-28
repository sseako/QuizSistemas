<?php

use App\Models\Grupo;

test('grupo', function () {
    $numeroCiclo = 1;
    $ano = 1;
    $numeroGrupo = 3;
    $abierto = true;
    $cursoId = 1600;

    $grupo = new Grupo();
    $grupo->numero_ciclo = $numeroCiclo;
    $grupo->ano = $ano;
    $grupo->numero_grupo = $numeroGrupo;
    $grupo->abierto = $abierto;
    $grupo->curso_id = $cursoId;

    expect($grupo->numero_ciclo)->toBe($numeroCiclo);
    expect($grupo->ano)->toBe($ano);
    expect($grupo->numero_grupo)->toBe($numeroGrupo);
    expect($grupo->abierto)->toBe($abierto);
    expect($grupo->curso_id)->toBe($cursoId);


});
