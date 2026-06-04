-- ============================================================================
-- 04_report_queries.sql — готовые SELECT-запросы для отчёта.
-- Вставляй ПО ОДНОМУ в phpMyAdmin (вкладка SQL) и делай скриншот результата.
-- ============================================================================
USE exam_db;

-- 1) Все заявки с ФИО заявителя (главная витрина данных)
SELECT o.id,
       u.fio          AS заявитель,
       u.phone        AS телефон,
       o.course_name  AS курс,
       o.desired_date AS дата_начала,
       o.payment_type AS оплата,
       o.status       AS статус
FROM orders o
JOIN users u ON u.id = o.user_id
ORDER BY o.id;

-- 2) Количество заявок в каждом статусе
SELECT status AS статус, COUNT(*) AS количество
FROM orders GROUP BY status;

-- 3) Самые популярные курсы (по числу заявок)
SELECT course_name AS курс, COUNT(*) AS число_заявок
FROM orders GROUP BY course_name ORDER BY число_заявок DESC;

-- 4) Заявки конкретного пользователя (пример: ivanov1)
SELECT o.course_name AS курс, o.desired_date AS дата, o.status AS статус
FROM orders o JOIN users u ON u.id = o.user_id
WHERE u.login = 'ivanov1';

-- 5) Все отзывы с автором и оценкой
SELECT u.fio AS автор, r.course_name AS курс, r.rating AS оценка, r.text AS отзыв
FROM reviews r JOIN users u ON u.id = r.user_id
ORDER BY r.created_at DESC;

-- 6) Средняя оценка по каждому курсу (где есть отзывы)
SELECT course_name AS курс, ROUND(AVG(rating),2) AS средняя_оценка, COUNT(*) AS отзывов
FROM reviews GROUP BY course_name;

-- 7) Сообщения из формы обратной связи
SELECT name AS имя, email, message AS сообщение, created_at AS когда
FROM feedback ORDER BY created_at DESC;

-- 8) Способы оплаты: сколько выбрали каждый
SELECT payment_type AS способ_оплаты, COUNT(*) AS количество
FROM orders GROUP BY payment_type;
