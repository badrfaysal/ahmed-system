const fs = require('fs');
const text = fs.readFileSync('transcript_grep2.json', 'utf8'); // Maybe it was utf8?
const lines = text.split('\n');
console.log('Lines:', lines.length);
let found = false;
for (const line of lines) {
    if (!line.trim()) continue;
    try {
        const data = JSON.parse(line.trim());
        if (data.tool_calls) {
            for (const call of data.tool_calls) {
                if (call.arguments && call.arguments.CodeContent) {
                   if (call.arguments.CodeContent.includes('class AcController')) {
                       fs.writeFileSync('app/Http/Controllers/AcController.php', call.arguments.CodeContent, 'utf8');
                       console.log('Recovered from JSON.parse correctly!');
                       found = true;
                   }
                }
            }
        }
    } catch (e) {
        console.error(e.message);
    }
}
