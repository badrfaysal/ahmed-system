<?php
$path = 'd:/Projects/ahmed-system/ahmed-system/resources/views/ac.blade.php';
$content = file_get_contents($path);

// The exact string to replace
$target = '<div class="cart-total">
                            إجمالي: <span id="cart-total-val">0</span> ج
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-bold">خزينة الدفع والإيداع</label>';

$replacement = '<div class="cart-total">
                            إجمالي: <span id="cart-total-val">0</span> ج
                        </div>

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
                            <label class="form-label fw-bold">خزينة الدفع والإيداع</label>';

// Normalize line endings to LF before replacing, just in case
$content_lf = str_replace("\r\n", "\n", $content);
$target_lf = str_replace("\r\n", "\n", $target);
$replacement_lf = str_replace("\r\n", "\n", $replacement);

if (strpos($content_lf, $target_lf) !== false) {
    $content_lf = str_replace($target_lf, $replacement_lf, $content_lf);
    
    // Add the javascript before the form closing tag
    $js = '
                        <script>
                            function togglePaymentAmount() {
                                const method = document.getElementById("payment_method").value;
                                const amountDiv = document.getElementById("paid_amount_div");
                                const treasuryDiv = document.getElementById("treasury_div");
                                const treasurySelect = document.querySelector("select[name=\'deposit_account_id\']");
                                const paidAmount = document.getElementById("paid_amount");
                                
                                if (method === "partial") {
                                    amountDiv.style.display = "block";
                                    paidAmount.required = true;
                                } else {
                                    amountDiv.style.display = "none";
                                    paidAmount.required = false;
                                }

                                if (method === "later") {
                                    treasuryDiv.style.display = "none";
                                    if(treasurySelect) treasurySelect.required = false;
                                } else {
                                    treasuryDiv.style.display = "block";
                                    if(treasurySelect) treasurySelect.required = true;
                                }
                            }
                            document.addEventListener("DOMContentLoaded", togglePaymentAmount);
                        </script>
                        <button type="button" class="btn btn-success w-100 fw-bold py-2 rounded-4" onclick="submitForm()"><i class="fa fa-check me-2"></i>حفظ العملية</button>
                    </form>';
                    
    $target_js = '<button type="button" class="btn btn-success w-100 fw-bold py-2 rounded-4" onclick="submitForm()"><i class="fa fa-check me-2"></i>حفظ العملية</button>
                    </form>';
                    
    $content_lf = str_replace($target_js, $js, $content_lf);

    file_put_contents($path, $content_lf);
    echo "Fixed successfully!\n";
} else {
    echo "Target not found in PHP!\n";
}
