document.addEventListener('DOMContentLoaded', function () {
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (!themeToggleBtn) return;

    const root = document.documentElement;

    function updateThemeButton() {
        if (root.classList.contains('dark-theme')) {
            themeToggleBtn.textContent = '☀';
        } else {
            themeToggleBtn.textContent = '🌙';
        }
    }

    updateThemeButton();

    themeToggleBtn.addEventListener('click', function () {
        root.classList.toggle('dark-theme');

        if (root.classList.contains('dark-theme')) {
            localStorage.setItem('site-theme', 'dark');
        } else {
            localStorage.setItem('site-theme', 'light');
        }

        updateThemeButton();
    });
});