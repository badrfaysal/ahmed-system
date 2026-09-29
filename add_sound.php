<?php
$path = 'd:/Projects/ahmed-system/ahmed-system/resources/views/partials/global_form_handler.blade.php';
$content = file_get_contents($path);

$target = "const isSwal = () => typeof Swal !== 'undefined';";

$js = <<<JS
    const isSwal = () => typeof Swal !== 'undefined';

    // O^O_O U,OUO_OO O_U^O O U,U+O_OO
    function playSuccessSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const playTone = (freq, startTime, duration) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'triangle'; // triangle gives a nice soft chime sound
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0, startTime);
                gain.gain.linearRampToValueAtTime(0.3, startTime + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
                osc.start(startTime);
                osc.stop(startTime + duration);
            };
            const now = ctx.currentTime;
            playTone(523.25, now, 0.2);       // C5
            playTone(659.25, now + 0.1, 0.4); // E5
        } catch(e) {
            console.error('Audio play failed', e);
        }
    }

    // O OOO1OO O O_OOO O Swal U,OOO_USU, O U,O_U^O
    let _swalPatched = false;
    function patchSwal() {
        if (!_swalPatched && isSwal()) {
            const orig = Swal.fire;
            Swal.fire = function(...args) {
                const arg = args[0];
                let isSuccess = false;
                if (typeof arg === 'string' && arg === 'success') isSuccess = true;
                if (args[2] === 'success') isSuccess = true;
                if (arg && typeof arg === 'object' && arg.icon === 'success') isSuccess = true;
                
                if (isSuccess) playSuccessSound();
                
                return orig.apply(this, args);
            };
            _swalPatched = true;
        }
    }
    
    // U.O-OU^U,O OOO_USU, Swal O"U.OO1O OO3UO1U,U OOU.USOU OOO O_O1
    let patchInterval = setInterval(() => {
        if (isSwal()) {
            patchSwal();
            clearInterval(patchInterval);
        }
    }, 100);
JS;

if (strpos($content, 'playSuccessSound') === false) {
    $content = str_replace($target, $js, $content);
    file_put_contents($path, $content);
    echo "Sound patch applied!\n";
} else {
    echo "Sound patch already exists!\n";
}
