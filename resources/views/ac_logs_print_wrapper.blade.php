<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>سجل العمليات والمدارس</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #fff; padding: 20px; }
        .accordion-button::after { display: none !important; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #dee2e6; padding: 8px; text-align: right; }
        .data-table th { background-color: #f8f9fa; }
        .highlight { background-color: yellow !important; font-weight: bold; }
        .accordion-button { padding: 10px 15px; border-bottom: 1px solid #dee2e6; background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; margin-top: 15px; font-weight: bold; font-size: 1.1em; display: flex; align-items: center; justify-content: space-between; border-radius: 5px; }
        .accordion-collapse.show { display: block !important; }
        .badge { border: 1px solid #000; color: #000 !important; background: transparent !important; }
        mark.bg-warning { background-color: yellow !important; -webkit-print-color-adjust: exact; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="text-end mb-4 no-print">
        <button onclick="window.print()" class="btn btn-primary px-4"><i class="fa fa-print"></i> طباعة</button>
        <button onclick="window.close()" class="btn btn-secondary px-4">إغلاق</button>
    </div>
    
    <div class="text-center mb-4 border-bottom pb-3">
        <h2 class="fw-bold"><i class="fa fa-book me-2"></i> سجل العمليات والمدارس</h2>
    </div>
    
    @include('ac_logs_partial', ['reports' => $reports, 'highlight_terms' => $highlight_terms, 'isPrint' => true])

    <script>
        window.onload = function() {
            setTimeout(() => { window.print(); }, 500);
        }
    </script>
</body>
</html>
