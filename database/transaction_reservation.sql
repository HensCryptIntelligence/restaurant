CREATE TABLE transaction_reservation (
    id_transaction_reservation INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_reservation INT NOT NULL,
    id_payment_reservation INT NOT NULL,
    status ENUM('pending','confirmed') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_user) REFERENCES users(id_user),
    FOREIGN KEY (id_reservation) REFERENCES reservation(id_reservation),
    FOREIGN KEY (id_payment_reservation) REFERENCES payment_reservation(id_payment_reservation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
