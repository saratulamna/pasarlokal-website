
document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('.pd-page');

    if (!page) return;

    // Salin nomor pesanan
    const copyButton = page.querySelector('[data-copy-order]');

    if (copyButton) {
        copyButton.addEventListener('click', async () => {
            const orderCode = copyButton.dataset.copyOrder;

            try {
                await navigator.clipboard.writeText(orderCode);

                copyButton.innerHTML = '<span aria-hidden="true">✓</span> Tersalin!';

                window.setTimeout(() => {
                    copyButton.innerHTML = '<span aria-hidden="true">▣</span> Salin';
                }, 1600);
            } catch {
                window.prompt('Salin nomor pesanan berikut:', orderCode);
            }
        });
    }

    // Preview file bukti pembayaran
    const input = page.querySelector('#pdProofFile');
    const zone = page.querySelector('#pdUploadZone');
    const preview = page.querySelector('#pdFilePreview');
    const fileName = page.querySelector('#pdFileName');
    const fileSize = page.querySelector('#pdFileSize');
    const fileIcon = page.querySelector('#pdFileIcon');
    const uploadTitle = page.querySelector('#pdUploadTitle');
    const uploadDescription = page.querySelector('#pdUploadDescription');
    const removeButton = page.querySelector('#pdRemoveFile');
    const form = page.querySelector('#pdPaymentForm');

    if (!input || !zone || !preview) return;

    const maxSize = 4 * 1024 * 1024;

    const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

    function formatBytes(bytes) {
        if (bytes < 1024 * 1024) {
            return `${(bytes / 1024).toFixed(0)} KB`;
        }

        return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
    }

    function resetFile() {
        input.value = '';
        preview.hidden = true;

        fileName.textContent = '';
        fileSize.textContent = '';
        fileIcon.textContent = 'FILE';

        uploadTitle.textContent = 'Pilih file atau klik di sini';
        uploadDescription.textContent = 'JPG, JPEG, PNG, WEBP, atau PDF';

        zone.classList.remove('is-dragging');
    }

    function showError(message) {
        window.alert(message);
        resetFile();
    }

    function handleFile(file) {
        if (!file) {
            resetFile();
            return;
        }

        const extension = file.name.split('.').pop().toLowerCase();

        if (!allowedExtensions.includes(extension)) {
            showError('Format file tidak didukung. Gunakan JPG, JPEG, PNG, WEBP, atau PDF.');
            return;
        }

        if (file.size > maxSize) {
            showError('Ukuran file melebihi 4 MB. Pilih file yang lebih kecil.');
            return;
        }

        fileName.textContent = file.name;
        fileSize.textContent = formatBytes(file.size);
        fileIcon.textContent = extension === 'pdf' ? 'PDF' : extension.toUpperCase();

        uploadTitle.textContent = 'File siap diunggah';
        uploadDescription.textContent = 'Periksa kembali nama file sebelum mengirim.';

        preview.hidden = false;
    }

    input.addEventListener('change', () => {
        handleFile(input.files?.[0]);
    });

    if (removeButton) {
        removeButton.addEventListener('click', resetFile);
    }

    // Drag and drop
    ['dragenter', 'dragover'].forEach((eventName) => {
        zone.addEventListener(eventName, (event) => {
            event.preventDefault();
            zone.classList.add('is-dragging');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        zone.addEventListener(eventName, (event) => {
            event.preventDefault();
            zone.classList.remove('is-dragging');
        });
    });

    zone.addEventListener('drop', (event) => {
        const file = event.dataTransfer?.files?.[0];

        if (!file) return;

        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;

        handleFile(file);
    });

    if (form) {
        form.addEventListener('submit', (event) => {
            const file = input.files?.[0];

            if (!file) {
                event.preventDefault();
                showError('Pilih file bukti pembayaran terlebih dahulu.');
                return;
            }

            if (file.size > maxSize) {
                event.preventDefault();
                showError('Ukuran file melebihi 4 MB.');
            }
        });
    }
});
