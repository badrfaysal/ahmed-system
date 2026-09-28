const fs = require('fs');
let content = fs.readFileSync('app/Http/Controllers/AcController.php', 'utf8');

content = content.replace(/str_contains\(\$i->category \?\? '', '.*?'\)/, "str_contains($i->category ?? '', 'تكييف')");

fs.writeFileSync('app/Http/Controllers/AcController.php', content, 'utf8');
console.log('Fixed str_contains to تكييف!');
