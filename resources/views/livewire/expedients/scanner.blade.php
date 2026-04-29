<div>
    <x-mary-header title="Escáner QR" subtitle="Escanea la etiqueta física para acceder al expediente" class="mb-8" separator />

    <div class="max-w-xl mx-auto">
        <x-mary-card class="premium-card border-none overflow-hidden">
            <div class="relative group">
                <div id="reader" class="rounded-2xl overflow-hidden bg-slate-900 border-4 border-slate-100 dark:border-slate-800 shadow-inner min-h-[300px] flex items-center justify-center">
                    <div id="start-screen" class="text-center p-8">
                        <x-mary-button label="Iniciar Cámara" onclick="startScanner()" icon="o-camera" class="btn-primary rounded-2xl px-10 h-14 font-black uppercase text-xs tracking-widest shadow-2xl shadow-primary/40 animate-bounce" />
                    </div>
                </div>
                <div class="absolute inset-0 border-[20px] border-black/20 pointer-events-none rounded-2xl"></div>
                <div id="scan-overlay" class="hidden absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48 border-2 border-primary/50 rounded-3xl animate-pulse pointer-events-none"></div>
            </div>
            
            <div id="result" class="mt-8 hidden animate-in zoom-in-95 duration-300">
                <div class="p-4 bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-900/30 rounded-2xl flex items-center gap-4">
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-100 dark:border-white/5 w-full">
                        <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-1">Código Detectado</p>
                        <p class="text-lg font-black text-slate-800 dark:text-slate-100 dark:text-slate-200" id="scanned-code">---</p>
                    </div>
                </div>
                <div class="mt-6">
                    <button id="redirect-btn" class="btn btn-primary w-full rounded-2xl h-14 shadow-xl shadow-primary/20">
                        <x-mary-icon name="o-arrow-right-circle" class="mr-2" />
                        Ir al Expediente
                    </button>
                </div>
            </div>

            <div class="mt-8 space-y-6">
                <div class="flex items-center gap-4 p-4 bg-primary/5 rounded-2xl border border-primary/10">
                    <div class="p-2 bg-primary/10 rounded-xl text-primary">
                        <x-mary-icon name="o-light-bulb" class="w-6 h-6" />
                    </div>
                    <p class="text-xs font-medium text-slate-600 dark:text-slate-300 dark:text-slate-500 leading-relaxed">Apunta la cámara hacia el código QR de la etiqueta del expediente. Asegúrate de tener buena iluminación.</p>
                </div>
                
                <x-mary-button label="Reiniciar Escáner" id="reset-btn" icon="o-arrow-path" class="btn-ghost btn-sm w-full hidden rounded-xl" />
            </div>
        </x-mary-card>
    </div>

    @script
    <script>
        window.html5QrCode = window.html5QrCode || null;

        window.startScanner = function() {
            const readerElement = document.getElementById('reader');
            if (!readerElement) return;

            // Ocultar pantalla de inicio y mostrar overlay
            document.getElementById('start-screen').classList.add('hidden');
            document.getElementById('scan-overlay').classList.remove('hidden');

            if (window.html5QrCode) {
                window.html5QrCode.stop().catch(() => {});
            }

            window.html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 15, qrbox: { width: 250, height: 250 } };

            const onSuccess = (decodedText, decodedResult) => {
                document.getElementById('scanned-code').innerText = decodedText;
                window.lastScannedCode = decodedText;

                document.getElementById('result').classList.remove('hidden');
                document.getElementById('reset-btn').classList.remove('hidden');
                
                document.getElementById('reader').classList.add('hidden');
                document.getElementById('scan-overlay').classList.add('hidden');

                if (window.html5QrCode) {
                    window.html5QrCode.stop().catch(() => {});
                }
            };

            const onGoToExpedient = () => {
                if (window.lastScannedCode) {
                    $wire.dispatch('code-scanned', { code: window.lastScannedCode });
                }
            };

            const btn = document.getElementById('redirect-btn');
            if (btn) btn.onclick = onGoToExpedient;

            window.html5QrCode.start({ facingMode: "environment" }, config, onSuccess, (err) => {})
                .catch((err) => {
                    console.error("Camera error:", err);
                    document.getElementById('scan-overlay').classList.add('hidden');
                    document.getElementById('start-screen').classList.remove('hidden');
                    alert("Error al acceder a la cámara.");
                });
        }

        document.addEventListener('livewire:navigating', () => {
            if (window.html5QrCode) {
                window.html5QrCode.stop().catch(() => {});
            }
        });
    </script>
    @endscript
</div>
