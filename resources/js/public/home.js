
document.addEventListener('DOMContentLoaded', () => {
    const targets = [
        ...document.querySelectorAll('[data-reveal]'),
    ];

    const reducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    if (reducedMotion || !('IntersectionObserver' in window)) {
        targets.forEach((element) => {
            element.classList.add('is-visible');
        });

        return;
    }

    document.body.classList.add('home-motion');

    // Atur jeda kecil untuk menciptakan efek stagger.
    targets.forEach((element, index) => {
        const isProduct = element.classList.contains('home-product-card');
        const isCategory = element.classList.contains('home-category-card');
        const isLatest = element.classList.contains('home-latest-card');

        if (isProduct) {
            element.style.setProperty(
                '--reveal-delay',
                `${(index % 4) * 85}ms`
            );
        } else if (isCategory || isLatest) {
            element.style.setProperty(
                '--reveal-delay',
                `${(index % 4) * 65}ms`
            );
        }
    });

    const observer = new IntersectionObserver(
        (entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                currentObserver.unobserve(entry.target);
            });
        },
        {
            threshold: 0.08,
            rootMargin: '0px 0px -35px 0px',
        }
    );

    targets.forEach((element) => {
        observer.observe(element);
    });

    // Hormati perubahan pengaturan animasi saat halaman terbuka.
    const motionPreference = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    );

    motionPreference.addEventListener?.('change', (event) => {
        if (!event.matches) {
            return;
        }

        document.body.classList.remove('home-motion');

        targets.forEach((element) => {
            element.classList.add('is-visible');
        });

        observer.disconnect();
    });
});
