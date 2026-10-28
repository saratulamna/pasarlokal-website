
// PasarLokal Admin Dashboard interactions
document.addEventListener('DOMContentLoaded', () => {
    const shell = document.getElementById('adminShell');
    const toggle = document.getElementById('adminMobileToggle');
    const overlay = document.getElementById('adminMobileOverlay');
    const refreshButton = document.getElementById('adminRefreshButton');

    const closeSidebar = () => {
        shell?.classList.remove('is-sidebar-open');
    };

    toggle?.addEventListener('click', () => {
        shell?.classList.toggle('is-sidebar-open');
    });

    overlay?.addEventListener('click', closeSidebar);

    document.querySelectorAll('.admin-nav-link').forEach((link) => {
        link.addEventListener('click', closeSidebar);
    });

    // Muat ulang data yang dikirim controller.
    refreshButton?.addEventListener('click', () => {
        refreshButton.classList.add('is-refreshing');
        window.location.reload();
    });

    // Pencarian pesanan terbaru.
    const orderSearch = document.getElementById('adminOrderSearch');
    const orderRows = [
        ...document.querySelectorAll('.admin-order-row'),
    ];
    const ordersEmpty = document.getElementById('adminOrdersNoMatch');

    orderSearch?.addEventListener('input', () => {
        const term = orderSearch.value.trim().toLowerCase();
        let visibleCount = 0;

        orderRows.forEach((row) => {
            const searchable = row.dataset.search || '';
            const matches = searchable.includes(term);

            row.hidden = !matches;

            if (matches) {
                visibleCount++;
            }
        });

        if (ordersEmpty) {
            ordersEmpty.hidden = visibleCount > 0 || orderRows.length === 0;
        }
    });

    // Pencarian produk terbaru.
    const productSearch = document.getElementById('adminProductSearch');
    const productRows = [
        ...document.querySelectorAll('.admin-product-row'),
    ];
    const productsEmpty = document.getElementById('adminProductsNoMatch');

    productSearch?.addEventListener('input', () => {
        const term = productSearch.value.trim().toLowerCase();
        let visibleCount = 0;

        productRows.forEach((row) => {
            const searchable = row.dataset.search || '';
            const matches = searchable.includes(term);

            row.hidden = !matches;

            if (matches) {
                visibleCount++;
            }
        });

        if (productsEmpty) {
            productsEmpty.hidden = visibleCount > 0 || productRows.length === 0;
        }
    });

    // Tutup notifikasi.
    document.querySelectorAll('[data-dismiss-flash]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('.admin-flash')?.remove();
        });
    });

    // Pastikan sidebar ditutup ketika kembali ke desktop.
    window.addEventListener('resize', () => {
        if (window.innerWidth > 760) {
            closeSidebar();
        }
    });
});
