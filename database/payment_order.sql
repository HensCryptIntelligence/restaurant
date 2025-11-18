CREATE TABLE IF NOT EXISTS `payment_order` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT,
  `id_cart` INT,
  `total_amount` DECIMAL(12,2),
  `received` DECIMAL(12,2),
  `return_amount` DECIMAL(12,2),
  `payment_method` ENUM('cash','e-wallet','bank'),
  `status` ENUM('pending','confirmed'),
  `created_at` DATETIME,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_payment_order_user` FOREIGN KEY (`id_user`) REFERENCES `users`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_payment_order_cart` FOREIGN KEY (`id_cart`) REFERENCES `cart_order`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;