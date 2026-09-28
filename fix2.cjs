const fs = require('fs');
let content = fs.readFileSync('app/Http/Controllers/AcController.php', 'utf8');

// Replace any mangled '?????' with correct Arabic
content = content.replace(/str_contains\(\->category \?\? '', '.*?'\)/, "str_contains(\->category ?? '', '?????')");

fs.writeFileSync('app/Http/Controllers/AcController.php', content, 'utf8');
console.log('Fixed str_contains!');
