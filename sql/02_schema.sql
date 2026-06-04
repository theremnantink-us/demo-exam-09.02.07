-- ============================================================================
-- 02_schema.sql — структура таблиц (5 таблиц со связями).
-- Вставь вторым в phpMyAdmin после 01_create_database.sql.
-- ============================================================================
USE exam_db;

-- Пользователи (пароль хранится ХЕШЕМ)
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    login      VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    fio        VARCHAR(150) NOT NULL,
    phone      VARCHAR(30)  NOT NULL,
    email      VARCHAR(150) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Справочник курсов
CREATE TABLE IF NOT EXISTS courses (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(150) NOT NULL,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Заявки на обучение
CREATE TABLE IF NOT EXISTS orders (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    course_name  VARCHAR(150) NOT NULL,
    desired_date VARCHAR(10)  NOT NULL,           -- ДД.ММ.ГГГГ
    payment_type VARCHAR(40)  NOT NULL,
    status       VARCHAR(20)  NOT NULL DEFAULT 'new',
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Отзывы (оставляются после завершения обучения)
CREATE TABLE IF NOT EXISTS reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    order_id    INT NULL,
    course_name VARCHAR(150) NOT NULL,
    rating      TINYINT NOT NULL DEFAULT 5,
    text        VARCHAR(1000) NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Обратная связь
CREATE TABLE IF NOT EXISTS feedback (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    message    VARCHAR(1000) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
