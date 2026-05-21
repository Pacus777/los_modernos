<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>WAYNA — Historial de donaciones</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #1c1917;
            line-height: 1.4;
            padding: 18px 22px;
        }
        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .header td { vertical-align: middle; }
        .brand {
            font-size: 22pt;
            font-weight: bold;
            color: #f07e26;
            letter-spacing: 0.02em;
        }
        .subtitle {
            font-size: 11pt;
            font-weight: bold;
            color: #1c1917;
            margin-top: 2px;
        }
        .intro {
            background: #fde9d6;
            border: 1px solid #f5d5b8;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 12px;
            font-size: 8.5pt;
            color: #57534e;
        }
        .intro strong { color: #d96d1c; }
        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta td {
            background: #f07e26;
            color: #fff;
            padding: 8px 10px;
            font-size: 8.5pt;
        }
        .meta .right { text-align: right; }
        .resumen {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 12px;
        }
        .resumen td {
            width: 25%;
            background: #fff;
            border: 1px solid #e7e5e4;
            border-top: 3px solid #f07e26;
            padding: 8px;
            text-align: center;
            border-radius: 6px;
        }
        .resumen .label {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #d96d1c;
            letter-spacing: 0.05em;
        }
        .resumen .valor {
            font-size: 14pt;
            font-weight: bold;
            color: #1c1917;
            margin-top: 4px;
        }
        .resumen .valor-verde { color: #047857; }
        .filtros {
            background: #f7f6f4;
            border: 1px solid #e7e5e4;
            padding: 7px 10px;
            margin-bottom: 10px;
            font-size: 8pt;
            border-radius: 6px;
        }
        .leyenda {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .leyenda td {
            width: 33.33%;
            padding: 6px 8px;
            font-size: 7.5pt;
            vertical-align: top;
            border: 1px solid #e7e5e4;
        }
        .leyenda .badge {
            display: inline-block;
            font-weight: bold;
            font-size: 7pt;
            padding: 2px 8px;
            border-radius: 10px;
            margin-bottom: 3px;
        }
        .tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .tabla th {
            background: #f07e26;
            color: #fff;
            font-size: 7.5pt;
            font-weight: bold;
            padding: 7px 5px;
            text-align: left;
            border: 1px solid #d96d1c;
        }
        .tabla td {
            padding: 6px 5px;
            border: 1px solid #e7e5e4;
            font-size: 7.5pt;
            vertical-align: top;
        }
        .tabla tr.par td { background: #fafaf9; }
        .tabla .col-id { width: 5%; text-align: center; font-weight: bold; color: #d96d1c; }
        .tabla .col-monto { text-align: right; font-weight: bold; white-space: nowrap; }
        .tabla .col-estado { text-align: center; }
        .estado-badge {
            display: inline-block;
            font-weight: bold;
            font-size: 7pt;
            padding: 3px 8px;
            border-radius: 10px;
        }
        .vacio {
            text-align: center;
            font-style: italic;
            color: #78716c;
            padding: 20px;
        }
        .pie {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 2px solid #f07e26;
            font-size: 7pt;
            color: #78716c;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 70px;">
                @if($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" alt="WAYNA" width="56" height="56" />
                @endif
            </td>
            <td>
                <div class="brand">WAYNA</div>
                <div class="subtitle">Tu historial de aportes recibidos</div>
            </td>
            <td style="text-align: right; font-size: 8pt; color: #78716c;">
                Reporte para emprendedor<br>
                <strong style="color: #1c1917;">#{{ $emprendedorId }}</strong>
            </td>
        </tr>
    </table>

    <div class="intro">
        <strong>¿Qué es este documento?</strong>
        Es el listado de donaciones que recibiste en tus campañas WAYNA.
        Los montos están en <strong>bolivianos (Bs.)</strong>.
        Solo el estado <strong>Validado</strong> suma al total recaudado confirmado.
    </div>

    <table class="meta">
        <tr>
            <td><strong>Emprendedor:</strong> {{ $emprendedorNombre }}</td>
            <td class="right"><strong>Generado:</strong> {{ $generado }}</td>
        </tr>
    </table>

    <table class="resumen">
        <tr>
            <td>
                <div class="label">Total registros</div>
                <div class="valor">{{ $resumen['total_registros'] ?? 0 }}</div>
            </td>
            <td>
                <div class="label">Monto validado (Bs)</div>
                <div class="valor valor-verde">{{ number_format((float) ($resumen['total_validado'] ?? 0), 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Aportes validados</div>
                <div class="valor">{{ $resumen['cantidad_validadas'] ?? 0 }}</div>
            </td>
            <td>
                <div class="label">Aún pendientes</div>
                <div class="valor">{{ $resumen['cantidad_pendientes'] ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <div class="filtros">
        <strong>Filtros de este reporte:</strong> {{ $filtrosTexto }}
    </div>

    <table class="leyenda">
        <tr>
            @foreach($estadosAyuda as $estado)
                <td style="background: {{ $estado['fondo'] }};">
                    <span class="estado-badge" style="background: {{ $estado['fondo'] }}; color: {{ $estado['texto'] }};">
                        {{ $estado['etiqueta'] }}
                    </span><br>
                    {{ $estado['ayuda'] }}
                </td>
            @endforeach
        </tr>
    </table>

    <table class="tabla">
        <thead>
            <tr>
                <th class="col-id">Nº</th>
                <th>Fecha</th>
                <th>Monto (Bs)</th>
                <th>Estado</th>
                <th>Campaña</th>
                <th>Forma de pago</th>
                <th>Método</th>
                <th>Quien apoyó</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filas as $i => $fila)
                <tr class="{{ $i % 2 === 0 ? 'par' : '' }}">
                    <td class="col-id">{{ $fila['id'] }}</td>
                    <td>{{ $fila['fecha'] }}</td>
                    <td class="col-monto">{{ $fila['monto'] }}</td>
                    <td class="col-estado">
                        <span class="estado-badge" style="background: {{ $fila['estado_fondo'] }}; color: {{ $fila['estado_texto'] }};">
                            {{ $fila['estado_etiqueta'] }}
                        </span>
                    </td>
                    <td>{{ $fila['campana'] }}</td>
                    <td>{{ $fila['tipo_pago'] }}</td>
                    <td>{{ $fila['metodo'] }}</td>
                    <td>{{ $fila['visitante'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="vacio">
                        No hay donaciones con los filtros seleccionados.
                        Probá ampliar las fechas o quitar el filtro de estado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pie">
        Documento generado por WAYNA · Emprendedor #{{ $emprendedorId }} ·
        Conservá este PDF para tu control; ante dudas contactá al equipo WAYNA.
    </div>
</body>
</html>
