-- ============================================================================
-- database.sql — готовый дамп базы данных для импорта через phpMyAdmin.
-- ----------------------------------------------------------------------------
-- КОГДА НУЖЕН: Модуль 1 и 2 говорят «используйте предоставленный файл с БД».
-- Этот файл — и есть такой файл. Импорт: phpMyAdmin -> Импорт -> выбрать database.sql.
-- (Если запускаешь систему «с нуля» — db.php создаст всё сам, импорт не обязателен.)
--
-- Содержит 3 таблицы и 10+ строк в каждой (требование: минимум 10 вводных данных).
-- 🔧 Под свою тему: переименуй услуги в services и при желании данные в orders.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS exam_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE exam_db;

DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS users;

-- --- Пользователи (заявители) ----------------------------------------------
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    login      VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    fio        VARCHAR(150) NOT NULL,
    phone      VARCHAR(30)  NOT NULL,
    email      VARCHAR(150) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (login, password, fio, phone, email) VALUES
('ivanov',   '123456', 'Иванов Иван Иванович',      '+7(900)-111-22-33', 'ivanov@mail.ru'),
('petrova',  '123456', 'Петрова Анна Сергеевна',    '+7(900)-111-22-34', 'petrova@mail.ru'),
('sidorov',  '123456', 'Сидоров Пётр Петрович',     '+7(900)-111-22-35', 'sidorov@mail.ru'),
('kozlov',   '123456', 'Козлов Дмитрий Андреевич',  '+7(900)-111-22-36', 'kozlov@mail.ru'),
('novikova', '123456', 'Новикова Мария Олеговна',   '+7(900)-111-22-37', 'novikova@mail.ru'),
('morozov',  '123456', 'Морозов Сергей Николаевич', '+7(900)-111-22-38', 'morozov@mail.ru'),
('volkova',  '123456', 'Волкова Елена Викторовна',  '+7(900)-111-22-39', 'volkova@mail.ru'),
('lebedev',  '123456', 'Лебедев Антон Павлович',    '+7(900)-111-22-40', 'lebedev@mail.ru'),
('sokolova', '123456', 'Соколова Ольга Дмитриевна', '+7(900)-111-22-41', 'sokolova@mail.ru'),
('popov',    '123456', 'Попов Алексей Михайлович',  '+7(900)-111-22-42', 'popov@mail.ru');

-- --- Справочник услуг -------------------------------------------------------
CREATE TABLE services (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(150) NOT NULL,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO services (name) VALUES
('Общий клининг'),
('Генеральная уборка'),
('Послестроительная уборка'),
('Химчистка ковров и мебели'),
('Мытьё окон'),
('Уборка после ремонта'),
('Дезинфекция помещений'),
('Уборка офисов'),
('Мойка фасадов'),
('Уход за газоном');

-- --- Заявки -----------------------------------------------------------------
CREATE TABLE orders (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    address       VARCHAR(255) NOT NULL,
    phone         VARCHAR(30)  NOT NULL,
    service_id    INT NULL,
    other_service VARCHAR(255) NULL,
    desired_date  DATETIME     NOT NULL,
    payment_type  VARCHAR(30)  NOT NULL,
    status        VARCHAR(20)  NOT NULL DEFAULT 'new',
    cancel_reason VARCHAR(255) NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO orders (user_id, address, phone, service_id, other_service, desired_date, payment_type, status, cancel_reason) VALUES
(1,  'г. Москва, ул. Примерная, д. 1',  '+7(900)-111-21-00', 2,  NULL, '2025-06-10 12:00:00', 'Наличные',         'in_work',  NULL),
(2,  'г. Москва, ул. Примерная, д. 2',  '+7(900)-111-22-00', 3,  NULL, '2025-06-11 12:00:00', 'Банковская карта', 'done',     NULL),
(3,  'г. Москва, ул. Примерная, д. 3',  '+7(900)-111-23-00', 4,  NULL, '2025-06-12 12:00:00', 'Наличные',         'canceled', 'Клиент перенёс дату'),
(4,  'г. Москва, ул. Примерная, д. 4',  '+7(900)-111-24-00', 5,  NULL, '2025-06-13 12:00:00', 'Банковская карта', 'new',      NULL),
(5,  'г. Москва, ул. Примерная, д. 5',  '+7(900)-111-25-00', 6,  NULL, '2025-06-14 12:00:00', 'Наличные',         'in_work',  NULL),
(6,  'г. Москва, ул. Примерная, д. 6',  '+7(900)-111-26-00', 7,  NULL, '2025-06-15 12:00:00', 'Банковская карта', 'done',     NULL),
(7,  'г. Москва, ул. Примерная, д. 7',  '+7(900)-111-27-00', NULL, 'Уборка снега во дворе', '2025-06-16 12:00:00', 'Наличные', 'new', NULL),
(8,  'г. Москва, ул. Примерная, д. 8',  '+7(900)-111-28-00', 9,  NULL, '2025-06-17 12:00:00', 'Банковская карта', 'canceled', 'Нет свободных мастеров'),
(9,  'г. Москва, ул. Примерная, д. 9',  '+7(900)-111-29-00', 10, NULL, '2025-06-18 12:00:00', 'Наличные',         'new',      NULL),
(10, 'г. Москва, ул. Примерная, д. 10', '+7(900)-111-30-00', 1,  NULL, '2025-06-19 12:00:00', 'Банковская карта', 'in_work',  NULL);
