document.addEventListener('DOMContentLoaded', () => {
    // Tampilkan atau sembunyikan password.
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.querySelector(button.dataset.target);

            if (!target) {
                return;
            }

            const shouldShow = target.type === 'password';

            target.type = shouldShow ? 'text' : 'password';
            button.textContent = shouldShow ? 'Sembunyikan' : 'Lihat';
            button.setAttribute(
                'aria-label',
                shouldShow ? 'Sembunyikan password' : 'Tampilkan password'
            );
        });
    });

    // Keterangan berubah mengikuti role yang dipilih saat registrasi.
    const roleInputs = document.querySelectorAll('input[name="role"]');
    const roleNote = document.querySelector('[data-role-note]');

    const updateRoleNote = () => {
        if (!roleNote) {
            return;
        }

        const selectedRole = document.querySelector(
            'input[name="role"]:checked'
        );

        roleNote.textContent =
            selectedRole?.value === 'umkm'
                ? 'Kamu akan membuat akun penjual. Setelah masuk, kamu dapat melanjutkan pengaturan toko.'
                : 'Kamu akan mendaftar sebagai pembeli dan dapat menjelajahi produk lokal.';
    };

    roleInputs.forEach((input) => {
        input.addEventListener('change', updateRoleNote);
    });

    updateRoleNote();

    // Mencegah pengiriman formulir berkali-kali saat sedang diproses.
    document.querySelectorAll('.auth-form').forEach((form) => {
        form.addEventListener('submit', () => {
            if (!form.reportValidity()) {
                return;
            }

            const button = form.querySelector('.auth-submit');

            if (!button) {
                return;
            }

            button.disabled = true;
            button.querySelector('span:first-child').textContent =
                'Memproses...';
        });
    });
});