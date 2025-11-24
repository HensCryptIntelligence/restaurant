-- =====================================
-- 2. MENU ITEM
-- =====================================
CREATE TABLE menu_item (
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

-- Dummy Data Menu Item
INSERT INTO menu_item (name_item, category_item, price, stock) VALUES
('Spring Rolls', 'Appetizer', 2.50, 50),
('Caesar Salad', 'Soup & Salad', 4.00, 30),
('Grilled Chicken', 'Main Course', 8.50, 20),
('Beef Steak', 'Grill & BBQ', 12.00, 15),
('Chocolate Cake', 'Dessert', 3.50, 25),
('Lemonade', 'Beverages', 1.50, 40),
('Tomato Soup', 'Soup & Salad', 3.00, 30),
('BBQ Ribs', 'Grill & BBQ', 14.00, 10),
('Pasta Carbonara', 'Main Course', 7.50, 20),
('Ice Cream Sundae', 'Dessert', 2.50, 30);
