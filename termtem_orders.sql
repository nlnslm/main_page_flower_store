-- TERMTEM+ : Orders table only
-- Import into the existing `termtem_store` database in phpMyAdmin.
-- Does not modify the existing `products` or `users` tables.
USE `termtem_store`;

CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` INT NOT NULL AUTO_INCREMENT,
  `product_id` TINYINT(4) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `recipient` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `delivery_date` DATE NOT NULL,
  `delivery_time` VARCHAR(30) NOT NULL,
  `card_message` TEXT DEFAULT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `order_status` VARCHAR(30) NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `idx_orders_product_id` (`product_id`),
  CONSTRAINT `fk_orders_product` FOREIGN KEY (`product_id`)
    REFERENCES `products` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
