const fs = require('fs');

let content = fs.readFileSync('resources/views/ac.blade.php', 'utf8');

// 1. Add SweetAlert2
if (!content.includes('sweetalert2')) {
    content = content.replace(
        '</head>',
        '    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>\n</head>'
    );
}

// 2. Replace confirm block with Swal
// We use match and replace to be safe.
const confirmRegex = /if\s*\(confirm\(result\.message \+ '\\n\\n.*?'\)\)\s*\{[\s\S]*?\}\s*else\s*\{[\s\S]*?\}/;

const swalCode = `
                let audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2013/2013-preview.mp3');
                audio.play().catch(e => console.log("Audio play blocked by browser", e));

                Swal.fire({
                    title: 'تمت العملية بنجاح!',
                    text: 'هل ترغب في طباعة الفاتورة الآن؟',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، اطبع الفاتورة',
                    cancelButtonText: 'لا، شكراً',
                    backdrop: \`rgba(0,0,123,0.4)\`
                }).then((r) => {
                    if (r.isConfirmed) {
                        let opId = result.id || result.op_id;
                        window.open('/ac/invoice/' + opId, '_blank');
                    }
                    window.location.reload();
                });
`;

if (confirmRegex.test(content)) {
    content = content.replace(confirmRegex, swalCode);
} else {
    console.log("Could not find confirm regex.");
}

fs.writeFileSync('resources/views/ac.blade.php', content, 'utf8');
console.log('Patched ac.blade.php!');
