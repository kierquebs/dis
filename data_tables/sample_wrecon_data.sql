-- DIS Database Schema Updates and Sample Data
-- This script contains necessary schema updates (ALTER TABLE) to support the reconciliation process
-- and sample data to populate the "With Recon Transaction Merchants" page.

-- ==========================================
-- 1. SCHEMA UPDATES
-- Run these if you encounter "Unknown column" errors.
-- ==========================================

-- Update REDEMPTION TABLE
ALTER TABLE `redemption`
  ADD COLUMN `REFUND_ID` INT NOT NULL AFTER `PAYMENT_MODE`,
  ADD COLUMN `PA_ID` INT NOT NULL AFTER `REFUND_ID`,
  ADD COLUMN `PA_TEMPID` INT NOT NULL AFTER `PA_ID`,
  ADD INDEX (`REFUND_ID`),
  ADD INDEX (`PA_ID`),
  ADD INDEX (`PA_TEMPID`);

-- Update RECONCILATION TABLE (Note the spelling in the database)
ALTER TABLE `reconcilation`
  ADD COLUMN `REDEEM_TBL_ID` INT NOT NULL AFTER `payment_mode`,
  ADD COLUMN `STAGE` VARCHAR(50) NOT NULL AFTER `REDEEM_TBL_ID`,
  ADD COLUMN `PA_TEMPID` INT NOT NULL AFTER `STAGE`,
  ADD INDEX (`REDEEM_TBL_ID`),
  ADD INDEX (`PA_TEMPID`);

-- Update REFUND TABLE
ALTER TABLE `refund`
  ADD COLUMN `REDEEM_TBL_ID` INT NOT NULL AFTER `PA_ID`,
  ADD COLUMN `RECON_TBL_ID` INT NOT NULL AFTER `REDEEM_TBL_ID`,
  ADD COLUMN `PA_TEMPID` INT NOT NULL AFTER `RECON_TBL_ID`,
  ADD INDEX (`REDEEM_TBL_ID`),
  ADD INDEX (`RECON_TBL_ID`),
  ADD INDEX (`PA_TEMPID`);

-- Update BRANCH_MERCHANT TABLE
ALTER TABLE `branch_merchant` ADD UNIQUE KEY `mid_bid` (`MERCHANT_ID`,`BRANCH_ID`);

-- Update PAYMENT_CUTOFF TABLE
ALTER TABLE `payment_cutoff` ADD COLUMN `DigitalSettlementType` VARCHAR(250) NOT NULL AFTER `SPECIFIC_DATE`;

-- ==========================================
-- 2. SAMPLE DATA
-- ==========================================

-- Insert Sample Product (if not exists)
INSERT IGNORE INTO `cp_product` (`SERVICE_ID`, `SERVICE_NAME`)
VALUES (42, 'Pluxee Digital Voucher');

-- Insert Sample Merchant
INSERT IGNORE INTO `cp_merchant` (`CP_ID`, `TIN`, `LegalName`, `TradingName`, `MerchantFee`, `VATCond`, `PayeeQtyOfDays`, `PayeeDayType`, `DigitalSettlementType`)
VALUES (1001, '123-456-789-000', 'Pluxee Sample Merchant', 'Pluxee Sample', 0.02, 'Taxable', 3, 1, '');

-- Insert Sample Branch
INSERT IGNORE INTO `branches` (`BRANCH_ID`, `MERCHANT_ID`, `BRANCH_NAME`, `CP_ID`)
VALUES ('BR001', 1, 'Main Branch', 1001);

-- Insert Branch Merchant Mapping
INSERT IGNORE INTO `branch_merchant` (`MERCHANT_ID`, `BRANCH_ID`)
VALUES (1, 'BR001');

-- Insert Payment Cutoff (Weekly on Sunday)
INSERT IGNORE INTO `payment_cutoff` (`MERCHANT_ID`, `TYPE`, `SPECIFIC_DAY`, `DigitalSettlementType`)
VALUES (1, 'Weekly', 'Sunday', '');

-- Insert Redemption Record
INSERT IGNORE INTO `redemption` (`ID`, `REDEEM_ID`, `MERCHANT_ID`, `BRANCH_ID`, `PROD_ID`, `VOUCHER_CODE`, `TRANSACTION_VALUE`, `STAGE`, `TRANSACTION_DATE_TIME`)
VALUES (500, 'RED001', 1, 'BR001', 42, 'VOUCH001', 100.00, 'RECONCILED', '2026-03-01 08:00:00');

-- Insert Reconciliation Record
INSERT IGNORE INTO `reconcilation` (`RECON_ID`, `REDEEM_ID`, `MERCHANT_ID`, `BRANCH_ID`, `TRANSACTION_VALUE`, `RECON_DATE_TIME`, `REDEEM_TBL_ID`, `PROD_ID`, `PA_ID`, `STAGE`, `TRANSACTION_DATE_TIME`)
VALUES ('REC001', 'RED001', 1, 'BR001', 100.00, '2026-03-01 10:00:00', 500, 42, 0, 'RECONCILED', '2026-03-01 08:00:00');
