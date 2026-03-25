-- Migration: Add missing POS_TXN_ID column to redemption table
-- Run this on existing databases to fix "Unknown column 'redeem.POS_TXN_ID'" error
-- on /pdf_pa/group_gen

ALTER TABLE `redemption`
  ADD COLUMN `POS_TXN_ID` VARCHAR(100) NOT NULL DEFAULT '' AFTER `POS_ID`;
