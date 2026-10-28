
document.addEventListener('DOMContentLoaded', () => {
    const quantityInput = document.getElementById('productQuantity');
    const quantityButtons = document.querySelectorAll('[data-quantity-step]');
    const cartForm = document.getElementById('productCartForm');
    const cartButton = document.getElementById('addToCartButton');
    const shareButton = document.getElementById('shareProduct');
    const toast = document.getElementById('productToast');

    let toastTimer;

    /* =====================================================
       TOAST NOTIFICATION
       ===================================================== */

    function showToast(message) {
        if (!toast) return;

        toast.textContent = message;
        toast.classList.add('is-visible');

        window.clearTimeout(toastTimer);

        toastTimer = window.setTimeout(() => {
            toast.classList.remove('is-visible');
        }, 2600);
    }

    /* =====================================================
       PRODUCT QUANTITY
       ===================================================== */

    function getQuantityBounds() {
        return {
            min: Math.max(1, Number(quantityInput?.min) || 1),
            max: Math.max(1, Number(quantityInput?.max) || 1)
        };
    }

    function updateQuantity(value) {
        if (!quantityInput) return;

        const { min, max } = getQuantityBounds();
        const parsedValue = Number.parseInt(value, 10);
        const nextValue = Number.isFinite(parsedValue)
            ? parsedValue
            : min;

        quantityInput.value = String(
            Math.min(max, Math.max(min, nextValue))
        );

        quantityButtons.forEach((button) => {
            const step = Number(button.dataset.quantityStep);
            const current = Number(quantityInput.value);

            button.disabled =
                (step < 0 && current <= min) ||
                (step > 0 && current >= max);
        });
    }

    if (quantityInput) {
        updateQuantity(quantityInput.value);

        quantityButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const current = Number(quantityInput.value);
                const step = Number(button.dataset.quantityStep) || 0;

                updateQuantity(current + step);
            });
        });

        quantityInput.addEventListener('change', () => {
            updateQuantity(quantityInput.value);
        });

        quantityInput.addEventListener('blur', () => {
            updateQuantity(quantityInput.value);
        });
    }

    /* =====================================================
       ADD TO CART FEEDBACK
       ===================================================== */

    if (cartForm && cartButton) {
        cartForm.addEventListener('submit', (event) => {
            if (!cartForm.checkValidity()) {
                event.preventDefault();
                cartForm.reportValidity();
                return;
            }

            if (quantityInput) {
                updateQuantity(quantityInput.value);
            }

            cartButton.disabled = true;

            const buttonText = cartButton.querySelector(
                'span:nth-of-type(1)'
            );

            if (buttonText) {
                buttonText.textContent = 'Menambahkan...';
            }
        });

        window.addEventListener('pageshow', () => {
            cartButton.disabled = false;

            const buttonText = cartButton.querySelector(
                'span:nth-of-type(1)'
            );

            if (buttonText) {
                buttonText.textContent = 'Tambah ke keranjang';
            }
        });
    }

    /* =====================================================
       SHARE PRODUCT
       ===================================================== */

    if (shareButton) {
        shareButton.addEventListener('click', async () => {
            const shareData = {
                title: document.title,
                text: 'Lihat produk lokal ini di PasarLokal.',
                url: window.location.href
            };

            try {
                if (navigator.share) {
                    await navigator.share(shareData);
                    return;
                }

                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(shareData.url);
                    showToast('Tautan produk berhasil disalin.');
                    return;
                }

                window.prompt('Salin tautan produk ini:', shareData.url);
            } catch (error) {
                if (error?.name === 'AbortError') {
                    return;
                }

                showToast('Belum berhasil membagikan tautan. Coba lagi.');
            }
        });
    }

    /* =====================================================
       SCROLL REVEAL
       ===================================================== */

    const revealTargets = [...document.querySelectorAll('[data-reveal]')];

    const reducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    if (
        reducedMotion ||
        !('IntersectionObserver' in window)
    ) {
        revealTargets.forEach((element) => {
            element.classList.add('is-visible');
        });

        return;
    }

    document.body.classList.add('pd-motion');

    const observer = new IntersectionObserver(
        (entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                entry.target.classList.add('is-visible');
                currentObserver.unobserve(entry.target);
            });
        },
        {
            threshold: 0.08,
            rootMargin: '0px 0px -30px 0px'
        }
    );

    revealTargets.forEach((element) => {
        observer.observe(element);
    });

    const motionPreference = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    );

    motionPreference.addEventListener?.('change', (event) => {
        if (!event.matches) return;

        document.body.classList.remove('pd-motion');

        revealTargets.forEach((element) => {
            element.classList.add('is-visible');
        });

        observer.disconnect();
    });
});
