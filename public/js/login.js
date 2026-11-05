(() => {
    const toggle = document.querySelector('.password-toggle');
    const password = document.querySelector('#password');

    if (!toggle || !password) {
        return;
    }

    toggle.addEventListener('click', () => {
        const willShow = password.type === 'password';
        password.type = willShow ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(willShow));
        toggle.setAttribute('aria-label', willShow ? 'Hide password' : 'Show password');
    });
})();
