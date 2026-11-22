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

    // تشغيل صوت الإشعار
    function playSuccessSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const playNote = (freq, delay, duration) => {
                const startTime = ctx.currentTime + delay;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine'; // موجة نقية جداً
                osc.frequency.value = freq;
                
                // تحكم في مستوى الصوت (Fade in -> Fade out)
                gain.gain.setValueAtTime(0, startTime);
                gain.gain.linearRampToValueAtTime(0.6, startTime + 0.05); // صوت أعلى وواضح
                gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
                
                osc.start(startTime);
                osc.stop(startTime + duration);
            };
            
            // نغمة نجاح متدرجة ورايقة جداً (C Major 7 Arpeggio) تستمر لـ 1.2 ثانية
            playNote(523.25, 0.00, 1.2); // C5
            playNote(659.25, 0.08, 1.1); // E5
            playNote(783.99, 0.16, 1.0); // G5
            playNote(987.77, 0.24, 0.9); // B5
        } catch(e) {
            console.error('Audio play failed', e);
        }
    }

    // O OOO1OO O O_OOO O Swal U,OOO_USU, O U,O_U^O
    let _swalPatched = false;
    function patchSwal() {
        if (!_swalPatched && isSwal()) {
            const orig = Swal.fire;
            Swal.fire = function(...args) {
                const arg = args[0];
                let isSuccess = false;
                if (typeof arg === 'string' && arg === 'success') isSuccess = true;
                if (args[2] === 'success') isSuccess = true;
                if (arg && typeof arg === 'object' && arg.icon === 'success') isSuccess = true;
                
                if (isSuccess) playSuccessSound();
                
                return orig.apply(this, args);
            };
            _swalPatched = true;
        }
    }
    
    // U.O-OU^U,O OOO_USU, Swal O"U.OO1O OO3UO1U,U OOU.USOU OOO O_O1
    let patchInterval = setInterval(() => {
        if (isSwal()) {
            patchSwal();
            clearInterval(patchInterval);
        }
    }, 100);

    // ═══════════════════════════════════════════════════════════════════════
    // 1) منع التكرار على submit + فحص تكرار الأسماء قبل الإرسال
    // ═══════════════════════════════════════════════════════════════════════
    const CHECK_DUP_URL = @json(route('api.check_duplicate_name'));

    function resolveUniqueInputParams(input) {
        let scope = input.dataset.uniqueScope || '';
        if (input.dataset.uniqueScopeEl) {
            const el = document.querySelector(input.dataset.uniqueScopeEl);
            if (el) {
                scope = (input.dataset.uniqueScopePrefix || '') + (el.value || '');
            }
        }
        let excludeId = input.dataset.uniqueExcludeId || '';
        if (input.dataset.uniqueExcludeIdEl) {
            const el = document.querySelector(input.dataset.uniqueExcludeIdEl);
            if (el) excludeId = el.value || '';
        }
        let excludeName = input.dataset.uniqueExcludeName || '';
        if (input.dataset.uniqueExcludeNameEl) {
            const el = document.querySelector(input.dataset.uniqueExcludeNameEl);
            if (el) excludeName = el.value || '';
        }
        const allowExisting = input.dataset.uniqueAllowExisting === '1' ? '1' : '0';
        return { scope, excludeId, excludeName, allowExisting };
    }

    function renderUniqueWarning(input, message) {
        let box = input._uniqueWarnBox;
        if (!box || !box.isConnected) {
            box = document.createElement('div');
            box.className = 'unique-name-warn-box';
            box.style.cssText = 'display:none;margin-top:6px;padding:8px 11px;border-radius:8px;background:#fffbeb;border:1.5px solid #f59e0b;color:#92400e;font-size:0.82rem;font-weight:700;line-height:1.5;text-align:right;';
            const parent = input.closest('.input-group')
                || (input.parentElement && input.parentElement.classList.contains('d-flex') && !input.parentElement.classList.contains('flex-column') ? input.parentElement : input);
            parent.insertAdjacentElement('afterend', box);
            input._uniqueWarnBox = box;
        }
        if (message) {
            box.textContent = message;
            box.style.display = 'block';
            input.style.borderColor = '#f59e0b';
            input.style.boxShadow = '0 0 0 0.2rem rgba(245, 158, 11, 0.25)';
            input.dataset.uniqueConflictMsg = message;
        } else {
            box.style.display = 'none';
            box.textContent = '';
            input.style.borderColor = '';
            input.style.boxShadow = '';
            delete input.dataset.uniqueConflictMsg;
        }
    }

    function isInputVisibleAndActive(inp) {
        if (!inp || inp.disabled) return false;
        if (inp.name === 'ignore_person') return false;
        return inp.offsetParent !== null;
    }

    async function checkUniqueInput(input) {
        const val = (input.value || '').trim();
        const { scope, excludeId, excludeName, allowExisting } = resolveUniqueInputParams(input);
        if (!val || !isInputVisibleAndActive(input)) {
            renderUniqueWarning(input, null);
            input.dataset.uniqueCheckedVal = val;
            input.dataset.uniqueCheckedScope = scope;
            return { exists: false, message: null };
        }
        const params = new URLSearchParams({
            name: val,
            scope: scope,
            allow_existing: allowExisting,
        });
        if (excludeId) params.set('exclude_id', excludeId);
        if (excludeName) params.set('exclude_name', excludeName);

        try {
            const res = await fetch(CHECK_DUP_URL + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return { exists: false, message: null };
            const data = await res.json();
            // تأكد أن القيمة لم تتغير أثناء الطلب
            if ((input.value || '').trim() !== val) return { exists: false, message: null };
            input.dataset.uniqueCheckedVal = val;
            input.dataset.uniqueCheckedScope = scope;
            if (data.exists && data.message) {
                renderUniqueWarning(input, data.message);
                return data;
            }
            renderUniqueWarning(input, null);
            return { exists: false, message: null };
        } catch (err) {
            return { exists: false, message: null };
        }
    }

    function showDuplicateWarningPopup(message, focusInput) {
        if (isSwal()) {
            Swal.fire({
                icon: 'warning',
                title: '⚠️ تحذير: لا يمكن تكرار الاسم!',
                text: message,
                confirmButtonColor: '#d97706',
                confirmButtonText: 'حسناً، سأغيّر الاسم',
            }).then(() => {
                if (focusInput && typeof focusInput.focus === 'function') focusInput.focus();
            });
        } else {
            alert(message);
            if (focusInput && typeof focusInput.focus === 'function') focusInput.focus();
        }
    }

    window.checkUniqueNameInput = checkUniqueInput;
    window.showDuplicateNameWarning = showDuplicateWarningPopup;
    window.clearUniqueNameWarning = (input) => { if (input) renderUniqueWarning(input, null); };

    // ربط فحص التكرار الفوري على كل الحقول المعلّمة بـ data-unique-scope
    let _dupDebounceTimers = new WeakMap();
    document.addEventListener('input', function (e) {
        const el = e.target;
        if (!(el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement)) return;
        if (!el.dataset.uniqueScope && !el.dataset.uniqueScopeEl) return;
        clearTimeout(_dupDebounceTimers.get(el));
        if (!(el.value || '').trim()) {
            renderUniqueWarning(el, null);
            return;
        }
        _dupDebounceTimers.set(el, setTimeout(() => checkUniqueInput(el), 350));
    }, true);

    document.addEventListener('blur', function (e) {
        const el = e.target;
        if (!(el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement)) return;
        if (!el.dataset.uniqueScope && !el.dataset.uniqueScopeEl) return;
        clearTimeout(_dupDebounceTimers.get(el));
        checkUniqueInput(el);
    }, true);

    document.addEventListener('change', function (e) {
        const sel = e.target;
        if (!sel || !sel.id) return;
        document.querySelectorAll(`[data-unique-scope-el="#${sel.id}"]`).forEach(inp => {
            if ((inp.value || '').trim()) checkUniqueInput(inp);
        });
    }, true);

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;

        // فحص أي حقل اسم مكرر ظاهر داخل الفورم قبل الإرسال
        const guardedInputs = Array.from(form.querySelectorAll('[data-unique-scope], [data-unique-scope-el]'))
            .filter(inp => isInputVisibleAndActive(inp) && (inp.value || '').trim() !== '');

        const conflictInput = guardedInputs.find(inp => inp.dataset.uniqueConflictMsg);
        if (conflictInput) {
            e.preventDefault();
            e.stopImmediatePropagation();
            showDuplicateWarningPopup(conflictInput.dataset.uniqueConflictMsg, conflictInput);
            return false;
        }

        // لو المستخدم ضغط حفظ بسرعة قبل ما الفحص اللحظي يخلّص: نفحص فوراً قبل الإرسال
        const uncheckedInputs = guardedInputs.filter(inp => {
            const { scope } = resolveUniqueInputParams(inp);
            return inp.dataset.uniqueCheckedVal !== (inp.value || '').trim()
                || inp.dataset.uniqueCheckedScope !== scope;
        });
        if (uncheckedInputs.length > 0 && form.dataset.uniqueChecking !== '1') {
            e.preventDefault();
            e.stopImmediatePropagation();
            form.dataset.uniqueChecking = '1';
            Promise.all(uncheckedInputs.map(inp => checkUniqueInput(inp))).then(results => {
                form.dataset.uniqueChecking = '0';
                const firstHitIdx = results.findIndex(r => r && r.exists && r.message);
                if (firstHitIdx !== -1) {
                    showDuplicateWarningPopup(results[firstHitIdx].message, uncheckedInputs[firstHitIdx]);
                    return;
                }
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }).catch(() => {
                form.dataset.uniqueChecking = '0';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            });
            return false;
        }

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

        // (أ-2) تحذير تكرار الاسم بالضبط
        @if(session('duplicate_warning'))
            showDuplicateWarningPopup(@json(session('duplicate_warning')));
        @endif

        // (ب) session error
        @if(session('error') && !$errors->any() && !session('duplicate_warning'))
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
            if (typeof playSuccessSound === 'function') playSuccessSound();
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
