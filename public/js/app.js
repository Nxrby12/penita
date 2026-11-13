document.addEventListener('DOMContentLoaded', () => {
    // ---------- Meniul de pe telefon ----------
    const toggle = document.querySelector('.nav-toggle');
    const nav = document.getElementById('main-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(open));
        });
    }

    // ---------- Inregistrare: "Pseudonim" doar pentru autori ----------
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

    // ---------- Confirmare inainte de stergere ----------
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // ---------- Verificarea dimensiunii fisierelor inainte de trimitere ----------
    // (doar pentru confortul utilizatorului; serverul verifica oricum)
    document.querySelectorAll('input[type="file"][data-max-bytes]').forEach((input) => {
        input.addEventListener('change', () => {
            const max = Number(input.dataset.maxBytes);
            const file = input.files[0];
            input.setCustomValidity(file && file.size > max ? 'Fișierul depășește limita de 12 MB.' : '');
            input.reportValidity();
        });
    });

    // ---------- CAPTCHA: alt cod ----------
    document.querySelectorAll('[data-captcha-refresh]').forEach((button) => {
        button.addEventListener('click', () => {
            const img = button.closest('.captcha-field').querySelector('.captcha-img');
            img.src = img.src.split('?')[0] + '?t=' + Date.now();
            button.closest('.captcha-field').querySelector('input').value = '';
        });
    });

    // ---------- Carusel ----------
    document.querySelectorAll('[data-carousel]').forEach(initCarousel);
});

function initCarousel(root) {
    const slides = [...root.querySelectorAll('.carousel-slide')];
    const dots = [...root.querySelectorAll('.carousel-dots button')];
    if (slides.length < 2) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let current = 0;
    let timer = null;

    const show = (index) => {
        current = (index + slides.length) % slides.length;
        slides.forEach((slide, i) => {
            const active = i === current;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', String(!active));
            slide.querySelectorAll('a, button').forEach((el) => {
                if (active) el.removeAttribute('tabindex');
                else el.setAttribute('tabindex', '-1');
            });
        });
        dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === current);
            if (i === current) dot.setAttribute('aria-current', 'true');
            else dot.removeAttribute('aria-current');
        });
    };

    const start = () => {
        if (!reduceMotion && !timer) timer = setInterval(() => show(current + 1), 5000);
    };
    const stop = () => {
        clearInterval(timer);
        timer = null;
    };

    root.querySelector('.carousel-prev').addEventListener('click', () => { show(current - 1); });
    root.querySelector('.carousel-next').addEventListener('click', () => { show(current + 1); });
    dots.forEach((dot, i) => dot.addEventListener('click', () => show(i)));

    // Tastele sageti cand caruselul are focus
    root.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') show(current - 1);
        if (event.key === 'ArrowRight') show(current + 1);
    });

    // Swipe pe telefon
    let startX = null;
    root.addEventListener('touchstart', (event) => { startX = event.touches[0].clientX; }, { passive: true });
    root.addEventListener('touchend', (event) => {
        if (startX === null) return;
        const dx = event.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) show(current + (dx < 0 ? 1 : -1));
        startX = null;
    });

    // Se opreste cat timp utilizatorul citeste sau foloseste caruselul
    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    root.addEventListener('focusin', stop);
    root.addEventListener('focusout', start);

    start();
}
