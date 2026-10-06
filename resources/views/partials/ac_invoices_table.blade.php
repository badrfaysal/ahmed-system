@if($invoices->isEmpty())
    <div class="alert alert-info text-center mt-3">لا توجد فواتير تكييفات مسجلة</div>
@else
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-striped align-middle text-center mt-3">
            <thead class="table-dark">
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>العميل (المدرسة)</th>
                    <th>التاريخ</th>
                    <th>الإجمالي</th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $inv)
                @if($inv->status === 'cancelled')
                <tr class="table-danger text-muted text-decoration-line-through">
                    <td class="fw-bold">
                        #{{ $inv->id }}
                        <br><span class="badge bg-danger mt-1">ملغاة / معدلة</span>
                        @if($inv->notes)
                            <br><small class="text-dark fw-bold text-decoration-none">{{ $inv->notes }}</small>
                        @endif
                    </td>
                    <td>{{ $inv->customer_name }}</td>
                    <td>{{ $inv->start_date }}</td>
                    <td class="text-primary fw-bold">{{ number_format($inv->cash_price, 2) }} ج</td>
                    <td class="text-success">{{ number_format($inv->down_payment, 2) }} ج</td>
                    <td class="text-danger">{{ number_format($inv->remaining_after_down, 2) }} ج</td>
                    <td>
                        <a href="{{ url('/ac/invoice/' . ($inv->op_ids ?: $inv->id)) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="طباعة">
                            <i class="fa fa-print"></i>
                        </a>
                    </td>
                </tr>
                @else
                <tr>
                    <td class="fw-bold">#{{ $inv->id }}</td>
                    <td>{{ $inv->customer_name }}</td>
                    <td>{{ $inv->start_date }}</td>
                    <td class="text-primary fw-bold">{{ number_format($inv->cash_price, 2) }} ج</td>
                    <td class="text-success">{{ number_format($inv->down_payment, 2) }} ج</td>
                    <td class="text-danger">{{ number_format($inv->remaining_after_down, 2) }} ج</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ url('/ac/invoice/' . ($inv->op_ids ?: $inv->id)) }}" target="_blank" class="btn btn-outline-dark" title="طباعة">
                                <i class="fa fa-print"></i>
                            </a>
                            <button class="btn btn-outline-danger" onclick="deleteAcInvoice({{ $inv->id }})" title="حذف">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center mt-3">
        {!! $invoices->links('pagination::bootstrap-5') !!}
    </div>
@endif
