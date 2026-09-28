const fs = require('fs');

let content = fs.readFileSync('resources/views/ac.blade.php', 'utf8');

const expensesTabRegex = /<!-- EXPENSES TAB -->[\s\S]*?<!-- Close tab-expenses -->/g;

const newExpensesTab = `<!-- EXPENSES TAB -->
        <div class="tab-pane fade" id="tab-expenses">
            <div class="row">
                <!-- Add Expense Form -->
                <div class="col-md-8 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 text-primary"><i class="fa fa-money-bill-wave me-2"></i>تسجيل مصروف جديد</h5>
                        </div>
                        <div class="card-body p-4">
                            <form id="expenseForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">المدرسة / العميل</label>
                                        <select class="form-select select2-clients" name="ac_client_id" required>
                                            <option value="">-- اختر --</option>
                                            @foreach($clients as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">بند المصروف</label>
                                        <select class="form-select" name="ac_expense_category_id" required>
                                            <option value="">-- اختر --</option>
                                            @foreach($expenseCategories as $ec)
                                                <option value="{{ $ec->id }}">{{ $ec->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">الخزينة / المحفظة</label>
                                        <select class="form-select" name="account_id" required>
                                            <option value="">-- اختر --</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance, 2) }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">المبلغ</label>
                                        <input type="number" step="0.01" class="form-control" name="amount" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">التاريخ</label>
                                        <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label fw-bold">ملاحظات / بيان</label>
                                        <input type="text" class="form-control" name="notes">
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-2"></i>حفظ المصروف</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 text-secondary"><i class="fa fa-chart-pie me-2"></i>تفنيد بنود المصروفات</h5>
                        </div>
                        <div class="card-body p-3 d-flex justify-content-center align-items-center">
                            @if(count($expensesByCategory ?? []) > 0)
                                <div style="position: relative; width: 100%; height: 280px;">
                                    <canvas id="expensesPieChart"></canvas>
                                </div>
                            @else
                                <div class="text-muted text-center"><i class="fa fa-info-circle fa-2x mb-2"></i><br>لا توجد بيانات</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Expenses -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 text-secondary"><i class="fa fa-history me-2"></i>أحدث المصروفات المسجلة</h5>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="expensesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>التاريخ</th>
                                    <th>المدرسة / العميل</th>
                                    <th>البند</th>
                                    <th>المبلغ</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentExpenses ?? [] as $exp)
                                <tr>
                                    <td>{{ \\Carbon\\Carbon::parse($exp->date)->format('Y-m-d') }}</td>
                                    <td class="fw-bold">{{ $exp->client_name }}</td>
                                    <td><span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">{{ $exp->category_name }}</span></td>
                                    <td class="fw-bold text-danger">{{ number_format($exp->amount, 2) }} ج</td>
                                    <td class="text-muted small">{{ $exp->notes ?: '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> <!-- Close tab-expenses -->`;

const newContent = content.replace(expensesTabRegex, newExpensesTab);

fs.writeFileSync('resources/views/ac.blade.php', newContent, 'utf8');
console.log('Patched expenses tab layout!');
