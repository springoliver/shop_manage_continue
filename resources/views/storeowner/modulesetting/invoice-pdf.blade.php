<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        .title { text-align: center; font-weight: bold; font-size: 14px; padding: 6px 0; border-bottom: 1px solid #000; }
        .section { margin-top: 10px; }
        .row { display: flex; justify-content: space-between; }
        .label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        .totals { width: 40%; margin-left: auto; margin-top: 10px; }
        .totals td { border: none; padding: 4px 6px; }
        .totals .label { text-align: right; }
    </style>
</head>
<body>
    <div class="title">ELECTRONIC INVOICE (PAID IN FULL)</div>

    <div class="section row">
        <div>
            <div><span class="label">Company:</span> {{ $store?->store_name ?? 'N/A' }}</div>
            <div><span class="label">Contact name:</span> {{ trim(($owner?->firstname ?? '') . ' ' . ($owner?->lastname ?? '')) ?: 'N/A' }}</div>
            <div><span class="label">Address:</span> {{ $store?->full_google_address ?? $owner?->address1 ?? 'N/A' }}</div>
            <div><span class="label">Tel:</span> {{ $owner?->phone ?? 'N/A' }}</div>
            <div><span class="label">Fax:</span> -</div>
        </div>
        <div>
            <div><span class="label">Invoice number:</span> {{ $invoiceNumber }}</div>
            <div><span class="label">Invoice date:</span> {{ $paidModule->purchase_date?->format('M d, Y') ?? now()->format('M d, Y') }}</div>
            <div><span class="label">Order Ref:</span> {{ $paidModule->pmid }}</div>
            <div><span class="label">Payment Ref:</span> {{ $paidModule->transactionid ?? '-' }}</div>
            <div><span class="label">Account Ref:</span> {{ $paidModule->storeid ?? '-' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="width: 120px;">Price</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $paidModule->module->module ?? 'Module' }}
                    @if ($paidModule->isTrial)
                        module installment
                    @else
                        module renewal
                    @endif
                </td>
                <td>€{{ number_format($paidModule->paid_amount ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @php
        $subtotal = (float) ($paidModule->paid_amount ?? 0);
        $vatRate = 0;
        $vatAmount = $subtotal * $vatRate;
        $total = $subtotal + $vatAmount;
    @endphp

    <table class="totals">
        <tr>
            <td class="label">Subtotal:</td>
            <td>€{{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label">VAT ({{ (int) ($vatRate * 100) }}%):</td>
            <td>€{{ number_format($vatAmount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Total:</td>
            <td>€{{ number_format($total, 2) }}</td>
        </tr>
    </table>
</body>
</html>
