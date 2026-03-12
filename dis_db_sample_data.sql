-- ============================================================
-- DIS Sample Data — covers all major app flows
-- ============================================================
-- PASSWORDS  (SHA1)
--   admin     → 'p@55123'  (default)
--   password: sha1('p@55123') = 805693f0a06d183b5cfc4cee08d5e7f572a030da
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- CLEAN SLATE — truncate all sample-data tables so re-running
-- this script on an existing DB always yields a consistent state
-- ============================================================
TRUNCATE TABLE `audit_upload`;
TRUNCATE TABLE `audit_trail`;
TRUNCATE TABLE `nav_detail`;
TRUNCATE TABLE `nav_header`;
TRUNCATE TABLE `rs_detail`;
TRUNCATE TABLE `rs_header`;
TRUNCATE TABLE `conversion`;
TRUNCATE TABLE `pa_detail`;
TRUNCATE TABLE `pa_header`;
TRUNCATE TABLE `temp_refund`;
TRUNCATE TABLE `refund`;
TRUNCATE TABLE `reconcilation`;
TRUNCATE TABLE `redemption`;
TRUNCATE TABLE `payment_cutoff`;
TRUNCATE TABLE `branch_merchant`;
TRUNCATE TABLE `branches`;
TRUNCATE TABLE `cp_merchant`;
TRUNCATE TABLE `cp_product`;
TRUNCATE TABLE `password_history`;
TRUNCATE TABLE `access_permission`;
TRUNCATE TABLE `access_role`;
TRUNCATE TABLE `user`;
TRUNCATE TABLE `utype`;


-- ============================================================
-- USER MANAGEMENT
-- ============================================================

-- User Types
-- utype_code: short code used programmatically
-- utype_desc: display label used by the app (e.g. audit.php, Login-pp.php -> utype_desc)
INSERT INTO `utype` (`utype_id`, `utype_code`, `utype_desc`) VALUES
(1, 'ADMIN',  'Admin'),
(2, 'REIMB',  'Reimbursement'),
(3, 'RDONLY', 'Read Only'),
(4, 'FIN',    'Finance');


-- Users  (all passwords = sha1('p@55123') = p@55123)
INSERT INTO `user` (`user_id`, `utype_id`, `user_name`, `full_name`, `email`, `password`, `status`, `activation_code`) VALUES
(1, 1, 'admin',        'System Administrator',   'admin@dis.local',   '805693f0a06d183b5cfc4cee08d5e7f572a030da', 1, ''),
(2, 2, 'reimb.user',   'Reimbursement Staff',    'reimb@dis.local',   '805693f0a06d183b5cfc4cee08d5e7f572a030da', 1, ''),
(3, 3, 'cs.user',      'Customer Service Staff', 'cs@dis.local',      '805693f0a06d183b5cfc4cee08d5e7f572a030da', 1, ''),
(4, 4, 'finance.user', 'Finance Officer',        'finance@dis.local', '805693f0a06d183b5cfc4cee08d5e7f572a030da', 1, ''),
(5, 1, 'inactive.usr', 'Inactive Test User',     'inactive@dis.local','805693f0a06d183b5cfc4cee08d5e7f572a030da', 0, 'ac1234567890abcdef');


-- Access Roles (modules)
-- acc_id matches the module IDs used in My_Layout.php::user_permission()
-- acc_name (NOT NULL in prod schema) added; app routes on acc_code
INSERT INTO `access_role` (`acc_id`, `acc_name`, `acc_code`) VALUES
(1, 'Transaction',  'transaction'),   -- BIN/card transaction tracking
(2, 'Process',      'process'),       -- Payment Advice (PA) processing
(3, 'Summary',      'summary'),       -- PA Summary reports
(4, 'RS Process',   'rs_process'),    -- Reimbursement Settlement processing
(5, 'Admin',        'admin'),         -- Admin panel
(6, 'RS Summary',   'rs_summary'),    -- RS Summary reports
(7, 'Conversion',   'conversion');    -- Digital Conversion vouchers


-- Access Permissions
-- Admin (user 1) → all modules, full access, default = admin module
INSERT INTO `access_permission` (`user_id`, `acc_id`, `acc_read_only`, `acc_all_access`, `def_page`) VALUES
(1, 1, 0, 1, 0),
(1, 2, 0, 1, 0),
(1, 3, 0, 1, 0),
(1, 4, 0, 1, 0),
(1, 5, 0, 1, 1),  -- def_page = admin
(1, 6, 0, 1, 0),
(1, 7, 0, 1, 0),

-- Reimbursement user (user 2) → process + rs_process full, rest read-only, default = process
(2, 1, 1, 0, 0),
(2, 2, 0, 1, 1),  -- def_page = process
(2, 3, 1, 0, 0),
(2, 4, 0, 1, 0),
(2, 6, 1, 0, 0),
(2, 7, 1, 0, 0),

-- Read-only user (user 3) → transaction + conversion read-only, default = transaction
(3, 1, 1, 0, 1),  -- def_page = transaction
(3, 7, 1, 0, 0),

-- Finance user (user 4) → summary read-only + rs_summary, default = summary
(4, 2, 1, 0, 0),
(4, 3, 1, 0, 1),  -- def_page = summary
(4, 4, 1, 0, 0),
(4, 6, 1, 0, 0);


-- Password History (so password-change policy can be tested)
INSERT INTO `password_history` (`user_id`, `password_hash`, `last_password_change_date`) VALUES
(1, '805693f0a06d183b5cfc4cee08d5e7f572a030da', '2026-01-01'),
(2, '805693f0a06d183b5cfc4cee08d5e7f572a030da', '2026-01-15'),
(3, '805693f0a06d183b5cfc4cee08d5e7f572a030da', '2026-02-01'),
(4, '805693f0a06d183b5cfc4cee08d5e7f572a030da', '2026-02-10');


-- ============================================================
-- MERCHANT & PRODUCT SETUP
-- ============================================================

-- Products
INSERT INTO `cp_product` (`SERVICE_ID`, `SERVICE_NAME`) VALUES
(42, 'Pluxee Digital Voucher'),
(43, 'Pluxee Gift Certificate');


-- Merchants
-- PayeeId (NOT NULL in prod schema) added; used by Corepass_model.php NAV export queries
INSERT INTO `cp_merchant`
  (`CP_ID`, `TIN`, `LegalName`, `TradingName`, `GroupTIN`, `GroupName`, `Address`,
   `MeanofPayment`, `PayeeCode`, `BankName`, `BankAccountNumber`, `PayeeName`, `PayeeId`,
   `MerchantFee`, `Industry`, `VATCond`, `InsertType`, `BankBranchCode`,
   `PayeeQtyOfDays`, `PayeeDayType`, `PayeeComments`,
   `AffiliateGroupCode`, `MerchantType`, `DIGITALSETTLEMENTTYPE`)
VALUES
(1001, '123-456-789-000', 'Golden Arches Dev Corp',       'McDonalds PH',   '123-456-789-000', 'QSR Group', 'Mckinley Pkwy, BGC Taguig',
   'Bank Transfer', 'PAYEE-MC-001', 'BDO', '1234-5678-9012', 'Golden Arches', 'PID-MC-001',
   0.020000, 'Food Service', 'Taxable', 'AUTO', 'BDO-BGC',
   3, 1, 'Standard 3 business days',
   'GADC', '', ''),

(1002, '987-654-321-000', '7-Eleven Philippines Inc',     '7-Eleven PH',    '987-654-321-000', 'CVS Group', '7-Eleven Bldg, Pasig City',
   'Bank Transfer', 'PAYEE-7E-001', 'BPI', '9876-5432-1098', '7-Eleven PH', 'PID-7E-001',
   0.015000, 'Convenience Store', 'VAT-Exempt', 'AUTO', 'BPI-ORTIGAS',
   5, 0, 'Standard 5 calendar days',
   'CVSG', '', ''),

(1003, '111-222-333-000', 'Robinsons Retail Holdings Inc','Robinsons Supermarket','111-222-333-000','Robinsons Group','Robinson Galleria, Ortigas',
   'Bank Transfer', 'PAYEE-RR-001', 'Metrobank', '1111-2222-3333', 'Robinsons Retail', 'PID-RR-001',
   0.018000, 'Supermarket', 'Taxable', 'AUTO', 'MBK-ORTIGAS',
   3, 1, '',
   'RRHI', 'Merchant Dormancy', '');  -- dormancy type for testing dormancy filter


-- Branches
INSERT INTO `branches` (`BRANCH_ID`, `MERCHANT_ID`, `BRANCH_NAME`, `CP_ID`, `AFFILIATEGROUPCODE`) VALUES
('BR-MC-001', 1, 'McDo Makati Ayala',    1001, 'GADC'),
('BR-MC-002', 2, 'McDo BGC High Street', 1001, 'GADC'),
('BR-7E-001', 3, '7-Eleven Ortigas',     1002, 'CVSG'),
('BR-7E-002', 4, '7-Eleven Pasig',       1002, 'CVSG'),
('BR-RR-001', 5, 'Robinsons Galleria',   1003, 'RRHI');


-- Branch–Merchant Mapping
INSERT INTO `branch_merchant` (`MERCHANT_ID`, `BRANCH_ID`) VALUES
(1, 'BR-MC-001'),
(2, 'BR-MC-002'),
(3, 'BR-7E-001'),
(4, 'BR-7E-002'),
(5, 'BR-RR-001');


-- Payment Cutoffs
INSERT INTO `payment_cutoff` (`MERCHANT_ID`, `TYPE`, `SPECIFIC_DAY`, `SPECIFIC_DATE`, `DigitalSettlementType`) VALUES
(1, 'Weekly',   'Sunday',   '',    ''),
(2, 'Weekly',   'Sunday',   '',    ''),
(3, 'Monthly',  '',         '[15]', ''),
(4, 'Monthly',  '',         '[15]', ''),
(5, 'Weekly',   'Friday',   '',    '');


-- ============================================================
-- INTERNAL ORDER MANAGEMENT (BIN/Card Tracking)
-- NOTE: tables below (companies, order_list, location, statorder,
-- statcategory, statreason, delsched_string, binloc, binloc_log,
-- delsched, transac, resoa, release_order, advance_soa,
-- co_orderinfo, co_transac) do NOT exist in prod schema — removed
-- ============================================================


-- ============================================================
-- DIGITAL TRANSACTION FLOW
-- redemption → reconcilation → refund → pa_header/pa_detail
-- ============================================================

-- REDEMPTIONS
-- PA1 covers RED-001 and RED-002 (RECONCILED)
-- RED-003 is RECONCILED then REVERSED (has refund)
-- RED-004, RED-005 are REDEEMED (uploaded, pending reconciliation)
-- MERCHANT_NAME, POS_TXN_ID, TRANSACTION_ID added (all NOT NULL in prod schema)
INSERT INTO `redemption`
  (`ID`, `REDEEM_ID`, `MERCHANT_ID`, `MERCHANT_NAME`, `BRANCH_ID`, `PROD_ID`, `VOUCHER_CODE`,
   `TRANSACTION_VALUE`, `STAGE`, `TRANSACTION_DATE_TIME`, `POS_ID`, `POS_TXN_ID`,
   `TRANSACTION_ID`, `PAYMENT_MODE`, `REFUND_ID`, `PA_ID`, `PA_TEMPID`)
VALUES
-- Reconciled — under PA 1
(1,  'RED-2026-001', 1, 'McDonalds PH', 'BR-MC-001', 42, 'VOUCH-MC-001',  500.00, 'RECONCILED', '2026-01-05 10:00:00', 'POS-MC-001', 'POSTXN-MC-001', 'TXN-2026-001', 'DIGITAL', 0, 1, 0),
(2,  'RED-2026-002', 1, 'McDonalds PH', 'BR-MC-001', 42, 'VOUCH-MC-002',  750.00, 'RECONCILED', '2026-01-06 11:00:00', 'POS-MC-001', 'POSTXN-MC-002', 'TXN-2026-002', 'DIGITAL', 0, 1, 0),
(3,  'RED-2026-003', 2, 'McDonalds PH', 'BR-MC-002', 42, 'VOUCH-MC-003',  300.00, 'RECONCILED', '2026-01-07 09:30:00', 'POS-MC-002', 'POSTXN-MC-003', 'TXN-2026-003', 'DIGITAL', 0, 2, 0),
(4,  'RED-2026-004', 2, 'McDonalds PH', 'BR-MC-002', 42, 'VOUCH-MC-004',  600.00, 'RECONCILED', '2026-01-08 14:00:00', 'POS-MC-002', 'POSTXN-MC-004', 'TXN-2026-004', 'DIGITAL', 0, 2, 0),
-- Reversed — under PA 1 → has refund
(5,  'RED-2026-005', 1, 'McDonalds PH', 'BR-MC-001', 42, 'VOUCH-MC-005',  400.00, 'REVERSED',   '2026-01-09 10:00:00', 'POS-MC-001', 'POSTXN-MC-005', 'TXN-2026-005', 'DIGITAL', 1, 0, 0),
-- Already in PA but with REFUND too (post-PA reversal)
(6,  'RED-2026-006', 1, 'McDonalds PH', 'BR-MC-001', 42, 'VOUCH-MC-006',  250.00, 'REVERSED',   '2026-01-10 10:00:00', 'POS-MC-001', 'POSTXN-MC-006', 'TXN-2026-006', 'DIGITAL', 2, 1, 0),
-- Merchant 3 (7-Eleven) — Reconciled, under PA 3
(7,  'RED-2026-007', 3, '7-Eleven PH',  'BR-7E-001', 42, 'VOUCH-7E-001',  200.00, 'RECONCILED', '2026-01-12 08:00:00', 'POS-7E-001', 'POSTXN-7E-001', 'TXN-2026-007', 'DIGITAL', 0, 3, 0),
(8,  'RED-2026-008', 3, '7-Eleven PH',  'BR-7E-001', 42, 'VOUCH-7E-002',  350.00, 'RECONCILED', '2026-01-13 09:00:00', 'POS-7E-001', 'POSTXN-7E-002', 'TXN-2026-008', 'DIGITAL', 0, 3, 0),
-- Redeemed — uploaded but not yet reconciled (pending)
(9,  'RED-2026-009', 1, 'McDonalds PH', 'BR-MC-001', 42, 'VOUCH-MC-009',  500.00, 'REDEEMED',   '2026-02-01 10:00:00', 'POS-MC-001', 'POSTXN-MC-009', 'TXN-2026-009', 'DIGITAL', 0, 0, 0),
(10, 'RED-2026-010', 3, '7-Eleven PH',  'BR-7E-001', 43, 'VOUCH-7E-010', 1000.00, 'REDEEMED',   '2026-02-02 11:00:00', 'POS-7E-001', 'POSTXN-7E-010', 'TXN-2026-010', 'DIGITAL', 0, 0, 0),
-- Void — cancelled transaction
(11, 'RED-2026-011', 4, '7-Eleven PH',  'BR-7E-002', 42, 'VOUCH-7E-011',  100.00, 'VOID',       '2026-02-03 12:00:00', 'POS-7E-002', 'POSTXN-7E-011', 'TXN-2026-011', 'DIGITAL', 0, 0, 0);


-- RECONCILIATIONS
-- MERCHANT_NAME, POS_ID, POS_TXN_ID, VOUCHER_CODE added (all NOT NULL in prod schema)
INSERT INTO `reconcilation`
  (`ID`, `RECON_ID`, `REDEEM_ID`, `MERCHANT_NAME`, `MERCHANT_ID`, `BRANCH_ID`, `PROD_ID`,
   `POS_ID`, `POS_TXN_ID`, `VOUCHER_CODE`,
   `TRANSACTION_VALUE`, `RECON_DATE_TIME`, `TRANSACTION_DATE_TIME`,
   `REFUND_ID`, `PA_ID`, `payment_mode`, `REDEEM_TBL_ID`, `STAGE`, `PA_TEMPID`)
VALUES
-- Under PA 1 (Merchant 1, BR-MC-001)
(1, 'RECON-2026-001', 'RED-2026-001', 'McDonalds PH', 1, 'BR-MC-001', 42, 'POS-MC-001', 'POSTXN-MC-001', 'VOUCH-MC-001',  500.00, '2026-01-15 08:00:00', '2026-01-05 10:00:00', 0, 1, 'DIGITAL', 1,  'RECONCILED', 0),
(2, 'RECON-2026-002', 'RED-2026-002', 'McDonalds PH', 1, 'BR-MC-001', 42, 'POS-MC-001', 'POSTXN-MC-002', 'VOUCH-MC-002',  750.00, '2026-01-15 08:05:00', '2026-01-06 11:00:00', 0, 1, 'DIGITAL', 2,  'RECONCILED', 0),
-- Under PA 2 (Merchant 2, BR-MC-002)
(3, 'RECON-2026-003', 'RED-2026-003', 'McDonalds PH', 2, 'BR-MC-002', 42, 'POS-MC-002', 'POSTXN-MC-003', 'VOUCH-MC-003',  300.00, '2026-01-16 08:00:00', '2026-01-07 09:30:00', 0, 2, 'DIGITAL', 3,  'RECONCILED', 0),
(4, 'RECON-2026-004', 'RED-2026-004', 'McDonalds PH', 2, 'BR-MC-002', 42, 'POS-MC-002', 'POSTXN-MC-004', 'VOUCH-MC-004',  600.00, '2026-01-16 08:10:00', '2026-01-08 14:00:00', 0, 2, 'DIGITAL', 4,  'RECONCILED', 0),
-- REVERSED — pre-PA reversal (PA_ID=0, REFUND_ID=1)
(5, 'RECON-2026-005', 'RED-2026-005', 'McDonalds PH', 1, 'BR-MC-001', 42, 'POS-MC-001', 'POSTXN-MC-005', 'VOUCH-MC-005',  400.00, '2026-01-17 08:00:00', '2026-01-09 10:00:00', 1, 0, 'DIGITAL', 5,  'REVERSED',   0),
-- REVERSED — post-PA reversal (PA_ID=1, REFUND_ID=2)
(6, 'RECON-2026-006', 'RED-2026-006', 'McDonalds PH', 1, 'BR-MC-001', 42, 'POS-MC-001', 'POSTXN-MC-006', 'VOUCH-MC-006',  250.00, '2026-01-17 08:30:00', '2026-01-10 10:00:00', 2, 1, 'DIGITAL', 6,  'REVERSED',   0),
-- Under PA 3 (Merchant 3 — 7-Eleven)
(7, 'RECON-2026-007', 'RED-2026-007', '7-Eleven PH',  3, 'BR-7E-001', 42, 'POS-7E-001', 'POSTXN-7E-001', 'VOUCH-7E-001',  200.00, '2026-01-20 08:00:00', '2026-01-12 08:00:00', 0, 3, 'DIGITAL', 7,  'RECONCILED', 0),
(8, 'RECON-2026-008', 'RED-2026-008', '7-Eleven PH',  3, 'BR-7E-001', 42, 'POS-7E-001', 'POSTXN-7E-002', 'VOUCH-7E-002',  350.00, '2026-01-20 08:05:00', '2026-01-13 09:00:00', 0, 3, 'DIGITAL', 8,  'RECONCILED', 0);


-- REFUNDS (reversals)
-- REFUND 1: pre-PA reversal (PA_ID=0) — should appear in "pending" refund list
-- REFUND 2: post-PA reversal (PA_ID=1) — already has a PA assigned
INSERT INTO `refund`
  (`REFUND_ID`, `REDEEM_ID`, `REVERSAL_TRANSACTION_ID`, `TRANSACTION_ID`, `RECON_ID`,
   `REVERSAL_MODE`, `PROD_ID`, `REDEEM_STATUS`, `MERCHANT_ID`, `BRANCH_ID`, `USER_ID`,
   `UPLOAD_ID`, `REVERSAL_DATE_TIME`, `DATE_CREATED`, `PA_ID`, `REDEEM_TBL_ID`, `RECON_TBL_ID`, `PA_TEMPID`)
VALUES
(1, 'RED-2026-005', 'REV-TXN-2026-001', 'TXN-2026-005', 'RECON-2026-005',
   'FULL', 42, 'REVERSED', 1, 'BR-MC-001', 1,
   1, '2026-01-17 10:00:00', '2026-01-17 10:00:00', 0, 5, 5, 0),

(2, 'RED-2026-006', 'REV-TXN-2026-002', 'TXN-2026-006', 'RECON-2026-006',
   'FULL', 42, 'REVERSED', 1, 'BR-MC-001', 1,
   1, '2026-01-18 10:00:00', '2026-01-18 10:00:00', 1, 6, 6, 0);


-- TEMP REFUND (staging table — simulates uploaded reversal file pending processing)
INSERT INTO `temp_refund`
  (`REFUND_ID`, `MERCHANT_NAME`, `MERCHANT_ID`, `BRANCH_ID`, `POS_ID`, `POS_TXN_ID`,
   `PROD_ID`, `TRANSACTION_DATE_TIME`, `TRANSACTION_ID`, `REDEMPTION_API_TRANSACTION_ID`,
   `REVERSAL_DATE_TIME`, `REVERSAL_TRANSACTION_ID`, `VOUCHER_CODE`, `TRANSACTION_VALUE`,
   `RECON_API_TRANSACTION_ID`, `PAYMENT_MODE`, `REVERSAL_MODE`, `UPLOAD_ID`, `ERROR_MESSAGE`)
VALUES
(0, 'McDonalds PH', '1', 'BR-MC-001', 'POS-MC-001', 'POS-TXN-9001',
   '42', '2026-02-01 10:00:00', 'TXN-2026-NEW-001', 'RED-API-NEW-001',
   '2026-02-05 09:00:00', 'REV-TXN-NEW-001', 'VOUCH-MC-NEW', '500.00',
   'RECON-API-NEW-001', 'DIGITAL', 'FULL', 2, '');


-- ============================================================
-- PAYMENT ADVICE (PA) TABLES
-- ============================================================

-- PA Headers
-- GENERATED added (NOT NULL in prod schema; 0=generated, 1=copy)
INSERT INTO `pa_header` (`PA_ID`, `MERCHANT_ID`, `USER_ID`, `DATE_CREATED`, `REIMBURSEMENT_DATE`, `ExpectedDueDate`, `MERCHANT_FEE`, `vatCond`, `GENERATED`) VALUES
(1, 1, 2, '2026-01-20 09:00:00', '2026-01-20 09:00:00', '2026-01-23', 0.020000, 'Taxable',    0),  -- McDo BR-MC-001 (3 biz days)
(2, 2, 2, '2026-01-21 09:00:00', '2026-01-21 09:00:00', '2026-01-24', 0.020000, 'Taxable',    0),  -- McDo BR-MC-002
(3, 3, 2, '2026-01-25 09:00:00', '2026-01-25 09:00:00', '2026-01-30', 0.015000, 'VAT-Exempt', 0);  -- 7-Eleven


-- PA Details  (RECON_ID links to reconcilation.RECON_ID)
-- PA 1 covers RECON-001, RECON-002 (and RECON-006 post-PA reversal is also under PA 1)
-- TOTAL_REFUND added (NOT NULL in prod schema)
INSERT INTO `pa_detail` (`PA_DID`, `PA_ID`, `RECON_ID`, `RATE`, `NUM_PASSES`, `TOTAL_FV`, `MARKETING_FEE`, `VAT`, `NET_DUE`, `TOTAL_REFUND`, `BRANCH_ID`) VALUES
-- PA 1 — McDo Makati
(1, 1, 'RECON-2026-001', 0.020000, 1,  500.00, 10.00, 1.20,  490.80, 0.00000, 'BR-MC-001'),
(2, 1, 'RECON-2026-002', 0.020000, 1,  750.00, 15.00, 1.80,  735.00, 0.00000, 'BR-MC-001'),
-- PA 2 — McDo BGC
(3, 2, 'RECON-2026-003', 0.020000, 1,  300.00,  6.00, 0.72,  294.00, 0.00000, 'BR-MC-002'),
(4, 2, 'RECON-2026-004', 0.020000, 1,  600.00, 12.00, 1.44,  588.00, 0.00000, 'BR-MC-002'),
-- PA 3 — 7-Eleven Ortigas
(5, 3, 'RECON-2026-007', 0.015000, 1,  200.00,  3.00, 0.00,  197.00, 0.00000, 'BR-7E-001'),
(6, 3, 'RECON-2026-008', 0.015000, 1,  350.00,  5.25, 0.00,  344.75, 0.00000, 'BR-7E-001');


-- ============================================================
-- CONVERSION & REIMBURSEMENT SETTLEMENT (RS) — Digital flow
-- ============================================================

-- CHANNEL added (NOT NULL in prod schema)
-- AGENT_ID changed from string ('AGT-001') to int (prod schema: int NOT NULL)
INSERT INTO `conversion`
  (`COV_ID`, `MERCHANT_ID`, `BRANCH_ID`, `BRANCH_NAME`, `PROD_ID`, `VOUCHER_CODES`,
   `AGENT_ID`, `DENO`, `TOTAL_AMOUNT`, `STAGE`, `CHANNEL`, `RS_ID`, `USER_ID`, `NAME`, `CREATED_AT`)
VALUES
(1, 1, 'BR-MC-001', 'McDo Makati Ayala',    42, 'VOUCH-MC-CONV-001,VOUCH-MC-CONV-002',
   1, 500.00, 1000.00, 'CONVERTED', 'DIGITAL', 1, 1, 'Juan Dela Cruz', '2026-01-10 08:00:00'),
(2, 1, 'BR-MC-001', 'McDo Makati Ayala',    42, 'VOUCH-MC-CONV-003',
   1, 500.00,  500.00, 'CONVERTED', 'DIGITAL', 1, 1, 'Juan Dela Cruz', '2026-01-10 09:00:00'),
(3, 3, 'BR-7E-001', '7-Eleven Ortigas',     42, 'VOUCH-7E-CONV-001',
   2, 200.00,  200.00, 'CONVERTED', 'DIGITAL', 0, 1, 'Pedro Cruz',     '2026-02-01 08:00:00');
   -- RS_ID=0 means not yet settled — will show in pending RS list


-- RS Headers
-- GENERATED added (NOT NULL in prod schema; 0=generated, 1=copy)
INSERT INTO `rs_header` (`RS_ID`, `BRANCH_ID`, `MERCHANT_ID`, `DATE_CREATED`, `REIMBURSEMENT_DATE`, `USER_ID`, `MERCHANT_FEE`, `VATCOND`, `ExpectedDueDate`, `RS_NUMBER`, `GENERATED`) VALUES
(1, 'BR-MC-001', 1, '2026-01-20 09:00:00', '2026-01-20 09:00:00', 1, 0.020000, 'Taxable', '2026-01-23', 'RS-2026-01-001', 0);


-- RS Details
INSERT INTO `rs_detail` (`RS_ID`, `COV_ID`, `RATE`, `TOTAL_FV`, `MARKETING_FEE`, `VAT`, `NET_DUE`, `DATE_CREATED`) VALUES
(1, 1, 0.020000, 1000.00, 20.00, 2.40,  980.00, '2026-01-20 09:00:00'),
(1, 2, 0.020000,  500.00, 10.00, 1.20,  490.80, '2026-01-20 09:00:00');


-- ============================================================
-- NAV (Navision/ERP Export) TABLES
-- ============================================================

INSERT INTO `nav_header` (`NAVH_ID`, `CP_ID`, `MERCHANT_ID`, `PA_ID`, `RECON_ID`, `PROD_ID`, `TotalAmount`, `DateofReceipt`, `ExpectedDueDate`) VALUES
(1, 1001, 1, 1, 'RECON-2026-001', 42, 1250.00, '2026-01-20 09:00:00', '2026-01-23 17:00:00'),
(2, 1002, 3, 3, 'RECON-2026-007', 42,  550.00, '2026-01-25 09:00:00', '2026-01-30 17:00:00');

INSERT INTO `nav_detail` (`NAVH_ID`, `PROD_ID`, `BillItem`, `FaceValue`, `OutputVAT`, `VATCond`) VALUES
(1, 42, 25.00, 1250.00, 0.00, 3.00),
(2, 42,  8.25,  550.00, 0.00, 0.00);


-- ============================================================
-- AUDIT TRAIL
-- ============================================================

-- audit_trail prod schema columns: action, user_id, pa_id, logist_id, dist_status
-- (dev schema had module_id/target_id/order_id/auc_id — none of these exist in prod)
INSERT INTO `audit_trail` (`action`, `user_id`, `pa_id`, `logist_id`, `dist_status`) VALUES
(0, 1, 0, 0, 0),  -- login: admin
(0, 2, 0, 0, 0),  -- login: reimb.user
(2, 1, 1, 0, 0),  -- add: PA 1 created
(2, 2, 2, 0, 0),  -- add: PA 2 created
(2, 2, 3, 0, 0),  -- add: PA 3 created
(2, 1, 0, 0, 0),  -- add: RS created
(3, 1, 1, 0, 1);  -- update: PA 1 status change


-- ============================================================
-- FILE UPLOAD LOG
-- ============================================================
-- NOTE: file_upload table does not exist in prod schema — insert removed

INSERT INTO `audit_upload` (`module_name`, `file_name`) VALUES
('reconciliation', 'recon_upload_2026_01_15.csv'),
('refund',         'refund_upload_2026_01_17.csv');


SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- QUICK REFERENCE
-- ============================================================
-- Login credentials (username / password):
--   admin        / p@55123   (Admin — full access, default page: admin)
--   reimb.user   / p@55123   (Reimbursement — PA + RS process, default: process)
--   cs.user      / p@55123   (Read-only — transaction + conversion)
--   finance.user / p@55123   (Finance — summary read-only, default: summary)
--   inactive.usr / p@55123   (Inactive — needs activation to log in)
--
-- Key test flows:
--   1. Login          → test all 4 user types + inactive block
--   2. Transaction    → order 1001/1002/1003 in binloc tracking
--   3. Redeem view    → RED-2026-001..011 (various stages)
--   4. Recon view     → RECON-2026-001..008
--   5. PA (process)   → PA 1 (McDo Makati), PA 2 (McDo BGC), PA 3 (7-Eleven)
--   6. Reversal       → REFUND 1 (pre-PA), REFUND 2 (post-PA)
--   7. RS process     → COV 3 (7-Eleven, RS_ID=0, pending settlement)
--   8. RS summary     → RS 1 (McDo Makati)
--   9. NAV export     → nav_header 1 & 2
--  10. Dormancy       → Merchant 1003 (Robinsons, MerchantType=Merchant Dormancy)
--  11. Admin panel    → manage users 1-5, access roles, merchant setup
-- ============================================================
