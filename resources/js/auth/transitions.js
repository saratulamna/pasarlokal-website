
(() => {
    const STORAGE_KEY = 'pl-auth-transition-target';

    function normalizePath(path) {
        return path.replace(/\/+$/, '') || '/';
    }

    /*
     * Jalankan pemeriksaan sebelum halaman mulai dirender.
     * Jika ini halaman tujuan transisi, siapkan cover-nya.
     */
    try {
        const target = sessionStorage.getItem(STORAGE_KEY);
        const current = normalizePath(window.location.pathname);

        if (target && target === current) {
            document.documentElement.classList.add(
                'auth-transition-pending'
            );
        }
    } catch {
        // Transisi tetap boleh dipakai tanpa sessionStorage.
    }

    document.addEventListener('DOMContentLoaded', () => {
        const root = document.documentElement;
        const overlay = document.getElementById('authTransitionOverlay');

        /*
         * Masuk ke halaman baru:
         * cover bergerak ke kiri, halaman baru tampil.
         */
        try {
            const target = sessionStorage.getItem(STORAGE_KEY);
            const current = normalizePath(window.location.pathname);

            if (target === current) {
                sessionStorage.removeItem(STORAGE_KEY);

                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        root.classList.add('auth-transition-in');
                        root.classList.remove('auth-transition-pending');
                    });
                });
            } else if (target) {
                sessionStorage.removeItem(STORAGE_KEY);
            }
        } catch {
            root.classList.remove('auth-transition-pending');
        }

        /*
         * Tangani tautan login/register saja.
         * Klik tengah, Ctrl+klik dan buka tab baru tetap normal.
         */
        document.querySelectorAll('[data-auth-transition]').forEach((link) => {
            link.addEventListener('click', (event) => {
                if (
                    event.defaultPrevented ||
                    event.button !== 0 ||
                    event.metaKey ||
                    event.ctrlKey ||
                    event.shiftKey ||
                    event.altKey
                ) {
                    return;
                }

                if (link.target && link.target !== '_self') {
                    return;
                }

                if (link.hasAttribute('download')) {
                    return;
                }

                if (
                    window.matchMedia(
                        '(prefers-reduced-motion: reduce)'
                    ).matches
                ) {
                    return;
                }

                const destination = new URL(link.href, window.location.href);

                if (destination.origin !== window.location.origin) {
                    return;
                }

                const targetPath = normalizePath(destination.pathname);
                const currentPath = normalizePath(window.location.pathname);

                if (targetPath === currentPath) {
                    return;
                }

                event.preventDefault();

                /*
                 * Kunci navigasi agar pengguna tidak memicu
                 * animasi berkali-kali.
                 */
                if (root.classList.contains('auth-transition-out')) {
                    return;
                }

                try {
                    sessionStorage.setItem(STORAGE_KEY, targetPath);
                } catch {
                    // Navigasi tetap dilanjutkan walau storage tidak tersedia.
                }

                root.classList.remove(
                    'auth-transition-in',
                    'auth-transition-pending'
                );

                root.classList.add('auth-transition-out');

                /*
                 * Beri waktu cover menutup layar sebelum pindah halaman.
                 */
                window.setTimeout(() => {
                    window.location.assign(destination.href);
                }, overlay ? 640 : 0);
            });
        });
    });
})();
