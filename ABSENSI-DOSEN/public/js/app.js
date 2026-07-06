document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const themeToggle = document.getElementById('themeToggle');

    const closeSidebar = () => {
        sidebar?.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
    };

    sidebarToggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('-translate-x-full');
        overlay?.classList.toggle('hidden');
    });

    overlay?.addEventListener('click', closeSidebar);

    document.querySelectorAll('[data-auto-submit]').forEach((field) => {
        field.addEventListener('change', () => field.form?.submit());
    });

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || 'Lanjutkan aksi ini?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.querySelector(button.getAttribute('data-toggle-password'));
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            const icon = button.querySelector('i');
            icon?.classList.toggle('fa-eye');
            icon?.classList.toggle('fa-eye-slash');
        });
    });

    document.querySelectorAll('[data-mark-all]').forEach((button) => {
        button.addEventListener('click', () => {
            const status = button.getAttribute('data-mark-all');
            document.querySelectorAll(`input[type="radio"][value="${status}"]`).forEach((input) => {
                input.checked = true;
            });
        });
    });

    const savedTheme = localStorage.getItem('absensi-theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
    }

    themeToggle?.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('absensi-theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
    });
});
