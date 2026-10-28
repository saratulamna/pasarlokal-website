import './pages/admin/umkms.js';
import './pages/admin/umkms.js';

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('portalSidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');

    if (!sidebar || !toggle) {
        return;
    }

    const closeSidebar = () => {
        sidebar.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Buka menu');
    };

    toggle.addEventListener('click', () => {
        const isOpen = sidebar.classList.toggle('is-open');

        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.setAttribute(
            'aria-label',
            isOpen ? 'Tutup menu' : 'Buka menu'
        );
    });

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeSidebar);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 760) {
            closeSidebar();
        }
    });
});


document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirmMessage
                || 'Yakin ingin menghapus data ini?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
