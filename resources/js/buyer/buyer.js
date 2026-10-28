
document.addEventListener('DOMContentLoaded', () => {
    const app = document.querySelector('.buyer-app');
    const menuToggle = document.getElementById('buyerMenuToggle');
    const menuClose = document.getElementById('buyerSidebarClose');
    const backdrop = document.getElementById('buyerBackdrop');

    function setMenuOpen(isOpen) {
        if (!app || !menuToggle) return;

        app.classList.toggle('is-menu-open', isOpen);
        menuToggle.setAttribute('aria-expanded', String(isOpen));

        document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    menuToggle?.addEventListener('click', () => {
        const isOpen = app?.classList.contains('is-menu-open') ?? false;
        setMenuOpen(!isOpen);
    });

    menuClose?.addEventListener('click', () => {
        setMenuOpen(false);
    });

    backdrop?.addEventListener('click', () => {
        setMenuOpen(false);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMenuOpen(false);
        }
    });

    document.querySelectorAll('.buyer-nav-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.matchMedia('(max-width: 760px)').matches) {
                setMenuOpen(false);
            }
        });
    });

    // Only dismiss informational flash messages automatically.
    document.querySelectorAll('[data-flash]').forEach((alert) => {
        const closeButton = alert.querySelector('.buyer-alert-close');

        const dismiss = () => {
            alert.classList.add('is-dismissing');

            window.setTimeout(() => {
                alert.remove();
            }, 180);
        };

        closeButton?.addEventListener('click', dismiss);

        window.setTimeout(() => {
            if (alert.isConnected) dismiss();
        }, 6000);
    });
});

// =========================================================
// PASARLOKAL — CHECKOUT INTERACTIONS
// =========================================================

document.querySelectorAll('.co-page [data-count-for]').forEach((counter) => {
    const fieldId = counter.dataset.countFor;
    const field = document.getElementById(fieldId);

    if (!field) return;

    const maxLength = Number(field.getAttribute('maxlength')) || 0;

    const updateCount = () => {
        counter.textContent = `${field.value.length}/${maxLength}`;
    };

    field.addEventListener('input', updateCount);
    updateCount();
});

document.querySelectorAll('.co-page .co-payment-option input[type="radio"]').forEach((radio) => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.co-page .co-payment-option').forEach((option) => {
            option.classList.toggle(
                'is-selected',
                option.querySelector('input[type="radio"]')?.checked === true
            );
        });
    });
});

// =========================================================
// PASARLOKAL — ORDER HISTORY SEARCH & FILTER
// =========================================================

function initPasarLokalOrderHistory() {
    const page = document.querySelector('.oh-page');

    if (!page || page.dataset.orderHistoryReady === 'true') {
        return;
    }

    page.dataset.orderHistoryReady = 'true';

    const searchInput = page.querySelector('#ohOrderSearch');
    const filterButtons = page.querySelectorAll('[data-order-filter]');
    const cards = [...page.querySelectorAll('[data-order-card]')];
    const emptyState = page.querySelector('#ohFilterEmpty');
    const resetButton = page.querySelector('#ohResetFilters');

    if (!cards.length) return;

    let activeFilter = 'all';

    function applyFilters() {
        const keyword = (searchInput?.value || '')
            .trim()
            .toLocaleLowerCase('id-ID');

        let visibleCount = 0;

        cards.forEach((card) => {
            const status = card.dataset.status || '';
            const searchableText = card.dataset.search || '';

            const matchesStatus =
                activeFilter === 'all' || status === activeFilter;

            const matchesSearch =
                keyword === '' || searchableText.includes(keyword);

            const visible = matchesStatus && matchesSearch;

            card.hidden = !visible;

            if (visible) {
                visibleCount += 1;
            }
        });

        if (emptyState) {
            emptyState.hidden = visibleCount !== 0;
        }
    }

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activeFilter = button.dataset.orderFilter || 'all';

            filterButtons.forEach((item) => {
                const isActive = item === button;

                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-pressed', String(isActive));
            });

            applyFilters();
        });
    });

    searchInput?.addEventListener('input', applyFilters);

    resetButton?.addEventListener('click', () => {
        if (searchInput) {
            searchInput.value = '';
        }

        activeFilter = 'all';

        filterButtons.forEach((button) => {
            const isActive = button.dataset.orderFilter === 'all';

            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });

        applyFilters();
        searchInput?.focus();
    });

    applyFilters();
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initPasarLokalOrderHistory,
        { once: true }
    );
} else {
    initPasarLokalOrderHistory();
}
