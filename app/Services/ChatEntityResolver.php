<?php

namespace App\Services;

use App\Models\Campana;
use App\Models\Emprendedor;
use App\Support\TextoPregunta;
use Illuminate\Support\Str;

class ChatEntityResolver
{
  /**
   * Intenta resolver emprendedores o campañas antes del conocimiento estático.
   *
   * @return array<string, mixed>|null
   */
  public function intentarResolver(
    string $pregunta,
    string $idioma = 'es',
    ?int $contextEmprendedorId = null,
  ): ?array {
    $idioma = in_array($idioma, ['es', 'en'], true) ? $idioma : 'es';
    $preguntaNorm = TextoPregunta::normalizar($pregunta);

    if ($preguntaNorm === '') {
      return null;
    }

    $quiereDonar = $this->tieneIntencionDonacion($preguntaNorm);
    $buscaPersona = $this->tieneIntencionBuscarEmprendedor($preguntaNorm);

    if ($contextEmprendedorId !== null && ($quiereDonar || $buscaPersona)) {
      $contextual = $this->resolverDesdeContexto($contextEmprendedorId, $idioma, $quiereDonar);

      if ($contextual !== null) {
        return $contextual;
      }
    }

    $coincidencias = $this->buscarEmprendedoresActivos($preguntaNorm);

    if ($coincidencias !== []) {
      return $this->respuestaEmprendedores($coincidencias, $idioma, $pregunta, $quiereDonar);
    }

    $campana = $this->buscarCampanaActiva($preguntaNorm);

    if ($campana !== null) {
      return $this->respuestaCampana($campana, $idioma, $pregunta, $quiereDonar);
    }

    if ($buscaPersona && $this->pareceBuscarNombrePropio($preguntaNorm)) {
      return $this->respuestaSinEmprendedor($idioma, $pregunta);
    }

    return null;
  }

  private function pareceBuscarNombrePropio(string $preguntaNorm): bool
  {
    $tokens = TextoPregunta::tokens($preguntaNorm);

    return count($tokens) >= 1;
  }

  private function tieneIntencionDonacion(string $preguntaNorm): bool
  {
    return TextoPregunta::contieneAlguna($preguntaNorm, [
      'donar', 'donacion', 'donación', 'apoyar', 'aporte', 'contribuir',
      'donate', 'donation', 'support', 'contribute', 'help him', 'help her',
      'quiero ayudar', 'want to help',
    ]);
  }

  private function tieneIntencionBuscarEmprendedor(string $preguntaNorm): bool
  {
    return TextoPregunta::contieneAlguna($preguntaNorm, [
      'emprendedor', 'perfil', 'producto', 'artesano', 'artesana',
      'entrepreneur', 'profile', 'find', 'buscar', 'busco', 'conocer',
      'donde esta', 'dónde está', 'where is', 'who is', 'quien es', 'quién es',
    ]);
  }

  /**
   * @return array<string, mixed>|null
   */
  private function resolverDesdeContexto(int $emprendedorId, string $idioma, bool $quiereDonar): ?array
  {
    $emprendedor = Emprendedor::query()
      ->whereKey($emprendedorId)
      ->where('estado', 'activo')
      ->first();

    if (! $emprendedor) {
      return null;
    }

    return $this->respuestaUnEmprendedor(
      $emprendedor,
      $idioma,
      null,
      $quiereDonar,
      'contexto_perfil',
    );
  }

  /**
   * @return list<array{emprendedor: Emprendedor, score: int}>
   */
  private function buscarEmprendedoresActivos(string $preguntaNorm): array
  {
    $resultados = [];

    $emprendedores = Emprendedor::query()
      ->where('estado', 'activo')
      ->orderBy('nombre')
      ->orderBy('apellidos')
      ->get();

    foreach ($emprendedores as $emprendedor) {
      $score = $this->puntajeEmprendedor($emprendedor, $preguntaNorm);

      if ($score >= 4) {
        $resultados[] = [
          'emprendedor' => $emprendedor,
          'score' => $score,
        ];
      }
    }

    usort($resultados, fn (array $a, array $b) => $b['score'] <=> $a['score']);

    return $resultados;
  }

  private function puntajeEmprendedor(Emprendedor $emprendedor, string $preguntaNorm): int
  {
    $nombre = TextoPregunta::normalizar((string) $emprendedor->nombre);
    $apellidos = TextoPregunta::normalizar((string) $emprendedor->apellidos);
    $completo = TextoPregunta::normalizar($emprendedor->nombreCompleto());

    $puntaje = 0;

    if ($completo !== '' && Str::contains($preguntaNorm, $completo)) {
      $puntaje += 12;
    }

    if ($nombre !== '' && mb_strlen($nombre) >= 3 && Str::contains($preguntaNorm, $nombre)) {
      $puntaje += 7;
    }

    if ($apellidos !== '' && mb_strlen($apellidos) >= 3 && Str::contains($preguntaNorm, $apellidos)) {
      $puntaje += 5;
    }

    $tokensPregunta = TextoPregunta::tokens($preguntaNorm);
    $tokensNombre = TextoPregunta::tokens($completo);
    $interseccion = array_intersect($tokensPregunta, $tokensNombre);

    if (count($interseccion) >= 1 && count($tokensNombre) >= 1) {
      $puntaje += 3 * count($interseccion);
    }

    return $puntaje;
  }

  private function buscarCampanaActiva(string $preguntaNorm): ?Campana
  {
    $campanas = Campana::query()
      ->visibleEnPerfilTurista()
      ->whereHas('emprendedor', fn ($q) => $q->where('estado', 'activo'))
      ->with('emprendedor')
      ->orderByDesc('fecha_inicio')
      ->get();

    $mejor = null;
    $mejorPuntaje = 0;

    foreach ($campanas as $campana) {
      if (! $campana->emprendedor) {
        continue;
      }

      $tituloNorm = TextoPregunta::normalizar((string) $campana->titulo);

      if ($tituloNorm === '') {
        continue;
      }

      $puntaje = 0;

      if (Str::contains($preguntaNorm, $tituloNorm)) {
        $puntaje += 10;
      }

      $tokensTitulo = TextoPregunta::tokens($tituloNorm);
      $tokensPregunta = TextoPregunta::tokens($preguntaNorm);
      $coincidencias = count(array_intersect($tokensTitulo, $tokensPregunta));

      if ($coincidencias >= 2) {
        $puntaje += 6;
      } elseif ($coincidencias === 1 && count($tokensTitulo) === 1) {
        $puntaje += 4;
      }

      if ($puntaje > $mejorPuntaje) {
        $mejorPuntaje = $puntaje;
        $mejor = $campana;
      }
    }

    return $mejorPuntaje >= 4 ? $mejor : null;
  }

  /**
   * @param  list<array{emprendedor: Emprendedor, score: int}>  $coincidencias
   * @return array<string, mixed>
   */
  private function respuestaEmprendedores(
    array $coincidencias,
    string $idioma,
    string $pregunta,
    bool $quiereDonar,
  ): array {
    if (count($coincidencias) === 1) {
      return $this->respuestaUnEmprendedor(
        $coincidencias[0]['emprendedor'],
        $idioma,
        $pregunta,
        $quiereDonar,
        'emprendedor_unico',
      );
    }

    $actions = [];

    foreach (array_slice($coincidencias, 0, 3) as $item) {
      $emprendedor = $item['emprendedor'];
      $actions[] = $this->accionPerfil($emprendedor, $idioma, 'primary');
    }

    $actions[] = $this->accionExplorar($idioma);

    return [
      'question' => $pregunta,
      'answer' => trans('chat.emprendedores_varios', [], $idioma),
      'matched_id' => 'emprendedores:'.collect($coincidencias)->pluck('emprendedor.id')->implode(','),
      'intent' => 'redirigir_emprendedor',
      'category' => 'entidad',
      'score' => $coincidencias[0]['score'],
      'language' => $idioma,
      'actions' => $actions,
    ];
  }

  /**
   * @return array<string, mixed>
   */
  private function respuestaUnEmprendedor(
    Emprendedor $emprendedor,
    string $idioma,
    ?string $pregunta,
    bool $quiereDonar,
    string $intent,
  ): array {
    $nombre = $emprendedor->nombreCompleto();
    $clave = $quiereDonar ? 'chat.emprendedor_encontrado_donar' : 'chat.emprendedor_encontrado';

    $actions = [
      $this->accionPerfil($emprendedor, $idioma, 'primary'),
    ];

    if ($quiereDonar) {
      $actions[] = $this->accionDonar($emprendedor, $idioma);
    }

    return [
      'question' => $pregunta,
      'answer' => trans($clave, ['nombre' => $nombre], $idioma),
      'matched_id' => 'emprendedor:'.$emprendedor->id,
      'intent' => $intent,
      'category' => 'entidad',
      'score' => 10,
      'language' => $idioma,
      'actions' => $actions,
      'entity' => [
        'type' => 'emprendedor',
        'id' => $emprendedor->id,
        'nombre' => $nombre,
      ],
    ];
  }

  /**
   * @return array<string, mixed>
   */
  private function respuestaCampana(
    Campana $campana,
    string $idioma,
    string $pregunta,
    bool $quiereDonar,
  ): array {
    $emprendedor = $campana->emprendedor;
    $nombreEmprendedor = $emprendedor?->nombreCompleto() ?? '';

    $actions = [];

    if ($emprendedor) {
      $actions[] = $this->accionPerfil($emprendedor, $idioma, 'primary');
      if ($quiereDonar) {
        $actions[] = $this->accionDonar($emprendedor, $idioma);
      }
    }

    return [
      'question' => $pregunta,
      'answer' => trans('chat.campana_encontrada', [
        'campana' => $campana->titulo,
        'nombre' => $nombreEmprendedor,
      ], $idioma),
      'matched_id' => 'campana:'.$campana->id,
      'intent' => 'redirigir_campana',
      'category' => 'entidad',
      'score' => 9,
      'language' => $idioma,
      'actions' => $actions,
      'entity' => [
        'type' => 'campana',
        'id' => $campana->id,
        'titulo' => $campana->titulo,
        'emprendedor_id' => $emprendedor?->id,
      ],
    ];
  }

  /**
   * @return array<string, mixed>
   */
  private function respuestaSinEmprendedor(string $idioma, string $pregunta): array
  {
    return [
      'question' => $pregunta,
      'answer' => trans('chat.emprendedor_no_encontrado', [], $idioma),
      'matched_id' => 'emprendedor:no_match',
      'intent' => 'emprendedor_no_encontrado',
      'category' => 'entidad',
      'score' => 1,
      'language' => $idioma,
      'actions' => [
        $this->accionExplorar($idioma),
      ],
    ];
  }

  /**
   * @return array{label: string, href: string, variant: string}
   */
  private function accionPerfil(Emprendedor $emprendedor, string $idioma, string $variant = 'primary'): array
  {
    return [
      'label' => trans('chat.actions.view_profile', [
        'nombre' => $emprendedor->nombreCompleto(),
      ], $idioma),
      'href' => route('turista.emprendedor.show', $emprendedor->id),
      'variant' => $variant,
    ];
  }

  /**
   * @return array{label: string, href: string, variant: string}
   */
  private function accionDonar(Emprendedor $emprendedor, string $idioma): array
  {
    return [
      'label' => trans('chat.actions.donate', [], $idioma),
      'href' => route('turista.emprendedor.show', $emprendedor->id).'#donar',
      'variant' => 'secondary',
    ];
  }

  /**
   * @return array{label: string, href: string, variant: string}
   */
  private function accionExplorar(string $idioma): array
  {
    return [
      'label' => trans('chat.actions.explore', [], $idioma),
      'href' => url('/#explorar'),
      'variant' => 'secondary',
    ];
  }
}
