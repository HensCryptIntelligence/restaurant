CREATE TABLE payment_reservation (
    id_payment_reservation INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_reservation INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    received DECIMAL(10,2) NOT NULL,
    return_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash','e-wallet','bank') NOT NULL,
    status ENUM('pending','confirmed') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_user) REFERENCES users(id_user),
    FOREIGN KEY (id_reservation) REFERENCES reservation(id_reservation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
