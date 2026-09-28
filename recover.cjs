const fs = require('fs');
const text = fs.readFileSync('transcript_grep2.json', 'utf16le');
const lines = text.split('\n');
console.log('Lines:', lines.length);
let found = false;
for (const line of lines) {
    if (!line.trim()) continue;
    try {
        const data = JSON.parse(line.trim());
        if (data.tool_calls) {
            for (const call of data.tool_calls) {
                if (call.arguments) {
                   const argsStr = JSON.stringify(call.arguments);
                   if (argsStr.includes('class AcController')) {
                       if (call.arguments.CodeContent) {
                           fs.writeFileSync('app/Http/Controllers/AcController.php', call.arguments.CodeContent, 'utf8');
                           console.log('Recovered from CodeContent!');
                           found = true;
                       } else if (call.arguments.ReplacementContent) {
                           console.log('Found replace_file_content...');
                       }
                   }
                }
            }
        }
    } catch (e) {
        // ignore
    }
}
if (!found) {
    // try searching raw text
    const match = text.match(/"CodeContent":"(.*?)","Description"/);
    if (match) {
        const unescaped = JSON.parse('"' + match[1] + '"');
        fs.writeFileSync('app/Http/Controllers/AcController.php', unescaped, 'utf8');
        console.log('Recovered via raw regex!');
    }
}
