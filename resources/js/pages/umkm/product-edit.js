
/* =========================================================
   PASARLOKAL — EDIT PRODUCT
   ========================================================= */

(() => {
    'use strict';

    function initProductEdit() {
        const form = document.querySelector('#upcForm');

        if (!form || form.dataset.editInitialized === 'true') {
            return;
        }

        form.dataset.editInitialized = 'true';

        const imageInput = form.querySelector('#upcImage');
        const checkImage = document.querySelector('#upcCheckImage');
        const submitButton = document.querySelector('#upcSubmit');
        const submitLabel = submitButton?.querySelector('.upc-submit-label');

        const originalImageExists =
            imageInput?.dataset.existingImage === 'true';

        let changed = false;
        let submitting = false;

        function updateImageChecklist(hasImage) {
            if (!checkImage) return;

            checkImage.classList.toggle(
                'is-complete',
                Boolean(hasImage)
            );

            const label = checkImage.querySelector('span:last-child');

            if (label) {
                label.textContent = hasImage
                    ? 'Foto produk tersedia'
                    : 'Foto belum tersedia (opsional)';
            }
        }

        function markChanged() {
            changed = true;
            form.classList.add('has-unsaved-changes');
        }

        // Pertahankan status foto lama jika tidak diganti.
        updateImageChecklist(
            originalImageExists || Boolean(imageInput?.files?.length)
        );

        // Pantau perubahan formulir.
        form.addEventListener('input', (event) => {
            if (event.target instanceof HTMLElement) {
                markChanged();
            }
        });

        form.addEventListener('change', (event) => {
            if (event.target instanceof HTMLElement) {
                markChanged();
            }
        });

        // Validasi ringan dan checklist foto baru.
        imageInput?.addEventListener('change', () => {
            const file = imageInput.files?.[0];

            if (!file) {
                updateImageChecklist(originalImageExists);
                return;
            }

            const validTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!validTypes.includes(file.type)) {
                window.alert('Format foto harus JPG, PNG, atau WEBP.');
                imageInput.value = '';
                updateImageChecklist(originalImageExists);
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                window.alert('Ukuran foto maksimal 2 MB.');
                imageInput.value = '';
                updateImageChecklist(originalImageExists);
                return;
            }

            updateImageChecklist(true);
        });

        // Peringatan jika keluar sebelum perubahan disimpan.
        window.addEventListener('beforeunload', (event) => {
            if (!changed || submitting) return;

            event.preventDefault();
            event.returnValue = '';
        });

        // Validasi dan cegah submit berulang.
        form.addEventListener('submit', (event) => {
            if (!form.reportValidity()) {
                event.preventDefault();
                return;
            }

            submitting = true;
            changed = false;
            form.classList.remove('has-unsaved-changes');

            if (submitButton) {
                submitButton.disabled = true;
            }

            if (submitLabel) {
                submitLabel.textContent = 'Menyimpan perubahan...';
            }
        });

        // Pulihkan tombol jika browser menampilkan ulang halaman.
        window.addEventListener('pageshow', () => {
            submitting = false;

            if (submitButton) {
                submitButton.disabled = false;
            }

            if (submitLabel) {
                submitLabel.textContent = 'Simpan perubahan';
            }
        });

        console.info('PasarLokal: halaman edit produk siap.');
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initProductEdit
        );
    } else {
        initProductEdit();
    }
})();
