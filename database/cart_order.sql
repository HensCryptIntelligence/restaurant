CREATE TABLE IF NOT EXISTS `cart_order` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT,
  `id_item` INT,
  `name_item` VARCHAR(255),
  `category_item` VARCHAR(255),
  `price` DECIMAL(12,2),
  `quantity` INT,
  `subtotal` DECIMAL(12,2),
  `created_at` DATETIME,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_cart_order_user` FOREIGN KEY (`id_user`) REFERENCES `users`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_cart_order_item` FOREIGN KEY (`id_item`) REFERENCES `menu_items`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;