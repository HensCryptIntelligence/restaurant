CREATE TABLE IF NOT EXISTS `transaction_reservation` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT,
  `id_reservation` INT,
  `id_payment_reservation` INT,
  `status` ENUM('pending','confirmed'),
  `created_at` DATETIME,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_transaction_reservation_user` FOREIGN KEY (`id_user`) REFERENCES `users`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_transaction_reservation_reservation` FOREIGN KEY (`id_reservation`) REFERENCES `reservation`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_transaction_reservation_payment` FOREIGN KEY (`id_payment_reservation`) REFERENCES `payment_reservation`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;