-- ==========================================
-- BITEHIVE RESTAURANT DATABASE
-- Database Name: restaurant
-- ==========================================

-- Create database
CREATE DATABASE IF NOT EXISTS restaurant;
USE restaurant;

-- ==========================================
-- TABLE: users
-- ==========================================
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: menu_items
-- ==========================================
CREATE TABLE IF NOT EXISTS menu_items (
    id_menu_item INT AUTO_INCREMENT PRIMARY KEY,
    name_item VARCHAR(150) NOT NULL,
    category_item ENUM(
        'Appetizer',
        'Main Course',
        'Soup & Salad',
        'Dessert',
        'Beverages',
        'Grill & BBQ'
    ) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: reservation_rooms
-- ==========================================
CREATE TABLE IF NOT EXISTS reservation_rooms (
    id_reservation_room VARCHAR(10) PRIMARY KEY,
    seats INT NOT NULL,
    price_place DECIMAL(10,2) DEFAULT 50000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: reservation
-- ==========================================
CREATE TABLE IF NOT EXISTS reservation (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_reservation_room VARCHAR(10) NOT NULL,
    seats INT NOT NULL,
    reservation_start DATETIME NOT NULL,
    reservation_time INT DEFAULT 120,
    reservation_date DATE NOT NULL,
    phone_number VARCHAR(20),
    email_address VARCHAR(255),
    status ENUM('pending', 'confirmed', 'seated', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_reservation_room) REFERENCES reservation_rooms(id_reservation_room) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: cart_order
-- ==========================================
CREATE TABLE IF NOT EXISTS cart_order (
    id_cart_order INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_menu_item INT NOT NULL,
    name_item VARCHAR(150) NOT NULL,
    category_item VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_menu_item) REFERENCES menu_items(id_menu_item) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: payment_order
-- ==========================================
CREATE TABLE IF NOT EXISTS payment_order (
    id_payment_order INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_cart_order INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    received DECIMAL(10,2) NOT NULL,
    return_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash','e-wallet','bank') NOT NULL,
    status ENUM('pending','confirmed') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_cart_order) REFERENCES cart_order(id_cart_order) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: payment_reservation
-- ==========================================
CREATE TABLE IF NOT EXISTS payment_reservation (
    id_payment_reservation INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_reservation INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    received DECIMAL(10,2) NOT NULL,
    return_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash','e-wallet','bank') NOT NULL,
    status ENUM('pending','confirmed') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_reservation) REFERENCES reservation(id_reservation) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: transaction_order
-- ==========================================
CREATE TABLE IF NOT EXISTS transaction_order (
    id_transaction_order INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_cart_order INT NOT NULL,
    id_payment_order INT NOT NULL,
    status ENUM('pending','confirmed') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_cart_order) REFERENCES cart_order(id_cart_order) ON DELETE CASCADE,
    FOREIGN KEY (id_payment_order) REFERENCES payment_order(id_payment_order) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: transaction_reservation
-- ==========================================
CREATE TABLE IF NOT EXISTS transaction_reservation (
    id_transaction_reservation INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_reservation INT NOT NULL,
    id_payment_reservation INT NOT NULL,
    status ENUM('pending','confirmed') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_reservation) REFERENCES reservation(id_reservation) ON DELETE CASCADE,
    FOREIGN KEY (id_payment_reservation) REFERENCES payment_reservation(id_payment_reservation) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- INSERT DUMMY DATA: users
-- ==========================================
INSERT INTO users (fullname, password_hash, role) VALUES 
('John Doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Jane Smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Robert Johnson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Maria Garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('David Lee', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Sarah Wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Admin User', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password untuk semua user: 'password' (admin: 'admin123')

-- ==========================================
-- INSERT DUMMY DATA: reservation_rooms
-- ==========================================
-- Floor 1
INSERT INTO reservation_rooms (id_reservation_room, seats, price_place) VALUES 
('Bar', 1, 50000),
('A1', 4, 50000),
('A2', 4, 50000),
('B1', 2, 50000),
('B2', 6, 50000),
('B3', 4, 50000),
('C1', 2, 50000),
('C2', 4, 50000);

-- Floor 2
INSERT INTO reservation_rooms (id_reservation_room, seats, price_place) VALUES 
('D1', 4, 50000),
('D2', 2, 50000),
('D3', 6, 50000),
('E1', 4, 50000),
('E2', 2, 50000),
('E3', 6, 50000),
('F1', 4, 50000),
('F2', 2, 50000);

-- Floor 3
INSERT INTO reservation_rooms (id_reservation_room, seats, price_place) VALUES 
('G1', 4, 50000),
('G2', 2, 50000),
('G3', 6, 50000),
('H1', 4, 50000),
('H2', 2, 50000),
('H3', 6, 50000),
('I1', 4, 50000),
('I2', 2, 50000);

-- ==========================================
-- INSERT DUMMY DATA: reservation (TODAY)
-- PENTING: Data ini akan muncul sesuai tanggal hari ini
-- ==========================================

-- Floor 1 Reservations
INSERT INTO reservation (id_user, id_reservation_room, seats, reservation_start, reservation_time, reservation_date, phone_number, email_address, status) VALUES 
(1, 'A1', 2, CONCAT(CURDATE(), ' 10:00:00'), 120, CURDATE(), '08123456781', 'john.doe@dummy.com', 'confirmed'),
(2, 'A1', 4, CONCAT(CURDATE(), ' 14:00:00'), 120, CURDATE(), '08123456782', 'jane.smith@dummy.com', 'pending'),
(3, 'A2', 3, CONCAT(CURDATE(), ' 11:00:00'), 120, CURDATE(), '08123456783', 'robert.johnson@dummy.com', 'seated'),
(4, 'B1', 2, CONCAT(CURDATE(), ' 12:00:00'), 120, CURDATE(), '08123456784', 'maria.garcia@dummy.com', 'seated'),
(5, 'B2', 4, CONCAT(CURDATE(), ' 18:00:00'), 120, CURDATE(), '08123456785', 'david.lee@dummy.com', 'confirmed'),
(6, 'B3', 3, CONCAT(CURDATE(), ' 19:00:00'), 120, CURDATE(), '08123456786', 'sarah.wilson@dummy.com', 'pending'),
(1, 'C1', 2, CONCAT(CURDATE(), ' 19:00:00'), 120, CURDATE(), '08123456781', 'john.doe@dummy.com', 'pending'),
(2, 'C2', 3, CONCAT(CURDATE(), ' 13:00:00'), 120, CURDATE(), '08123456782', 'jane.smith@dummy.com', 'confirmed'),
(3, 'Bar', 1, CONCAT(CURDATE(), ' 20:00:00'), 120, CURDATE(), '08123456783', 'robert.johnson@dummy.com', 'seated');

-- Floor 2 Reservations
INSERT INTO reservation (id_user, id_reservation_room, seats, reservation_start, reservation_time, reservation_date, phone_number, email_address, status) VALUES 
(4, 'D1', 3, CONCAT(CURDATE(), ' 11:00:00'), 120, CURDATE(), '08123456784', 'maria.garcia@dummy.com', 'confirmed'),
(5, 'D2', 2, CONCAT(CURDATE(), ' 15:00:00'), 120, CURDATE(), '08123456785', 'david.lee@dummy.com', 'pending'),
(6, 'D3', 5, CONCAT(CURDATE(), ' 18:00:00'), 120, CURDATE(), '08123456786', 'sarah.wilson@dummy.com', 'confirmed'),
(1, 'E1', 3, CONCAT(CURDATE(), ' 17:00:00'), 120, CURDATE(), '08123456781', 'john.doe@dummy.com', 'pending'),
(2, 'E2', 2, CONCAT(CURDATE(), ' 12:00:00'), 120, CURDATE(), '08123456782', 'jane.smith@dummy.com', 'seated'),
(3, 'E3', 4, CONCAT(CURDATE(), ' 20:00:00'), 120, CURDATE(), '08123456783', 'robert.johnson@dummy.com', 'confirmed'),
(4, 'F1', 3, CONCAT(CURDATE(), ' 14:00:00'), 120, CURDATE(), '08123456784', 'maria.garcia@dummy.com', 'pending'),
(5, 'F2', 2, CONCAT(CURDATE(), ' 21:00:00'), 120, CURDATE(), '08123456785', 'david.lee@dummy.com', 'confirmed');

-- Floor 3 Reservations
INSERT INTO reservation (id_user, id_reservation_room, seats, reservation_start, reservation_time, reservation_date, phone_number, email_address, status) VALUES 
(6, 'G1', 3, CONCAT(CURDATE(), ' 13:00:00'), 120, CURDATE(), '08123456786', 'sarah.wilson@dummy.com', 'confirmed'),
(1, 'G2', 2, CONCAT(CURDATE(), ' 16:00:00'), 120, CURDATE(), '08123456781', 'john.doe@dummy.com', 'seated'),
(2, 'G3', 4, CONCAT(CURDATE(), ' 19:00:00'), 120, CURDATE(), '08123456782', 'jane.smith@dummy.com', 'pending'),
(3, 'H1', 2, CONCAT(CURDATE(), ' 15:00:00'), 120, CURDATE(), '08123456783', 'robert.johnson@dummy.com', 'seated'),
(4, 'H2', 3, CONCAT(CURDATE(), ' 17:00:00'), 120, CURDATE(), '08123456784', 'maria.garcia@dummy.com', 'confirmed'),
(5, 'H3', 4, CONCAT(CURDATE(), ' 20:00:00'), 120, CURDATE(), '08123456785', 'david.lee@dummy.com', 'pending'),
(6, 'I1', 2, CONCAT(CURDATE(), ' 12:00:00'), 120, CURDATE(), '08123456786', 'sarah.wilson@dummy.com', 'confirmed'),
(1, 'I2', 3, CONCAT(CURDATE(), ' 18:00:00'), 120, CURDATE(), '08123456781', 'john.doe@dummy.com', 'seated');

-- ==========================================
-- INSERT DUMMY DATA: menu_items
-- ==========================================
INSERT INTO menu_items (name_item, category_item, price, stock) VALUES 
-- Appetizers
('Caesar Salad', 'Appetizer', 45000, 50),
('Bruschetta', 'Appetizer', 35000, 40),
('Chicken Wings', 'Appetizer', 55000, 30),
('Garlic Bread', 'Appetizer', 25000, 60),

-- Main Course
('Grilled Salmon', 'Main Course', 125000, 25),
('Beef Steak', 'Main Course', 150000, 20),
('Chicken Alfredo Pasta', 'Main Course', 85000, 35),
('Seafood Paella', 'Main Course', 135000, 15),

-- Soup & Salad
('Tomato Soup', 'Soup & Salad', 35000, 40),
('Greek Salad', 'Soup & Salad', 45000, 35),
('Mushroom Soup', 'Soup & Salad', 40000, 30),

-- Dessert
('Chocolate Lava Cake', 'Dessert', 45000, 25),
('Tiramisu', 'Dessert', 50000, 20),
('Ice Cream Sundae', 'Dessert', 35000, 40),

-- Beverages
('Cappuccino', 'Beverages', 35000, 100),
('Fresh Orange Juice', 'Beverages', 30000, 80),
('Iced Tea', 'Beverages', 20000, 100),
('Mineral Water', 'Beverages', 15000, 150),

-- Grill & BBQ
('BBQ Ribs', 'Grill & BBQ', 145000, 15),
('Grilled Chicken', 'Grill & BBQ', 95000, 25),
('Mixed Grill Platter', 'Grill & BBQ', 185000, 10);

-- ==========================================
-- QUERY EXAMPLES FOR TESTING
-- ==========================================

-- View all reservations for today
-- SELECT r.*, u.fullname, rr.id_reservation_room 
-- FROM reservation r
-- JOIN users u ON r.id_user = u.id_user
-- JOIN reservation_rooms rr ON r.id_reservation_room = rr.id_reservation_room
-- WHERE DATE(r.reservation_date) = CURDATE()
-- ORDER BY r.reservation_start;

-- View all available rooms
-- SELECT * FROM reservation_rooms ORDER BY id_reservation_room;

-- View all users
-- SELECT id_user, fullname, role FROM users;

-- View all menu items by category
-- SELECT * FROM menu_items ORDER BY category_item, name_item;