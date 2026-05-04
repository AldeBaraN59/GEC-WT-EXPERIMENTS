-- setup.sql
-- Run this file once in your MySQL client to set up the database
-- Command: mysql -u root -p < setup.sql

CREATE DATABASE IF NOT EXISTS luminary_db;
USE luminary_db;

CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Optional: insert a test user (password = "test1234")
-- Password is hashed using PHP password_hash()
-- INSERT IGNORE INTO users (name, email, password)
-- VALUES (
--     'Alex Rivera',
--     'alex@luminary.io',
--     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
-- );
