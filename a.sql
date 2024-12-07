-- Veritabanını kullan
USE ccshop;

-- Kullanıcılar tablosu
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Kartlar tablosu
CREATE TABLE cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bin VARCHAR(6) NOT NULL,
    country VARCHAR(50),
    price DECIMAL(10, 2),
    full_card VARCHAR(255) NOT NULL,
    status ENUM('available', 'sold') DEFAULT 'available',
    user_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Balance Requests tablosu
CREATE TABLE balance_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Örnek kullanıcılar
INSERT INTO users (username, email, password, balance, role) VALUES
('testuser', 'test@example.com', '$2y$10$eimA2xU4wAAyxdXPJ0w4Se4y3lKN5r9HYIdAZR3Md3hr4DzhIfRjW', 100.00, 'user'), -- Şifre: 123456
('adminuser', 'admin@example.com', '$2y$10$X1OYcA7Rv8OaLJvWVnU1pOhTLDdW.Yv4KcO3hzNdXd5cfAa/nYmMe', 0.00, 'admin'); -- Şifre: admin123

-- Örnek kartlar
INSERT INTO cards (bin, country, price, full_card, status) VALUES
('438857', 'USA', 10.00, '4388576125381593|09|2028|958', 'available'),
('654321', 'UK', 15.50, '6543210987654321|12|2025|123', 'available'),
('111222', 'CAN', 20.00, '1112223344556677|06|2026|789', 'available');
