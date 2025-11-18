CREATE TABLE IF NOT EXISTS `transaction_order` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT,
  `id_cart` INT,
  `id_payment` INT,
  `status` ENUM('pending','confirmed'),
  `created_at` DATETIME,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_transaction_order_user` FOREIGN KEY (`id_user`) REFERENCES `users`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_transaction_order_cart` FOREIGN KEY (`id_cart`) REFERENCES `cart_order`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_transaction_order_payment` FOREIGN KEY (`id_payment`) REFERENCES `payment_order`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;