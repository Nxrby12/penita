// Meniul de pe telefon: se deschide / inchide la apasarea butonului
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.nav-toggle');
    const nav = document.getElementById('main-nav');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', () => {
        const open = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(open));
    });
});
