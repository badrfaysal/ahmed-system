const fs = require('fs');
let content = fs.readFileSync('resources/views/ac.blade.php', 'utf8');

// 1. Rename tab
content = content.replace(/شاشة الكاشير/g, 'تسجيل عملية');

// 2. Add localstorage logic for tabs
const localstorageScript = `
      // Remember active tab
      document.addEventListener("DOMContentLoaded", function() {
          let activeTab = localStorage.getItem('ac_active_tab');
          if(activeTab) {
              let tabBtn = document.querySelector('button[data-bs-target="' + activeTab + '"]');
              if(tabBtn) {
                  let bsTab = new bootstrap.Tab(tabBtn);
                  bsTab.show();
              }
          }
          
          document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(btn => {
              btn.addEventListener('shown.bs.tab', function (e) {
                  localStorage.setItem('ac_active_tab', e.target.getAttribute('data-bs-target'));
              });
          });
      });
`;

if (content.includes('<script>')) {
    content = content.replace('<script>', '<script>\n' + localstorageScript);
} else {
    // just append it
    content += '\n<script>\n' + localstorageScript + '\n</script>';
}

fs.writeFileSync('resources/views/ac.blade.php', content, 'utf8');
console.log('Fixed ac.blade.php view issues!');
