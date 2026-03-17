<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        /* Optimizaciones para PDF */
        @page { 
            /* Aumentamos el margen inferior de la hoja para dejarle espacio al footer */
            margin: 1cm 1.2cm 2cm 1.2cm; 
        }

        body {
            font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            background-color: #ffffff;
        }

        #watermark {
            position: fixed;
            top: 10%;
            left: 10%;
            width: 80%;
            opacity: 0.1;
            z-index: -1000;
        }
/* Estilos sugeridos para acompañar el HTML */
        .header-table { 
            width: 100%; 
            border-collapse: collapse;
            margin-bottom: 30px; /* Más espacio con lo que sigue */
        }

        .logo-cell {
            width: 30%;
            vertical-align: bottom; /* Alineado a la base de la línea verde */
            padding-bottom: 5px;
        }

        .brand-name {
            color: #059669; 
            font-size: 22px; /* Más grande para que destaque */
            font-weight: 900; 
            letter-spacing: -1px; /* Toque moderno */
            font-family: 'Inter', sans-serif;
        }

        .title-cell {
            width: 70%;
            text-align: right;
            border-bottom: 4px solidrgb(73, 150, 5); /* Línea más gruesa y sólida */
            padding-bottom: 5px;
        }

        .title-main {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b; /* Color pizarra oscuro para contraste */
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .subtitle {
            font-size: 8.5px;
            font-weight: 600;
            color: #64748b;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .info-label {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            padding: 5px 10px;
            border: 1px solid #e2e8f0;
            width: 15%;
        }

        .info-value {
            padding: 5px 10px;
            border: 1px solid #e2e8f0;
            color: #0f172a;
            width: 35%;
        }

        .section-header {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 4px 4px 0 0;
            margin-top: 12px;
            text-transform: uppercase;
        }

        .audit-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .audit-table th {
            background-color: #f1f5f9;
            color: #64748b;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            padding: 5px;
            border: 1px solid #e2e8f0;
            text-transform: uppercase;
        }

        .audit-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .criterio-text {
            font-size: 10px;
            color: #334155;
        }

        .status-cell {
            text-align: center;
            width: 35px;
        }

        .check-circle {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
            border: 1.5px solid #cbd5e1;
            line-height: 14px;
            font-size: 10px;
        }

        .checked {
            background-color: #059669;
            border-color: #059669;
            color: white;
            font-weight: bold;
        }

        .summary-card {
            margin-top: 20px;
            width: 100%;
            border-radius: 8px;
            border-collapse: separate;
        }

        .result-box {
            padding: 15px;
            border-radius: 8px;
            text-align: left;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 800;
            font-size: 12px;
            margin-top: 5px;
        }

        .score-display {
            text-align: center;
            vertical-align: middle;
            border-left: 2px dashed #cbd5e1;
        }

        .score-value {
            font-size: 24px;
            font-weight: 900;
        }

        .bg-pass { background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .bg-fail { background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .signature-container {
            margin-top: 40px;
            width: 100%;
        }

        .sig-box {
            text-align: center;
            width: 45%;
        }

        .sig-line {
            border-top: 1px solid #0f172a;
            margin: 0 20px 5px 20px;
        }

        /* Footer Mejorado para no estorbar */
        .footer {
            position: fixed;
            /* Bajamos la posición: entre más negativo sea el número, más cerca del borde estará */
            bottom: -2.2cm; 
            left: 0;
            right: 0;
            height: 2cm;
            font-size: 7.5px;
            color: #94a3b8;
            text-align: justify;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
            line-height: 1.1;
        }
    </style>
</head>

@php
    $test = $data['test_bpm'][0] ?? null;
    $isPass = ($test['result'] ?? '') == 'APROBADO';

    $secciones = [
        ['t' => '1. Ingreso del Proveedor', 's' => 'A', 'c' => ["Registro en bitácora de ingreso", "Identificación vigente", "Lavado/desinfección de manos", "Notifica enfermedad o lesión"]],
        ['t' => '2. Higiene Personal (BPM)', 's' => 'B', 'c' => ["Uso de cofia y cubrebocas", "Uniforme limpio y completo", "Zapato cerrado", "Sin joyería o artículos sueltos", "Uñas cortas y limpias", "Sin maquillaje/perfume"]],
        ['t' => '3. Inspección del Transporte', 's' => 'C', 'c' => ["Unidad limpia", "Sin humedad u olores", "Sin presencia de plagas", "Sin daños estructurales", "Fumigación vigente"]],
        ['t' => '4. Condiciones del Material', 's' => 'D', 'c' => ["Material limpio/protegido", "Empaques íntegros", "Etiquetado legible", "Identificación de lote", "Certificado disponible", "Ficha técnica"]],
        ['t' => '5. Control de Riesgos Físicos', 's' => 'E', 'c' => ["Sin presencia de madera", "Identificación de proveedor", "Sin presencia de vidrio", "Sin objetos extraños"]]
    ];
@endphp

<body>

<div id="watermark">
    <img src="/public/images/jorial.png" style="width: 100%;">    
</div>

<table class="header-table">
    <tr>
        <td class="logo-cell">
            <img src="/public/images/logo (1).png" style="width: 100%;">     
        </td>
        <td class="title-cell">     
            <h1 class="title-main">Check-in BPM Proveedor</h1>
            <div class="subtitle">Sistema de Gestión de Calidad | Registro RE-ADM-4.1.2</div>
        </td>
    </tr>
</table>

<table class="info-grid">
    <tr>
        <td class="info-label">Fecha de Auditoría</td>
        <td class="info-value">{{ isset($data['date']) ? date('d/m/Y', strtotime($data['date'])) : 'N/A' }}</td>
        <td class="info-label">Folio Interno</td>
        <td class="info-value" style="font-weight: bold; color: #059669;">{{ $data['folio'] ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="info-label">Razón Social</td>
        <td class="info-value">{{ $data['provider']['name'] ?? 'N/A' }}</td>
        <td class="info-label">Inspector</td>
        <td class="info-value">{{ $test['user']['name'] ?? 'PENDIENTE' }}</td>
    </tr>
    <tr>
        <td class="info-label">Operador / Chofer</td>
        <td class="info-value">{{ $test['name_provider'] ?? 'N/A' }}</td>
        <td class="info-label">Placas Unidad</td>
        <td class="info-value">{{ $data['plates'][0]['plate_number'] ?? 'N/A' }}</td>
    </tr>
</table>

@foreach($secciones as $sec)
<div class="section-header">{{ $sec['t'] }}</div>
<table class="audit-table">
    <thead>
        <tr>
            <th style="width: 70%; text-align: left;">Criterio de Evaluación</th>
            <th style="width: 10%;">CUMPLE</th>
            <th style="width: 10%;">N.C.</th>
            <th style="width: 10%;">N/A</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sec['c'] as $i => $criterio)
            @php
                $item = collect($test['details'] ?? [])->where('sector', $sec['s'])->values()->get($i);
                $score = $item['score'] ?? '';
            @endphp
            <tr>
                <td class="criterio-text">{{ $criterio }}</td>
                <td class="status-cell"><div class="check-circle {{ $score == 'SI' ? 'checked' : '' }}">{{ $score == 'SI' ? 'X' : '' }}</div></td>
                <td class="status-cell"><div class="check-circle {{ $score == 'NO' ? 'checked' : '' }}">{{ $score == 'NO' ? 'X' : '' }}</div></td>
                <td class="status-cell"><div class="check-circle {{ $score == 'N/A' ? 'checked' : '' }}">{{ $score == 'N/A' ? '—' : '' }}</div></td>
            </tr>
        @endforeach
    </tbody>
</table>
@endforeach

<table class="summary-card {{ $isPass ? 'bg-pass' : 'bg-fail' }}">
    <tr>
        <td class="result-box">
            <span style="text-transform: uppercase; font-size: 9px; font-weight: bold; display: block;">Dictamen de Inspección</span>
            <span class="status-badge">
                {{ $isPass ? '✓ UNIDAD Y PROVEEDOR APROBADOS' : 'X RECHAZADO - NO CUMPLE REQUISITOS' }}
            </span>
            <div style="margin-top: 10px; font-size: 9px;">
                <strong>Observaciones:</strong> {{ $test['observations'] ?? 'Sin observaciones registradas.' }}
            </div>
        </td>
        <td class="score-display" style="width: 25%;">
            <div style="font-size: 9px; font-weight: bold; text-transform: uppercase;">Puntaje</div>
            <div class="score-value">{{ number_format($test['percentage'] ?? 0, 0) }}%</div>
        </td>
    </tr>
</table>

<table class="signature-container">
    <tr>
        <td class="sig-box">
            <div class="sig-line"></div>
            <div style="font-weight: bold;">{{ $test['user']['name'] ?? 'Auditor Responsable' }}</div>
            <div style="color: #64748b; font-size: 8px;">Firma de Conformidad - Jorial</div>
        </td>
        <td style="width: 10%;"></td>
        <td class="sig-box">
            <div class="sig-line"></div>
            <div style="font-weight: bold;">{{ $test['name_provider'] ?? 'Representante Proveedor' }}</div>
            <div style="color: #64748b; font-size: 8px;">Firma de Operador / Proveedor</div>
        </td>
    </tr>
</table>

<div class="footer">
    Este documento contiene información CONTROLADA propiedad de Tecnologías Plásticas Jorial, queda prohibida su reproducción total o parcial no autorizada, podrá ser empleada internamente, cualquier divulgación a terceros, deberá ser expresamente autorizada por: director general. Este formato verifica condiciones de BPM al ingreso. No sustituye la inspección ni la liberación del material conforme al PR-ADM-4.1.0 Recepción de Materia Prima, criterios de aceptación y rechazo definidos en MA-ADM4.3.0. Tolerancia y tipos de contaminación para aceptación y rechazo. | Generado por Sistema Jorial - {{ date('Y') }}
</div>

</body>
</html>