CREATE TABLE cart_order (
    id_cart_order INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_menu_item INT NOT NULL,
    name_item VARCHAR(150) NOT NULL,
    category_item VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_user) REFERENCES users(id_user),
    FOREIGN KEY (id_menu_item) REFERENCES menu_items(id_menu_item)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
