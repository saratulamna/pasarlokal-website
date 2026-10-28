document.addEventListener('DOMContentLoaded', () => {
    const shell = document.getElementById('auShell');
    const toggle = document.getElementById('auMenuToggle');
    const overlay = document.getElementById('auOverlay');

    const closeSidebar = () => {
        shell?.classList.remove('is-sidebar-open');
    };

    toggle?.addEventListener('click', () => {
        shell?.classList.toggle('is-sidebar-open');
    });

    overlay?.addEventListener('click', closeSidebar);

    document.querySelectorAll('.au-nav-link').forEach((link) => {
        link.addEventListener('click', closeSidebar);
    });

    // Filter role dan status langsung menerapkan pilihan.
    const filterForm = document.getElementById('auFilterForm');

    filterForm?.querySelectorAll('select').forEach((select) => {
        select.addEventListener('change', () => {
            filterForm.requestSubmit();
        });
    });

    // Konfirmasi penghapusan pengguna.
    document.querySelectorAll('[data-delete-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const userName = form.dataset.userName || 'pengguna ini';

            const confirmed = window.confirm(
                `Yakin ingin menghapus akun "${userName}"?\n\n` +
                'Tindakan ini tidak dapat dibatalkan.'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    // Tutup pesan sukses atau error.
    document.querySelectorAll('[data-dismiss-flash]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('.au-flash')?.remove();
        });
    });

    // Jika layar kembali lebar, tutup sidebar mobile.
    window.addEventListener('resize', () => {
        if (window.innerWidth > 980) {
            closeSidebar();
        }
    });
});