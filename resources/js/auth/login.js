
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const password = document.getElementById('password');
    const passwordToggle = document.getElementById('passwordToggle');
    const submitButton = document.getElementById('loginSubmit');

    if (password && passwordToggle) {
        passwordToggle.addEventListener('click', () => {
            const showPassword = password.type === 'password';

            password.type = showPassword ? 'text' : 'password';

            passwordToggle.textContent = showPassword
                ? 'Sembunyikan'
                : 'Lihat';

            passwordToggle.setAttribute(
                'aria-label',
                showPassword
                    ? 'Sembunyikan kata sandi'
                    : 'Tampilkan kata sandi'
            );

            passwordToggle.setAttribute(
                'aria-pressed',
                String(showPassword)
            );

            password.focus({ preventScroll: true });
        });
    }

    // Hilangkan indikator error ketika pengguna mulai mengedit.
    if (form) {
        form.querySelectorAll('input').forEach((input) => {
            input.addEventListener('input', () => {
                input.setAttribute('aria-invalid', 'false');

                const container = input.closest('.input-container');

                if (container) {
                    container.classList.remove('has-error');
                }

                const field = input.closest('.form-field');
                const error = field?.querySelector('.field-error');

                if (error) {
                    error.remove();
                }
            });
        });
    }

    // Tampilkan status saat form dikirim dan cegah klik ganda.
    if (form && submitButton) {
        let submitting = false;

        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                return;
            }

            if (submitting) {
                event.preventDefault();
                return;
            }

            submitting = true;
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');

            const text = submitButton.querySelector('.submit-text');

            if (text) {
                text.textContent = 'Memeriksa akun...';
            }
        });

        // Pulihkan tombol jika halaman ditampilkan kembali dari cache browser.
        window.addEventListener('pageshow', () => {
            submitting = false;
            submitButton.disabled = false;
            submitButton.removeAttribute('aria-busy');

            const text = submitButton.querySelector('.submit-text');

            if (text) {
                text.textContent = 'Masuk ke akun';
            }
        });
    }
});
