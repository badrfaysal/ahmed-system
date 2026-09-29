<?php

$path = 'd:\\Projects\\ahmed-system\\ahmed-system\\resources\\views\\ac.blade.php';
$content = file_get_contents($path);

$target = <<<EOT
                        <div class="mt-3">
                            <label class="form-label fw-bold">خزينة الدفع والإيداع</label>
                            <select name="deposit_account_id" class="form-select mb-3" required>
                                @foreach(\$accounts as \$acc)
                                    <option value="{{ \$acc->id }}">{{ \$acc->account_name }} ({{ number_format(\$acc->balance,2) }})</option>
                                @endforeach
                            </select>
                        </div>
EOT;

$replacement = <<<EOT
                        <div class="mt-3">
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
                                @foreach(\$accounts as \$acc)
                                    <option value="{{ \$acc->id }}">{{ \$acc->account_name }} ({{ number_format(\$acc->balance,2) }})</option>
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
                        </script>
EOT;

$content = str_replace($target, $replacement, $content);
$content = str_replace(str_replace("\r\n", "\n", $target), $replacement, $content);

file_put_contents($path, $content);
echo "Replaced successfully!\n";
