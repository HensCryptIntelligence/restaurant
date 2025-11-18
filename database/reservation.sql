CREATE TABLE IF NOT EXISTS `reservation` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT,
  `id_room` INT,
  `seats` INT,
  `reservation_start` DATETIME,
  `reservation_time` INT,
  `reservation_date` DATETIME,
  `phone_number` VARCHAR(255),
  `email_address` VARCHAR(255),
  `status` ENUM('pending','confirmed','cancelled'),
  `created_at` DATETIME,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_reservation_user` FOREIGN KEY (`id_user`) REFERENCES `users`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_reservation_room` FOREIGN KEY (`id_room`) REFERENCES `reservation_rooms`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
