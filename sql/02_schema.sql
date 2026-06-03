-- ============================================================================
-- 02_schema.sql — структура таблиц (3 таблицы со связями).
-- Вставь вторым в phpMyAdmin после 01_create_database.sql.
-- ============================================================================
USE exam_db;

-- Пользователи (заявители)
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    login      VARCHAR(50)  NOT NULL UNIQUE,   -- уникальный логин
    password   VARCHAR(255) NOT NULL,
    fio        VARCHAR(150) NOT NULL,
    phone      VARCHAR(30)  NOT NULL,
    email      VARCHAR(150) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Справочник услуг
CREATE TABLE IF NOT EXISTS services (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(150) NOT NULL,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Заявки
CREATE TABLE IF NOT EXISTS orders (
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
