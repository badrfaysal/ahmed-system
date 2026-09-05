{{--
    ═══════════════════════════════════════════════════════════════════════════
    🛡️ Global Form Handler — يطبّق على كل الصفحات
    ═══════════════════════════════════════════════════════════════════════════
    1) منع التكرار: أي زر submit أو action يتقفل بعد الضغطة الأولى
    2) Validation errors → SweetAlert popup
    3) session('error')/session('success') → popup
    4) session('open_modal') → فتح الـ modal تلقائياً (مع الداتا في الفورم عبر old())

    لاستخدام reopen-on-error: في الـ controller استخدم:
        return back()->with('error', $msg)->withInput()->with('open_modal', 'myModalId');
--}}

@once
<script>
(function () {
    'use strict';

    const isSwal = () => typeof Swal !== 'undefined';

    // ═══════════════════════════════════════════════════════════════════════
    // 1) منع التكرار على submit
    // ═══════════════════════════════════════════════════════════════════════
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;

        // لو الفورم اتعلم بـ data-no-busy، تجاهله
        if (form.dataset.noBusy === '1') return;

        // لو الفورم قيد التنفيذ، امنع submission تاني
        if (form.dataset.busy === '1') {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
        }
        form.dataset.busy = '1';

        const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        buttons.forEach(btn => {
            if (btn.disabled) return;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.dataset.wasDisabled  = 'now';
            btn.disabled = true;
            btn.style.cursor = 'wait';
            btn.style.opacity = '0.75';
            if (btn.tagName === 'BUTTON' && !btn.dataset.keepLabel) {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>جاري التنفيذ...';
            }
        });

        // 💡 توليد كود العملية
        if (form.method && form.method.toUpperCase() === 'POST' && !form.querySelector('input[name="transaction_code"]')) {
            const codeInput = document.createElement('input');
            codeInput.type = 'hidden';
            codeInput.name = 'transaction_code';
            codeInput.value = crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).substring(2);
            form.appendChild(codeInput);
        }

        // ضمان: لو الـ submission فشل (مفيش redirect)، يرجع الزر شغال بعد 30 ثانية
        setTimeout(() => releaseForm(form), 30000);
    }, true);

    function releaseForm(form) {
        if (!form || form.dataset.busy !== '1') return;
        form.dataset.busy = '0';
        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(btn => {
            if (btn.dataset.wasDisabled === 'now') {
                btn.disabled = false;
                btn.style.cursor = '';
                btn.style.opacity = '';
                if (btn.dataset.originalHtml) btn.innerHTML = btn.dataset.originalHtml;
                delete btn.dataset.originalHtml;
                delete btn.dataset.wasDisabled;
            }
        });
    }

    // منع double-click على الـ action buttons (مش submit)
    let lastClick = { el: null, t: 0 };
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('a[data-action], button[data-action]');
        if (!btn) return;
        const now = Date.now();
        if (lastClick.el === btn && now - lastClick.t < 1500) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
        }
        lastClick = { el: btn, t: now };
    }, true);

    // ═══════════════════════════════════════════════════════════════════════
    // 2) عرض الأخطاء / النجاح في popup
    // ═══════════════════════════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function () {
        // (أ) Validation errors
        @if($errors->any())
            if (isSwal()) {
                const errs = @json($errors->all());
                Swal.fire({
                    icon: 'error',
                    title: 'في خطأ في الإدخال',
                    html: '<ul style="text-align:right; padding-right:1.5em; font-size:0.95rem; line-height:1.7; margin:0;">'
                          + errs.map(e => '<li>' + e + '</li>').join('')
                          + '</ul>',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'فهمت',
                });
            }
        @endif

        // (ب) session error
        @if(session('error') && !$errors->any())
            if (isSwal()) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: @json(session('error')),
                    confirmButtonColor: '#dc2626',
                });
            }
        @endif

        // (ج) session success
        @if(session('success'))
            if (isSwal()) {
                Swal.fire({
                    icon: 'success',
                    title: 'تمت العملية بنجاح',
                    text: @json(session('success')),
                    timer: 3500,
                    showConfirmButton: false,
                    timerProgressBar: true,
                });
            }
        @endif

        // (د) فتح modal تلقائياً (لما الـ controller بيرجع with('open_modal'))
        @if(session('open_modal'))
            setTimeout(() => {
                const m = document.getElementById(@json(session('open_modal')));
                if (m && typeof bootstrap !== 'undefined') {
                    try { new bootstrap.Modal(m).show(); } catch(e) {}
                }
            }, 200);
        @endif
    });

    // expose helper لو حد محتاج يحرر form يدوياً
    window.releaseFormBusy = releaseForm;
})();
</script>
@endonce
