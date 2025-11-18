CREATE TABLE reservation (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_reservation_room INT NOT NULL,
    seats INT NOT NULL,
    reservation_start DATETIME NOT NULL,
    reservation_time INT NOT NULL,
    reservation_date DATETIME NOT NULL,
    phone_number VARCHAR(50) NOT NULL,
    email_address VARCHAR(150) NOT NULL,
    status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_user) REFERENCES users(id_user),
    FOREIGN KEY (id_reservation_room) REFERENCES reservation_rooms(id_reservation_room)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
