const fs = require('fs');

let content = fs.readFileSync('resources/views/ac.blade.php', 'utf8');

// Replace openSchoolProfile
const regex = /let currentSchoolNetProfit = 0;[\s\S]*?function simulateDiscount\(\) \{/g;

const replacement = `let currentSchoolNetProfit = 0;
    let currentSchoolIdForProfile = null;

    function openSchoolProfile(id) {
        currentSchoolIdForProfile = id;
        document.getElementById('sp_filter_start').value = '';
        document.getElementById('sp_filter_end').value = '';
        loadSchoolProfileData(id, '', '');
    }

    function applySchoolProfileFilter() {
        if (!currentSchoolIdForProfile) return;
        let start = document.getElementById('sp_filter_start').value;
        let end = document.getElementById('sp_filter_end').value;
        loadSchoolProfileData(currentSchoolIdForProfile, start, end);
    }

    function resetSchoolProfileFilter() {
        if (!currentSchoolIdForProfile) return;
        document.getElementById('sp_filter_start').value = '';
        document.getElementById('sp_filter_end').value = '';
        loadSchoolProfileData(currentSchoolIdForProfile, '', '');
    }

    function loadSchoolProfileData(id, start = '', end = '') {
        Swal.fire({ title: 'جاري تحميل الملف...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        let url = \`/ac/clients/\${id}/profile\`;
        if (start || end) {
            url += \`?start_date=\${start}&end_date=\${end}\`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if(data.success) {
                    const p = data.data;
                    document.getElementById('sp_client_name').innerText = p.client_name;
                    document.getElementById('sp_floors').innerText = p.floors_count;
                    document.getElementById('sp_classes').innerText = p.classes_count;
                    document.getElementById('sp_total_ops').innerText = p.sales_count + p.maint_count;

                    document.getElementById('sp_sales_rev').innerText = Number(p.sales_revenue).toFixed(2);
                    document.getElementById('sp_sales_cost').innerText = Number(p.sales_cost).toFixed(2);
                    document.getElementById('sp_sales_profit').innerText = Number(p.sales_profit).toFixed(2);

                    document.getElementById('sp_maint_rev').innerText = Number(p.maint_revenue).toFixed(2);
                    document.getElementById('sp_maint_cost').innerText = Number(p.maint_cost).toFixed(2);
                    document.getElementById('sp_maint_profit').innerText = Number(p.maint_profit).toFixed(2);

                    document.getElementById('sp_discounts').innerText = Number(p.total_discounts).toFixed(2);
                    document.getElementById('sp_expenses').innerText = Number(p.total_expenses).toFixed(2);

                    currentSchoolNetProfit = Number(p.net_profit);
                    document.getElementById('sp_net_profit').innerText = currentSchoolNetProfit.toFixed(2) + ' ج';
                    
                    let netCard = document.getElementById('sp_net_card');
                    let netStatus = document.getElementById('sp_net_status');
                    
                    if(currentSchoolNetProfit > 0) {
                        netCard.className = "card border-0 shadow-sm rounded-4 h-100 bg-success bg-opacity-10 border border-success-subtle";
                        document.getElementById('sp_net_profit').className = "display-4 fw-bold mb-0 text-success";
                        netStatus.innerHTML = '<span class="text-success fw-bold"><i class="fa fa-smile me-1"></i>المدرسة تحقق ربحاً</span>';
                    } else if (currentSchoolNetProfit < 0) {
                        netCard.className = "card border-0 shadow-sm rounded-4 h-100 bg-danger bg-opacity-10 border border-danger-subtle";
                        document.getElementById('sp_net_profit').className = "display-4 fw-bold mb-0 text-danger";
                        netStatus.innerHTML = '<span class="text-danger fw-bold"><i class="fa fa-frown me-1"></i>المدرسة تتسبب في خسارة</span>';
                    } else {
                        netCard.className = "card border-0 shadow-sm rounded-4 h-100 bg-warning bg-opacity-10 border border-warning-subtle";
                        document.getElementById('sp_net_profit').className = "display-4 fw-bold mb-0 text-warning";
                        netStatus.innerHTML = '<span class="text-warning fw-bold"><i class="fa fa-meh me-1"></i>نقطة التعادل (لا ربح ولا خسارة)</span>';
                    }

                    document.getElementById('sp_sim_discount').value = '';
                    document.getElementById('sp_sim_result').innerText = '';

                    let m = bootstrap.Modal.getInstance(document.getElementById('schoolProfileModal'));
                    if (!m) {
                        m = new bootstrap.Modal(document.getElementById('schoolProfileModal'));
                    }
                    m.show();
                } else {
                    Swal.fire('خطأ', data.message, 'error');
                }
            }).catch(err => {
                Swal.fire('خطأ', 'فشل الاتصال بالسيرفر.', 'error');
            });
    }

    function simulateDiscount() {`;

content = content.replace(regex, replacement);
fs.writeFileSync('resources/views/ac.blade.php', content, 'utf8');
console.log('Patched JS!');
