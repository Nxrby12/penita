-- =====================================================================
--  Baza de date: platforma de self-publishing + "Blind Date with a Book"
--  Normalizata in a treia forma normala (3NF)
--  Import: phpMyAdmin -> Import -> alege acest fisier -> Go
-- =====================================================================

DROP DATABASE IF EXISTS biblioteca;
CREATE DATABASE biblioteca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE biblioteca;

-- ---------------------------------------------------------------------
-- 1. ROLURI: cititor, autor, admin  (autorizare pe baza de rol)
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id   TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. UTILIZATORI  (autentificare)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id       TINYINT UNSIGNED NOT NULL,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,            -- password_hash() din PHP
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. PROFIL DE AUTOR  (1:1 cu users; doar utilizatorii cu rol "autor")
--    Separat de users ca datele de autor sa nu depinda de rol (3NF)
-- ---------------------------------------------------------------------
CREATE TABLE author_profiles (
    user_id   INT UNSIGNED PRIMARY KEY,
    pen_name  VARCHAR(100) NOT NULL,
    bio       TEXT NULL,
    photo_path VARCHAR(255) NULL,                   -- poza de profil (upload)
    CONSTRAINT fk_author_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. GENURI LITERARE
-- ---------------------------------------------------------------------
CREATE TABLE genres (
    id   SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. CARTI  (publicate de autori, aprobate de admin)
-- ---------------------------------------------------------------------
CREATE TABLE books (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id     INT UNSIGNED NOT NULL,             -- -> author_profiles
    title         VARCHAR(200) NOT NULL,
    slug          VARCHAR(220) NOT NULL UNIQUE,      -- URL prietenos SEO
    description   TEXT NOT NULL,
    price         DECIMAL(8,2) NOT NULL CHECK (price >= 0),
    cover_path    VARCHAR(255) NOT NULL,             -- .jpg / .png (upload)
    pdf_path      VARCHAR(255) NOT NULL,             -- .pdf (upload, max 12 MB)
    status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    is_blind_date BOOLEAN NOT NULL DEFAULT FALSE,    -- apare in sectiunea "Blind Date"
    reviewed_by   INT UNSIGNED NULL,                 -- adminul care a aprobat/respins
    reviewed_at   DATETIME NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_books_author   FOREIGN KEY (author_id)   REFERENCES author_profiles(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_books_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_books_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. CARTE <-> GEN  (relatie N:M)
-- ---------------------------------------------------------------------
CREATE TABLE book_genres (
    book_id  INT UNSIGNED NOT NULL,
    genre_id SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (book_id, genre_id),
    CONSTRAINT fk_bg_book  FOREIGN KEY (book_id)  REFERENCES books(id)  ON DELETE CASCADE,
    CONSTRAINT fk_bg_genre FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. INDICII "BLIND DATE"  (mai multe indicii pe carte, in ordine)
-- ---------------------------------------------------------------------
CREATE TABLE blind_date_clues (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    book_id  INT UNSIGNED NOT NULL,
    position TINYINT UNSIGNED NOT NULL,
    clue     VARCHAR(255) NOT NULL,
    UNIQUE KEY uq_clue_position (book_id, position),
    CONSTRAINT fk_clue_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. COMENZI  (plata prin Stripe Checkout)
--    Totalul NU se stocheaza: se calculeaza din order_items (evita date derivate)
-- ---------------------------------------------------------------------
CREATE TABLE orders (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id           INT UNSIGNED NOT NULL,
    stripe_session_id VARCHAR(255) NULL UNIQUE,
    status            ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at           DATETIME NULL,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. PRODUSELE DINTR-O COMANDA
--    unit_price = pretul din momentul cumpararii (istoric, nu redundanta)
-- ---------------------------------------------------------------------
CREATE TABLE order_items (
    order_id   INT UNSIGNED NOT NULL,
    book_id    INT UNSIGNED NOT NULL,
    unit_price DECIMAL(8,2) NOT NULL,
    PRIMARY KEY (order_id, book_id),
    CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_oi_book  FOREIGN KEY (book_id)  REFERENCES books(id)  ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 10. RECENZII  (o recenzie per utilizator per carte)
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    book_id    INT UNSIGNED NOT NULL,
    rating     TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment    TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_review (user_id, book_id),
    CONSTRAINT fk_rev_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_rev_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 11. RATE LIMITING  (max 100 cereri / minut / IP)
-- ---------------------------------------------------------------------
CREATE TABLE rate_limits (
    ip            VARCHAR(45) PRIMARY KEY,           -- 45 caractere = suporta si IPv6
    window_start  DATETIME NOT NULL,
    request_count INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- =====================================================================
--  DATE INITIALE
-- =====================================================================
INSERT INTO roles (name) VALUES ('cititor'), ('autor'), ('admin');

INSERT INTO genres (name) VALUES
    ('Fantasy'), ('Science Fiction'), ('Thriller'), ('Romance'),
    ('Horror'), ('Mister'), ('Poezie'), ('Dezvoltare personala');
