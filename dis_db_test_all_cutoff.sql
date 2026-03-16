-- ============================================================
-- ALL CUTOFF TYPES TEST DATA
-- Run AFTER dis_db_sample_data.sql + dis_db_test_setup.sql
--
-- Source CSV files are in: dcj_test_to_upload/test_cutoff/
--   branches/DIS Branch Format_All Cutoff Types.csv
--   branches/DIS Payment Cut-Off_All Cutoff Types.csv
--   redemption/RedemptionReport-Monthly-2026-03-15.csv
--   redemption/RedemptionReport-SemiMonthly-2026-03-15.csv
--   redemption/RedemptionReport-Weekly-Wed-2026-03-11.csv
--   redemption/RedemptionReport-Every10Days-2026-03-10.csv
--   reconciliation/ReconciliationReport-Monthly-2026-03-15.csv
--   reconciliation/ReconciliationReport-SemiMonthly-2026-03-15.csv
--   reconciliation/ReconciliationReport-Weekly-Wed-2026-03-11.csv
--   reconciliation/ReconciliationReport-Every10Days-2026-03-10.csv
--
-- Covers all 4 cutoff types × both recon modes:
--   Type 1: Monthly        → {1,5,10,15,30,31}
--   Type 2: Semi-Monthly   → {5,15,21,30,31}
--   Type 3: Weekly         → Wednesday (existing: Sunday, Friday)
--   Type 4: Every 10 days  → {9,10,19,20,29,30}
--
-- Sample test URLs:
--   Type 1 (Monthly)      : /syscore/offlineprocess/per_cutoff?type=1&date=15
--   Type 2 (Semi-Monthly) : /syscore/offlineprocess/per_cutoff?type=2&date=15
--   Type 3 (Weekly)       : /syscore/offlineprocess/per_cutoff?type=3&date=wednesday
--   Type 4 (Every 10 days): /syscore/offlineprocess/per_cutoff?type=4&date=10
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- STEP 1: Companies (cp_merchant) — one per test group
-- ============================================================
INSERT INTO `cp_merchant`
  (`CP_ID`, `TIN`, `LegalName`, `TradingName`, `GroupTIN`, `GroupName`, `Address`,
   `MeanofPayment`, `PayeeCode`, `BankName`, `BankAccountNumber`, `PayeeName`, `PayeeId`,
   `MerchantFee`, `Industry`, `VATCond`, `InsertType`, `BankBranchCode`,
   `PayeeQtyOfDays`, `PayeeDayType`, `PayeeComments`,
   `AffiliateGroupCode`, `MerchantType`, `DIGITALSETTLEMENTTYPE`)
VALUES
(2001, '300-100-001-000', 'Monthly Test Corp',       'MonthlyTestCo',  '300-100-001-000', 'Test Group A', '100 Test St, Makati',
  'Bank Transfer', 'PAYEE-MO-001', 'BDO', '3001-0001-0001', 'Monthly Test',  'PID-MO-001',
  0.020000, 'Retail', 'Taxable',    'AUTO', 'BDO-TEST',  3, 1, '', 'TESTA', '', 'NRECON'),

(2002, '300-100-002-000', 'SemiMonthly Test Corp',   'SemiMoTestCo',   '300-100-002-000', 'Test Group B', '200 Test St, Makati',
  'Bank Transfer', 'PAYEE-SM-001', 'BPI', '3002-0002-0002', 'SemiMo Test',   'PID-SM-001',
  0.015000, 'Retail', 'VAT-Exempt', 'AUTO', 'BPI-TEST',  3, 1, '', 'TESTB', '', ''),

(2003, '300-100-003-000', 'Weekly Test Corp',        'WeeklyTestCo',   '300-100-003-000', 'Test Group C', '300 Test St, Makati',
  'Bank Transfer', 'PAYEE-WK-001', 'Metrobank', '3003-0003-0003', 'Weekly Test', 'PID-WK-001',
  0.018000, 'Retail', 'Taxable',    'AUTO', 'MBK-TEST',  3, 1, '', 'TESTC', '', ''),

(2004, '300-100-004-000', 'Every10Days Test Corp',   'Ten10TestCo',    '300-100-004-000', 'Test Group D', '400 Test St, Makati',
  'Bank Transfer', 'PAYEE-TD-001', 'BDO', '3004-0004-0004', 'Ten10 Test',    'PID-TD-001',
  0.020000, 'Retail', 'Taxable',    'AUTO', 'BDO-TEST2', 3, 1, '', 'TESTD', '', 'NRECON');


-- ============================================================
-- STEP 2: Branches — loaded from CSV
-- Source: dcj_test_to_upload/test_cutoff/branches/DIS Branch Format_All Cutoff Types.csv
-- ============================================================
LOAD DATA LOCAL INFILE 'dcj_test_to_upload/test_cutoff/branches/DIS Branch Format_All Cutoff Types.csv'
INTO TABLE `branches`
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(`BRANCH_ID`, `MERCHANT_ID`, `BRANCH_NAME`, `CP_ID`, `AFFILIATEGROUPCODE`);

INSERT INTO `branch_merchant` (`MERCHANT_ID`, `BRANCH_ID`)
SELECT `MERCHANT_ID`, `BRANCH_ID` FROM `branches`
WHERE `MERCHANT_ID` IN (10, 11, 12, 13, 14, 15);


-- ============================================================
-- STEP 3: Payment Cutoff config — loaded from CSV
-- Source: dcj_test_to_upload/test_cutoff/branches/DIS Payment Cut-Off_All Cutoff Types.csv
-- ============================================================
LOAD DATA LOCAL INFILE 'dcj_test_to_upload/test_cutoff/branches/DIS Payment Cut-Off_All Cutoff Types.csv'
INTO TABLE `payment_cutoff`
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(`MERCHANT_ID`, `TYPE`, `SPECIFIC_DAY`, `SPECIFIC_DATE`);

-- Set DigitalSettlementType: merchants 10, 13, 15 = nrecon; 11, 12, 14 = wrecon
UPDATE `payment_cutoff` SET `DigitalSettlementType` = 'NRECON' WHERE `MERCHANT_ID` IN (10, 13, 15);
UPDATE `payment_cutoff` SET `DigitalSettlementType` = ''       WHERE `MERCHANT_ID` IN (11, 12, 14);


-- ============================================================
-- STEP 4: Redemptions — loaded from CSVs per cutoff type
-- All rows: STAGE=RECONCILED, PA_ID=0 (ready to be processed)
-- ============================================================

-- Type 1: Monthly (merchant 10)
LOAD DATA LOCAL INFILE 'dcj_test_to_upload/test_cutoff/redemption/RedemptionReport-Monthly-2026-03-15.csv'
INTO TABLE `redemption`
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(`MERCHANT_NAME`, `MERCHANT_ID`, `BRANCH_ID`, `POS_ID`, `POS_TXN_ID`, `PROD_ID`,
 `TRANSACTION_DATE_TIME`, `TRANSACTION_ID`, `VOUCHER_CODE`, `TRANSACTION_VALUE`,
 `STAGE`, `REDEEM_ID`, `PAYMENT_MODE`);

-- Type 2: Semi-Monthly (merchant 11)
LOAD DATA LOCAL INFILE 'dcj_test_to_upload/test_cutoff/redemption/RedemptionReport-SemiMonthly-2026-03-15.csv'
INTO TABLE `redemption`
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(`MERCHANT_NAME`, `MERCHANT_ID`, `BRANCH_ID`, `POS_ID`, `POS_TXN_ID`, `PROD_ID`,
 `TRANSACTION_DATE_TIME`, `TRANSACTION_ID`, `VOUCHER_CODE`, `TRANSACTION_VALUE`,
 `STAGE`, `REDEEM_ID`, `PAYMENT_MODE`);

-- Type 3: Weekly Wednesday (merchants 12, 13)
LOAD DATA LOCAL INFILE 'dcj_test_to_upload/test_cutoff/redemption/RedemptionReport-Weekly-Wed-2026-03-11.csv'
INTO TABLE `redemption`
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(`MERCHANT_NAME`, `MERCHANT_ID`, `BRANCH_ID`, `POS_ID`, `POS_TXN_ID`, `PROD_ID`,
 `TRANSACTION_DATE_TIME`, `TRANSACTION_ID`, `VOUCHER_CODE`, `TRANSACTION_VALUE`,
 `STAGE`, `REDEEM_ID`, `PAYMENT_MODE`);

-- Type 4: Every 10 days (merchants 14, 15)
LOAD DATA LOCAL INFILE 'dcj_test_to_upload/test_cutoff/redemption/RedemptionReport-Every10Days-2026-03-10.csv'
INTO TABLE `redemption`
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(`MERCHANT_NAME`, `MERCHANT_ID`, `BRANCH_ID`, `POS_ID`, `POS_TXN_ID`, `PROD_ID`,
 `TRANSACTION_DATE_TIME`, `TRANSACTION_ID`, `VOUCHER_CODE`, `TRANSACTION_VALUE`,
 `STAGE`, `REDEEM_ID`, `PAYMENT_MODE`);


-- ============================================================
-- STEP 5: Reconciliation records — loaded from CSVs per cutoff type
-- ============================================================

-- Type 1: Monthly
LOAD DATA LOCAL INFILE 'dcj_test_to_upload/test_cutoff/reconciliation/ReconciliationReport-Monthly-2026-03-15.csv'
INTO TABLE `reconcilation`
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(`MERCHANT_NAME`, `MERCHANT_ID`, `BRANCH_ID`, `POS_ID`, `POS_TXN_ID`,
 `TRANSACTION_DATE_TIME`, `PROD_ID`, `REDEEM_ID`, `VOUCHER_CODE`, `TRANSACTION_VALUE`,
 `RECON_DATE_TIME`, `RECON_ID`, `PAYMENT_MODE`);

-- Type 2: Semi-Monthly
LOAD DATA LOCAL INFILE 'dcj_test_to_upload/test_cutoff/reconciliation/ReconciliationReport-SemiMonthly-2026-03-15.csv'
INTO TABLE `reconcilation`
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(`MERCHANT_NAME`, `MERCHANT_ID`, `BRANCH_ID`, `POS_ID`, `POS_TXN_ID`,
 `TRANSACTION_DATE_TIME`, `PROD_ID`, `REDEEM_ID`, `VOUCHER_CODE`, `TRANSACTION_VALUE`,
 `RECON_DATE_TIME`, `RECON_ID`, `PAYMENT_MODE`);

-- Type 3: Weekly Wednesday
LOAD DATA LOCAL INFILE 'dcj_test_to_upload/test_cutoff/reconciliation/ReconciliationReport-Weekly-Wed-2026-03-11.csv'
INTO TABLE `reconcilation`
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(`MERCHANT_NAME`, `MERCHANT_ID`, `BRANCH_ID`, `POS_ID`, `POS_TXN_ID`,
 `TRANSACTION_DATE_TIME`, `PROD_ID`, `REDEEM_ID`, `VOUCHER_CODE`, `TRANSACTION_VALUE`,
 `RECON_DATE_TIME`, `RECON_ID`, `PAYMENT_MODE`);

-- Type 4: Every 10 days
LOAD DATA LOCAL INFILE 'dcj_test_to_upload/test_cutoff/reconciliation/ReconciliationReport-Every10Days-2026-03-10.csv'
INTO TABLE `reconcilation`
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(`MERCHANT_NAME`, `MERCHANT_ID`, `BRANCH_ID`, `POS_ID`, `POS_TXN_ID`,
 `TRANSACTION_DATE_TIME`, `PROD_ID`, `REDEEM_ID`, `VOUCHER_CODE`, `TRANSACTION_VALUE`,
 `RECON_DATE_TIME`, `RECON_ID`, `PAYMENT_MODE`);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- FULL MERCHANT MATRIX AFTER RUNNING ALL 3 SQL FILES:
--
-- MERCHANT_ID  TYPE           DAY/DATE              RECON    TEST URL
-- -----------  -------------  --------------------  -------  -------------------------------------------
-- 1            Weekly         Sunday                wrecon   /per_cutoff?type=3&date=sunday
-- 2            Weekly         Sunday                nrecon   /per_cutoff?type=3&date=sunday
-- 3            Monthly        [15]                  wrecon   /per_cutoff?type=1&date=15
-- 4            Monthly        [15]                  wrecon   /per_cutoff?type=1&date=15
-- 5            Weekly         Friday                wrecon   /per_cutoff?type=3&date=friday
-- 677          Semi-Monthly   {15,31}               nrecon   /per_cutoff?type=2&date=15
-- 10           Monthly        {1,5,10,15,30,31}    nrecon   /per_cutoff?type=1&date=15
-- 11           Semi-Monthly   {5,15,21,30,31}      wrecon   /per_cutoff?type=2&date=15
-- 12           Weekly         Wednesday             wrecon   /per_cutoff?type=3&date=wednesday
-- 13           Weekly         Wednesday             nrecon   /per_cutoff?type=3&date=wednesday
-- 14           Every 10 days  {9,10,19,20,29,30}   wrecon   /per_cutoff?type=4&date=10
-- 15           Every 10 days  {9,10,19,20,29,30}   nrecon   /per_cutoff?type=4&date=10
--
-- per_merchantID test URLs:
--   /per_cutoff?process=10  → Monthly nrecon
--   /per_cutoff?process=11  → Semi-Monthly wrecon
--   /per_cutoff?process=12  → Weekly Wednesday wrecon
--   /per_cutoff?process=13  → Weekly Wednesday nrecon
--   /per_cutoff?process=14  → Every 10 days wrecon
--   /per_cutoff?process=15  → Every 10 days nrecon
-- ============================================================
