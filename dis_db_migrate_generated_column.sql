-- Migration: Add missing columns to pa_header and rs_header
-- Run this script on existing databases.

SET SESSION sql_mode = '';

ALTER TABLE `pa_header`
  ADD COLUMN `DATE_CREATED`  DATETIME   NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `USER_ID`,
  ADD COLUMN `generated`     TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `rs_header`
  ADD COLUMN `generated`     TINYINT(1) NOT NULL DEFAULT 0;

-- Populate DATE_CREATED from REIMBURSEMENT_DATE for existing rows
UPDATE `pa_header` SET `DATE_CREATED` = `REIMBURSEMENT_DATE` WHERE `DATE_CREATED` = '0000-00-00 00:00:00';
