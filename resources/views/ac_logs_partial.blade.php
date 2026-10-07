@php $isPrint = $isPrint ?? false; @endphp
@if(!isset($tab))

@endif

        <div class="panel-pro mt-2 border-0 bg-transparent shadow-none">
    
    @if(empty($reports))
        <div class="panel-pro"><div class="empty-mini"><i class="fa fa-folder-open"></i> لا توجد عمليات في هذه الفترة</div></div>
    @else
        <div class="accordion" id="schoolsAccordion">
            @foreach($reports as $index => $rep)
            <div class="accordion-item border-0 mb-3 rounded shadow-sm overflow-hidden">
                <h2 class="accordion-header" id="heading-{{ $index }}">
                    <button class="accordion-button {{ $isPrint ? '' : 'collapsed' }} bg-white fw-bold text-dark fs-5" type="button" @if(!$isPrint) data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}" @endif>
                        <i class="fa fa-school text-primary me-2"></i> {{ $rep['client_name'] }}
                        <span class="badge bg-secondary ms-auto rounded-pill">{{ count($rep['operations']) }} عملية</span>
                    </button>
                </h2>
                <div id="collapse-{{ $index }}" class="accordion-collapse collapse {{ $isPrint ? 'show' : '' }}">
                    <div class="accordion-body p-0">
                        @if(!$isPrint)
                        <div class="p-2 text-end bg-light border-bottom">
                            <button type="button" class="btn btn-sm btn-primary" onclick="printClientRecord('collapse-{{ $index }}', '{{ addslashes($rep['client_name']) }}')">
                                <i class="fa fa-print me-1"></i> طباعة السجل
                            </button>
                        </div>
                        @endif
                        <div class="table-responsive">
                            <table class="data-table mb-0">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>نوع العملية / الأصناف</th>
                                        <th>الفني</th>
                                        <th>الدور</th>
                                        <th>الفصل / الغرفة</th>
                                        <th>الإجمالي</th>
                                        <th>الخصم</th>
                                        <th class="no-print">فاتورة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rep['operations'] as $op)
                                    @php
                                        $isCancelled = $op->status === 'cancelled';
                                        $isReturned = $op->status === 'returned';
                                        $rowClass = $isCancelled ? 'text-decoration-line-through text-muted opacity-50' : ($isReturned ? 'text-muted' : '');
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td class="text-nowrap">
                                            {{ \Carbon\Carbon::parse($op->date)->format('Y-m-d') }}
                                            @if($isCancelled)
                                                <br><span class="badge bg-danger mt-1">ملغية</span>
                                            @elseif($isReturned)
                                                <br><span class="badge bg-warning mt-1">مرتجع</span>
                                            @endif
                                        </td>
                                        <td class="text-start">
                                            @if($op->type == 'sale')
                                                <span class="badge-soft primary mb-1"><i class="fa fa-fan"></i> بيع وتركيب</span>
                                            @else
                                                <span class="badge-soft info mb-1"><i class="fa fa-tools"></i> صيانة</span>
                                            @endif
                                            <br>
                                            <small class="text-muted fw-bold mt-1 d-block">{{ !empty($op->items_text) ? $op->items_text : 'بدون تفاصيل' }}</small>
                                        </td>
                                        @php
                                            $techName = $op->tech_name ? explode(' - ', $op->tech_name)[0] : '-';
                                        @endphp
                                        <td class="text-secondary"><small><i class="fa fa-user-cog"></i> {{ $techName }}</small></td>
                                        @php
                                            $floorText = $op->multi_floors_text ?: ($op->floor_name ?? 'بدون / محذوف');
                                            $classText = $op->multi_classes_text ?: ($op->class_name ?? 'بدون / محذوف');
                                            if (!empty($highlight_terms)) {
                                                foreach ($highlight_terms as $term) {
                                                    $floorText = preg_replace('/(' . preg_quote($term, '/') . ')/i', '<mark class="bg-warning text-dark px-1">$1</mark>', $floorText);
                                                    $classText = preg_replace('/(' . preg_quote($term, '/') . ')/i', '<mark class="bg-warning text-dark px-1">$1</mark>', $classText);
                                                }
                                            }
                                        @endphp
                                        <td>{!! $floorText !!}</td>
                                        <td>{!! $classText !!}</td>
                                        <td class="fw-bold text-dark">{{ fmtMoney($op->total_amount) }}</td>
                                        <td class="num-neg">{{ $op->discount_amount > 0 ? fmtMoney($op->discount_amount) : '-' }}</td>
                                        <td class="no-print">
                                            <a href="{{ route('ac.invoice', $op->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill {{ $op->status !== 'active' ? 'disabled' : '' }}" title="طباعة الفاتورة"><i class="fa fa-print"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
