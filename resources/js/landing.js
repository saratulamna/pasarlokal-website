
// PasarLokal — landing page interactions

document.documentElement.classList.add('motion-ready');

document.addEventListener('DOMContentLoaded', () => {
    const reducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    const header = document.getElementById('siteHeader');
    const menuToggle = document.getElementById('mobileMenuToggle');
    const navigation = document.getElementById('mainNavigation');

    // Sticky header responds to scrolling.
    const updateHeader = () => {
        header?.classList.toggle('is-scrolled', window.scrollY > 12);
    };

    updateHeader();

    window.addEventListener('scroll', updateHeader, {
        passive: true,
    });

    // Mobile navigation.
    const closeMenu = () => {
        if (!menuToggle || !navigation) return;

        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.setAttribute('aria-label', 'Buka menu navigasi');
        navigation.classList.remove('is-open');
    };

    menuToggle?.addEventListener('click', () => {
        const expanded =
            menuToggle.getAttribute('aria-expanded') === 'true';

        menuToggle.setAttribute('aria-expanded', String(!expanded));
        menuToggle.setAttribute(
            'aria-label',
            expanded ? 'Buka menu navigasi' : 'Tutup menu navigasi'
        );

        navigation?.classList.toggle('is-open', !expanded);
    });

    navigation?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMenu);
    });

    document.addEventListener('click', (event) => {
        if (
            navigation?.classList.contains('is-open') &&
            !navigation.contains(event.target) &&
            !menuToggle?.contains(event.target)
        ) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 780) {
            closeMenu();
        }
    });

    // Reveal content as it enters the viewport.
    const revealElements = document.querySelectorAll('.reveal');

    if (reducedMotion || !('IntersectionObserver' in window)) {
        revealElements.forEach((element) => {
            element.classList.add('is-visible');
        });
    } else {
        const revealObserver = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            {
                threshold: 0.12,
                rootMargin: '0px 0px -35px 0px',
            }
        );

        revealElements.forEach((element) => {
            revealObserver.observe(element);
        });
    }

    // Animate real statistics from zero to their database values.
    const counters = document.querySelectorAll('[data-counter]');

    const formatNumber = (value) =>
        new Intl.NumberFormat('id-ID').format(value);

    const animateCounter = (element) => {
        const target = Number(element.dataset.counter);

        if (!Number.isFinite(target) || target < 0) {
            return;
        }

        if (reducedMotion) {
            element.textContent = formatNumber(target);
            return;
        }

        const duration = 1000;
        let startTime = null;

        const frame = (timestamp) => {
            if (startTime === null) {
                startTime = timestamp;
            }

            const progress = Math.min(
                (timestamp - startTime) / duration,
                1
            );

            // Ease-out cubic.
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(target * eased);

            element.textContent = formatNumber(current);

            if (progress < 1) {
                requestAnimationFrame(frame);
            } else {
                element.textContent = formatNumber(target);
            }
        };

        requestAnimationFrame(frame);
    };

    if ('IntersectionObserver' in window && !reducedMotion) {
        const counterObserver = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                });
            },
            {
                threshold: 0.65,
            }
        );

        counters.forEach((counter) => {
            counterObserver.observe(counter);
        });
    } else {
        counters.forEach((counter) => {
            const target = Number(counter.dataset.counter);

            if (Number.isFinite(target)) {
                counter.textContent = formatNumber(target);
            }
        });
    }

    // Automatically submit when the category changes.
    const categorySelect = document.querySelector(
        '.search-category-wrap select'
    );

    categorySelect?.addEventListener('change', () => {
        categorySelect.form?.requestSubmit();
    });

    // Keep the selected product tab visually active.
    document.querySelectorAll('.product-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.product-tab').forEach((item) => {
                item.classList.remove('is-active');
            });

            tab.classList.add('is-active');
        });
    });
});
