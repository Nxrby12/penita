document.addEventListener('DOMContentLoaded', () => {
    // Meniul de pe telefon: se deschide / inchide la apasarea butonului
    const toggle = document.querySelector('.nav-toggle');
    const nav = document.getElementById('main-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(open));
        });
    }

    // Inregistrare: campul "Pseudonim" apare doar cand e ales rolul de autor
    const authorOnly = document.querySelector('.author-only');
    const roleInputs = document.querySelectorAll('input[name="role"]');
    if (authorOnly && roleInputs.length) {
        const update = () => {
            const checked = document.querySelector('input[name="role"]:checked');
            authorOnly.hidden = !checked || checked.value !== 'autor';
        };
        roleInputs.forEach((input) => input.addEventListener('change', update));
        update();
    }
});
