# ER-диаграмма базы данных (Модуль 3)

Модуль 3 требует «диаграмму базы данных с помощью средств графических редакторов».

**Готовые файлы диаграммы лежат рядом:**
- `ER.png` — растровое изображение (можно сразу сдавать/вставлять в отчёт).
- `ER.svg` — векторный вариант (открой в браузере или Inkscape, экспортируй в PNG любого размера).

Ниже — та же схема в формате **Mermaid** (для правки), текстовое описание связей и
заметка про ERwin.

```mermaid
erDiagram
    users ||--o{ orders : "оставляет"
    services ||--o{ orders : "указывается в"

    users {
        int id PK
        varchar login "UNIQUE"
        varchar password
        varchar fio
        varchar phone
        varchar email
        datetime created_at
    }
    services {
        int id PK
        varchar name
        tinyint is_active
    }
    orders {
        int id PK
        int user_id FK
        varchar address
        varchar phone
        int service_id FK
        varchar other_service
        datetime desired_date
        varchar payment_type
        varchar status
        varchar cancel_reason
        datetime created_at
    }
```

## Связи
- **users 1 — ∞ orders**: один пользователь оставляет много заявок (`orders.user_id → users.id`).
- **services 1 — ∞ orders**: одна услуга встречается во многих заявках (`orders.service_id → services.id`). Связь необязательная: если выбрана «Иная услуга», `service_id = NULL`, а текст лежит в `other_service`.

## Как сдать диаграмму
Самый быстрый путь — уже готовые `ER.png` / `ER.svg`. Если нужен свой вариант:
1. Открой https://app.diagrams.net (draw.io) — работает без регистрации.
2. Нарисуй три прямоугольника-таблицы как выше, проведи связи «один-ко-многим».
3. Экспортируй: File → Export as → PNG → сохрани как `docs/ER.png`.

## Если на экзамене требуют именно ERwin
Родной формат ERwin (`.erwin`/`.er1`) — закрытый бинарный, его нельзя подготовить
заранее текстом, только в самой программе ERwin. Но схема тривиальная (3 таблицы),
собирается за ~5 минут:
1. ERwin → New Model → Logical/Physical, СУБД: MySQL.
2. Создай 3 сущности: `users`, `services`, `orders` с полями из схемы выше.
   PK — `id` (Primary Key), типы как в `database.sql`.
3. Проведи связи (Identifying/Non-Identifying relationship):
   `users → orders` (1:M) и `services → orders` (1:M).
4. Сохрани модель (`Файл → Сохранить`) — получишь нужный `.erwin`-файл.
Изображение `ER.png` используй как образец, чтобы не сверяться с кодом.
