-- ============================================================================
-- erwin_reverse.sql -- ASCII-only DDL script for ERwin REVERSE ENGINEERING.
-- ----------------------------------------------------------------------------
-- WHY a separate file: ERwin Data Modeler reads import scripts only in ANSI/ASCII
-- encoding. A UTF-8 file with Cyrillic gives a BLANK model. This file has NO
-- Cyrillic at all, so ERwin builds the model (3 tables + relationships) cleanly.
--
-- HOW TO USE IN ERwin (see docs/ER.md for full steps):
--   1. ERwin -> Actions -> Reverse Engineer.
--   2. New model type: Logical/Physical. Target DBMS: MySQL. Next.
--   3. Reverse Engineer From: Script -> Browse -> select THIS file. Next/Finish.
--   4. ERwin draws users, services, orders with FK relationships automatically.
--   (Reverse engineer only into a BLANK model.)
-- ============================================================================

CREATE TABLE users (
    id         INT          NOT NULL AUTO_INCREMENT,
    login      VARCHAR(50)  NOT NULL,
    password   VARCHAR(255) NOT NULL,
    fio        VARCHAR(150) NOT NULL,
    phone      VARCHAR(30)  NOT NULL,
    email      VARCHAR(150) NOT NULL,
    created_at DATETIME,
    PRIMARY KEY (id),
    UNIQUE (login)
);

CREATE TABLE services (
    id        INT          NOT NULL AUTO_INCREMENT,
    name      VARCHAR(150) NOT NULL,
    is_active TINYINT,
    PRIMARY KEY (id)
);

CREATE TABLE orders (
    id            INT          NOT NULL AUTO_INCREMENT,
    user_id       INT          NOT NULL,
    address       VARCHAR(255) NOT NULL,
    phone         VARCHAR(30)  NOT NULL,
    service_id    INT,
    other_service VARCHAR(255),
    desired_date  DATETIME     NOT NULL,
    payment_type  VARCHAR(30)  NOT NULL,
    status        VARCHAR(20)  NOT NULL,
    cancel_reason VARCHAR(255),
    created_at    DATETIME,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id)    REFERENCES users (id),
    FOREIGN KEY (service_id) REFERENCES services (id)
);
