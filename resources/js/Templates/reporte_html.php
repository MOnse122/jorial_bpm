<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header-table { width: 100%; border-bottom: 2px solid #2c3e50; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #2c3e50; }
        .folio { font-size: 16px; color: #e74c3c; text-align: right; }
        
        .section-title { background-color: #f4f4f4; padding: 5px; font-weight: bold; border-left: 4px solid #2c3e50; margin-top: 20px; margin-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table th { background-color: #2c3e50; color: white; padding: 6px; text-align: left; font-size: 11px; }
        table td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        
        .status-badge { padding: 4px 8px; border-radius: 4px; color: white; font-weight: bold; font-size: 10px; }
        .aprobado { background-color: #27ae60; }
        .pendiente { background-color: #f39c12; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="border: none;">
                <div class="title">REPORTE DE INSPECCIÓN Y CALIDAD</div>
                <strong>Proveedor:</strong> {{ $data['provider']['name'] }}
            </td>
            <td style="border: none; text-align: right;">
                <div class="folio">{{ $data['folio'] }}</div>
                <strong>Fecha:</strong> {{ date('d/m/Y', strtotime($data['date'])) }}
            </td>
        </tr>
    </table>

    <div class="section-title">DETALLES DE LA ORDEN</div>
    <table>
        <thead>
            <tr>
                <th>Lote</th>
                <th>Medida</th>
                <th>Placa Relacionada</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['details'] as $detail)
            <tr>
                <td>{{ $detail['lot'] }}</td>
                <td>{{ $detail['unit_measure'] }}</td>
                <td>{{ $detail['plate']['plate_number'] }}</td>
                <td><span class="status-badge {{ strtolower($data['status']) == 'pendiente1' ? 'pendiente' : '' }}">{{ $data['status'] }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">RESULTADO PRUEBA BPM (Buenas Prácticas)</div>
    @foreach($data['test_bpm'] as $test)
    <table>
        <tr>
            <td style="width: 25%;"><strong>Inspector:</strong></td>
            <td>{{ $test['name_provider'] }}</td>
            <td style="width: 25%;"><strong>Puntaje Total:</strong></td>
            <td>{{ $test['total_score'] }} pts ({{ $test['percentage'] }}%)</td>
        </tr>
        <tr>
            <td><strong>Resultado:</strong></td>
            <td colspan="3"><strong style="color: #27ae60;">{{ $test['result'] }}</strong></td>
        </tr>
    </table>
    @endforeach

    <div class="section-title">INSPECCIÓN POR PRODUCTO (MIL-STD 105E)</div>
    @foreach($data['products'] as $product)
    <div style="margin-bottom: 15px; border: 1px solid #ddd; padding: 10px;">
        <table style="margin-bottom: 5px;">
            <tr style="background-color: #eee;">
                <td colspan="2"><strong>{{ $product['code'] }} - {{ $product['title'] }}</strong></td>
            </tr>
            @if($product['details_mil_std'])
            <tr>
                <td>
                    <strong>Nivel Insp:</strong> {{ $product['details_mil_std']['inspection_level'] }}<br>
                    <strong>AQL:</strong> {{ $product['details_mil_std']['aql'] }}%<br>
                    <strong>Tamaño Muestra:</strong> {{ $product['details_mil_std']['sample_size'] }}
                </td>
                <td>
                    <strong>Aceptación (Ac):</strong> {{ $product['details_mil_std']['sample_acept'] }}<br>
                    <strong>Rechazo (Re):</strong> {{ $product['details_mil_std']['sample_reject'] }}
                </td>
            </tr>
            @else
            <tr>
                <td colspan="2" style="color: #888; font-style: italic;">Sin detalles de inspección MIL-STD registrados.</td>
            </tr>
            @endif
        </table>

        @if($product['details_mil_std'] && count($product['details_mil_std']['samplings']) > 0)
        <table style="font-size: 10px;">
            <thead>
                <tr style="background-color: #f9f9f9;">
                    <th>Ancho</th>
                    <th>Largo</th>
                    <th>Espesor</th>
                    <th>Sellado</th>
                    <th>Color</th>
                </tr>
            </thead>
            <tbody>
                @foreach($product['details_mil_std']['samplings'] as $sample)
                <tr>
                    <td>{{ $sample['width'] }}</td>
                    <td>{{ $sample['length'] }}</td>
                    <td>{{ $sample['thickness'] }}</td>
                    <td>{{ $sample['seal_resistance'] }}</td>
                    <td>{{ $sample['color_detachment'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endforeach

    <div class="footer">
        Documento generado el {{ date('d/m/Y H:i') }} - Sistema de Gestión de Calidad (QMS)
    </div>

</body>
</html>