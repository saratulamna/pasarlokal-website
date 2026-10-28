
/* =========================================================
   PASARLOKAL — UMKM PRODUCT CATALOG
   File: resources/js/pages/umkm/products-index.js
   ========================================================= */

(() => {
    'use strict';

    function initUmkmProductCatalog() {
        const app = document.querySelector('#upiApp');

        if (!app || app.dataset.catalogReady === 'true') {
            return;
        }

        app.dataset.catalogReady = 'true';

        const grid = app.querySelector('#upiProductGrid');
        const cards = Array.from(
            app.querySelectorAll('[data-product-card]')
        );

        const filterForm = app.querySelector('#upiFilterForm');
        const searchInput = app.querySelector('#upiSearch');
        const categoryFilter = app.querySelector('#upiCategory');
        const statusFilter = app.querySelector('#upiStatus');
        const sortSelect = app.querySelector('#upiSort');

        const resultCount = app.querySelector('[data-result-count]');
        const filterEmpty = app.querySelector('.upi-filter-empty');

        let activeQuickFilter = 'all';

        const normalize = (value) =>
            String(value ?? '')
                .normalize('NFKC')
                .toLocaleLowerCase('id-ID')
                .trim();

        function getName(card) {
            return card.dataset.name
                || card.querySelector('[data-product-name]')?.textContent
                || '';
        }

        function getDescription(card) {
            return card.dataset.description
                || card.querySelector('[data-product-description]')?.textContent
                || '';
        }

        function getCategory(card) {
            return card.dataset.category
                || card.querySelector('[data-product-category]')?.textContent
                || '';
        }

        function getStatus(card) {
            return normalize(card.dataset.status);
        }

        function getPrice(card) {
            const price = Number(card.dataset.price ?? 0);
            return Number.isFinite(price) ? price : 0;
        }

        function getStock(card) {
            const stock = Number(card.dataset.stock ?? 0);
            return Number.isFinite(stock) ? stock : 0;
        }

        function getId(card) {
            const id = Number(card.dataset.id ?? 0);
            return Number.isFinite(id) ? id : 0;
        }

        function matchesStatus(cardStatus, requestedStatus) {
            if (!requestedStatus || requestedStatus === 'all') {
                return true;
            }

            const current = normalize(cardStatus);
            const requested = normalize(requestedStatus);

            if (
                requested === 'aktif'
                || requested === 'active'
            ) {
                return current === 'aktif' || current === 'active';
            }

            if (
                requested === 'nonaktif'
                || requested === 'non-aktif'
                || requested === 'inactive'
            ) {
                return [
                    'nonaktif',
                    'non-aktif',
                    'inactive'
                ].includes(current);
            }

            return current === requested;
        }

        function matchesQuickFilter(card) {
            const status = getStatus(card);
            const stock = getStock(card);

            switch (activeQuickFilter) {
                case 'active':
                case 'aktif':
                    return status === 'aktif' || status === 'active';

                case 'inactive':
                case 'nonaktif':
                    return [
                        'nonaktif',
                        'non-aktif',
                        'inactive'
                    ].includes(status);

                case 'low-stock':
                    return stock > 0 && stock <= 5;

                case 'empty-stock':
                    return stock <= 0;

                case 'all':
                default:
                    return true;
            }
        }

        function updateQuickFilterButtons() {
            app.querySelectorAll('[data-quick-filter]').forEach((button) => {
                const value = normalize(button.dataset.quickFilter || 'all');
                const isActive = value === activeQuickFilter;

                button.classList.toggle('is-active', isActive);
                button.setAttribute(
                    'aria-pressed',
                    isActive ? 'true' : 'false'
                );
            });
        }

        function applyFilters() {
            const search = normalize(searchInput?.value);
            const category = normalize(categoryFilter?.value || 'all');
            const status = normalize(statusFilter?.value || 'all');

            let visibleCount = 0;

            cards.forEach((card) => {
                const name = normalize(getName(card));
                const description = normalize(getDescription(card));
                const cardCategory = normalize(getCategory(card));
                const cardStatus = getStatus(card);

                const matchesSearch =
                    !search
                    || name.includes(search)
                    || description.includes(search);

                const matchesCategory =
                    category === 'all'
                    || cardCategory === category;

                const matchesSelectedStatus =
                    matchesStatus(cardStatus, status);

                const matchesQuick = matchesQuickFilter(card);

                const isVisible =
                    matchesSearch
                    && matchesCategory
                    && matchesSelectedStatus
                    && matchesQuick;

                card.hidden = !isVisible;
                card.classList.toggle('is-filtered-out', !isVisible);

                if (isVisible) {
                    visibleCount++;
                }
            });

            if (resultCount) {
                resultCount.textContent = String(visibleCount);
            }

            if (filterEmpty) {
                filterEmpty.hidden = visibleCount !== 0;
            }

            updateQuickFilterButtons();
        }

        /* -----------------------------------------------
           SORTING
           ----------------------------------------------- */

        function sortCards() {
            if (!grid || cards.length === 0) {
                applyFilters();
                return;
            }

            const sortBy = sortSelect?.value || 'latest';

            const sortedCards = [...cards].sort((a, b) => {
                const nameA = normalize(getName(a));
                const nameB = normalize(getName(b));

                switch (sortBy) {
                    case 'oldest':
                        return getId(a) - getId(b);

                    case 'name':
                    case 'name-asc':
                        return nameA.localeCompare(nameB, 'id');

                    case 'name-desc':
                        return nameB.localeCompare(nameA, 'id');

                    case 'price-asc':
                        return getPrice(a) - getPrice(b);

                    case 'price-desc':
                        return getPrice(b) - getPrice(a);

                    case 'stock-asc':
                        return getStock(a) - getStock(b);

                    case 'stock-desc':
                        return getStock(b) - getStock(a);

                    case 'latest':
                    default:
                        return getId(b) - getId(a);
                }
            });

            sortedCards.forEach((card) => {
                grid.appendChild(card);
            });

            if (filterEmpty) {
                grid.appendChild(filterEmpty);
            }

            applyFilters();
        }

        /* -----------------------------------------------
           SEARCH AND FILTERS
           ----------------------------------------------- */

        searchInput?.addEventListener('input', applyFilters);
        categoryFilter?.addEventListener('change', applyFilters);
        statusFilter?.addEventListener('change', applyFilters);

        sortSelect?.addEventListener('change', sortCards);

        app.querySelectorAll('[data-quick-filter]').forEach((button) => {
            button.addEventListener('click', () => {
                activeQuickFilter = normalize(
                    button.dataset.quickFilter || 'all'
                );

                // Filter cepat tidak saling bertentangan dengan dropdown status.
                if (statusFilter) {
                    statusFilter.value = 'all';
                }

                applyFilters();
            });
        });

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

                if (sortSelect) {
                    sortSelect.value = 'latest';
                }

                activeQuickFilter = 'all';

                sortCards();

                // Bersihkan parameter pencarian di alamat halaman.
                if (filterForm && window.history?.replaceState) {
                    window.history.replaceState(
                        {},
                        document.title,
                        filterForm.action
                    );
                }
            });
        });

        /* -----------------------------------------------
           GRID / LIST VIEW
           ----------------------------------------------- */

        app.querySelectorAll('[data-view-mode]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!grid) return;

                const mode = button.dataset.viewMode;

                if (!['grid', 'list'].includes(mode)) {
                    return;
                }

                grid.classList.toggle('is-list-view', mode === 'list');
                grid.classList.toggle('is-grid-view', mode === 'grid');

                app.querySelectorAll('[data-view-mode]').forEach((item) => {
                    const isActive = item.dataset.viewMode === mode;

                    item.classList.toggle('is-active', isActive);
                    item.setAttribute(
                        'aria-pressed',
                        isActive ? 'true' : 'false'
                    );
                });
            });
        });

        /* -----------------------------------------------
           PRODUCT ACTION MENUS
           ----------------------------------------------- */

        function closeAllMenus(exceptMenu = null) {
            app.querySelectorAll('[data-product-menu]').forEach((menu) => {
                if (menu === exceptMenu) return;

                menu.hidden = true;
                menu.classList.remove('is-open');
            });

            app.querySelectorAll('[data-product-menu-toggle]').forEach((button) => {
                const menuId = button.dataset.menuTarget;
                const menu = menuId
                    ? document.getElementById(menuId)
                    : button.nextElementSibling;

                if (menu !== exceptMenu) {
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

                if (!menu) return;

                const willOpen = menu.hidden;

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
                !event.target.closest('[data-product-menu]')
                && !event.target.closest('[data-product-menu-toggle]')
            ) {
                closeAllMenus();
            }
        });

        /* -----------------------------------------------
           CONFIRM DELETE
           ----------------------------------------------- */

        app.querySelectorAll('[data-delete-form]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                // Submit kedua sesudah pengguna mengonfirmasi.
                if (form.dataset.confirmed === 'true') {
                    delete form.dataset.confirmed;
                    return;
                }

                event.preventDefault();

                const productName =
                    form.dataset.productName || 'produk ini';

                const confirmed = window.confirm(
                    `Yakin ingin menghapus "${productName}"? Tindakan ini tidak dapat dibatalkan.`
                );

                if (!confirmed) return;

                form.dataset.confirmed = 'true';
                form.requestSubmit();
            });
        });

        app.querySelectorAll('[data-confirm-delete]').forEach((button) => {
            button.addEventListener('click', () => {
                const formId = button.dataset.deleteFormId;

                const form =
                    (formId ? document.getElementById(formId) : null)
                    || button.closest('.upi-product-card')
                        ?.querySelector('[data-delete-form]');

                if (!form) {
                    window.alert('Form penghapusan produk tidak ditemukan.');
                    return;
                }

                closeAllMenus();
                form.requestSubmit();
            });
        });

        /* -----------------------------------------------
           DISMISS FLASH MESSAGES
           ----------------------------------------------- */

        app.querySelectorAll('[data-dismiss-alert]').forEach((button) => {
            button.addEventListener('click', () => {
                const alert = button.closest('[data-alert], [role="alert"]');

                if (alert) {
                    alert.remove();
                }
            });
        });

        /* -----------------------------------------------
           MOBILE SIDEBAR
           ----------------------------------------------- */

        const main = app.querySelector(':scope > .upi-main');

        const sidebar =
            app.querySelector(':scope > [data-sidebar]')
            || app.querySelector(':scope > .upi-sidebar')
            || app.querySelector(':scope > .umkm-sidebar')
            || (main ? main.previousElementSibling : app.firstElementChild);

        const sidebarToggle = app.querySelector('[data-sidebar-toggle]');
        let overlay = null;

        if (sidebar && sidebar !== main && sidebarToggle) {
            overlay = app.querySelector('.upi-sidebar-overlay');

            if (!overlay) {
                overlay = document.createElement('button');
                overlay.type = 'button';
                overlay.className = 'upi-sidebar-overlay';
                overlay.setAttribute('aria-label', 'Tutup navigasi');
                app.insertBefore(overlay, main || null);
            }

            function closeSidebar() {
                sidebar.classList.remove('is-open');
                overlay?.classList.remove('is-visible');
                sidebarToggle.setAttribute('aria-expanded', 'false');
                sidebarToggle.setAttribute('aria-label', 'Buka navigasi');
                document.body.style.overflow = '';
            }

            function openSidebar() {
                sidebar.classList.add('is-open');
                overlay?.classList.add('is-visible');
                sidebarToggle.setAttribute('aria-expanded', 'true');
                sidebarToggle.setAttribute('aria-label', 'Tutup navigasi');
                document.body.style.overflow = 'hidden';
            }

            sidebarToggle.addEventListener('click', () => {
                if (sidebar.classList.contains('is-open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            overlay?.addEventListener('click', closeSidebar);

            sidebar.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 720) {
                        closeSidebar();
                    }
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeSidebar();
                    closeAllMenus();
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth > 720) {
                    closeSidebar();
                }
            });
        }

        // Inisialisasi pertama.
        if (grid) {
            sortCards();
        } else {
            applyFilters();
        }

        console.info('PasarLokal: katalog produk siap.');
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initUmkmProductCatalog
        );
    } else {
        initUmkmProductCatalog();
    }
})();
