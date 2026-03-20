<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MuestreoL {{ $data['folio'] }}</title>

    <style>
        @page{ 
                margin: 0.8cm 1.5cm 1.5cm 1.5cm; 
            }        
        body {
            font-family: 'Inter', 'Helvetica', Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }

        /* Marca de agua: centrada y detrás del contenido */
        #watermark {
            position: fixed;
            top: 0%;
            left: 0%;
            width: 80%;
            opacity: .15;
        }


        /* Ajuste para que el logo no empuje la línea verde del título */
        .logo-cell img {
            display: block;
            margin-bottom: -5px; /* Ajuste fino para alinear con la base del título */
        }

        .title-cell {
            vertical-align: bottom;
        }

        /* --- Header & Branding --- */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        
        .brand-name {
            color: #059669; 
            font-size: 24px; 
            font-weight: 900; 
            letter-spacing: -1px;
        }

        .title-cell {
            text-align: right;
            border-bottom: 4px solid #059669;
            padding-bottom: 5px;
        }

        .title-main {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            margin: 0;
        }

        /* --- Info Sections --- */
        .section-header {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 4px 4px 0 0;
            margin-top: 15px;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            width: 20%;
            border: 1px solid #e2e8f0;
            padding: 6px;
            text-align: left;
        }

        .info-table td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            width: 30%;
        }

        /* --- Sampling Tables --- */
        .audit-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .audit-table th {
            background-color: #f1f5f9;
            color: #64748b;
            font-size: 8px;
            padding: 5px;
            border: 1px solid #e2e8f0;
            text-transform: uppercase;
        }

        .audit-table td {
            border: 1px solid #e2e8f0;
            padding: 5px;
            text-align: center;
        }

        /* --- Badges & Status --- */
        .status-badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-conforme { background-color: #dcfce7; color: #166534; }
        .status-no-conforme { background-color: #fee2e2; color: #991b1b; }

        .footer {
            position: fixed;
            bottom: -0.5cm;
            left: 0; right: 0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
        .master-header {
                width: 100%;
                border-collapse: collapse;
                margin-top: -10px; /* Sube el contenido un poco más */
                margin-bottom: 1px;
                table-layout:initial; /* Ayuda a mantener proporciones */
            }
        .master-header td {
            border: 0px solid #e2e8f0;
            padding: 1px 1px;
            vertical-align: middle;
        }

        .logo-section img {
                width: 100px;
                display: block;
                margin-top: -5px; /* Sube el logo individualmente */
            }        
        .meta-label { color: #64748b; text-align: right; font-weight: bold; width: 15%; border: none !important; font-size: 8px; }
        .meta-value { color: #1e293b; width: 25%; border: none !important; font-size: 8px;}

        .doc-title-row td {
            border-top: 1px solid #1e293b !important;
            padding-top: -10px;
            text-align: right;
            border-bottom: none !important;
            border-left: none !important;
            border-right: none !important;
        }
    </style>
</head>
<body>
   
    <div id="watermark">
        <img src="{{ public_path('images/jorial.jpg') }}" >
    </div>

    <table class="master-header">
        <tr>
            <td rowspan="5" class="logo-section">
                <img src="{{ public_path('images/logo.jpg') }}" style="width: 140px;">
            </td>
            <td class="meta-label">Código:</td>
            <td class="meta-value">RE-ADM-4.1.3</td>
        </tr>
        <tr>
            <td class="meta-label">Fecha de emisión:</td>
            <td class="meta-value">21-may-2024</td>
        </tr>
        <tr>
            <td class="meta-label">Fecha de revisión:</td>
            <td class="meta-value">09-ene-2026</td>
        </tr>
        <tr>
            <td class="meta-label">Versión:</td>
            <td class="meta-value">004</td>
        </tr>
        <tr>
            <td class="meta-label">Referencias</td>
            <td class="meta-value"> PR-ADM-4.1.0 Recepción de Materia Prima/ RE-ADM-4.1.2 Check in BPM Proveedor / MIL-STD-105D / FO-ADM-4.1.5 Guía rápida para clasificar el impacto del material, MA-ADM-4.3.0 Tolerancia y tipos de contaminación para aceptación y rechazo</td>
        </tr>
        <tr>
            <td class="meta-label">Documento:</td>
            <td class="meta-value"><strong>Controlado</strong></td>
        </tr>
        <tr class="doc-title-row">
            <td colspan="3">
                <div class="doc-title">Registro de muestreo local – recepción de materia prima.</div>
                <div style="font-size: 9px; color: #94a3b8;">Folio: {{ $data['folio'] }} | Página 1 de 1</div>
            </td>
        </tr>
    </table>


    <div class="section-header">Información del Proveedor</div>
    <table class="info-table">
        <tr>
            <th>Proveedor:</th>
            <td colspan="3">{{ strtoupper($data['provider']['name'] ?? 'N/A') }}</td>
        </tr>
    </table>

    @foreach($data['products'] as $product)
        <div style="page-break-inside: avoid; margin-bottom: 20px;">
            <div class="section-header">
                Producto: {{ $product['title'] }} ({{ $product['code'] }})
            </div>
            
            <table class="info-table">
                <tr>
                    <th>Lote:</th>
                    <td>{{ $data['details'][$loop->index]['lot'] ?? 'N/A' }}</td>
                    <th>Dimensiones:</th>
                    <td>{{ $product['width'] }}x{{ $product['height'] }} | Cal: {{ $product['cal'] }}</td>
                </tr>
            </table>

            @if(isset($product['details_mil_std']))
                <div style="font-weight: bold; margin: 10px 0 5px 0;">Plan de Muestreo (MIL-STD-105E)</div>
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Nivel</th>
                            <th>AQL</th>
                            <th>Tamaño Muestra ($n$)</th>
                            <th>Aceptación ($Ac$)</th>
                            <th>Rechazo ($Re$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $product['details_mil_std']['inspection_level'] }}</td>
                            <td>{{ $product['details_mil_std']['aql'] }}%</td>
                            <td><strong>{{ $product['details_mil_std']['sample_size'] }}</strong></td>
                            <td style="color: #059669;">{{ $product['details_mil_std']['sample_acept'] }}</td>
                            <td style="color: #dc2626;">{{ $product['details_mil_std']['sample_reject'] }}</td>
                        </tr>
                    </tbody>
                </table>

                @if(!empty($product['details_mil_std']['samplings']))
                    <div style="font-weight: bold; margin: 10px 0 5px 0;">Resultados del Muestreo</div>
                    <table class="audit-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ancho (mm)</th>
                                <th>Largo (mm)</th>
                                <th>Espesor (µm)</th>
                                <th>Sello</th>
                                <th>Color</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product['details_mil_std']['samplings'] as $index => $s)
                                <tr>
                                    <td style="background-color: #f8fafc;">{{ $index + 1 }}</td>
                                    <td>{{ number_format($s['width'], 2) }}</td>
                                    <td>{{ number_format($s['length'], 2) }}</td>
                                    <td>{{ number_format($s['thickness'], 2) }}</td>
                                    <td>
                                        <span class="status-badge {{ strtolower($s['seal_resistance']) == 'conforme' ? 'status-conforme' : 'status-no-conforme' }}">
                                            {{ $s['seal_resistance'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ strtolower($s['color_detachment']) == 'conforme' ? 'status-conforme' : 'status-no-conforme' }}">
                                            {{ $s['color_detachment'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @else
                <div style="padding: 10px; color: #64748b; text-align: center; border: 1px dashed #e2e8f0; margin-top: 5px;">
                    No hay datos técnicos de muestreo registrados.
                </div>
            @endif
        </div>
    @endforeach

    <div class="footer">
        Generado automáticamente por Jorial BPM - Sistema de Gestión de Calidad
    </div>

</body>
</html>