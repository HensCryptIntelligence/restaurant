CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name_item` VARCHAR(255),
  `category_item` VARCHAR(255),
  `price` DECIMAL(12,2),
  `stock` INT,
  `created_at` DATETIME,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;