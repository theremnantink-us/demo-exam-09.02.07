<?php
/* ============================================================================
   helpers.php — экранирование вывода + ВАЛИДАЦИЯ полей (серверная сторона).
   ----------------------------------------------------------------------------
   Валидация здесь — главная по требованиям Модуля 3. Клиентская (app.js) лишь
   дублирует её для удобства; настоящая проверка всегда на сервере.
   Каждая функция возвращает строку-ошибку или '' (пустую строку), если всё ок.
   🔧 Если в задании ДРУГИЕ правила (например телефон без +7) — правишь regex тут
      и в assets/app.js (там тот же шаблон для подсказок пользователю).
   ========================================================================== */

// Экранирование для безопасного вывода в HTML (защита от XSS). Используй ВЕЗДЕ,
// где выводишь данные из БД или из формы, например внутри тегов вывода: e($row['fio']).
function e($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// --- Правила валидации (форматы задания-2026) -------------------------------
// 🔧 Если в задании другие правила — правишь regex здесь И в assets/app.js.

// Логин: латиница и цифры, не менее 6 символов. Уникальность — в register.php.
function err_login(string $v): string {
    if ($v === '') return 'Введите логин';
    if (!preg_match('/^[A-Za-z0-9]{6,}$/', $v)) return 'Логин: латиница и цифры, не менее 6 символов';
    return '';
}

// Пароль: минимум 8 символов (задание-2026).
function err_password(string $v): string {
    if ($v === '') return 'Введите пароль';
    if (mb_strlen($v) < 8) return 'Пароль должен быть не короче 8 символов';
    return '';
}

// ФИО: только кириллица и пробелы.
function err_fio(string $v): string {
    if ($v === '') return 'Введите ФИО';
    if (!preg_match('/^[А-Яа-яЁё\s]+$/u', $v)) return 'ФИО: только русские буквы и пробелы';
    return '';
}

// Телефон в формате 8(XXX)XXX-XX-XX (задание-2026).
function err_phone(string $v): string {
    if ($v === '') return 'Введите телефон';
    if (!preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $v)) return 'Формат: 8(XXX)XXX-XX-XX';
    return '';
}

// Email — стандартная проверка PHP.
function err_email(string $v): string {
    if ($v === '') return 'Введите email';
    if (!filter_var($v, FILTER_VALIDATE_EMAIL)) return 'Некорректный email';
    return '';
}

// Дата в формате ДД.ММ.ГГГГ (задание-2026: дата начала обучения).
function err_date(string $v): string {
    if ($v === '') return 'Введите дату';
    if (!preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $v, $m)) return 'Формат даты: ДД.ММ.ГГГГ';
    if (!checkdate((int)$m[2], (int)$m[1], (int)$m[3])) return 'Такой даты не существует';
    return '';
}

// Универсальная проверка «поле не пустое».
function err_required(string $v, string $label = 'Поле'): string {
    return trim($v) === '' ? "$label обязательно для заполнения" : '';
}

// Удобный сборщик: принимает массив [имя_поля => текст_ошибки], отбрасывает пустые.
function collect_errors(array $checks): array {
    return array_filter($checks, fn($msg) => $msg !== '');
}

/* ============================================================================
   ОПТИМИЗАЦИЯ ИЗОБРАЖЕНИЙ (даёт баллы в Модуле 2/3).
   media_picture() выводит картинку с:
     • ленивой загрузкой (loading="lazy") — грузится только при прокрутке;
     • WebP-версией через <picture>, если рядом есть файл .webp (меньше вес),
       с автоматическим фолбэком на оригинал для старых браузеров;
     • decoding="async" и размерами (защита от «прыжков» вёрстки, CLS).
   WebP-файлы делает скрипт tools/convert-webp.php (см. docs/ОПТИМИЗАЦИЯ.md).
   Параметры $o: lazy(bool), class, w, h, priority(bool — для hero/LCP, грузить сразу).
   ============================================================================ */
function media_picture(string $src, string $alt, array $o = []): string {
    $lazy     = $o['lazy'] ?? true;
    $loading  = $lazy ? 'lazy' : 'eager';
    $priority = !empty($o['priority']) ? ' fetchpriority="high"' : '';
    $cls = isset($o['class']) ? ' class="' . e($o['class']) . '"' : '';
    $w   = isset($o['w']) ? ' width="' . (int)$o['w'] . '"' : '';
    $h   = isset($o['h']) ? ' height="' . (int)$o['h'] . '"' : '';

    $img = '<img src="' . e($src) . '" alt="' . e($alt) . '" loading="' . $loading
         . '" decoding="async"' . $cls . $w . $h . $priority . '>';

    // Есть ли рядом WebP-версия (для .jpg/.jpeg/.png)?
    $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $src);
    if ($webp !== $src && is_file(__DIR__ . '/' . ltrim($webp, '/'))) {
        return '<picture><source srcset="' . e($webp) . '" type="image/webp">' . $img . '</picture>';
    }
    return $img;   // для .svg и когда webp ещё не сгенерирован
}
