-- Per-zone delivery method and delivery charge.
--
-- A row exists per zone. Any NULL tariff column means "inherit the global value
-- from delivery_method id=1", which keeps every existing zone behaving exactly
-- as it does today until an admin actually enters a value for that zone.
-- This mirrors the nullable-inherit convention already used by
-- zone.platform_fee / zone.convenience_fee and get_zone_fees().

CREATE TABLE IF NOT EXISTS `zone_delivery_method` (
  `id`                  INT(11) NOT NULL AUTO_INCREMENT,
  `zone_id`             INT(10) UNSIGNED NOT NULL,
  `storepickup`         TINYINT(1) NOT NULL DEFAULT 0,
  `in_person_delivery`  TINYINT(1) NOT NULL DEFAULT 0,
  `courier`             TINYINT(1) NOT NULL DEFAULT 0,
  `first_km`            DECIMAL(10,2) DEFAULT NULL,
  `first_km_amount`     DECIMAL(10,2) DEFAULT NULL,
  `rest_km_amount`      DECIMAL(10,2) DEFAULT NULL,
  `is_active`           TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_zone_id` (`zone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Seed one inheriting row per existing zone so the admin screen has something
-- to edit straight away. Copying the zone's current method flags keeps the
-- on/off switches consistent with the global setting they inherit.
INSERT IGNORE INTO `zone_delivery_method`
  (`zone_id`, `storepickup`, `in_person_delivery`, `courier`, `is_active`)
SELECT
  z.`id`,
  dm.`storepickup`,
  dm.`in_persion_delivery`,
  dm.`Delivery_by_courier`,
  1
FROM `zone` z
CROSS JOIN `delivery_method` dm
WHERE dm.`id` = 1 AND z.`status` = 1;