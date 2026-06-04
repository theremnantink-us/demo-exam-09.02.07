<?php
/* ============================================================================
   db.php — подключение к MySQL (PDO) + автосоздание базы/таблиц + сидинг.
   ----------------------------------------------------------------------------
   Таблицы: users, courses, orders (заявки), reviews (отзывы), feedback (обр.связь).
   Все запросы к БД делаются ТОЛЬКО через подготовленные выражения (q/one/run) —
   это защита от SQL-инъекций (см. security.php, docs/БЕЗОПАСНОСТЬ.md).
   ----------------------------------------------------------------------------
   ХЕЛПЕРЫ:
     db()                  -> PDO
     q($sql,$p=[])         -> все строки
     one($sql,$p=[])       -> первая строка или null
     run($sql,$p=[])       -> выполнить INSERT/UPDATE/DELETE
     last_id()             -> id последней вставки
   ========================================================================== */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';   // нужна hash_password() для сидинга

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
    $pass = getenv('DB_PASS_OVERRIDE') !== false ? getenv('DB_PASS_OVERRIDE') : DB_PASS;
    try {
        $pdo = new PDO($dsn, DB_USER, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // настоящие подготовленные выражения
        ]);
    } catch (PDOException $e) {
        die('Не удалось подключиться к MySQL: ' . $e->getMessage()
            . ' — проверь, что в XAMPP запущен MySQL и пароль в config.php верный.');
    }

    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . DB_NAME . '`');
    init_schema($pdo);
    seed_if_empty($pdo);
    return $pdo;
}

function init_schema(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        login      VARCHAR(50)  NOT NULL UNIQUE,
        password   VARCHAR(255) NOT NULL,           -- ХЕШ пароля (password_hash)
        fio        VARCHAR(150) NOT NULL,
        phone      VARCHAR(30)  NOT NULL,
        email      VARCHAR(150) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
        id        INT AUTO_INCREMENT PRIMARY KEY,
        name      VARCHAR(150) NOT NULL,
        is_active TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        user_id      INT NOT NULL,
        course_name  VARCHAR(150) NOT NULL,          -- название курса из заявки
        desired_date VARCHAR(10)  NOT NULL,          -- дата начала ДД.ММ.ГГГГ
        payment_type VARCHAR(40)  NOT NULL,
        status       VARCHAR(20)  NOT NULL DEFAULT 'new',
        created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        user_id     INT NOT NULL,
        order_id    INT NULL,                        -- по какой заявке отзыв
        course_name VARCHAR(150) NOT NULL,
        rating      TINYINT NOT NULL DEFAULT 5,      -- оценка 1..5
        text        VARCHAR(1000) NOT NULL,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id)  REFERENCES users(id),
        FOREIGN KEY (order_id) REFERENCES orders(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS feedback (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        name       VARCHAR(150) NOT NULL,
        email      VARCHAR(150) NOT NULL,
        message    VARCHAR(1000) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function seed_if_empty(PDO $pdo): void {
    // Курсы — из config (≥10).
    if ((int)$pdo->query('SELECT COUNT(*) c FROM courses')->fetch()['c'] === 0) {
        $ins = $pdo->prepare('INSERT INTO courses (name) VALUES (?)');
        foreach (app('courses') as $name) $ins->execute([$name]);
    }

    // Пользователи — 10 шт. Пароль у всех: Parol12345 (хранится ХЕШЕМ).
    if ((int)$pdo->query('SELECT COUNT(*) c FROM users')->fetch()['c'] === 0) {
        $hash = hash_password('Parol12345');
        $users = [
            ['ivanov1',   'Иванов Иван Иванович',      '8(900)111-22-33', 'ivanov@mail.ru'],
            ['petrova1',  'Петрова Анна Сергеевна',    '8(900)111-22-34', 'petrova@mail.ru'],
            ['sidorov1',  'Сидоров Пётр Петрович',     '8(900)111-22-35', 'sidorov@mail.ru'],
            ['kozlov12',  'Козлов Дмитрий Андреевич',  '8(900)111-22-36', 'kozlov@mail.ru'],
            ['novikova1', 'Новикова Мария Олеговна',   '8(900)111-22-37', 'novikova@mail.ru'],
            ['morozov1',  'Морозов Сергей Николаевич', '8(900)111-22-38', 'morozov@mail.ru'],
            ['volkova12', 'Волкова Елена Викторовна',  '8(900)111-22-39', 'volkova@mail.ru'],
            ['lebedev12', 'Лебедев Антон Павлович',    '8(900)111-22-40', 'lebedev@mail.ru'],
            ['sokolova1', 'Соколова Ольга Дмитриевна', '8(900)111-22-41', 'sokolova@mail.ru'],
            ['popov1234', 'Попов Алексей Михайлович',  '8(900)111-22-42', 'popov@mail.ru'],
        ];
        $ins = $pdo->prepare('INSERT INTO users (login,password,fio,phone,email) VALUES (?,?,?,?,?)');
        foreach ($users as $u) $ins->execute([$u[0], $hash, $u[1], $u[2], $u[3]]);
    }

    // Заявки — 10 шт. с разными статусами (часть «Обучение завершено» для отзывов).
    if ((int)$pdo->query('SELECT COUNT(*) c FROM orders')->fetch()['c'] === 0) {
        $statuses = array_keys(app('statuses'));   // new, studying, done
        $payments = app('payment_types');
        $courses  = app('courses');
        $ins = $pdo->prepare('INSERT INTO orders (user_id,course_name,desired_date,payment_type,status) VALUES (?,?,?,?,?)');
        for ($i = 1; $i <= 10; $i++) {
            $ins->execute([
                $i,
                $courses[($i - 1) % count($courses)],
                str_pad((string)(($i % 28) + 1), 2, '0', STR_PAD_LEFT) . '.09.2025',
                $payments[$i % count($payments)],
                $statuses[$i % count($statuses)],
            ]);
        }
    }

    // Отзывы — несколько (по завершённым заявкам), показываются на главной.
    if ((int)$pdo->query('SELECT COUNT(*) c FROM reviews')->fetch()['c'] === 0) {
        $reviews = [
            [2, 'Основы веб-дизайна',                    5, 'Отличный курс, всё понятно и по делу!'],
            [5, 'Основы алгоритмизации и программирования', 5, 'Преподаватель объясняет супер, рекомендую.'],
            [8, 'Веб-разработка на PHP',                  4, 'Полезно, много практики. Спасибо!'],
        ];
        $ins = $pdo->prepare('INSERT INTO reviews (user_id,course_name,rating,text) VALUES (?,?,?,?)');
        foreach ($reviews as $r) $ins->execute($r);
    }

    // Обратная связь — пара примеров.
    if ((int)$pdo->query('SELECT COUNT(*) c FROM feedback')->fetch()['c'] === 0) {
        $ins = $pdo->prepare('INSERT INTO feedback (name,email,message) VALUES (?,?,?)');
        $ins->execute(['Гость', 'guest@mail.ru', 'Подскажите, есть ли рассрочка на обучение?']);
        $ins->execute(['Мария', 'maria@mail.ru', 'Хочу записаться на курс по дизайну, как начать?']);
    }
}

function q(string $sql, array $params = []): array {
    $st = db()->prepare($sql); $st->execute($params); return $st->fetchAll();
}
function one(string $sql, array $params = []): ?array {
    $st = db()->prepare($sql); $st->execute($params); $r = $st->fetch(); return $r === false ? null : $r;
}
function run(string $sql, array $params = []): PDOStatement {
    $st = db()->prepare($sql); $st->execute($params); return $st;
}
function last_id(): string { return db()->lastInsertId(); }
