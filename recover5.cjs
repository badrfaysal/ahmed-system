const fs = require('fs');
const text = fs.readFileSync('transcript_grep2.json', 'utf16le');
const lines = text.split('\n');
for (const line of lines) {
    if (!line.trim()) continue;
    try {
        const data = JSON.parse(line.trim());
        if (data.tool_calls) {
            for (const call of data.tool_calls) {
                if (call.name === 'write_to_file') {
                    let args = call.arguments;
                    if (typeof args === 'string') args = JSON.parse(args);
                    fs.writeFileSync('app/Http/Controllers/AcController.php', args.CodeContent, 'utf8');
                    console.log('RECOVERED PERFECTLY!');
                }
            }
        }
    } catch (e) {
    }
}
