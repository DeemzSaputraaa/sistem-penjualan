import './bootstrap';

const THEME_STORAGE_KEY = 'theme';
const THEME_DARK = 'dark';

const applyTheme = (theme) => {
    if (theme === THEME_DARK) {
        document.body.classList.add('theme-dark');
    } else {
        document.body.classList.remove('theme-dark');
    }
};

const getStoredTheme = () => {
    try {
        return localStorage.getItem(THEME_STORAGE_KEY);
    } catch (error) {
        return null;
    }
};

const storeTheme = (theme) => {
    try {
        localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (error) {
        // Ignore storage errors (private mode, disabled storage).
    }
};

const toggleTheme = () => {
    const isDark = document.body.classList.contains('theme-dark');
    const nextTheme = isDark ? 'light' : THEME_DARK;
    applyTheme(nextTheme);
    storeTheme(nextTheme);
};

document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = getStoredTheme();
    applyTheme(savedTheme);

    const toggleButton = document.querySelector('[data-theme-toggle]');
    if (toggleButton) {
        toggleButton.addEventListener('click', toggleTheme);
    }
});
