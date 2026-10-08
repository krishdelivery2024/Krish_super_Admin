-- ============================================================
-- zone: columns required by the zone-wise delivery feature.
--
-- Run this against the target schema first (select the DB, e.g.
-- `USE live_db;`). Safe to re-run: every statement is guarded on
-- information_schema, so nothing fails when the column already exists.
--
-- Why this file exists: production/zone_migration.sql only does
-- `CREATE TABLE IF NOT EXISTS zone`, so on any database where `zone`
-- already exists it silently adds nothing. That left existing installs
-- with a `zone` table that has no `polygon` and no `status`, while the
-- code queries `SELECT id, polygon FROM zone WHERE status = 1`.
--
-- Column choices match the live schema:
--   polygon   longtext NULL  - JSON array of {"lat":..,"lng":..} vertices.
--                              NULL/invalid is tolerated by the code; those
--                              zones are simply skipped by the point tests.
--   status    tinyint(1) NOT NULL DEFAULT 1 - 1=active, 0=disabled. The
--                              code only ever reads `status = 1`.
--   platform_fee / convenience_fee  DECIMAL(10,2) NULL - NULL means
--                              "inherit the global value", matching
--                              get_zone_fees() and the convention already
--                              used by zone_delivery_method.
-- ============================================================

DROP PROCEDURE IF EXISTS `zone_add_column_if_missing`;

DELIMITER //
CREATE PROCEDURE `zone_add_column_if_missing`(
    IN p_table VARCHAR(128),
    IN p_column VARCHAR(128),
    IN p_definition TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table
          AND COLUMN_NAME = p_column
    ) THEN
        SET @ddl = CONCAT('ALTER TABLE `', p_table, '` ADD COLUMN `',
                          p_column, '` ', p_definition);
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END//
DELIMITER ;

CALL `zone_add_column_if_missing`('zone', 'polygon',
    'longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
CALL `zone_add_column_if_missing`('zone', 'status',
    "tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=active/enabled, 0=disabled'");
CALL `zone_add_column_if_missing`('zone', 'platform_fee',
    'DECIMAL(10,2) NULL DEFAULT NULL');
CALL `zone_add_column_if_missing`('zone', 'convenience_fee',
    'DECIMAL(10,2) NULL DEFAULT NULL');

DROP PROCEDURE IF EXISTS `zone_add_column_if_missing`;

-- ------------------------------------------------------------
-- Verification: every row returned below is still missing. An
-- empty result set means the schema is ready for batch A.
-- ------------------------------------------------------------
SELECT required.COLUMN_NAME AS still_missing
FROM (
              SELECT 'polygon'         AS COLUMN_NAME
    UNION ALL SELECT 'status'
    UNION ALL SELECT 'platform_fee'
    UNION ALL SELECT 'convenience_fee'
) AS required
LEFT JOIN information_schema.COLUMNS c
       ON c.TABLE_SCHEMA = DATABASE()
      AND c.TABLE_NAME = 'zone'
      AND c.COLUMN_NAME = required.COLUMN_NAME
WHERE c.COLUMN_NAME IS NULL;
