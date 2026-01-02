ALTER TABLE `accounts` 
ADD COLUMN `coins` INT(11) NOT NULL DEFAULT 0 AFTER `password`;

ALTER TABLE `accounts` ADD INDEX `coins` (`coins`);

ALTER TABLE `accounts` 
ADD COLUMN `lastAccess` BIGINT(13) UNSIGNED NOT NULL DEFAULT 0 AFTER `lastactive`;

ALTER TABLE `accounts` ADD INDEX `lastAccess` (`lastAccess`);

CREATE TABLE IF NOT EXISTS `site_shop_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `item_id` INT(11) NOT NULL COMMENT 'ID do item no Lineage 2',
  `count` INT(11) NOT NULL DEFAULT 1 COMMENT 'Quantidade do item',
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `price` INT(11) NOT NULL COMMENT 'Preço em moedas',
  `icon` VARCHAR(255) DEFAULT NULL COMMENT 'fa-icon ou URL da imagem',
  `stock` INT(11) NOT NULL DEFAULT -1 COMMENT '-1 = infinito, 0 = esgotado',
  `limit_count` INT(11) NOT NULL DEFAULT 0 COMMENT '0 = sem limite por conta',
  `active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `site_shop_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `login` VARCHAR(45) NOT NULL,
  `item_db_id` INT(11) NOT NULL COMMENT 'ID da tabela site_shop_items',
  `count` INT(11) NOT NULL DEFAULT 1,
  `purchase_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `login` (`login`),
  INDEX `item_db_id` (`item_db_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela donations (versão compatível)
CREATE TABLE IF NOT EXISTS `donations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `payment_id` VARCHAR(100) NOT NULL UNIQUE,
  `account_name` VARCHAR(45) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `coins_received` INT(11) NOT NULL,
  `status` ENUM('pending', 'approved', 'cancelled', 'expired') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX `idx_payment_id` (`payment_id`),
  INDEX `idx_account_name` (`account_name`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
