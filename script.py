import sys

path = r'd:\Projects\ahmed-system\ahmed-system\resources\views\ac.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """                        <div class="mt-3">
                            <label class="form-label fw-bold">خزينة الدفع والإيداع</label>
                            <select name="deposit_account_id" class="form-select mb-3" required>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance,2) }})</option>
                                @endforeach
                            </select>
                        </div>"""

replacement = """                        <div class="mt-3">
                            <label class="form-label fw-bold">طريقة الدفع</label>
                            <select name="payment_method" id="payment_method" class="form-select mb-3" onchange="togglePaymentAmount()">
                                <option value="cash">كله كاش</option>
                                <option value="later">كله آجل</option>
                                <option value="partial">جزئي (مقدم)</option>
                            </select>
                        </div>

                        <div class="mt-3" id="paid_amount_div" style="display: none;">
                            <label class="form-label fw-bold">المبلغ المدفوع (المقدم)</label>
                            <input type="number" name="paid_amount" id="paid_amount" class="form-control mb-3" step="0.01" min="0">
                        </div>

                        <div class="mt-3" id="treasury_div">
                            <label class="form-label fw-bold">خزينة الدفع والإيداع</label>
                            <select name="deposit_account_id" id="deposit_account_id" class="form-select mb-3" required>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance,2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <script>
                            function togglePaymentAmount() {
                                const method = document.getElementById('payment_method').value;
                                const amountDiv = document.getElementById('paid_amount_div');
                                const treasuryDiv = document.getElementById('treasury_div');
                                const treasurySelect = document.getElementById('deposit_account_id');
                                const paidAmount = document.getElementById('paid_amount');
                                
                                if (method === 'partial') {
                                    amountDiv.style.display = 'block';
                                    paidAmount.required = true;
                                } else {
                                    amountDiv.style.display = 'none';
                                    paidAmount.required = false;
                                }

                                if (method === 'later') {
                                    treasuryDiv.style.display = 'none';
                                    treasurySelect.required = false;
                                } else {
                                    treasuryDiv.style.display = 'block';
                                    treasurySelect.required = true;
                                }
                            }
                            document.addEventListener('DOMContentLoaded', togglePaymentAmount);
                        </script>"""

if target in content:
    content = content.replace(target, replacement)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Replaced successfully!")
else:
    print("Target not found. Doing a fallback replace.")
    # In case of line ending issues:
    content = content.replace(target.replace('\r\n', '\n'), replacement)
    content = content.replace(target.replace('\n', '\r\n'), replacement)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Fallback replace done.")
