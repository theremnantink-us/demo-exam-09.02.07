-- ============================================================================
-- database.sql — полный дамп БД (структура + данные) для импорта целиком.
-- phpMyAdmin → Импорт → выбрать этот файл. Пароль всех юзеров: Parol12345.
-- Если запускаешь систему «с нуля» — db.php создаст всё сам, импорт не обязателен.
-- ============================================================================
CREATE DATABASE IF NOT EXISTS exam_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE exam_db;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fio VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(150) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_name VARCHAR(150) NOT NULL,
    desired_date VARCHAR(10) NOT NULL,
    payment_type VARCHAR(40) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NULL,
    course_name VARCHAR(150) NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    text VARCHAR(1000) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message VARCHAR(1000) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (login, password, fio, phone, email) VALUES
('ivanov1',   '$2y$12$aZ34HBbb2932.IpO3L3R2.LpTQ53LbazJITYwa7kkt9bzOXHWhN4W', 'Иванов Иван Иванович',      '8(900)111-22-33', 'ivanov@mail.ru'),
('petrova1',  '$2y$12$aZ34HBbb2932.IpO3L3R2.LpTQ53LbazJITYwa7kkt9bzOXHWhN4W', 'Петрова Анна Сергеевна',    '8(900)111-22-34', 'petrova@mail.ru'),
('sidorov1',  '$2y$12$aZ34HBbb2932.IpO3L3R2.LpTQ53LbazJITYwa7kkt9bzOXHWhN4W', 'Сидоров Пётр Петрович',     '8(900)111-22-35', 'sidorov@mail.ru'),
('kozlov12',  '$2y$12$aZ34HBbb2932.IpO3L3R2.LpTQ53LbazJITYwa7kkt9bzOXHWhN4W', 'Козлов Дмитрий Андреевич',  '8(900)111-22-36', 'kozlov@mail.ru'),
('novikova1', '$2y$12$aZ34HBbb2932.IpO3L3R2.LpTQ53LbazJITYwa7kkt9bzOXHWhN4W', 'Новикова Мария Олеговна',   '8(900)111-22-37', 'novikova@mail.ru'),
('morozov1',  '$2y$12$aZ34HBbb2932.IpO3L3R2.LpTQ53LbazJITYwa7kkt9bzOXHWhN4W', 'Морозов Сергей Николаевич', '8(900)111-22-38', 'morozov@mail.ru'),
('volkova12', '$2y$12$aZ34HBbb2932.IpO3L3R2.LpTQ53LbazJITYwa7kkt9bzOXHWhN4W', 'Волкова Елена Викторовна',  '8(900)111-22-39', 'volkova@mail.ru'),
('lebedev12', '$2y$12$aZ34HBbb2932.IpO3L3R2.LpTQ53LbazJITYwa7kkt9bzOXHWhN4W', 'Лебедев Антон Павлович',    '8(900)111-22-40', 'lebedev@mail.ru'),
('sokolova1', '$2y$12$aZ34HBbb2932.IpO3L3R2.LpTQ53LbazJITYwa7kkt9bzOXHWhN4W', 'Соколова Ольга Дмитриевна', '8(900)111-22-41', 'sokolova@mail.ru'),
('popov1234', '$2y$12$aZ34HBbb2932.IpO3L3R2.LpTQ53LbazJITYwa7kkt9bzOXHWhN4W', 'Попов Алексей Михайлович',  '8(900)111-22-42', 'popov@mail.ru');

INSERT INTO courses (name) VALUES
('Основы алгоритмизации и программирования'),
('Основы веб-дизайна'),
('Основы проектирования баз данных'),
('Веб-разработка на PHP'),
('Основы Python'),
('Компьютерные сети'),
('Информационная безопасность'),
('Графический дизайн'),
('Системное администрирование'),
('Управление проектами');

INSERT INTO orders (user_id, course_name, desired_date, payment_type, status) VALUES
(1,  'Основы алгоритмизации и программирования', '01.09.2025', 'Перевод по номеру телефона', 'done'),
(2,  'Основы веб-дизайна',                       '03.09.2025', 'Наличными',                 'done'),
(3,  'Основы проектирования баз данных',         '05.09.2025', 'Перевод по номеру телефона', 'new'),
(4,  'Веб-разработка на PHP',                    '07.09.2025', 'Наличными',                 'studying'),
(5,  'Основы Python',                            '09.09.2025', 'Перевод по номеру телефона', 'done'),
(6,  'Компьютерные сети',                        '11.09.2025', 'Наличными',                 'new'),
(7,  'Информационная безопасность',              '13.09.2025', 'Перевод по номеру телефона', 'studying'),
(8,  'Графический дизайн',                       '15.09.2025', 'Наличными',                 'done'),
(9,  'Системное администрирование',              '17.09.2025', 'Перевод по номеру телефона', 'new'),
(10, 'Управление проектами',                     '19.09.2025', 'Наличными',                 'studying');

INSERT INTO reviews (user_id, order_id, course_name, rating, text) VALUES
(2, 2, 'Основы веб-дизайна', 5, 'Отличный курс, всё понятно и по делу!'),
(5, 5, 'Основы Python',      5, 'Преподаватель объясняет супер, рекомендую.'),
(8, 8, 'Графический дизайн', 4, 'Полезно, много практики. Спасибо!');

INSERT INTO feedback (name, email, message) VALUES
('Гость', 'guest@mail.ru', 'Подскажите, есть ли рассрочка на обучение?'),
('Мария', 'maria@mail.ru', 'Хочу записаться на курс по дизайну, как начать?');
