-- Migration: add convenience_fee column to parcel_requests
-- Run this on the live database BEFORE deploying the updated add-parcel-request.php.
-- The `platform_fee`, `gst` and `grand_total` columns already exist in production; this only adds the new field.
ALTER TABLE `parcel_requests`
  ADD COLUMN `convenience_fee` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `platform_fee`;