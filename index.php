<?php
/* ============================================================================
   index.php — ГЛАВНАЯ СТРАНИЦА с наполнением (за это дают баллы).
   Блоки: hero (лого+фото), слайдер (4 фото, автосмена 3 сек, вперёд/назад),
   преимущества, список курсов, отзывы (из БД), форма обратной связи.
   Всё наполнение и стиль берутся из theme.php — меняешь тему в одном месте.
   ========================================================================== */
require_once __DIR__ . '/layout.php';

$flash = '';
// --- Форма обратной связи (POST) -------------------------------------------
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

$courses = q('SELECT name FROM courses WHERE is_active = 1 ORDER BY id LIMIT 6');
$reviews = q('SELECT r.*, u.fio FROM reviews r JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC LIMIT 6');
$hero = theme('hero');

layout_header('');   // на главной свой заголовок внутри hero
?>

<!-- ========================= HERO ========================= -->
<section class="hero">
    <div class="hero-text">
        <h1 class="hero-title"><?= e($hero['title']) ?></h1>
        <p class="hero-sub"><?= e($hero['subtitle']) ?></p>
        <a class="btn btn-lg" href="<?= current_user() ? 'order.php' : 'register.php' ?>"><?= e($hero['cta']) ?></a>
    </div>
    <div class="hero-media">
        <?php /* hero — это LCP, грузим сразу (priority), не лениво */ ?>
        <?= media_picture($hero['image'], theme('site_name'), ['lazy' => false, 'priority' => true, 'w' => 640, 'h' => 440]) ?>
    </div>
</section>

<!-- ===================== СЛАЙДЕР (4 фото, авто 3 сек) ===================== -->
<section class="slider" data-slider data-interval="3000" aria-label="Галерея">
    <div class="slides">
        <?php foreach (theme('slides') as $i => $s): ?>
            <figure class="slide <?= $i === 0 ? 'active' : '' ?>">
                <?php /* первый слайд виден сразу — грузим без lazy, остальные лениво */ ?>
                <?= media_picture($s['img'], $s['caption'], ['lazy' => $i !== 0, 'w' => 900, 'h' => 380]) ?>
                <figcaption><?= e($s['caption']) ?></figcaption>
            </figure>
        <?php endforeach; ?>
    </div>
    <button class="slider-btn prev" data-prev aria-label="Назад">‹</button>
    <button class="slider-btn next" data-next aria-label="Вперёд">›</button>
    <div class="dots">
        <?php foreach (theme('slides') as $i => $s): ?>
            <span class="dot <?= $i === 0 ? 'active' : '' ?>" data-dot="<?= $i ?>"></span>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===================== ПРЕИМУЩЕСТВА ===================== -->
<section class="features">
    <?php foreach (theme('features') as $ft): ?>
        <div class="feature card-soft">
            <div class="feature-icon"><?= $ft['icon'] ?></div>
            <h3><?= e($ft['title']) ?></h3>
            <p class="muted"><?= e($ft['text']) ?></p>
        </div>
    <?php endforeach; ?>
</section>

<!-- ===================== КУРСЫ ===================== -->
<section class="block">
    <h2>Популярные курсы</h2>
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
<?php if ($reviews): ?>
<section class="block">
    <h2>Отзывы учеников</h2>
    <div class="reviews-grid">
        <?php foreach ($reviews as $rv): ?>
            <blockquote class="review card-soft">
                <div class="stars"><?= str_repeat('★', (int)$rv['rating']) . str_repeat('☆', 5 - (int)$rv['rating']) ?></div>
                <p>«<?= e($rv['text']) ?>»</p>
                <footer><?= e($rv['fio']) ?> · <span class="muted small"><?= e($rv['course_name']) ?></span></footer>
            </blockquote>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ===================== ОБРАТНАЯ СВЯЗЬ ===================== -->
<section class="block" id="contacts">
    <h2>Обратная связь</h2>
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
