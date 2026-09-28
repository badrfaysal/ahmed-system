const fs = require('fs');
const path = 'd:/Projects/ahmed-system/ahmed-system/resources/views/ac.blade.php';
let content = fs.readFileSync(path, 'utf8');

// 1. Add total expenses below pie chart
const oldPieEnd = `@endif\r\n                        </div>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n\r\n            <!-- Recent Expenses -->`;

const newPieEnd = `@endif\r\n                            <div class="w-100 mt-3 pt-3 border-top">\r\n                                <div class="d-flex justify-content-between align-items-center bg-danger bg-opacity-10 rounded-3 p-3">\r\n                                    <span class="fw-bold text-danger"><i class="fa fa-money-bill-wave me-2"></i>\u0625\u062c\u0645\u0627\u0644\u064a \u0627\u0644\u0645\u0635\u0631\u0648\u0641\u0627\u062a</span>\r\n                                    <span class="fw-bold text-danger fs-4">{{ number_format(collect($recentExpenses ?? [])->sum('amount'), 2) }} \u062c</span>\r\n                                </div>\r\n                            </div>\r\n                        </div>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n\r\n            <!-- Recent Expenses -->`;

if (content.includes(oldPieEnd)) {
    content = content.replace(oldPieEnd, newPieEnd);
    console.log('✅ Added total expenses');
} else {
    console.log('❌ Could not find pie chart end marker');
    // Debug: find the @endif near line 418
    const lines = content.split('\n');
    for (let i = 415; i < 430; i++) {
        console.log(`${i+1}: ${JSON.stringify(lines[i])}`);
    }
}

// 2. Fix expenses table headers - center align
content = content.replace(
    '<th>التاريخ</th>\r\n                                    <th>المدرسة / العميل</th>\r\n                                    <th>البند</th>\r\n                                    <th>المبلغ</th>\r\n                                    <th>ملاحظات</th>',
    '<th class="text-center">التاريخ</th>\r\n                                    <th class="text-center">المدرسة / العميل</th>\r\n                                    <th class="text-center">البند</th>\r\n                                    <th class="text-center">المبلغ</th>\r\n                                    <th class="text-center">ملاحظات</th>'
);

// 3. Fix expenses table data cells - center align
content = content.replace(
    '<td>{{ \\Carbon\\Carbon::parse($exp->date)->format(\'Y-m-d\') }}</td>\r\n                                    <td class="fw-bold">{{ $exp->client_name }}</td>\r\n                                    <td><span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">{{ $exp->category_name }}</span></td>\r\n                                    <td class="fw-bold text-danger">{{ number_format($exp->amount, 2) }} ج</td>\r\n                                    <td class="text-muted small">{{ $exp->notes ?: \'-\' }}</td>',
    '<td class="text-center">{{ \\Carbon\\Carbon::parse($exp->date)->format(\'Y-m-d\') }}</td>\r\n                                    <td class="text-center fw-bold">{{ $exp->client_name }}</td>\r\n                                    <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">{{ $exp->category_name }}</span></td>\r\n                                    <td class="text-center fw-bold text-danger">{{ number_format($exp->amount, 2) }} ج</td>\r\n                                    <td class="text-center text-muted small">{{ $exp->notes ?: \'-\' }}</td>'
);

fs.writeFileSync(path, content);
console.log('✅ All changes applied');
