
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registerForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmationInput = document.getElementById('password_confirmation');

    const passwordStrength = document.getElementById('passwordStrength');
    const confirmationWrap = document.getElementById('confirmationWrap');
    const confirmationError = document.getElementById('confirmation-error');

    const submitButton = document.getElementById('registerSubmit');
    const submitText = submitButton?.querySelector('.register-submit-text');

    /* ================================================
       TOGGLE PASSWORD VISIBILITY
    ================================================ */

    document.querySelectorAll('[data-password-target]').forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.passwordTarget;
            const targetInput = document.getElementById(targetId);

            if (!targetInput) {
                return;
            }

            const shouldShow = targetInput.type === 'password';

            targetInput.type = shouldShow ? 'text' : 'password';
            button.textContent = shouldShow ? 'Sembunyi' : 'Lihat';

            button.setAttribute(
                'aria-label',
                shouldShow
                    ? 'Sembunyikan kata sandi'
                    : 'Tampilkan kata sandi'
            );

            button.setAttribute('aria-pressed', String(shouldShow));
        });
    });

    /* ================================================
       PASSWORD STRENGTH
       This is a local hint, not server-side validation.
    ================================================ */

    function calculatePasswordStrength(value) {
        if (!value) {
            return 0;
        }

        let score = 0;

        if (value.length >= 8) score++;
        if (value.length >= 12) score++;
        if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
        if (/\d/.test(value) || /[^A-Za-z0-9]/.test(value)) score++;

        return Math.min(score, 4);
    }

    function updatePasswordStrength() {
        if (!passwordInput || !passwordStrength) {
            return;
        }

        const strength = calculatePasswordStrength(passwordInput.value);

        const labels = [
            'Gunakan minimal 8 karakter',
            'Kata sandi masih lemah',
            'Kata sandi cukup',
            'Kata sandi cukup kuat',
            'Kata sandi kuat'
        ];

        passwordStrength.dataset.strength = String(strength);

        const text = passwordStrength.querySelector('.strength-text');

        if (text) {
            text.textContent = labels[strength];
        }
    }

    passwordInput?.addEventListener('input', () => {
        updatePasswordStrength();
        updateConfirmation();
    });

    /* ================================================
       PASSWORD CONFIRMATION
    ================================================ */

    function updateConfirmation() {
        if (!passwordInput || !confirmationInput || !confirmationWrap) {
            return true;
        }

        const passwordValue = passwordInput.value;
        const confirmationValue = confirmationInput.value;

        const mismatch =
            confirmationValue.length > 0 &&
            passwordValue !== confirmationValue;

        const bothFilled =
            passwordValue.length > 0 &&
            confirmationValue.length > 0;

        confirmationWrap.classList.toggle(
            'has-error',
            mismatch
        );

        confirmationWrap.classList.toggle(
            'is-valid',
            bothFilled && !mismatch
        );

        confirmationInput.setAttribute(
            'aria-invalid',
            String(mismatch)
        );

        if (confirmationError) {
            confirmationError.hidden = !mismatch;
            confirmationError.textContent = mismatch
                ? 'Konfirmasi kata sandi belum cocok.'
                : '';
        }

        return !mismatch;
    }

    confirmationInput?.addEventListener('input', updateConfirmation);

    /* ================================================
       INLINE ERROR CLEANUP
    ================================================ */

    form?.querySelectorAll('input').forEach((input) => {
        input.addEventListener('input', () => {
            const wrapper = input.closest('.register-input-wrap');

            if (wrapper && input.value.trim().length > 0) {
                wrapper.classList.remove('has-error');
            }

            if (input.id !== 'password_confirmation') {
                input.setAttribute('aria-invalid', 'false');
            }

            const field = input.closest('.register-field');
            const error = field?.querySelector('.register-error:not([hidden])');

            if (error && error.id !== 'confirmation-error') {
                error.remove();
            }
        });

        input.addEventListener('blur', () => {
            const wrapper = input.closest('.register-input-wrap');

            if (!wrapper) {
                return;
            }

            if (input.validity.valid && input.value.trim().length > 0) {
                wrapper.classList.add('is-valid');
                wrapper.classList.remove('has-error');
            } else {
                wrapper.classList.remove('is-valid');

                if (input.value.length > 0 && !input.validity.valid) {
                    wrapper.classList.add('has-error');
                }
            }

            if (input.id === 'password_confirmation') {
                updateConfirmation();
            }
        });
    });

    /* ================================================
       FORM VALIDATION AND DOUBLE-SUBMIT GUARD
    ================================================ */

    let submitting = false;

    form?.addEventListener('submit', (event) => {
        updatePasswordStrength();

        const confirmationMatches = updateConfirmation();

        // Validate using browser constraints.
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }

        if (!confirmationMatches) {
            event.preventDefault();
            confirmationInput?.focus();
            return;
        }

        // Prevent accidental repeated submissions.
        if (submitting) {
            event.preventDefault();
            return;
        }

        submitting = true;

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
        }

        if (submitText) {
            submitText.textContent = 'Membuat akun...';
        }
    });

    // Restore button state if the browser restores the page.
    window.addEventListener('pageshow', () => {
        submitting = false;

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.removeAttribute('aria-busy');
        }

        if (submitText) {
            submitText.textContent = 'Buat akun sekarang';
        }
    });

    updatePasswordStrength();
});
