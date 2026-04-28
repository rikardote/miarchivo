<div>
    <x-mary-header title="Escáner QR" subtitle="Escanea la etiqueta física para acceder al expediente" separator />

    <div class="max-w-xl mx-auto">
        <x-mary-card>
            <div id="reader" class="rounded-xl overflow-hidden bg-base-200 border-2 border-dashed border-base-300"></div>
            
            <div id="result" class="mt-6 hidden">
                <div class="alert alert-success">
                    <x-mary-icon name="o-check-circle" />
                    <span>Código detectado: <strong id="scanned-code"></strong></span>
                </div>
                <div class="mt-4">
                    <a id="redirect-btn" href="#" class="btn btn-primary w-full">Ir al Expediente</a>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                <div class="flex items-center gap-3 p-4 bg-primary/5 rounded-lg border border-primary/10">
                    <x-mary-icon name="o-light-bulb" class="text-primary w-6 h-6" />
                    <p class="text-sm">Apunta la cámara hacia el código QR de la etiqueta del expediente.</p>
                </div>
                
                <x-mary-button label="Reiniciar Escáner" id="reset-btn" icon="o-arrow-path" class="btn-ghost btn-sm w-full hidden" />
            </div>
        </x-mary-card>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            const readerElement = document.getElementById('reader');
            if (!readerElement) return;

            const html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };

            const onSuccess = (decodedText, decodedResult) => {
                console.log(`Code matched = ${decodedText}`, decodedResult);
                
                // Parar el escáner
                html5QrCode.stop().then((ignore) => {
                    document.getElementById('reader').classList.add('hidden');
                    document.getElementById('result').classList.remove('hidden');
                    document.getElementById('reset-btn').classList.remove('hidden');
                    document.getElementById('scanned-code').innerText = decodedText;
                    
                    // Si el texto es una URL del sistema, usarla directamente
                    if (decodedText.includes(window.location.origin) || decodedText.startsWith('http')) {
                        window.location.href = decodedText;
                    } else {
                        // Usar la nueva ruta de búsqueda por código
                        document.getElementById('redirect-btn').href = `/expedients/find/${decodedText}`;
                    }
                }).catch((err) => {
                    console.warn(err);
                });
            };

            const onError = (err) => {
                // Silently ignore errors (they happen every frame if no QR is found)
            };

            html5QrCode.start({ facingMode: "environment" }, config, onSuccess, onError);

            document.getElementById('reset-btn').addEventListener('click', () => {
                location.reload();
            });
        });
    </script>
    @endpush
</div>
