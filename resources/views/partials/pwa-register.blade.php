<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then((registration) => {
                    registration.addEventListener('updatefound', () => {
                        const worker = registration.installing;

                        if (!worker) {
                            return;
                        }

                        worker.addEventListener('statechange', () => {
                            if (worker.state === 'installed' && navigator.serviceWorker.controller) {
                                worker.postMessage({ type: 'SKIP_WAITING' });
                            }
                        });
                    });
                })
                .catch((error) => {
                    console.warn('Service worker registration failed:', error);
                });

            let refreshing = false;

            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (refreshing) {
                    return;
                }

                refreshing = true;
                window.location.reload();
            });
        });
    }
</script>
