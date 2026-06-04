<?php
/* ----------------------------------------------------------------------------
   index.php — главная страница.
   ИНСТРУКЦИЯ (удали после настройки): тексты, картинки и цифры ниже меняешь
   прямо здесь под свою тему. Картинки слайдера — 4 фото одинакового размера
   в папке assets/img/ (замени slide1..4 на фото из задания).
---------------------------------------------------------------------------- */
require_once __DIR__ . '/layout.php';

$flash = '';
// Обработка формы обратной связи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'feedback') {
    csrf_require();
    $name    = sanitize($_POST['name'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $err = collect_errors([
        'name'    => err_required($name, 'Имя'),
        'email'   => err_email($email),
        'message' => err_required($message, 'Сообщение'),
    ]);
    if (!$err) {
        run('INSERT INTO feedback (name,email,message) VALUES (?,?,?)', [$name, $email, $message]);
        header('Location: index.php?fb=1#contacts');
        exit;
    }
    $flash = 'Проверьте поля формы: ' . implode(', ', $err);
}

// Данные для главной из базы
$courses = q('SELECT name FROM courses WHERE is_active = 1 ORDER BY id LIMIT 6');
$reviews = q('SELECT r.*, u.fio FROM reviews r JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC LIMIT 6');

// 4 слайда для галереи (поменяй пути и подписи под свою тему)
$slides = [
    ['img' => 'assets/img/slide1.svg', 'caption' => 'Практика на реальных задачах'],
    ['img' => 'assets/img/slide2.svg', 'caption' => 'Преподаватели-эксперты'],
    ['img' => 'assets/img/slide3.svg', 'caption' => 'Гибкий график обучения'],
    ['img' => 'assets/img/slide4.svg', 'caption' => 'Документ об окончании'],
];

layout_header('');   // на главной свой заголовок внутри hero
?>

<!-- ========================= HERO ========================= -->
<section class="hero">
    <div class="hero-text">
        <span class="eyebrow">Онлайн-образование</span>
        <h1 class="hero-title">Получи новую профессию онлайн</h1>
        <p class="hero-sub">Курсы дополнительного образования с документом по окончании. Учись в удобное время.</p>
        <div class="hero-actions">
            <a class="btn btn-lg" href="<?= current_user() ? 'order.php' : 'register.php' ?>">Выбрать курс</a>
            <a class="btn-ghost btn-lg" href="#contacts">Связаться</a>
        </div>
        <div class="hero-stats">
            <div class="stat"><b>1200+</b><span>учеников</span></div>
            <div class="stat"><b>10</b><span>курсов</span></div>
            <div class="stat"><b>4.9</b><span>рейтинг</span></div>
        </div>
    </div>
    <div class="hero-media">
        <!-- hero — главное фото, грузим сразу (не lazy) -->
        <?= media_picture('assets/img/hero.svg', 'Корочки.есть', ['lazy' => false, 'priority' => true, 'w' => 640, 'h' => 440]) ?>
    </div>
</section>

<!-- ===================== СЛАЙДЕР (4 фото, авто 3 сек) ===================== -->
<section class="slider" data-slider data-interval="3000" aria-label="Галерея">
    <div class="slides">
        <?php foreach ($slides as $i => $s): ?>
            <figure class="slide <?= $i === 0 ? 'active' : '' ?>">
                <?= media_picture($s['img'], $s['caption'], ['lazy' => $i !== 0, 'w' => 900, 'h' => 380]) ?>
                <figcaption><?= e($s['caption']) ?></figcaption>
            </figure>
        <?php endforeach; ?>
    </div>
    <button class="slider-btn prev" data-prev aria-label="Назад">‹</button>
    <button class="slider-btn next" data-next aria-label="Вперёд">›</button>
    <div class="dots">
        <?php foreach ($slides as $i => $s): ?>
            <span class="dot <?= $i === 0 ? 'active' : '' ?>" data-dot="<?= $i ?>"></span>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===================== ПРЕИМУЩЕСТВА ===================== -->
<section class="features">
    <div class="feature card-soft">
        <div class="feature-icon">🎓</div>
        <h3>Документ об окончании</h3>
        <p class="muted">Официальное удостоверение о повышении квалификации.</p>
    </div>
    <div class="feature card-soft">
        <div class="feature-icon">💻</div>
        <h3>Полностью онлайн</h3>
        <p class="muted">Учитесь из любой точки в удобное время.</p>
    </div>
    <div class="feature card-soft">
        <div class="feature-icon">⭐</div>
        <h3>Практика</h3>
        <p class="muted">Реальные проекты и обратная связь от наставника.</p>
    </div>
</section>

<!-- ===================== КУРСЫ ===================== -->
<section class="block">
    <div class="block-head"><span class="eyebrow">Каталог</span><h2>Популярные курсы</h2></div>
    <div class="courses-grid">
        <?php foreach ($courses as $cr): ?>
            <div class="course-card card-soft">
                <div class="course-badge">Курс</div>
                <h3><?= e($cr['name']) ?></h3>
                <a class="btn-mini" href="<?= current_user() ? 'order.php' : 'login.php' ?>">Записаться</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===================== ОТЗЫВЫ ===================== -->
<section class="block" id="reviews">
    <div class="block-head"><span class="eyebrow">Нам доверяют</span><h2>Отзывы учеников</h2></div>
    <?php if ($reviews): ?>
    <div class="reviews-grid">
        <?php foreach ($reviews as $rv): ?>
            <?php
                // инициалы для аватара (первые буквы фамилии и имени)
                $parts = preg_split('/\s+/', trim($rv['fio']));
                $initials = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
            ?>
            <blockquote class="review card-soft">
                <div class="stars"><?= str_repeat('★', (int)$rv['rating']) . str_repeat('☆', 5 - (int)$rv['rating']) ?></div>
                <p>«<?= e($rv['text']) ?>»</p>
                <footer class="review-author">
                    <span class="avatar"><?= e($initials) ?></span>
                    <span><?= e($rv['fio']) ?><br><span class="muted small"><?= e($rv['course_name']) ?></span></span>
                </footer>
            </blockquote>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="muted center">Пока нет отзывов — станьте первым после завершения курса.</p>
    <?php endif; ?>

    <!-- Кнопка оставить отзыв (по заданию — доступно после завершения обучения) -->
    <div class="reviews-cta">
        <a class="btn btn-lg" href="<?= current_user() ? 'cabinet.php#review' : 'login.php' ?>">Оставить отзыв</a>
        <p class="muted small">Отзыв можно оставить в личном кабинете после завершения курса.</p>
    </div>
</section>

<!-- ===================== ОБРАТНАЯ СВЯЗЬ ===================== -->
<section class="block" id="contacts">
    <div class="block-head"><span class="eyebrow">Контакты</span><h2>Обратная связь</h2></div>
    <?php if (isset($_GET['fb'])): ?><div class="toast" data-toast>Спасибо! Сообщение отправлено.</div><?php endif; ?>
    <?php if ($flash): ?><div class="alert"><?= e($flash) ?></div><?php endif; ?>
    <form method="post" class="card feedback-card">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="feedback">
        <label>Ваше имя <input name="name" required></label>
        <label>Email <input type="email" name="email" required></label>
        <label>Сообщение <textarea name="message" rows="3" required></textarea></label>
        <button type="submit">Отправить сообщение</button>
    </form>
</section>

<?php
layout_footer();
