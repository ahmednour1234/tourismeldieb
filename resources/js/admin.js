import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('adminShell', () => ({
    sidebarOpen: false,
    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
    darkMode: localStorage.getItem('darkMode') === 'true',
    init() {
        this.applyDarkMode();
    },
    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('sidebarCollapsed', String(this.sidebarCollapsed));
    },
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', String(this.darkMode));
        this.applyDarkMode();
    },
    applyDarkMode() {
        document.documentElement.classList.toggle('dark', this.darkMode);
    },
}));

Alpine.start();
