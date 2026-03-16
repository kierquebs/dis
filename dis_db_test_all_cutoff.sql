-- ============================================================
-- ALL CUTOFF TYPES TEST DATA
-- Run AFTER dis_db_sample_data.sql + dis_db_test_setup.sql
--
-- Covers all 4 cutoff types × both recon modes:
--   Type 1: Monthly        → {1,5,10,15,30,31}
--   Type 2: Semi-Monthly   → {5,15,21,30,31}
--   Type 3: Weekly         → Monday,Wednesday,Friday,Saturday,Sunday
--   Type 4: Every 10 days  → {9,10,19,20,29,30}
--
-- Sample URLs to test each:
--   Type 1 (Monthly)       : /syscore/offlineprocess/per_cutoff?type=1&date=15
--   Type 2 (Semi-Monthly)  : /syscore/offlineprocess/per_cutoff?type=2&date=15
--   Type 3 (Weekly)        : /syscore/offlineprocess/per_cutoff?type=3&date=friday
--   Type 4 (Every 10 days) : /syscore/offlineprocess/per_cutoff?type=4&date=10
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- COMPANIES (cp_merchant) — one per group
-- ============================================================
INSERT INTO `cp_merchant`
  (`CP_ID`, `TIN`, `LegalName`, `TradingName`, `GroupTIN`, `GroupName`, `Address`,
   `MeanofPayment`, `PayeeCode`, `BankName`, `BankAccountNumber`, `PayeeName`, `PayeeId`,
   `MerchantFee`, `Industry`, `VATCond`, `InsertType`, `BankBranchCode`,
   `PayeeQtyOfDays`, `PayeeDayType`, `PayeeComments`,
   `AffiliateGroupCode`, `MerchantType`, `DIGITALSETTLEMENTTYPE`)
VALUES
-- Monthly group (wrecon=existing merchants 3,4; nrecon=new merchant 10)
(2001, '300-100-001-000', 'Monthly Test Corp',        'MonthlyTestCo',   '300-100-001-000', 'Test Group A', '100 Test St, Makati',
  'Bank Transfer', 'PAYEE-MO-001', 'BDO', '3001-0001-0001', 'Monthly Test', 'PID-MO-001',
  0.020000, 'Retail', 'Taxable', 'AUTO', 'BDO-TEST',
  3, 1, '', 'TESTA', '', 'NRECON'),

-- Semi-Monthly group (nrecon=existing merchant 677; wrecon=new merchant 11)
(2002, '300-100-002-000', 'SemiMonthly Test Corp',    'SemiMoTestCo',    '300-100-002-000', 'Test Group B', '200 Test St, Makati',
  'Bank Transfer', 'PAYEE-SM-001', 'BPI', '3002-0002-0002', 'SemiMo Test', 'PID-SM-001',
  0.015000, 'Retail', 'VAT-Exempt', 'AUTO', 'BPI-TEST',
  3, 1, '', 'TESTB', '', ''),

-- Weekly extra days group (wrecon=merchant 12; nrecon=merchant 13)
(2003, '300-100-003-000', 'Weekly Test Corp',         'WeeklyTestCo',    '300-100-003-000', 'Test Group C', '300 Test St, Makati',
  'Bank Transfer', 'PAYEE-WK-001', 'Metrobank', '3003-0003-0003', 'Weekly Test', 'PID-WK-001',
  0.018000, 'Retail', 'Taxable', 'AUTO', 'MBK-TEST',
  3, 1, '', 'TESTC', '', ''),

-- Every 10 days group (wrecon=merchant 14; nrecon=merchant 15)
(2004, '300-100-004-000', 'Every10Days Test Corp',    'Ten10TestCo',     '300-100-004-000', 'Test Group D', '400 Test St, Makati',
  'Bank Transfer', 'PAYEE-TD-001', 'BDO', '3004-0004-0004', 'Ten10 Test', 'PID-TD-001',
  0.020000, 'Retail', 'Taxable', 'AUTO', 'BDO-TEST2',
  3, 1, '', 'TESTD', '', 'NRECON');


-- ============================================================
-- BRANCHES — one branch per merchant
-- ============================================================
INSERT INTO `branches` (`BRANCH_ID`, `MERCHANT_ID`, `BRANCH_NAME`, `CP_ID`, `AFFILIATEGROUPCODE`) VALUES
('BR-MO-010', 10, 'MonthlyTestCo Branch',     2001, 'TESTA'),
('BR-SM-011', 11, 'SemiMoTestCo Branch',      2002, 'TESTB'),
('BR-WK-012', 12, 'WeeklyTestCo Wed Wrecon',  2003, 'TESTC'),
('BR-WK-013', 13, 'WeeklyTestCo Wed Nrecon',  2003, 'TESTC'),
('BR-TD-014', 14, 'Ten10TestCo Wrecon',       2004, 'TESTD'),
('BR-TD-015', 15, 'Ten10TestCo Nrecon',       2004, 'TESTD');


-- ============================================================
-- BRANCH-MERCHANT MAPPING
-- ============================================================
INSERT INTO `branch_merchant` (`MERCHANT_ID`, `BRANCH_ID`) VALUES
(10, 'BR-MO-010'),
(11, 'BR-SM-011'),
(12, 'BR-WK-012'),
(13, 'BR-WK-013'),
(14, 'BR-TD-014'),
(15, 'BR-TD-015');


-- ============================================================
-- PAYMENT CUTOFF CONFIG
--
-- Existing (from dis_db_sample_data.sql):
--   ID=1  MERCHANT_ID=1   Weekly    Sunday   ''     wrecon
--   ID=2  MERCHANT_ID=2   Weekly    Sunday   ''     nrecon (EVOUCHER after test_setup.sql)
--   ID=3  MERCHANT_ID=3   Monthly   ''       [15]   wrecon
--   ID=4  MERCHANT_ID=4   Monthly   ''       [15]   wrecon
--   ID=5  MERCHANT_ID=5   Weekly    Friday   ''     wrecon
--   ID=6  MERCHANT_ID=677 Semi-Monthly ''   {15,31} nrecon
--
-- New merchants added here:
-- ============================================================
INSERT INTO `payment_cutoff` (`MERCHANT_ID`, `TYPE`, `SPECIFIC_DAY`, `SPECIFIC_DATE`, `DigitalSettlementType`) VALUES
-- Type 1: Monthly + nrecon  (monthly wrecon already exists: merchants 3, 4)
(10,  'Monthly',       '',          '{1,5,10,15,30,31}', 'NRECON'),

-- Type 2: Semi-Monthly + wrecon  (semi-monthly nrecon already exists: merchant 677)
(11,  'Semi-Monthly',  '',          '{5,15,21,30,31}',   ''),

-- Type 3: Weekly extra days
(12,  'Weekly',        'Wednesday', '',                  ''),        -- wrecon
(13,  'Weekly',        'Wednesday', '',                  'NRECON'),  -- nrecon

-- Type 4: Every 10 days (completely new type — not yet in sample data)
(14,  'Every 10 days', '',          '{9,10,19,20,29,30}', ''),       -- wrecon
(15,  'Every 10 days', '',          '{9,10,19,20,29,30}', 'NRECON'); -- nrecon


-- ============================================================
-- REDEMPTIONS — RECONCILED, PA_ID=0 so they get picked up
-- Transaction dates within March 2026 cutoff windows:
--   Monthly   date=15  → txn between Mar 11 – Mar 15
--   Semi-Mo   date=15  → txn between Mar 6  – Mar 15
--   Weekly    Wed      → txn on or before last Wednesday (Mar 11)
--   Every10   date=10  → txn between Mar 1  – Mar 10
-- ============================================================
INSERT INTO `redemption`
  (`ID`, `REDEEM_ID`, `MERCHANT_ID`, `MERCHANT_NAME`, `BRANCH_ID`, `PROD_ID`, `VOUCHER_CODE`,
   `TRANSACTION_VALUE`, `STAGE`, `TRANSACTION_DATE_TIME`, `POS_ID`, `POS_TXN_ID`,
   `TRANSACTION_ID`, `PAYMENT_MODE`, `REFUND_ID`, `PA_ID`, `PA_TEMPID`)
VALUES
-- Merchant 10: Monthly nrecon, date=15
(100, 'RED-TEST-100', 10, 'MonthlyTestCo', 'BR-MO-010', 42, 'VOUCH-MO-001',  900.00, 'RECONCILED', '2026-03-12 09:00:00', 'POS-MO-010', 'PTXN-MO-001', 'TXN-TEST-100', 'DIGITAL', 0, 0, 0),
(101, 'RED-TEST-101', 10, 'MonthlyTestCo', 'BR-MO-010', 42, 'VOUCH-MO-002', 1100.00, 'RECONCILED', '2026-03-14 10:30:00', 'POS-MO-010', 'PTXN-MO-002', 'TXN-TEST-101', 'DIGITAL', 0, 0, 0),

-- Merchant 11: Semi-Monthly wrecon, date=15
(110, 'RED-TEST-110', 11, 'SemiMoTestCo', 'BR-SM-011', 42, 'VOUCH-SM-011',  500.00, 'RECONCILED', '2026-03-08 08:00:00', 'POS-SM-011', 'PTXN-SM-011', 'TXN-TEST-110', 'DIGITAL', 0, 0, 0),
(111, 'RED-TEST-111', 11, 'SemiMoTestCo', 'BR-SM-011', 42, 'VOUCH-SM-012',  750.00, 'RECONCILED', '2026-03-13 11:00:00', 'POS-SM-011', 'PTXN-SM-012', 'TXN-TEST-111', 'DIGITAL', 0, 0, 0),

-- Merchant 12: Weekly Wednesday wrecon (last Wed = Mar 11)
(120, 'RED-TEST-120', 12, 'WeeklyTestCo Wed Wrecon', 'BR-WK-012', 42, 'VOUCH-WK-012',  400.00, 'RECONCILED', '2026-03-09 09:00:00', 'POS-WK-012', 'PTXN-WK-012', 'TXN-TEST-120', 'DIGITAL', 0, 0, 0),
(121, 'RED-TEST-121', 12, 'WeeklyTestCo Wed Wrecon', 'BR-WK-012', 42, 'VOUCH-WK-013',  600.00, 'RECONCILED', '2026-03-11 14:00:00', 'POS-WK-012', 'PTXN-WK-013', 'TXN-TEST-121', 'DIGITAL', 0, 0, 0),

-- Merchant 13: Weekly Wednesday nrecon (last Wed = Mar 11)
(130, 'RED-TEST-130', 13, 'WeeklyTestCo Wed Nrecon', 'BR-WK-013', 42, 'VOUCH-WK-014',  350.00, 'RECONCILED', '2026-03-09 10:00:00', 'POS-WK-013', 'PTXN-WK-014', 'TXN-TEST-130', 'DIGITAL', 0, 0, 0),
(131, 'RED-TEST-131', 13, 'WeeklyTestCo Wed Nrecon', 'BR-WK-013', 42, 'VOUCH-WK-015',  450.00, 'RECONCILED', '2026-03-11 15:00:00', 'POS-WK-013', 'PTXN-WK-015', 'TXN-TEST-131', 'DIGITAL', 0, 0, 0),

-- Merchant 14: Every 10 days wrecon, date=10
(140, 'RED-TEST-140', 14, 'Ten10TestCo Wrecon', 'BR-TD-014', 42, 'VOUCH-TD-014',  700.00, 'RECONCILED', '2026-03-03 08:00:00', 'POS-TD-014', 'PTXN-TD-014', 'TXN-TEST-140', 'DIGITAL', 0, 0, 0),
(141, 'RED-TEST-141', 14, 'Ten10TestCo Wrecon', 'BR-TD-014', 42, 'VOUCH-TD-015',  800.00, 'RECONCILED', '2026-03-08 09:30:00', 'POS-TD-014', 'PTXN-TD-015', 'TXN-TEST-141', 'DIGITAL', 0, 0, 0),

-- Merchant 15: Every 10 days nrecon, date=10
(150, 'RED-TEST-150', 15, 'Ten10TestCo Nrecon', 'BR-TD-015', 42, 'VOUCH-TD-016',  950.00, 'RECONCILED', '2026-03-05 10:00:00', 'POS-TD-015', 'PTXN-TD-016', 'TXN-TEST-150', 'DIGITAL', 0, 0, 0),
(151, 'RED-TEST-151', 15, 'Ten10TestCo Nrecon', 'BR-TD-015', 42, 'VOUCH-TD-017', 1050.00, 'RECONCILED', '2026-03-09 11:00:00', 'POS-TD-015', 'PTXN-TD-017', 'TXN-TEST-151', 'DIGITAL', 0, 0, 0);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- FULL MERCHANT MATRIX AFTER RUNNING ALL 3 SQL FILES:
--
-- ID  MERCHANT_ID  TYPE           DAY/DATE             RECON    TEST URL
-- --  -----------  -------------  -------------------  -------  -----------------------------------------------
--  1  1            Weekly         Sunday               wrecon   /per_cutoff?type=3&date=sunday
--  2  2            Weekly         Sunday               nrecon   /per_cutoff?type=3&date=sunday
--  3  3            Monthly        [15]                 wrecon   /per_cutoff?type=1&date=15
--  4  4            Monthly        [15]                 wrecon   /per_cutoff?type=1&date=15
--  5  5            Weekly         Friday               wrecon   /per_cutoff?type=3&date=friday
--  6  677          Semi-Monthly   {15,31}              nrecon   /per_cutoff?type=2&date=15
--  7  10           Monthly        {1,5,10,15,30,31}   nrecon   /per_cutoff?type=1&date=15
--  8  11           Semi-Monthly   {5,15,21,30,31}     wrecon   /per_cutoff?type=2&date=15
--  9  12           Weekly         Wednesday            wrecon   /per_cutoff?type=3&date=wednesday
-- 10  13           Weekly         Wednesday            nrecon   /per_cutoff?type=3&date=wednesday
-- 11  14           Every 10 days  {9,10,19,20,29,30}  wrecon   /per_cutoff?type=4&date=10
-- 12  15           Every 10 days  {9,10,19,20,29,30}  nrecon   /per_cutoff?type=4&date=10
--
-- per_merchantID test URLs:
--   /per_cutoff?process=10   → Monthly nrecon
--   /per_cutoff?process=11   → Semi-Monthly wrecon
--   /per_cutoff?process=12   → Weekly Wednesday wrecon
--   /per_cutoff?process=13   → Weekly Wednesday nrecon
--   /per_cutoff?process=14   → Every 10 days wrecon
--   /per_cutoff?process=15   → Every 10 days nrecon
-- ============================================================
