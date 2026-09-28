const fs = require('fs');
const text = fs.readFileSync('transcript_grep2.json', 'utf16le');
const lines = text.split('\n');
for (const line of lines) {
    if (!line.trim()) continue;
    try {
        const data = JSON.parse(line.trim());
        if (data.tool_calls) {
            for (const call of data.tool_calls) {
                console.log('Tool:', call.name);
                if (call.name === 'write_to_file' || call.name === 'replace_file_content') {
                    console.log('Keys:', Object.keys(call.arguments));
                }
            }
        }
    } catch (e) {
    }
}
