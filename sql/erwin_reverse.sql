-- ============================================================================
-- erwin_reverse.sql -- ASCII-only DDL for ERwin REVERSE ENGINEERING.
-- ERwin reads import scripts only in ANSI/ASCII encoding (no Cyrillic), so this
-- file has English-only identifiers and builds the model cleanly.
-- ERwin -> Actions -> Reverse Engineer -> From Script -> select this file.
-- Reverse engineer only into a BLANK model. See docs/ER.md for full steps.
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

CREATE TABLE courses (
    id        INT          NOT NULL AUTO_INCREMENT,
    name      VARCHAR(150) NOT NULL,
    is_active TINYINT,
    PRIMARY KEY (id)
);

CREATE TABLE orders (
    id           INT          NOT NULL AUTO_INCREMENT,
    user_id      INT          NOT NULL,
    course_name  VARCHAR(150) NOT NULL,
    desired_date VARCHAR(10)  NOT NULL,
    payment_type VARCHAR(40)  NOT NULL,
    status       VARCHAR(20)  NOT NULL,
    created_at   DATETIME,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users (id)
);

CREATE TABLE reviews (
    id          INT           NOT NULL AUTO_INCREMENT,
    user_id     INT           NOT NULL,
    order_id    INT,
    course_name VARCHAR(150)  NOT NULL,
    rating      TINYINT       NOT NULL,
    text        VARCHAR(1000) NOT NULL,
    created_at  DATETIME,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id)  REFERENCES users (id),
    FOREIGN KEY (order_id) REFERENCES orders (id)
);

CREATE TABLE feedback (
    id         INT           NOT NULL AUTO_INCREMENT,
    name       VARCHAR(150)  NOT NULL,
    email      VARCHAR(150)  NOT NULL,
    message    VARCHAR(1000) NOT NULL,
    created_at DATETIME,
    PRIMARY KEY (id)
);
