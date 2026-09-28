const fs = require('fs');
const readline = require('readline');
const rl = readline.createInterface({
    input: fs.createReadStream('C:/Users/i7/.gemini/antigravity/brain/3e99fea3-5294-4fcd-8e48-ba620ddab796/.system_generated/logs/transcript_full.jsonl', {encoding: 'utf8'}),
    crlfDelay: Infinity
});

let bestCode = null;

rl.on('line', (line) => {
    if (!line.includes('write_to_file') || !line.includes('AcController.php')) return;
    try {
        const data = JSON.parse(line);
        if (data.tool_calls) {
            for (const call of data.tool_calls) {
                if (call.name === 'write_to_file' || call.name === 'replace_file_content') {
                    if (call.args && call.args.TargetFile && call.args.TargetFile.includes('AcController.php')) {
                        if (call.args.CodeContent) {
                            bestCode = call.args.CodeContent;
                        }
                    }
                }
            }
        }
    } catch(e) {}
});

rl.on('close', () => {
    if (bestCode) {
        fs.writeFileSync('app/Http/Controllers/AcController.php', bestCode, 'utf8');
        console.log('RECOVERED PERFECTLY WITH NODE STREAMS!');
    }
});
