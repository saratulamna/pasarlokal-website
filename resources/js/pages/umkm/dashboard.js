(() => {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        const dashboard = document.querySelector('[data-umkm-dashboard]');

        if (!dashboard) {
            return;
        }

        // Tempat untuk interaksi khusus dashboard UMKM.
        // Script hanya aktif jika elemen dashboard tersedia.
        console.info('Dashboard UMKM berhasil dimuat.');
    });
})();