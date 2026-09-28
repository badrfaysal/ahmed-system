const fs = require('fs'); 
let content = fs.readFileSync('resources/views/ac.blade.php', 'utf8'); 
content = content.replace(/let html = await res.text\(\);\s*container\.innerHTML = html;/g, 'let html = await res.text();\n            $(container).html(html);'); 
fs.writeFileSync('resources/views/ac.blade.php', content);
