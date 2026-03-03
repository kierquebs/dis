-- Sample Data for DIS Reconcilation
-- This script inserts a sample merchant, branch, product, and a reconciled transaction
-- to populate the "With Recon Transaction Merchants" page.

-- 1. Insert Sample Product (if not exists)
INSERT IGNORE INTO `cp_product` (`SERVICE_ID`, `SERVICE_NAME`)
VALUES (42, 'Pluxee Digital Voucher');

-- 2. Insert Sample Merchant
-- CP_ID: 1001
-- MERCHANT_ID: 1
INSERT IGNORE INTO `cp_merchant` (`CP_ID`, `TIN`, `LegalName`, `TradingName`, `MerchantFee`, `VATCond`, `PayeeQtyOfDays`, `PayeeDayType`, `DigitalSettlementType`)
VALUES (1001, '123-456-789-000', 'Pluxee Sample Merchant', 'Pluxee Sample', 0.02, 'Taxable', 3, 1, '');

-- 3. Insert Sample Branch
INSERT IGNORE INTO `branches` (`BRANCH_ID`, `MERCHANT_ID`, `BRANCH_NAME`, `CP_ID`)
VALUES ('BR001', 1, 'Main Branch', 1001);

-- 4. Insert Branch Merchant Mapping
INSERT IGNORE INTO `branch_merchant` (`MERCHANT_ID`, `BRANCH_ID`)
VALUES (1, 'BR001');

-- 5. Insert Payment Cutoff (Weekly on Sunday)
-- This matches the 'Weekly' and 'Sunday' filters in the application
INSERT IGNORE INTO `payment_cutoff` (`MERCHANT_ID`, `TYPE`, `SPECIFIC_DAY`, `DigitalSettlementType`)
VALUES (1, 'Weekly', 'Sunday', '');

-- 6. Insert Redemption Record
-- This serves as the source for the reconciliation
INSERT IGNORE INTO `redemption` (`ID`, `REDEEM_ID`, `MERCHANT_ID`, `BRANCH_ID`, `PROD_ID`, `VOUCHER_CODE`, `TRANSACTION_VALUE`, `STAGE`, `TRANSACTION_DATE_TIME`)
VALUES (500, 'RED001', 1, 'BR001', 42, 'VOUCH001', 100.00, 'RECONCILED', '2026-03-01 08:00:00');

-- 7. Insert Reconciliation Record
-- Must have REDEEM_TBL_ID matching the redemption ID
-- STAGE must be 'RECONCILED'
-- PA_ID must be 0 (unprocessed)
INSERT IGNORE INTO `reconcilation` (`RECON_ID`, `REDEEM_ID`, `MERCHANT_ID`, `BRANCH_ID`, `TRANSACTION_VALUE`, `RECON_DATE_TIME`, `REDEEM_TBL_ID`, `PROD_ID`, `PA_ID`, `STAGE`, `TRANSACTION_DATE_TIME`)
VALUES ('REC001', 'RED001', 1, 'BR001', 100.00, '2026-03-01 10:00:00', 500, 42, 0, 'RECONCILED', '2026-03-01 08:00:00');
