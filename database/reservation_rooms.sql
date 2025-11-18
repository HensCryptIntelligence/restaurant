CREATE TABLE IF NOT EXISTS `reservation_rooms` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `seats` INT,
  `price_place` DECIMAL(12,2),
  `created_at` DATETIME,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;