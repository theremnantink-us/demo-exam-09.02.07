-- ============================================================================
-- 04_report_queries.sql — готовые SELECT-запросы для отчёта.
-- Вставляй ПО ОДНОМУ в phpMyAdmin (вкладка SQL) и делай скриншот результата.
-- Это типовые запросы, которые красиво смотрятся в пояснительной записке.
-- ============================================================================
USE exam_db;

-- 1) Все заявки с ФИО заявителя и названием услуги (главная витрина данных)
SELECT o.id,
       u.fio          AS заявитель,
       u.phone        AS телефон,
       COALESCE(s.name, o.other_service) AS услуга,
       o.address      AS адрес,
       o.desired_date AS «дата_и_время»,
       o.payment_type AS оплата,
       o.status       AS статус
FROM orders o
JOIN users u    ON u.id = o.user_id
LEFT JOIN services s ON s.id = o.service_id
ORDER BY o.id;

-- 2) Сколько заявок в каждом статусе
SELECT status AS статус, COUNT(*) AS количество
FROM orders
GROUP BY status;

-- 3) Самые востребованные услуги (ТОП по числу заявок)
SELECT s.name AS услуга, COUNT(o.id) AS число_заявок
FROM services s
LEFT JOIN orders o ON o.service_id = s.id
GROUP BY s.id, s.name
ORDER BY число_заявок DESC;

-- 4) Заявки конкретного пользователя (пример: ivanov)
SELECT o.id, COALESCE(s.name, o.other_service) AS услуга, o.status
FROM orders o
JOIN users u ON u.id = o.user_id
LEFT JOIN services s ON s.id = o.service_id
WHERE u.login = 'ivanov';

-- 5) Все отменённые заявки с причиной отмены
SELECT o.id, u.fio AS заявитель, o.cancel_reason AS причина_отмены
FROM orders o
JOIN users u ON u.id = o.user_id
WHERE o.status = 'canceled';

-- 6) Сколько заявок выбрали наличные, а сколько карту
SELECT payment_type AS тип_оплаты, COUNT(*) AS количество
FROM orders
GROUP BY payment_type;
