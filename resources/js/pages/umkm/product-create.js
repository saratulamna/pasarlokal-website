
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

        let imageUrl = null;

        function updateCheck(element, complete) {
            element?.classList.toggle('is-complete', Boolean(complete));
        }

        function updateName() {
            const value = nameInput?.value.trim() || '';

            if (previewName) {
                previewName.textContent = value || 'Nama produkmu';
            }

            if (nameCount) {
                nameCount.textContent = String(nameInput?.value.length || 0);
            }

            updateCheck(checkName, value.length > 0);
        }

        function updateCategory() {
            if (!categoryInput || !previewCategory) return;

            const option = categoryInput.options[categoryInput.selectedIndex];

            previewCategory.textContent = option?.value
                ? option.text.trim()
                : 'KATEGORI PRODUK';
        }

        function updateDescription() {
            const value = descriptionInput?.value.trim() || '';

            if (previewDescription) {
                previewDescription.textContent =
                    value || 'Deskripsi singkat produk akan tampil di sini.';
            }

            if (descriptionCount) {
                descriptionCount.textContent =
                    String(descriptionInput?.value.length || 0);
            }
        }

        function updatePrice() {
            const raw = priceInput?.value ?? '';
            const amount = Number(raw);
            const formatted = rupiah.format(
                raw !== '' && Number.isFinite(amount) && amount >= 0
                    ? amount
                    : 0
            );

            if (previewPrice) previewPrice.textContent = formatted;
            if (livePrice) livePrice.textContent = formatted;

            updateCheck(
                checkPrice,
                raw !== '' && Number.isFinite(amount) && amount >= 0
            );
        }

        function showImage(file) {
            if (!file) return;

            const supportedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!supportedTypes.includes(file.type)) {
                window.alert('Gunakan gambar JPG, PNG, atau WEBP.');
                if (imageInput) imageInput.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                window.alert('Ukuran gambar maksimal 2 MB.');
                if (imageInput) imageInput.value = '';
                return;
            }

            if (imageUrl) {
                URL.revokeObjectURL(imageUrl);
            }

            imageUrl = URL.createObjectURL(file);

            if (uploadPreview) {
                uploadPreview.src = imageUrl;
                uploadPreview.hidden = false;
            }

            if (uploadPlaceholder) uploadPlaceholder.hidden = true;
            if (uploadChange) uploadChange.hidden = false;

            if (cardImage) {
                cardImage.src = imageUrl;
                cardImage.hidden = false;
            }

            if (cardPlaceholder) cardPlaceholder.hidden = true;

            updateCheck(checkImage, true);
        }

        nameInput?.addEventListener('input', updateName);
        categoryInput?.addEventListener('change', updateCategory);
        descriptionInput?.addEventListener('input', updateDescription);
        priceInput?.addEventListener('input', updatePrice);

        imageInput?.addEventListener('change', () => {
            showImage(imageInput.files?.[0]);
        });

        // Upload dengan drag-and-drop.
        if (uploadZone && imageInput) {
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

                if (!file) return;

                showImage(file);

                // Memastikan foto ikut dikirim dalam formulir.
                try {
                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    imageInput.files = transfer.files;
                } catch {
                    // Upload melalui tombol pilih file tetap tersedia.
                }
            });
        }

        // Mencegah klik simpan berkali-kali.
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
        updateCheck(checkImage, Boolean(imageInput?.files?.length));

        window.addEventListener('pagehide', () => {
            if (imageUrl) {
                URL.revokeObjectURL(imageUrl);
            }
        }, { once: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductCreate);
    } else {
        initProductCreate();
    }
})();
