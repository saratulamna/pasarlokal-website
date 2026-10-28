
/* =========================================================
   PASARLOKAL — EDIT PRODUCT INTERACTIONS
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

        function setImageChecklist(hasImage) {
            if (!checkImage) return;

            checkImage.classList.toggle('is-complete', Boolean(hasImage));

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

        function markSaved() {
            submitting = true;
            changed = false;
            form.classList.remove('has-unsaved-changes');
        }

        // Foto yang sudah tersimpan tetap dihitung meskipun
        // pengguna tidak memilih foto pengganti.
        setImageChecklist(
            originalImageExists || Boolean(imageInput?.files?.length)
        );

        // Pantau perubahan di seluruh kolom, termasuk radio status.
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

        // Penanda checklist foto mengikuti pilihan file terbaru.
        imageInput?.addEventListener('change', () => {
            const selectedFile = imageInput.files?.[0];

            if (selectedFile) {
                const validTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (!validTypes.includes(selectedFile.type)) {
                    setImageChecklist(originalImageExists);
                    return;
                }

                if (selectedFile.size > 2 * 1024 * 1024) {
                    setImageChecklist(originalImageExists);
                    return;
                }

                setImageChecklist(true);
                return;
            }

            // Jika tidak memilih file baru, foto tersimpan tetap digunakan.
            setImageChecklist(originalImageExists);
        });

        // Konfirmasi hanya muncul setelah pengguna mengubah data.
        window.addEventListener('beforeunload', (event) => {
            if (!changed || submitting) return;

            event.preventDefault();
            event.returnValue = '';
        });

        // Saat formulir dikirim, hindari peringatan keluar halaman.
        form.addEventListener('submit', (event) => {
            if (!form.reportValidity()) {
                event.preventDefault();
                return;
            }

            markSaved();

            if (submitButton) {
                submitButton.disabled = true;
            }

            if (submitLabel) {
                submitLabel.textContent = 'Menyimpan perubahan...';
            }
        });

        // Pulihkan tombol saat browser mengembalikan halaman melalui Back.
        window.addEventListener('pageshow', () => {
            submitting = false;

            if (submitButton) {
                submitButton.disabled = false;
            }

            if (submitLabel) {
                submitLabel.textContent = 'Simpan perubahan';
            }
        });

        console.info('PasarLokal: interaksi edit produk siap.');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductEdit);
    } else {
        initProductEdit();
    }
})();
