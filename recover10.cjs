const fs = require('fs');
const readline = require('readline');
const rl = readline.createInterface({
    input: fs.createReadStream('C:/Users/i7/.gemini/antigravity/brain/3e99fea3-5294-4fcd-8e48-ba620ddab796/.system_generated/logs/transcript_full.jsonl', {encoding: 'utf8'}),
    crlfDelay: Infinity
});

rl.on('line', (line) => {
    if (!line.includes('write_to_file') || !line.includes('AcController.php')) return;
    try {
        const data = JSON.parse(line);
        if (data.tool_calls) {
            for (const call of data.tool_calls) {
                if (call.name === 'write_to_file') {
                    if (call.args && call.args.TargetFile && call.args.TargetFile.includes('AcController.php')) {
                        if (call.args.CodeContent && call.args.CodeContent.includes('acItems')) {
                            const match1 = call.args.CodeContent.match(/\.*?;/);
                            const match2 = call.args.CodeContent.match(/\.*?;/);
                            console.log(match1 ? match1[0] : 'No acItems');
                            console.log(match2 ? match2[0] : 'No maintenanceItems');
                        }
                    }
                }
            }
        }
    } catch(e) {}
});
