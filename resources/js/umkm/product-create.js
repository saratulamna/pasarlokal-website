
(() => {
    'use strict';

    function initProductCreate() {
        const form = document.querySelector('#upcForm');

        if (!form || form.dataset.initialized === 'true') {
            return;
        }

        form.dataset.initialized = 'true';

        const nameInput = document.querySelector('#upcName');
        const categoryInput = document.querySelector('#upcCategory');
        const descriptionInput = document.querySelector('#upcDescription');
        const priceInput = document.querySelector('#upcPrice');
        const stockInput = document.querySelector('#upcStock');
        const imageInput = document.querySelector('#upcImage');

        const uploadZone = document.querySelector('#upcDropzone');
        const uploadPlaceholder = document.querySelector('#upcUploadPlaceholder');
        const uploadPreview = document.querySelector('#upcPreviewImage');
        const uploadChange = document.querySelector('#upcUploadChange');

        const cardPlaceholder = document.querySelector('#upcCardPlaceholder');
        const cardImage = document.querySelector('#upcPreviewCardImage');

        const previewName = document.querySelector('#upcPreviewName');
        const previewCategory = document.querySelector('#upcPreviewCategory');
        const previewDescription = document.querySelector('#upcPreviewDescription');
        const previewPrice = document.querySelector('#upcPreviewPrice');
        const livePrice = document.querySelector('#upcLivePrice');

        const nameCount = document.querySelector('#upcNameCount');
        const descriptionCount = document.querySelector('#upcDescriptionCount');

        const checkName = document.querySelector('#upcCheckName');
        const checkPrice = document.querySelector('#upcCheckPrice');
        const checkImage = document.querySelector('#upcCheckImage');

        const submitButton = document.querySelector('#upcSubmit');

        const rupiah = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        });

        let currentImageUrl = null;

        function updateChecklist(element, complete) {
            if (!element) return;

            element.classList.toggle('is-complete', Boolean(complete));
        }

        function updateName() {
            const value = nameInput?.value.trim() || '';

            if (previewName) {
                previewName.textContent = value || 'Nama produkmu';
            }

            if (nameCount) {
                nameCount.textContent = String(nameInput?.value.length || 0);
            }

            updateChecklist(checkName, value.length > 0);
        }

        function updateCategory() {
            if (!categoryInput || !previewCategory) return;

            const selectedOption = categoryInput.options[categoryInput.selectedIndex];
            const label = selectedOption?.value
                ? selectedOption.text.trim()
                : 'KATEGORI PRODUK';

            previewCategory.textContent = label;
        }

        function updateDescription() {
            const value = descriptionInput?.value.trim() || '';

            if (previewDescription) {
                previewDescription.textContent =
                    value || 'Deskripsi singkat produk akan tampil di sini.';
            }

            if (descriptionCount) {
                descriptionCount.textContent = String(
                    descriptionInput?.value.length || 0
                );
            }
        }

        function updatePrice() {
            const number = Number(priceInput?.value || 0);
            const formatted = rupiah.format(
                Number.isFinite(number) && number > 0 ? number : 0
            );

            if (previewPrice) {
                previewPrice.textContent = formatted;
            }

            if (livePrice) {
                livePrice.textContent = formatted;
            }

            updateChecklist(
                checkPrice,
                priceInput?.value !== '' &&
                priceInput?.value != null &&
                Number(priceInput.value) >= 0
            );
        }

        function setImage(file) {
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                window.alert('Pilih file gambar dengan format yang didukung.');
                return;
            }

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!allowedTypes.includes(file.type)) {
                window.alert('Format yang didukung: JPG, PNG, atau WEBP.');
                if (imageInput) imageInput.value = '';
                return;
            }

            const maxBytes = 2 * 1024 * 1024;

            if (file.size > maxBytes) {
                window.alert('Ukuran foto maksimal 2 MB. Pilih gambar yang lebih kecil.');
                if (imageInput) imageInput.value = '';
                return;
            }

            if (currentImageUrl) {
                URL.revokeObjectURL(currentImageUrl);
            }

            currentImageUrl = URL.createObjectURL(file);

            if (uploadPreview) {
                uploadPreview.src = currentImageUrl;
                uploadPreview.hidden = false;
            }

            if (uploadPlaceholder) {
                uploadPlaceholder.hidden = true;
            }

            if (uploadChange) {
                uploadChange.hidden = false;
            }

            if (cardImage) {
                cardImage.src = currentImageUrl;
                cardImage.hidden = false;
            }

            if (cardPlaceholder) {
                cardPlaceholder.hidden = true;
            }

            updateChecklist(checkImage, true);
        }

        nameInput?.addEventListener('input', updateName);
        categoryInput?.addEventListener('change', updateCategory);
        descriptionInput?.addEventListener('input', updateDescription);
        priceInput?.addEventListener('input', updatePrice);

        imageInput?.addEventListener('change', () => {
            const file = imageInput.files?.[0];

            if (file) {
                setImage(file);
            }
        });

        // Mendukung drag-and-drop gambar.
        if (uploadZone) {
            uploadZone.addEventListener('dragover', (event) => {
                event.preventDefault();
                uploadZone.classList.add('is-dragging');
            });

            uploadZone.addEventListener('dragleave', () => {
                uploadZone.classList.remove('is-dragging');
            });

            uploadZone.addEventListener('drop', (event) => {
                event.preventDefault();
                uploadZone.classList.remove('is-dragging');

                const file = event.dataTransfer?.files?.[0];

                if (!file || !imageInput) return;

                setImage(file);

                // Simpan file ke input agar ikut dikirim ketika submit.
                try {
                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    imageInput.files = transfer.files;
                } catch {
                    // Jika browser tidak mendukung DataTransfer,
                    // pengguna masih bisa memilih gambar lewat input.
                }
            });
        }

        // Status dipakai sebagai pilihan pengguna.
        form.querySelectorAll('input[name="status"]').forEach((radio) => {
            radio.addEventListener('change', () => {
                const status = form.querySelector('input[name="status"]:checked');

                if (status) {
                    form.dataset.productStatus = status.value;
                }
            });
        });

        form.addEventListener('submit', (event) => {
            if (!form.reportValidity()) {
                event.preventDefault();
                return;
            }

            if (submitButton) {
                submitButton.disabled = true;

                const label = submitButton.querySelector('.upc-submit-label');

                if (label) {
                    label.textContent = 'Menyimpan produk...';
                }
            }
        });

        updateName();
        updateCategory();
        updateDescription();
        updatePrice();

        updateChecklist(checkImage, Boolean(imageInput?.files?.length));

        window.addEventListener('pagehide', () => {
            if (currentImageUrl) {
                URL.revokeObjectURL(currentImageUrl);
            }
        }, { once: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductCreate);
    } else {
        initProductCreate();
    }
})();
