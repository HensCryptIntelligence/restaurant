CREATE TABLE transaction_order (
    id_transaction_order INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_cart_order INT NOT NULL,
    id_payment_order INT NOT NULL,
    status ENUM('pending','confirmed') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_user) REFERENCES users(id_user),
    FOREIGN KEY (id_cart_order) REFERENCES cart_order(id_cart_order),
    FOREIGN KEY (id_payment_order) REFERENCES payment_order(id_payment_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
