-- Create table
CREATE TABLE IF NOT EXISTS menu_item (
    id_menu_item INT AUTO_INCREMENT PRIMARY KEY,
    name_item VARCHAR(150) NOT NULL,
    category_item ENUM(
        'Appetizer',
        'Main Course',
        'Soup & Salad',
        'Dessert',
        'Beverages',
        'Grill & BBQ',
    ) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
