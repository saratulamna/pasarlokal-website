
(() => {
    'use strict';

    const initSellerDashboard = () => {
        const app = document.querySelector('#sellerApp');

        if (!app || app.dataset.initialized === 'true') return;

        app.dataset.initialized = 'true';

        const sidebar = app.querySelector('#sellerSidebar');
        const overlay = app.querySelector('#sellerMobileOverlay');
        const toggle = app.querySelector('#sellerMobileToggle');
        const refresh = app.querySelector('#sellerRefreshButton');

        // Sidebar mobile
        const closeSidebar = () => {
            app.classList.remove('is-sidebar-open');
            toggle?.setAttribute('aria-expanded', 'false');
            toggle?.setAttribute('aria-label', 'Buka navigasi');
        };

        const openSidebar = () => {
            app.classList.add('is-sidebar-open');
            toggle?.setAttribute('aria-expanded', 'true');
            toggle?.setAttribute('aria-label', 'Tutup navigasi');
        };

        toggle?.addEventListener('click', () => {
            if (app.classList.contains('is-sidebar-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        overlay?.addEventListener('click', closeSidebar);

        sidebar?.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.matchMedia('(max-width: 800px)').matches) {
                    closeSidebar();
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeSidebar();
        });

        // Refresh dashboard
        refresh?.addEventListener('click', () => {
            refresh.classList.add('is-refreshing');
            window.location.reload();
        });

        // Tutup notifikasi
        app.querySelectorAll('[data-dismiss-flash]').forEach((button) => {
            button.addEventListener('click', () => {
                const flash = button.closest('.seller-flash');

                if (!flash) return;

                flash.style.transition =
                    'opacity .18s ease, transform .18s ease';

                flash.style.opacity = '0';
                flash.style.transform = 'translateY(-4px)';

                window.setTimeout(() => flash.remove(), 190);
            });
        });

        // Pencarian pesanan
        const orderSearch = app.querySelector('#sellerOrderSearch');
        const orderRows = [
            ...app.querySelectorAll('.seller-order-row')
        ];
        const noOrderMatch = app.querySelector('#sellerOrdersNoMatch');

        orderSearch?.addEventListener('input', () => {
            const query = orderSearch.value
                .trim()
                .toLocaleLowerCase('id-ID');

            let visible = 0;

            orderRows.forEach((row) => {
                const text = (
                    row.dataset.search || row.textContent
                ).toLocaleLowerCase('id-ID');

                const matches = text.includes(query);

                row.hidden = !matches;

                if (matches) visible++;
            });

            if (noOrderMatch) {
                noOrderMatch.hidden =
                    visible !== 0 || orderRows.length === 0;
            }
        });

        // Pencarian produk
        const productSearch = app.querySelector('#sellerProductSearch');
        const productRows = [
            ...app.querySelectorAll('.seller-product-row')
        ];
        const noProductMatch = app.querySelector('#sellerProductsNoMatch');

        productSearch?.addEventListener('input', () => {
            const query = productSearch.value
                .trim()
                .toLocaleLowerCase('id-ID');

            let visible = 0;

            productRows.forEach((row) => {
                const text = (
                    row.dataset.search || row.textContent
                ).toLocaleLowerCase('id-ID');

                const matches = text.includes(query);

                row.hidden = !matches;

                if (matches) visible++;
            });

            if (noProductMatch) {
                noProductMatch.hidden =
                    visible !== 0 || productRows.length === 0;
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initSellerDashboard,
            { once: true }
        );
    } else {
        initSellerDashboard();
    }
})();
