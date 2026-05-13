-- CarTrader Database Setup
-- run this in phpMyAdmin

CREATE DATABASE IF NOT EXISTS cartrader
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cartrader;

-- sellers table
CREATE TABLE IF NOT EXISTS sellers (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(50)  NOT NULL,
    address    VARCHAR(200),
    phone      VARCHAR(20),
    email      VARCHAR(100) NOT NULL,
    username   VARCHAR(30)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- cars table
CREATE TABLE IF NOT EXISTS cars (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    seller_id  INT           NOT NULL,
    model      VARCHAR(100)  NOT NULL,
    year       INT           NOT NULL,
    price      DECIMAL(12,2) NOT NULL,
    colour     VARCHAR(50),
    location   VARCHAR(100),
    image_path VARCHAR(255),
    added_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE
) ENGINE=InnoDB;