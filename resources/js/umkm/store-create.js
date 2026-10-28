
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#stcStoreForm');

    if (!form) return;

    const nameInput = form.querySelector('[data-store-name]');
    const nameAlias = form.querySelector('#stcNamaUsahaAlias');

    const categoryInput = form.querySelector('[data-store-category]');
    const categoryAlias = form.querySelector('#stcKategoriAlias');

    const descriptionInput = form.querySelector('[data-store-description]');
    const descriptionCount = document.querySelector('#stcDescriptionCount');

    const previewName = document.querySelector('#stcPreviewName');
    const previewCategory = document.querySelector('#stcPreviewCategory');
    const previewDescription = document.querySelector('#stcPreviewDescription');

    const initial = document.querySelector('#stcPreviewInitial');
    const logoInitial = document.querySelector('#stcLogoInitial');

    const logoInput = form.querySelector('#logo');
    const logoImage = document.querySelector('#stcLogoImage');
    const previewLogo = document.querySelector('#stcPreviewLogo');
    const logoFileName = document.querySelector('#stcLogoFileName');

    const submitButton = document.querySelector('#stcSubmitButton');

    // Preview nama toko
    function updateName() {
        const name = nameInput?.value.trim() || '';

        if (previewName) {
            previewName.textContent = name || 'Nama tokomu di sini';
        }

        if (nameAlias) {
            nameAlias.value = name;
        }

        const firstLetter = [...(name || 'P')][0].toLocaleUpperCase('id-ID');

        if (initial) initial.textContent = firstLetter;
        if (logoInitial) logoInitial.textContent = firstLetter;
    }

    // Preview kategori
    function updateCategory() {
        const category = categoryInput?.value || '';

        if (previewCategory) {
            previewCategory.textContent = category || 'Bidang usaha';
        }

        if (categoryAlias) {
            categoryAlias.value = category;
        }
    }

    // Preview deskripsi dan penghitung karakter
    function updateDescription() {
        const description = descriptionInput?.value.trim() || '';

        if (previewDescription) {
            previewDescription.textContent =
                description || 'Cerita unik dan karya lokalmu akan tampil di sini.';
        }

        if (descriptionCount && descriptionInput) {
            descriptionCount.textContent = descriptionInput.value.length;
        }
    }

    nameInput?.addEventListener('input', updateName);
    categoryInput?.addEventListener('change', updateCategory);
    descriptionInput?.addEventListener('input', updateDescription);

    // Preview logo toko
    let currentObjectUrl = null;

    logoInput?.addEventListener('change', () => {
        const file = logoInput.files?.[0];

        if (!file) {
            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                currentObjectUrl = null;
            }

            if (logoImage) {
                logoImage.src = '';
                logoImage.hidden = true;
            }

            if (previewLogo) {
                previewLogo.src = '';
                previewLogo.hidden = true;
            }

            if (logoInitial) logoInitial.hidden = false;
            if (initial) initial.hidden = false;

            if (logoFileName) {
                logoFileName.textContent = 'Belum ada gambar dipilih';
            }

            return;
        }

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        if (!allowedTypes.includes(file.type)) {
            window.alert('Format logo harus JPG, PNG, atau WEBP.');
            logoInput.value = '';
            logoInput.dispatchEvent(new Event('change'));
            return;
        }

        const maxSize = 2 * 1024 * 1024;

        if (file.size > maxSize) {
            window.alert('Ukuran logo terlalu besar. Pilih gambar maksimal 2 MB.');
            logoInput.value = '';
            logoInput.dispatchEvent(new Event('change'));
            return;
        }

        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
        }

        currentObjectUrl = URL.createObjectURL(file);

        if (logoImage) {
            logoImage.src = currentObjectUrl;
            logoImage.hidden = false;
        }

        if (previewLogo) {
            previewLogo.src = currentObjectUrl;
            previewLogo.hidden = false;
        }

        if (logoInitial) logoInitial.hidden = true;
        if (initial) initial.hidden = true;

        if (logoFileName) {
            logoFileName.textContent = file.name;
        }
    });

    // Ubah tombol saat formulir dikirim.
    // Validasi server Laravel tetap menjadi pemeriksaan utama.
    form.addEventListener('submit', (event) => {
        updateName();
        updateCategory();
        updateDescription();

        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.querySelector('span').textContent = 'Sedang mengirim...';
            submitButton.querySelector('b').textContent = '…';
        }
    });

    // Isi preview dengan nilai lama ketika validasi server gagal.
    updateName();
    updateCategory();
    updateDescription();
});
