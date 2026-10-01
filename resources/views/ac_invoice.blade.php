<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة تكييفات وصيانة رقم {{ $operation->id }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
            color: #000;
        }
        .invoice-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .invoice-header {
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: 900;
            color: #333;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .table th {
            background-color: #f1f1f1;
            font-weight: bold;
        }
        .total-row {
            font-size: 1.2rem;
            font-weight: bold;
            background-color: #f8f9fa;
        }
        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .invoice-container { box-shadow: none; margin: 0; padding: 20px; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="container invoice-container">
    <div class="text-end no-print mb-3">
        <button onclick="window.print()" class="btn btn-primary px-4"><i class="fa fa-print"></i> طباعة</button>
        <button onclick="window.close()" class="btn btn-secondary px-4">إغلاق</button>
    </div>

    <div class="invoice-header d-flex justify-content-between align-items-center">
        <div>
            @php
                $settings = \Illuminate\Support\Facades\DB::table('system_settings')->pluck('value', 'key');
                $companyName = $settings['invoice_header_text'] ?? 'نظام أحمد';
            @endphp
            <h2 class="invoice-title m-0">{{ $companyName }}</h2>
            <p class="text-muted m-0 mt-1">قسم التكييفات والصيانة</p>
        </div>
        <div class="text-start">
            <h4 class="m-0 fw-bold">فاتورة رقم: #{{ str_pad($operation->id, 5, '0', STR_PAD_LEFT) }}</h4>
            <p class="m-0 mt-1 text-muted">التاريخ: {{ \Carbon\Carbon::parse($operation->created_at)->format('Y-m-d h:i A') }}</p>
        </div>
    </div>

    <div class="row invoice-details border rounded p-3 mb-4 mx-0 bg-light">
        <div class="col-sm-6">
            <h5 class="fw-bold mb-3">بيانات العميل</h5>
            <p class="m-1"><strong>الاسم / الجهة:</strong> {{ $operation->client_name }}</p>
            @if($operation->multi_floors_text)
            <p class="m-1"><strong>الدور:</strong> {{ $operation->multi_floors_text }}</p>
            @elseif($operation->floor_name)
            <p class="m-1"><strong>الدور:</strong> {{ $operation->floor_name }}</p>
            @endif

            @if($operation->multi_classes_text)
            <p class="m-1"><strong>الفصل / الغرفة:</strong> {{ $operation->multi_classes_text }}</p>
            @elseif($operation->class_name)
            <p class="m-1"><strong>الفصل / الغرفة:</strong> {{ $operation->class_name }}</p>
            @endif
        </div>
        <div class="col-sm-6 text-sm-start mt-3 mt-sm-0">
            <h5 class="fw-bold mb-3">بيانات العملية</h5>
            <p class="m-1"><strong>نوع العملية:</strong> 
                {{ $operation->type == 'sale' ? 'مبيعات وتركيب' : ($operation->type == 'متعدد' ? 'عمليات متعددة' : 'صيانة') }}
            </p>
            @if($operation->maintenance_type_name)
            <p class="m-1"><strong>نوع الصيانة:</strong> {{ $operation->maintenance_type_name }}</p>
            @endif
            <p class="m-1"><strong>المسؤول:</strong> {{ \App\Models\User::find($operation->created_by)->name ?? 'مدير النظام' }}</p>
        </div>
    </div>

    <table class="table table-bordered text-center align-middle">
        <thead>
            <tr>
                <th width="5%">م</th>
                <th width="50%">البيان (الصنف)</th>
                <th width="15%">الكمية</th>
                <th width="15%">السعر</th>
                <th width="15%">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @if($items->count() > 0)
                @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-start">{{ $item->product_name ?? $item->item_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            @endif
            
            @if($operation->type == 'maintenance' && $operation->maintenance_type_name && $items->count() == 0)
                <tr>
                    <td>1</td>
                    <td class="text-start">مصنعية صيانة يدوية ({{ $operation->maintenance_type_name }})</td>
                    <td>1</td>
                    <td>{{ number_format($operation->total_amount + $operation->discount_amount, 2) }}</td>
                    <td>{{ number_format($operation->total_amount + $operation->discount_amount, 2) }}</td>
                </tr>
            @endif
            
            @if($operation->discount_amount > 0)
            <tr>
                <td colspan="4" class="text-start fw-bold text-danger">الخصم</td>
                <td class="fw-bold text-danger">-{{ number_format($operation->discount_amount, 2) }}</td>
            </tr>
            @endif
            
            <tr class="total-row">
                <td colspan="4" class="text-start">الصافي المطلوب (جنيهاً)</td>
                <td>{{ number_format($operation->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-5 text-center">
        <p class="text-muted">{{ $settings['invoice_footer_text'] ?? 'شكراً لتعاملكم معنا' }}</p>
    </div>
</div>

<script>
    // Automatically open print dialog
    window.onload = function() {
        setTimeout(function() {
            window.print();
        }, 500);
    }
</script>

</body>
</html>
