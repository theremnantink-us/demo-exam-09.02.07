/* ============================================================================
   app.js — клиентская логика (необязательная, но улучшает UX и нужна Модулю 3):
     1. Маска и проверка телефона +7(XXX)-XXX-XX-XX.
     2. Чекбокс «Иная услуга» -> показ/скрытие текстового поля.
     3. В админке: поле «причина отмены» появляется только при статусе «Отменено».
   Серверная валидация (helpers.php) всё равно главная — JS лишь подсказывает.
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {

    // --- 1. Маска телефона: на любой input с data-mask="phone" ---------------
    document.querySelectorAll('input[data-mask="phone"]').forEach(input => {
        input.addEventListener('input', () => {
            let d = input.value.replace(/\D/g, '');          // только цифры
            if (d.startsWith('8')) d = '7' + d.slice(1);       // 8... -> 7...
            if (!d.startsWith('7')) d = '7' + d;               // всегда с 7
            d = d.slice(0, 11);                                 // максимум 11 цифр
            let r = '+7';
            if (d.length > 1) r += '(' + d.slice(1, 4);
            if (d.length >= 4) r += ')-' + d.slice(4, 7);
            if (d.length >= 7) r += '-' + d.slice(7, 9);
            if (d.length >= 9) r += '-' + d.slice(9, 11);
            input.value = r;
        });
    });

    // --- 2. Чекбокс «Иная услуга» -------------------------------------------
    const other = document.getElementById('is_other');
    const wrap  = document.getElementById('other_wrap');
    const select = document.getElementById('service_id');
    if (other && wrap) {
        const toggle = () => {
            wrap.style.display = other.checked ? 'block' : 'none';
            if (other.checked) { wrap.classList.remove('reveal'); void wrap.offsetWidth; wrap.classList.add('reveal'); }
            if (select) select.disabled = other.checked;       // нельзя выбрать оба
        };
        other.addEventListener('change', toggle);
        toggle();   // применить при загрузке (если форма вернулась с ошибкой)
    }

    // --- 3. Админка: причина отмены видна только при статусе «Отменено» ------
    document.querySelectorAll('[data-status-form]').forEach(form => {
        const sel = form.querySelector('select[name="status"]');
        const reason = form.querySelector('.reason');
        if (!sel || !reason) return;
        const sync = () => {
            const cancel = sel.value === 'canceled';
            reason.style.display = cancel ? 'block' : 'none';
            reason.required = cancel;
        };
        sel.addEventListener('change', sync);
        sync();
    });
});
