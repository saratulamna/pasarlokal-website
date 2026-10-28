document.addEventListener('DOMContentLoaded', () => {
    const shell = document.getElementById('amShell');
    const toggle = document.getElementById('amMenuToggle');
    const overlay = document.getElementById('amOverlay');
    const filterForm = document.getElementById('amFilterForm');

    const closeSidebar = () => {
        shell?.classList.remove('is-sidebar-open');
    };

    toggle?.addEventListener('click', () => {
        shell?.classList.toggle('is-sidebar-open');
    });

    overlay?.addEventListener('click', closeSidebar);

    document.querySelectorAll('.am-nav-link').forEach((link) => {
        link.addEventListener('click', closeSidebar);
    });

    // Filter status dikirim ke controller.
    filterForm?.querySelector('select[name="status"]')?.addEventListener(
        'change',
        () => filterForm.requestSubmit()
    );

    // Pencarian lokal pada baris yang sedang ditampilkan.
    const search = document.getElementById('amSearch');
    const rows = [...document.querySelectorAll('.am-store-row')];
    const emptyResult = document.getElementById('amNoSearchResult');

    search?.addEventListener('input', () => {
        const keyword = search.value.trim().toLocaleLowerCase('id');
        let visible = 0;

        rows.forEach((row) => {
            const matches = (row.dataset.search || '').includes(keyword);

            row.hidden = !matches;

            if (matches) visible++;
        });

        if (emptyResult) {
            emptyResult.hidden = visible !== 0 || rows.length === 0;
        }
    });

    // Minta konfirmasi sebelum menyetujui atau menolak toko.
    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirm || 'Lanjutkan tindakan ini?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-dismiss-flash]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('.am-flash')?.remove();
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 980) {
            closeSidebar();
        }
    });
});