<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice - {{ $sale->invoice_number ?? ('SALE-'.$sale->id) }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111827; background: #e5e7eb; margin: 0; padding: 20px; font-size: 13px; }
        .actions { width: 720px; max-width: 100%; margin: 0 auto 12px auto; display: flex; gap: 8px; }
        .btn { display: inline-block; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 13px; }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-back { background: #d1d5db; color: #111827; }
        .invoice { width: 720px; max-width: 100%; margin: 0 auto; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 40px 48px; }
        .center { text-align: center; }
        .muted { color: #6b7280; }
        .company-name { font-size: 20px; font-weight: 700; }
        .phones { font-size: 12px; margin-top: 4px; }
        hr { border: none; border-top: 1px solid #e5e7eb; margin: 14px 0; }
        .row { display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .box { flex: 1; min-width: 200px; line-height: 1.55; }
        .box-title { font-weight: 700; margin-bottom: 6px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px 6px; font-size: 12px; }
        th { text-align: left; background: #f9fafb; font-size: 11px; text-transform: uppercase; color: #6b7280; }
        .right { text-align: right; }
        .totals { margin-top: 14px; width: 240px; margin-left: auto; }
        .totals div { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; }
        .totals .grand { border-top: 2px solid #111827; margin-top: 6px; padding-top: 8px; font-weight: 700; font-size: 14px; }
        .footer { text-align: center; color: #6b7280; margin-top: 28px; font-size: 11px; }
        @media print { body { background: #fff; padding: 0; } .actions { display: none !important; } .invoice { width: 100%; border: none; border-radius: 0; padding: 20px 30px; } }
    </style>
</head>
<body>
<div class="actions">
    <a href="javascript:window.print()" class="btn btn-print">Print</a>
    <a href="{{ route('sales.index') }}" class="btn btn-back">Back to Sales</a>
</div>
<div class="invoice">
    <div class="center">
        <div class="company-name">Deo Traders</div>
        <div class="phones">Tel: <strong>0772991035</strong> | <strong>0758554160</strong> | <strong>0750531168</strong></div>
        <div>Issued: {{ optional($sale->created_at)->timezone('Africa/Kampala')->format('d M Y, h:i A') }}</div>
    </div>
    <hr>
    <div class="row">
        <div class="box">
            <div class="box-title">Invoice Details</div>
            <div><strong>Invoice No:</strong> {{ $sale->invoice_number ?? ('SALE-'.$sale->id) }}</div>
            <div><strong>Date:</strong> {{ optional($sale->created_at)->timezone('Africa/Kampala')->format('d M Y') }}</div>
            <div><strong>Time:</strong> {{ optional($sale->created_at)->timezone('Africa/Kampala')->format('h:i A') }}</div>
            <div><strong>Status:</strong> {{ ucfirst($sale->status ?? '-') }}</div>
        </div>
        <div class="box">
            <div class="box-title">Customer</div>
            <div><strong>Name:</strong> {{ $sale->customer_name ?: ($sale->customer->name ?? 'Walk-in Customer') }}</div>
            <div><strong>Phone:</strong> {{ $sale->customer->phone ?? '-' }}</div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:36px;">#</th>
                <th>Product</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($sale->items ?? []) as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product_name ?? '-' }}</td>
                    <td class="right">{{ number_format($item->quantity ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($item->unit_price ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($item->line_total ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="center muted">No items found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="totals">
        <div><span>Subtotal</span><span>{{ number_format((float) ($sale->subtotal ?? 0), 2) }}</span></div>
        <div><span>Discount</span><span>{{ number_format((float) ($sale->discount ?? 0), 2) }}</span></div>
        <div><span>Tax</span><span>{{ number_format((float) ($sale->tax ?? 0), 2) }}</span></div>
        <div class="grand"><span>Grand Total</span><span>{{ number_format((float) ($sale->total ?? 0), 2) }}</span></div>
        <div><span>Paid</span><span>{{ number_format((float) ($sale->paid ?? 0), 2) }}</span></div>
        <div style="color:#dc2626; font-weight:700;"><span>Balance</span><span>{{ number_format((float) ($sale->balance ?? 0), 2) }}</span></div>
    </div>
    <div class="footer">Thank you for doing business with Deo Traders</div>
</div>
</body>
</html>
