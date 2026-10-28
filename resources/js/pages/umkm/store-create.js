(() => {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        // Preview logo toko sebelum formulir dikirim.
        const logoInput = document.querySelector(
            'input[name="logo"][type="file"], #logo, [data-store-logo-input]'
        );

        const logoPreview = document.querySelector(
            '[data-logo-preview], #logoPreview, .store-create__logo-preview'
        );

        let previewUrl = null;

        if (logoInput && logoPreview) {
            logoInput.addEventListener('change', () => {
                const file = logoInput.files?.[0];

                if (!file) {
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    alert('Silakan pilih file gambar untuk logo toko.');
                    logoInput.value = '';
                    return;
                }

                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                }

                previewUrl = URL.createObjectURL(file);

                if (logoPreview instanceof HTMLImageElement) {
                    logoPreview.src = previewUrl;
                    logoPreview.hidden = false;
                    logoPreview.alt = 'Preview logo toko';
                } else {
                    const image = logoPreview.querySelector('img');

                    if (image) {
                        image.src = previewUrl;
                        image.hidden = false;
                        image.alt = 'Preview logo toko';
                    }
                }
            });
        }

        // Penghitung karakter deskripsi, jika tersedia di Blade.
        const description = document.querySelector(
            'textarea[name="deskripsi"], [data-store-description]'
        );

        const characterCount = document.querySelector(
            '[data-character-count]'
        );

        function updateCharacterCount() {
            if (!description || !characterCount) {
                return;
            }

            characterCount.textContent = description.value.length;
        }

        if (description) {
            description.addEventListener('input', updateCharacterCount);
            updateCharacterCount();
        }

        // Konfirmasi sebelum mengosongkan formulir.
        document.querySelectorAll('[data-reset-store-form]').forEach((button) => {
            button.addEventListener('click', (event) => {
                const form = button.closest('form');

                if (!form) {
                    return;
                }

                const confirmed = window.confirm(
                    'Kosongkan semua isian formulir toko?'
                );

                if (!confirmed) {
                    event.preventDefault();
                }
            });
        });

        console.info('Script halaman Buat Toko berhasil dimuat.');
    });
})();