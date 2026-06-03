<?php
/* ============================================================================
   db.php — подключение к MySQL через PDO + автосоздание базы и таблиц + сидинг.
   ----------------------------------------------------------------------------
   Что делает при первом обращении:
     1. Подключается к серверу MySQL.
     2. Создаёт базу (DB_NAME), если её нет.
     3. Создаёт таблицы users / services / orders, если их нет.
     4. Заполняет их тестовыми данными (10+ строк), если они пустые.
   То есть систему можно запустить «с нуля» — БД соберётся сама.
   Если на экзамене дают ГОТОВЫЙ файл БД (Модуль 1 и 2) — импортируй database.sql
   через phpMyAdmin, и этот автосидинг просто ничего не будет пересоздавать.
   ----------------------------------------------------------------------------
   ХЕЛПЕРЫ (используй их везде, НЕ пиши mysqli вручную):
     db()                       -> объект PDO
     q($sql, $params=[])        -> вернуть ВСЕ строки (массив)
     one($sql, $params=[])      -> вернуть ПЕРВУЮ строку или null
     run($sql, $params=[])      -> выполнить INSERT/UPDATE/DELETE, вернуть PDOStatement
     last_id()                  -> id последней вставленной строки
   ВСЕГДА передавай данные через параметры (?), а не склейкой строк — это защита
   от SQL-инъекций и обязательное требование на экзамене.
   ========================================================================== */

require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
    // Пароль: обычно из config (XAMPP — пусто). Можно временно переопределить
    // переменной окружения DB_PASS_OVERRIDE, не трогая config (удобно на чужой
    // машине, где у MySQL есть пароль). На экзамене это просто не используется.
    $pass = getenv('DB_PASS_OVERRIDE') !== false ? getenv('DB_PASS_OVERRIDE') : DB_PASS;
    try {
        $pdo = new PDO($dsn, DB_USER, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        // 🔧 Если видишь эту ошибку — проверь, что в XAMPP запущен MySQL,
        //    и что логин/пароль в config.php совпадают (XAMPP: root / пусто).
        die('Не удалось подключиться к MySQL: ' . $e->getMessage());
    }

    // Создаём БД и выбираем её.
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . DB_NAME . '`');

    init_schema($pdo);
    seed_if_empty($pdo);
    return $pdo;
}

// --- Создание таблиц --------------------------------------------------------
function init_schema(PDO $pdo): void {
    // Пользователи (заявители). 🔧 Поля под почти любую тему уже есть.
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        login      VARCHAR(50)  NOT NULL UNIQUE,   -- уникальный логин (требование)
        password   VARCHAR(255) NOT NULL,          -- пароль (открытым текстом, как решили)
        fio        VARCHAR(150) NOT NULL,
        phone      VARCHAR(30)  NOT NULL,
        email      VARCHAR(150) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Справочник услуг/категорий. 🔧 Список реально берётся из config['services'].
    $pdo->exec("CREATE TABLE IF NOT EXISTS services (
        id        INT AUTO_INCREMENT PRIMARY KEY,
        name      VARCHAR(150) NOT NULL,
        is_active TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Заявки (ядро системы).
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        user_id       INT NOT NULL,
        address       VARCHAR(255) NOT NULL,
        phone         VARCHAR(30)  NOT NULL,
        service_id    INT NULL,                    -- ссылка на справочник
        other_service VARCHAR(255) NULL,           -- текст для чекбокса «Иная услуга»
        desired_date  DATETIME     NOT NULL,       -- желаемые дата и время
        payment_type  VARCHAR(30)  NOT NULL,       -- «Наличные» / «Банковская карта»
        status        VARCHAR(20)  NOT NULL DEFAULT 'new',
        cancel_reason VARCHAR(255) NULL,           -- причина отмены (обязательна при отмене)
        created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id)    REFERENCES users(id),
        FOREIGN KEY (service_id) REFERENCES services(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// --- Заполнение тестовыми данными (только если таблицы пустые) --------------
function seed_if_empty(PDO $pdo): void {
    // Услуги — из config (минимум 10 наименований).
    if ((int)$pdo->query('SELECT COUNT(*) c FROM services')->fetch()['c'] === 0) {
        $ins = $pdo->prepare('INSERT INTO services (name) VALUES (?)');
        foreach (app('services') as $name) $ins->execute([$name]);
    }

    // Пользователи — 10 тестовых заявителей.
    if ((int)$pdo->query('SELECT COUNT(*) c FROM users')->fetch()['c'] === 0) {
        $users = [
            ['ivanov',   '123456', 'Иванов Иван Иванович',        '+7(900)-111-22-33', 'ivanov@mail.ru'],
            ['petrova',  '123456', 'Петрова Анна Сергеевна',      '+7(900)-111-22-34', 'petrova@mail.ru'],
            ['sidorov',  '123456', 'Сидоров Пётр Петрович',       '+7(900)-111-22-35', 'sidorov@mail.ru'],
            ['kozlov',   '123456', 'Козлов Дмитрий Андреевич',    '+7(900)-111-22-36', 'kozlov@mail.ru'],
            ['novikova', '123456', 'Новикова Мария Олеговна',     '+7(900)-111-22-37', 'novikova@mail.ru'],
            ['morozov',  '123456', 'Морозов Сергей Николаевич',   '+7(900)-111-22-38', 'morozov@mail.ru'],
            ['volkova',  '123456', 'Волкова Елена Викторовна',    '+7(900)-111-22-39', 'volkova@mail.ru'],
            ['lebedev',  '123456', 'Лебедев Антон Павлович',      '+7(900)-111-22-40', 'lebedev@mail.ru'],
            ['sokolova', '123456', 'Соколова Ольга Дмитриевна',   '+7(900)-111-22-41', 'sokolova@mail.ru'],
            ['popov',    '123456', 'Попов Алексей Михайлович',    '+7(900)-111-22-42', 'popov@mail.ru'],
        ];
        $ins = $pdo->prepare('INSERT INTO users (login,password,fio,phone,email) VALUES (?,?,?,?,?)');
        foreach ($users as $u) $ins->execute($u);
    }

    // Заявки — 10 тестовых записей с разными статусами.
    if ((int)$pdo->query('SELECT COUNT(*) c FROM orders')->fetch()['c'] === 0) {
        $statuses = array_keys(app('statuses'));            // new,in_work,done,canceled
        $payments = app('payment_types');
        $ins = $pdo->prepare(
            'INSERT INTO orders (user_id,address,phone,service_id,desired_date,payment_type,status,cancel_reason)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        for ($i = 1; $i <= 10; $i++) {
            $status = $statuses[$i % count($statuses)];
            $reason = $status === 'canceled' ? 'Клиент перенёс дату' : null;
            $ins->execute([
                $i,                                            // user_id 1..10
                'г. Москва, ул. Примерная, д. ' . $i,
                '+7(900)-111-' . str_pad((string)(20 + $i), 2, '0', STR_PAD_LEFT) . '-00',
                ($i % 10) + 1,                                 // service_id 1..10
                date('Y-m-d H:i:s', strtotime("+$i day 12:00")),
                $payments[$i % count($payments)],
                $status,
                $reason,
            ]);
        }
    }
}

// --- Короткие хелперы для запросов ------------------------------------------
function q(string $sql, array $params = []): array {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}
function one(string $sql, array $params = []): ?array {
    $st = db()->prepare($sql);
    $st->execute($params);
    $row = $st->fetch();
    return $row === false ? null : $row;
}
function run(string $sql, array $params = []): PDOStatement {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}
function last_id(): string {
    return db()->lastInsertId();
}
