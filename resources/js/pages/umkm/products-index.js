(() => {
    'use strict';

    function initUmkmProductCatalog() {
        const app = document.querySelector('#upiApp');

        if (!app || app.dataset.catalogReady === 'true') {
            return;
        }

        app.dataset.catalogReady = 'true';

        const cards = Array.from(
            app.querySelectorAll('[data-product-card]')
        );

        const grid = app.querySelector(
            '[data-product-grid], #upiProductGrid, .upi-product-grid, .upi-products-grid'
        );

        const searchInput = app.querySelector(
            '[data-search-input], #upiSearch, input[name="search"], input[name="q"]'
        );

        const categoryFilter = app.querySelector(
            '[data-category-filter], #upiCategory, select[name="category"]'
        );

        const statusFilter = app.querySelector(
            '[data-status-filter], #upiStatus, select[name="status"]'
        );

        const sortSelect = app.querySelector(
            '[data-sort-select], #upiSort, select[name="sort"]'
        );

        const emptyState = app.querySelector(
            '[data-empty-state], #upiEmptyState'
        );

        const resultCount = app.querySelector(
            '[data-result-count], #upiResultCount'
        );

        const normalize = (value) =>
            String(value ?? '')
                .toLowerCase()
                .trim();

        const getCardValue = (card, key, fallback = '') =>
            card.dataset[key] ?? fallback;

        function getCardName(card) {
            return getCardValue(
                card,
                'name',
                card.querySelector(
                    '[data-product-name], .upi-product-name'
                )?.textContent ?? ''
            );
        }

        function getCardCategory(card) {
            return getCardValue(
                card,
                'category',
                card.querySelector(
                    '[data-product-category], .upi-product-category'
                )?.textContent ?? ''
            );
        }

        function getCardStatus(card) {
            return getCardValue(
                card,
                'status',
                card.querySelector(
                    '[data-product-status], .upi-product-status'
                )?.textContent ?? ''
            );
        }

        function getCardStock(card) {
            const rawStock = getCardValue(
                card,
                'stock',
                card.querySelector(
                    '[data-product-stock], .upi-product-stock'
                )?.textContent ?? ''
            );

            const parsedStock = Number(
                String(rawStock).replace(/[^\d-]/g, '')
            );

            return Number.isFinite(parsedStock) ? parsedStock : 0;
        }

        let activeQuickFilter = 'all';

        function cardMatchesQuickFilter(card) {
            const status = normalize(getCardStatus(card));
            const stock = getCardStock(card);

            switch (activeQuickFilter) {
                case 'active':
                case 'aktif':
                    return status.includes('aktif');

                case 'inactive':
                case 'nonaktif':
                    return (
                        status.includes('nonaktif') ||
                        status.includes('non-aktif')
                    );

                case 'low-stock':
                case 'low_stock':
                case 'stok-menipis':
                    return stock > 0 && stock <= 5;

                case 'empty-stock':
                case 'out-of-stock':
                case 'stok-habis':
                    return stock <= 0;

                case 'all':
                default:
                    return true;
            }
        }

        function applyFilters() {
            const search = normalize(searchInput?.value);
            const category = normalize(categoryFilter?.value);
            const status = normalize(statusFilter?.value);

            let visibleCount = 0;

            cards.forEach((card) => {
                const name = normalize(getCardName(card));
                const description = normalize(
                    getCardValue(card, 'description') +
                    ' ' +
                    (
                        card.querySelector(
                            '[data-product-description], .upi-product-description'
                        )?.textContent ?? ''
                    )
                );
                const cardCategory = normalize(getCardCategory(card));
                const cardStatus = normalize(getCardStatus(card));

                const matchesSearch =
                    !search ||
                    name.includes(search) ||
                    description.includes(search);

                const matchesCategory =
                    !category ||
                    category === 'all' ||
                    cardCategory === category ||
                    normalize(cardCategory).includes(category);

                const matchesStatus =
                    !status ||
                    status === 'all' ||
                    cardStatus === status;

                const matchesQuick =
                    cardMatchesQuickFilter(card);

                const visible =
                    matchesSearch &&
                    matchesCategory &&
                    matchesStatus &&
                    matchesQuick;

                card.hidden = !visible;
                card.classList.toggle('is-filtered-out', !visible);

                if (visible) {
                    visibleCount++;
                }
            });

            if (emptyState) {
                emptyState.hidden = visibleCount !== 0;
            }

            if (resultCount) {
                resultCount.textContent = String(visibleCount);
            }

            app.querySelectorAll('[data-quick-filter]').forEach((button) => {
                const value = normalize(
                    button.dataset.quickFilter || 'all'
                );

                const active =
                    value === activeQuickFilter ||
                    (
                        activeQuickFilter === 'all' &&
                        value === 'all'
                    );

                button.classList.toggle('is-active', active);
                button.setAttribute(
                    'aria-pressed',
                    active ? 'true' : 'false'
                );
            });
        }

        function sortCards() {
            if (!grid || !cards.length) {
                return;
            }

            const sortBy = sortSelect?.value || 'latest';

            const sortedCards = [...cards].sort((a, b) => {
                const nameA = normalize(getCardName(a));
                const nameB = normalize(getCardName(b));

                const priceA = Number(
                    getCardValue(a, 'price', 0)
                ) || 0;

                const priceB = Number(
                    getCardValue(b, 'price', 0)
                ) || 0;

                const stockA = getCardStock(a);
                const stockB = getCardStock(b);

                const idA = Number(
                    getCardValue(a, 'id', 0)
                ) || 0;

                const idB = Number(
                    getCardValue(b, 'id', 0)
                ) || 0;

                switch (sortBy) {
                    case 'name':
                    case 'name-asc':
                        return nameA.localeCompare(nameB, 'id');

                    case 'name-desc':
                        return nameB.localeCompare(nameA, 'id');

                    case 'price-asc':
                    case 'price-low':
                        return priceA - priceB;

                    case 'price-desc':
                    case 'price-high':
                        return priceB - priceA;

                    case 'stock-asc':
                        return stockA - stockB;

                    case 'stock-desc':
                        return stockB - stockA;

                    case 'oldest':
                        return idA - idB;

                    case 'latest':
                    default:
                        return idB - idA;
                }
            });

            sortedCards.forEach((card) => {
                grid.appendChild(card);
            });

            applyFilters();
        }

        // Pencarian langsung.
        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        // Filter kategori.
        if (categoryFilter) {
            categoryFilter.addEventListener('change', applyFilters);
        }

        // Filter status.
        if (statusFilter) {
            statusFilter.addEventListener('change', applyFilters);
        }

        // Pengurutan.
        if (sortSelect) {
            sortSelect.addEventListener('change', sortCards);
        }

        // Tombol filter cepat.
        app.querySelectorAll('[data-quick-filter]').forEach((button) => {
            button.addEventListener('click', () => {
                activeQuickFilter = normalize(
                    button.dataset.quickFilter || 'all'
                );

                applyFilters();
            });
        });

        // Tombol reset filter.
        app.querySelectorAll('[data-reset-filters]').forEach((button) => {
            button.addEventListener('click', () => {
                if (searchInput) {
                    searchInput.value = '';
                }

                if (categoryFilter) {
                    categoryFilter.value = 'all';
                }

                if (statusFilter) {
                    statusFilter.value = 'all';
                }

                activeQuickFilter = 'all';

                applyFilters();
            });
        });

        // Tampilan grid/list.
        app.querySelectorAll('[data-view-mode]').forEach((button) => {
            button.addEventListener('click', () => {
                const mode = button.dataset.viewMode;

                if (!grid || !mode) {
                    return;
                }

                grid.classList.toggle(
                    'is-list-view',
                    mode === 'list'
                );

                grid.classList.toggle(
                    'is-grid-view',
                    mode === 'grid'
                );

                app.querySelectorAll('[data-view-mode]').forEach((item) => {
                    const active = item.dataset.viewMode === mode;

                    item.classList.toggle('is-active', active);
                    item.setAttribute(
                        'aria-pressed',
                        active ? 'true' : 'false'
                    );
                });
            });
        });

        // Menu aksi tiap produk.
        function closeAllMenus(except = null) {
            app.querySelectorAll('[data-product-menu]').forEach((menu) => {
                if (menu === except) {
                    return;
                }

                menu.hidden = true;
                menu.classList.remove('is-open');
            });

            app.querySelectorAll('[data-product-menu-toggle]').forEach((button) => {
                const menuId = button.dataset.menuTarget;
                const menu = menuId
                    ? document.getElementById(menuId)
                    : button.nextElementSibling;

                if (menu !== except) {
                    button.setAttribute('aria-expanded', 'false');
                }
            });
        }

        app.querySelectorAll('[data-product-menu-toggle]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();

                const menuId = button.dataset.menuTarget;

                const menu = menuId
                    ? document.getElementById(menuId)
                    : button.nextElementSibling;

                if (!menu) {
                    return;
                }

                const willOpen =
                    menu.hidden ||
                    !menu.classList.contains('is-open');

                closeAllMenus();

                menu.hidden = !willOpen;
                menu.classList.toggle('is-open', willOpen);
                button.setAttribute(
                    'aria-expanded',
                    willOpen ? 'true' : 'false'
                );
            });
        });

        document.addEventListener('click', (event) => {
            if (!app.contains(event.target)) {
                closeAllMenus();
                return;
            }

            if (
                !event.target.closest('[data-product-menu]') &&
                !event.target.closest('[data-product-menu-toggle]')
            ) {
                closeAllMenus();
            }
        });

        // Konfirmasi penghapusan produk.
        app.querySelectorAll('[data-delete-form]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmed === 'true') {
                    delete form.dataset.confirmed;
                    return;
                }

                event.preventDefault();

                const productName =
                    form.dataset.productName || 'produk ini';

                const approved = window.confirm(
                    `Yakin ingin menghapus "${productName}"? Tindakan ini tidak dapat dibatalkan.`
                );

                if (!approved) {
                    return;
                }

                form.dataset.confirmed = 'true';
                form.requestSubmit();
            });
        });

        app.querySelectorAll('[data-confirm-delete]').forEach((button) => {
            button.addEventListener('click', () => {
                const formId = button.dataset.deleteFormId;

                const form = formId
                    ? document.getElementById(formId)
                    : button.closest('[data-product-card]')
                        ?.querySelector('[data-delete-form]');

                if (!form) {
                    return;
                }

                closeAllMenus();
                form.requestSubmit();
            });
        });

        // Tutup pesan notifikasi.
        app.querySelectorAll('[data-dismiss-alert]').forEach((button) => {
            button.addEventListener('click', () => {
                const alert = button.closest(
                    '[role="alert"], [data-alert]'
                );

                if (alert) {
                    alert.remove();
                }
            });
        });

        // Toggle sidebar pada layar kecil.
        app.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const sidebar = app.querySelector('[data-sidebar], .upi-sidebar');

                if (!sidebar) {
                    return;
                }

                const opened = sidebar.classList.toggle('is-open');

                button.setAttribute(
                    'aria-expanded',
                    opened ? 'true' : 'false'
                );
            });
        });

        applyFilters();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUmkmProductCatalog);
    } else {
        initUmkmProductCatalog();
    }
})();
