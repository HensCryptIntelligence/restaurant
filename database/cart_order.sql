
-- =====================================
-- 3. CART ORDER
-- =====================================
CREATE TABLE cart_order (
    id_cart_order INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_menu_item INT NOT NULL,
    name_item VARCHAR(150) NOT NULL,
    category_item VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL,
    status ENUM('in_cart','checked_out') NOT NULL DEFAULT 'in_cart',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE ON UPDATE CASCADE,

    FOREIGN KEY (id_menu_item) REFERENCES menu_item(id_menu_item)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
