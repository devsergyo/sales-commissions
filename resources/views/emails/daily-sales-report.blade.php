<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Diário de Vendas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .header p {
            color: #7f8c8d;
            font-size: 16px;
            margin-top: 0;
        }
        .summary {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .summary-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .summary-label {
            font-weight: bold;
        }
        .summary-value {
            color: #2980b9;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #7f8c8d;
            border-top: 2px solid #f0f0f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório Diário de Vendas</h1>
        <p>{{ $reportDate }}</p>
    </div>

    <p>Olá, {{ $seller->first_name }} {{ $seller->last_name }}!</p>

    <p>Segue abaixo o resumo das suas vendas realizadas hoje:</p>

    <div class="summary">
        <h2>Resumo do Dia</h2>
        <div class="summary-item">
            <span class="summary-label">Total de Vendas:</span>
            <span class="summary-value">{{ $salesData['total_sales'] }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Valor Total:</span>
            <span class="summary-value">R$ {{ number_format($salesData['total_amount'], 2, ',', '.') }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total de Comissões:</span>
            <span class="summary-value">R$ {{ number_format($salesData['total_commission'], 2, ',', '.') }}</span>
        </div>
    </div>

    @if(count($salesData['sales']) > 0)
    <h3>Detalhamento das Vendas</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Data/Hora</th>
                <th>Valor</th>
                <th>Comissão</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesData['sales'] as $index => $sale)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $sale->sale_date->format('d/m/Y H:i') }}</td>
                <td>R$ {{ number_format($sale->amount, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($sale->commission, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>Este é um e-mail automático. Por favor, não responda.</p>
        <p>&copy; {{ date('Y') }} Sistema de Comissões de Vendas</p>
    </div>
</body>
</html>
