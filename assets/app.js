/* ============================================================================
   app.js — клиентская логика: слайдер, всплывающие сообщения, маски ввода.
   Серверная валидация (helpers.php) всё равно главная; JS — для удобства.
   ============================================================================ */
document.addEventListener('DOMContentLoaded', () => {

    /* ---------- 1. Слайдер: автосмена каждые 3 сек + вперёд/назад + точки ---- */
    document.querySelectorAll('[data-slider]').forEach(slider => {
        const slides = [...slider.querySelectorAll('.slide')];
        const dots   = [...slider.querySelectorAll('.dot')];
        const interval = parseInt(slider.dataset.interval || '3000', 10);
        let i = 0, timer = null;

        const show = n => {
            i = (n + slides.length) % slides.length;
            slides.forEach((s, k) => s.classList.toggle('active', k === i));
            dots.forEach((d, k) => d.classList.toggle('active', k === i));
        };
        const next = () => show(i + 1);
        const prev = () => show(i - 1);
        const start = () => { stop(); timer = setInterval(next, interval); };  // авто 3 сек
        const stop  = () => { if (timer) clearInterval(timer); };

        slider.querySelector('[data-next]')?.addEventListener('click', () => { next(); start(); });
        slider.querySelector('[data-prev]')?.addEventListener('click', () => { prev(); start(); });
        dots.forEach((d, k) => d.addEventListener('click', () => { show(k); start(); }));
        slider.addEventListener('mouseenter', stop);   // пауза при наведении
        slider.addEventListener('mouseleave', start);
        if (slides.length > 1) start();
    });

    /* ---------- 2. Всплывающие сообщения: сами исчезают через 3.5 сек -------- */
    document.querySelectorAll('[data-toast]').forEach(t => {
        setTimeout(() => t.classList.add('hide'), 3500);
    });

    /* ---------- 3. Маска телефона 8(XXX)XXX-XX-XX --------------------------- */
    document.querySelectorAll('input[data-mask="phone"]').forEach(input => {
        input.addEventListener('input', () => {
            let d = input.value.replace(/\D/g, '');
            if (d.startsWith('7')) d = '8' + d.slice(1);   // +7 -> 8
            if (!d.startsWith('8')) d = '8' + d;
            d = d.slice(0, 11);
            let r = '8';
            if (d.length > 1) r += '(' + d.slice(1, 4);
            if (d.length >= 4) r += ')' + d.slice(4, 7);
            if (d.length >= 7) r += '-' + d.slice(7, 9);
            if (d.length >= 9) r += '-' + d.slice(9, 11);
            input.value = r;
        });
    });

    /* ---------- 4. Маска даты ДД.ММ.ГГГГ ------------------------------------ */
    document.querySelectorAll('input[data-mask="date"]').forEach(input => {
        input.addEventListener('input', () => {
            let d = input.value.replace(/\D/g, '').slice(0, 8);
            let r = d.slice(0, 2);
            if (d.length >= 3) r += '.' + d.slice(2, 4);
            if (d.length >= 5) r += '.' + d.slice(4, 8);
            input.value = r;
        });
    });
});
