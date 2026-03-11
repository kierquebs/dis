-- Migration: Add missing `generated` column to pa_header and rs_header
-- Run this script on existing databases to fix the "Unknown column 'generated'" error.

SET SESSION sql_mode = '';

ALTER TABLE `pa_header`
  ADD COLUMN `generated` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `rs_header`
  ADD COLUMN `generated` TINYINT(1) NOT NULL DEFAULT 0;
