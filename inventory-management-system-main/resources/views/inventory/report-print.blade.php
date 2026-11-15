<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Stock Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 12px; padding: 20px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<div class="no-print mb-3">
    <button onclick="window.print()" class="btn btn-sm btn-primary">Print</button>
</div>
<h5 class="mb-1">Stock Report</h5>
<p class="text-muted small">
    Warehouse: {{ $warehouse->name }} |
    Period: {{ $dateFrom && $dateTo ? $dateFrom.' — '.$dateTo : 'All' }} |
    Printed: {{ now()->format('d/m/Y H:i') }}
</p>
<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>Code</th><th>Name</th><th>Category</th><th>Unit</th>
            <th class="text-end">In</th><th class="text-end">Out</th>
            <th class="text-end">On Hand</th><th class="text-end">Value</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row->code }}</td>
                <td>{{ $row->name }}</td>
                <td>{{ $row->category }}</td>
                <td>{{ $row->unit }}</td>
                <td class="text-end">{{ number_format($row->qty_in, 2) }}</td>
                <td class="text-end">{{ number_format($row->qty_out, 2) }}</td>
                <td class="text-end">{{ number_format($row->qty_on_hand, 2) }}</td>
                <td class="text-end">{{ number_format($row->stock_value, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="7" class="text-end">Total Value</th>
            <th class="text-end">Rp {{ number_format($summary->total_value, 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>
</body>
</html>
