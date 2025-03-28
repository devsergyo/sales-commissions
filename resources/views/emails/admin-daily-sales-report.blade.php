<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resumo de Vendas Diárias - {{ $reportDate }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .summary-box {
            background: #fff;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .summary-item {
            margin-bottom: 10px;
        }
        .summary-item strong {
            display: inline-block;
            width: 200px;
        }
        .summary-item span {
            font-weight: bold;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #7f8c8d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Resumo de Vendas Diárias</h1>
        <p>Prezado Administrador,</p>
        <p>Segue o resumo de todas as vendas realizadas em <strong>{{ $reportDate }}</strong>:</p>

        <div class="summary-box">
            <div class="summary-item">
                <strong>Total de Vendas:</strong> <span>{{ $salesData['total_sales'] }}</span>
            </div>
            <div class="summary-item">
                <strong>Valor Total:</strong> <span>R$ {{ number_format($salesData['total_amount'], 2, ',', '.') }}</span>
            </div>
            <div class="summary-item">
                <strong>Total de Comissões:</strong> <span>R$ {{ number_format($salesData['total_commission'], 2, ',', '.') }}</span>
            </div>
            <div class="summary-item">
                <strong>Número de Vendedores:</strong> <span>{{ $salesData['total_sellers'] }}</span>
            </div>
            <div class="summary-item">
                <strong>Vendedores com Vendas:</strong> <span>{{ $salesData['sellers_with_sales'] }}</span>
            </div>
        </div>

        @if(count($salesData['top_sellers']) > 0)
        <h2>Top 5 Vendedores</h2>
        <table>
            <thead>
                <tr>
                    <th>Vendedor</th>
                    <th>Vendas</th>
                    <th>Valor Total</th>
                    <th>Comissão</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesData['top_sellers'] as $seller)
                <tr>
                    <td>{{ $seller['name'] }}</td>
                    <td>{{ $seller['sales_count'] }}</td>
                    <td>R$ {{ number_format($seller['total_amount'], 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($seller['total_commission'], 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="footer">
            <p>Este é um e-mail automático. Por favor, não responda.</p>
            <p>&copy; {{ date('Y') }} Sistema de Comissões de Vendas</p>
        </div>
    </div>
</body>
</html>
