-- Migration: Add missing `generated` column to pa_header and rs_header
-- Run this script on existing databases to fix the "Unknown column 'generated'" error.

ALTER TABLE `pa_header`
  ADD COLUMN `generated` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `rs_header`
  ADD COLUMN `generated` TINYINT(1) NOT NULL DEFAULT 0;

-- Fix: Assign pa_header records to a reimbursement user (utype_id = 2)
-- so they appear in the /process page button filter.
-- The application filters PAs by USER_ID IN (users with utype_id = 2).
UPDATE `pa_header`
SET `USER_ID` = (SELECT `user_id` FROM `user` WHERE `utype_id` = 2 LIMIT 1)
WHERE `USER_ID` NOT IN (SELECT `user_id` FROM `user` WHERE `utype_id` = 2);
