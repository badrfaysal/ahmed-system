    `;
    openInstPrint(html);
};

// ──────────────────────────────────────────────
// 2. طباعة العقود المنتهية
// ──────────────────────────────────────────────
window.printCompletedInstallments = function() {
    const data = PRINT_COMPLETED;
    if (!data.length) { alert('لا توجد عقود منتهية للطباعة'); return; }

    let totalValue = 0, totalProfit = 0;
    const rows = data.map((c, i) => {
        totalValue  += c.total;
        totalProfit += c.profit;
        return `<tr>
            <td>${i + 1}</td>
            <td class="text-start"><strong>${c.name}</strong></td>
            <td dir="ltr">${c.phone}</td>
            <td class="text-start">${c.product}</td>
            <td>${fmtN(c.total)} ج</td>
            <td>${fmtN(c.down)} ج</td>
            <td class="num-pos"><strong>${fmtN(c.profit)} ج</strong></td>
            <td dir="ltr">${c.date}</td>
        </tr>`;
    }).join('');

    const html = `
        <!DOCTYPE html><html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>العقود المنتهية - شركة الضبع</title>
            <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>${getInstPrintStyles(true)}</style>
        </head>
        <body>
            <div class="page">
                ${getInstHeader('سجل العقود المنتهية')}
                <div class="summary cols-4">
                    <div class="box accent"><div class="label">عدد العقود</div><div class="val">${data.length}</div></div>
                    <div class="box"><div class="label">إجمالي قيمة العقود</div><div class="val">${fmtN(totalValue)} ج</div></div>
                    <div class="box success"><div class="label">إجمالي الأرباح</div><div class="val">${fmtN(totalProfit)} ج</div></div>
                    <div class="box warning"><div class="label">متوسط ربح/عقد</div><div class="val">${fmtN(totalProfit / Math.max(1, data.length))} ج</div></div>
                </div>
                <div class="section-title">تفاصيل العقود المنتهية</div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th><th class="text-start">العميل</th><th>الهاتف</th>
                            <th class="text-start">المنتج</th><th>إجمالي العقد</th>
                            <th>المقدم</th><th>الربح</th><th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-start" style="text-align:right; padding-right:14px;">الإجماليات:</td>
                            <td>${fmtN(totalValue)} ج</td>
                            <td>—</td>
                            <td class="num-pos">${fmtN(totalProfit)} ج</td>
                            <td>—</td>
                        </tr>
                    </tfoot>
                </table>
                ${getInstFooter('المحاسب', 'المدير المالي')}
            </div>
        </body></html>
    `;
    openInstPrint(html);
};

            `;
        }

        return `
            <div style="font-size:10px; font-weight:700; color:#334155; border-bottom:1px solid #e2e8f0; padding-bottom:4px; margin:12px 0 6px;">
                عقد #${c.id} - <span style="color:#0f172a;">${c.product}</span>
            </div>
            <table class="pay-table">
                <tr><td class="lbl">تاريخ التعاقد</td><td class="val">${c.date}</td></tr>
                <tr><td class="lbl">سعر الجهاز كاش</td><td class="val">${fmtN(c.cash)} ج</td></tr>
                <tr><td class="lbl">المقدم</td><td class="val">${fmtN(c.down)} ج</td></tr>
                <tr><td class="lbl">نسبة الفائدة</td><td class="val">${c.rate}%</td></tr>
                <tr><td class="lbl">إجمالي العقد</td><td class="val">${fmtN(c.total)} ج</td></tr>
                <tr class="summary-row"><td class="lbl">إجمالي المدفوع</td><td class="val">${fmtN(c.paid_total)} ج</td></tr>
                <tr class="summary-row remaining-row"><td class="lbl">إجمالي المتبقي</td><td class="val">${fmtN(c.remaining)} ج</td></tr>
            </table>
            ${payGrid}
        `;
    }

// ──────────────────────────────────────────────
// 4. طباعة كشف حساب عميل
// ──────────────────────────────────────────────
function generateContractHtml(c, idx = null) {
    let payGrid = '';
    if (c.months > 0) {
        const cellsPerRow = 6;
        let cellsHtml = '';
        for (let i = 1; i <= c.months; i++) {
            const pay = c.payments[i - 1];
            if (pay) {
                cellsHtml += `<td class="pay-paid"><div style="font-size:8px; opacity:0.7;">${i}</div><div style="font-weight:700;">${fmtN(pay.amount)}</div><div style="font-size:7.5px; opacity:0.65;" dir="ltr">${pay.date}</div></td>`;
            } else if (i === c.payments.length + 1) {
                cellsHtml += `<td class="pay-pending"><div style="font-size:8px; opacity:0.7;">${i}</div><div style="font-weight:700;">${fmtN(c.monthly)}</div><div style="font-size:7.5px;">⏳ التالي</div></td>`;
            } else {
                cellsHtml += `<td class="pay-empty"><div style="font-size:8px;">${i}</div><div style="font-weight:600;">${fmtN(c.monthly)}</div></td>`;
            }
            if (i % cellsPerRow === 0 && i < c.months) cellsHtml += '</tr><tr>';
        }
        const remainder = c.months % cellsPerRow;
        if (remainder > 0) {
            for (let f = 0; f < cellsPerRow - remainder; f++) cellsHtml += '<td style="background:#fff; border:none;"></td>';
        }
        payGrid = `
            <div style="font-size:9.5px; font-weight:600; color:#5a6478; margin: 4px 0 2px; display:flex; justify-content:space-between;">
                <span>جدول السداد (${c.payments.length}/${c.months} قسط)</span>
                <span style="font-size:8.5px;">
                    <span style="display:inline-block; width:10px; height:10px; background:#ecfdf5; border:1px solid #059669; vertical-align:middle; margin-left:3px;"></span>مدفوع
                    <span style="display:inline-block; width:10px; height:10px; background:#fef9f3; border:1px solid #92400e; vertical-align:middle; margin-right:8px; margin-left:3px;"></span>التالي
                    <span style="display:inline-block; width:10px; height:10px; background:#f8fafc; border:1px solid #c5cbd6; vertical-align:middle; margin-right:8px; margin-left:3px;"></span>منتظر
                </span>
            </div>
            <table class="pay-grid"><tr>${cellsHtml}</tr></table>
        `;
    }

    return `
        ${idx !== null ? `<div style="margin-top:10px; padding:5px 10px; background:#0f172a; color:#fff; font-weight:600; font-size:11px; border-radius:4px;">عقد رقم ${idx + 1}: ${c.product}</div>` : ''}
        <table class="statement">
            ${idx === null ? `<tr class="title-row"><td colspan="2" style="text-align:center;">${c.product}</td></tr>` : ''}
            <tr><td class="lbl">سعر الجهاز كاش</td><td class="val">${fmtN(c.device_price)} ج</td></tr>
            ${c.extras_total > 0 ? `
                ${c.transport_cost > 0 ? `<tr><td class="lbl">— نقل</td><td class="val">${fmtN(c.transport_cost)} ج</td></tr>` : ''}
                ${c.installation_cost > 0 ? `<tr><td class="lbl">— تركيب</td><td class="val">${fmtN(c.installation_cost)} ج</td></tr>` : ''}
                ${c.materials_cost > 0 ? `<tr><td class="lbl">— خامات</td><td class="val">${fmtN(c.materials_cost)} ج</td></tr>` : ''}
                <tr><td class="lbl">إجمالي بنود التركيب</td><td class="val">${fmtN(c.extras_total)} ج</td></tr>
                <tr><td class="lbl">الإجمالي (جهاز + تركيب)</td><td class="val">${fmtN(c.cash_price)} ج</td></tr>
            ` : ''}
            <tr><td class="lbl">المقدم</td><td class="val">${fmtN(c.down)} ج</td></tr>
            <tr><td class="lbl">عدد الأشهر</td><td class="val">${c.months} شهر</td></tr>
            ${c.interest > 0 ? `<tr><td class="lbl">النسبة</td><td class="val">${c.interest}%</td></tr>` : ''}
            <tr><td class="lbl">إجمالي بعد النسبة</td><td class="val">${fmtN(c.total)} ج</td></tr>
            <tr><td class="lbl">القسط الشهري</td><td class="val">${fmtN(c.monthly)} ج</td></tr>
            <tr><td class="lbl">يوم السداد الشهري</td><td class="val">يوم ${c.due_day}</td></tr>
            <tr class="summary-row"><td class="lbl">إجمالي المدفوع</td><td class="val">${fmtN(c.paid_total)} ج</td></tr>
            <tr class="summary-row remaining-row"><td class="lbl">إجمالي المتبقي</td><td class="val">${fmtN(c.remaining)} ج</td></tr>
        </table>
        ${payGrid}
    `;
}

window.printSingleContract = function(groupKey, contractId) {
    const cust = PRINT_CUSTOMERS[groupKey];
    if (!cust) { alert('بيانات العميل غير متاحة'); return; }

    const c = cust.contracts.find(ct => ct.id == contractId);
    if (!c) { alert('العقد غير موجود'); return; }

    const contractsHtml = generateContractHtml(c, null);

    const html = `
        <!DOCTYPE html><html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>طباعة عقد ${c.product} - ${cust.name}</title>
            <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>${getInstPrintStyles(false)}</style>
        </head>
        <body>
            <div class="page">
                ${getInstHeader('تفاصيل عقد تقسيط')}

                <div style="background:#fafbfd; border:1px solid #e6ebf3; border-radius:6px; padding:7px 12px; margin-bottom:8px; display:grid; grid-template-columns:repeat(4, 1fr); gap:8px;">
                    <div>
                        <div style="font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px;">اسم العميل</div>
                        <div style="font-size:12px; font-weight:700; color:#0f172a;">${cust.name}</div>
                    </div>
                    <div>
                        <div style="font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px;">رقم الهاتف</div>
                        <div style="font-size:11px; font-weight:600; color:#0f172a;" dir="ltr">${cust.phone}</div>
                    </div>
                    <div>
                        <div style="font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px;">رقم العقد</div>
                        <div style="font-size:12px; font-weight:700; color:#4f46e5;">#${c.id}</div>
                    </div>
                    <div>
                        <div style="font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px;">المتبقي بالعقد</div>
                        <div style="font-size:13px; font-weight:700; color:#dc2626;">${fmtN(c.remaining)} ج</div>
                    </div>
                </div>

                ${contractsHtml}
            </div>
        </body>
        </html>
    `;
    openInstPrint(html);
};

window.printCustomerStatement = function(groupKey) {
    const cust = PRINT_CUSTOMERS[groupKey];
    if (!cust) { alert('بيانات العميل غير متاحة'); return; }

    const contractsHtml = cust.contracts.map((c, idx) => generateContractHtml(c, idx)).join('');

    const totalContractValue = cust.contracts.reduce((s, c) => s + c.total, 0);
    const totalPaid          = cust.contracts.reduce((s, c) => s + c.paid_total, 0);
    const totalRemaining     = cust.contracts.reduce((s, c) => s + c.remaining, 0);

    const html = `
        <!DOCTYPE html><html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>كشف حساب ${cust.name} - شركة الضبع</title>
            <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>${getInstPrintStyles(false)}</style>
        </head>
        <body>
            <div class="page">
                ${getInstHeader('كشف حساب عميل')}

                <div style="background:#fafbfd; border:1px solid #e6ebf3; border-radius:6px; padding:7px 12px; margin-bottom:8px; display:grid; grid-template-columns:repeat(4, 1fr); gap:8px;">
                    <div>
                        <div style="font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px;">اسم العميل</div>
                        <div style="font-size:12px; font-weight:700; color:#0f172a;">${cust.name}</div>
                    </div>
                    <div>
                        <div style="font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px;">رقم الهاتف</div>
                        <div style="font-size:11px; font-weight:600; color:#0f172a;" dir="ltr">${cust.phone}</div>
                    </div>
                    <div>
                        <div style="font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px;">عدد العقود</div>
                        <div style="font-size:12px; font-weight:700; color:#4f46e5;">${cust.contracts.length} عقد</div>
                    </div>
                    <div>
                        <div style="font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px;">المتبقي الإجمالي</div>
                        <div style="font-size:13px; font-weight:700; color:#dc2626;">${fmtN(totalRemaining)} ج</div>
                    </div>
                </div>

                ${contractsHtml}

                ${cust.contracts.length > 1 ? `
                <div class="section-title">الإجمالي العام للعميل</div>
                <table class="statement">
                    <tr><td class="lbl">إجمالي قيمة العقود</td><td class="val">${fmtN(totalContractValue)} ج</td></tr>
                    <tr class="summary-row"><td class="lbl">إجمالي المسدد</td><td class="val">${fmtN(totalPaid)} ج</td></tr>
                    <tr class="summary-row remaining-row"><td class="lbl">المتبقي بالخارج</td><td class="val">${fmtN(totalRemaining)} ج</td></tr>
                </table>
                ` : ''}

                ${getInstFooter('توقيع العميل', 'موظف التحصيل')}
            </div>
        </body></html>
    `;
    openInstPrint(html);
};
</script>
</body>
</html>
