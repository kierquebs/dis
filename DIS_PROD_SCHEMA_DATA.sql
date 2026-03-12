-- ============================================================
-- DIS Production Schema — Sample Data
-- Target schema : DIS_PROD_SCHEMA.sql  (MariaDB 10.1.38)
-- Generated     : 2026-03-12
-- ============================================================
-- Passwords (SHA1):  'p@55123' → 805693f0a06d183b5cfc4cee08d5e7f572a030da
-- ============================================================
-- Insert order (FK dependency):
--   branch_merchant → cp_merchant → branches
--   → redemption → reconcilation → refund
--   → pa_header → pa_detail
--   → rs_header → rs_detail → conversion
--   → nav_header → nav_detail
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- USER MANAGEMENT
-- ============================================================

-- utype
-- Prod schema: utype_id, utype_code (NOT NULL), utype_desc (NOT NULL)
-- NOTE: dev schema uses `utype_name` — prod uses `utype_code` + `utype_desc`
INSERT INTO `utype` (`utype_id`, `utype_code`, `utype_desc`) VALUES
(1, 'ADMIN',  'Administrator'),
(2, 'REIMB',  'Reimbursement'),
(3, 'RDONLY', 'Read Only'),
(4, 'FIN',    'Finance');


-- user
INSERT INTO `user`
  (`user_id`, `utype_id`, `email`, `user_name`, `password`, `full_name`, `status`, `activation_code`)
VALUES
(1, 1, 'admin@dis.local',    'admin',        '805693f0a06d183b5cfc4cee08d5e7f572a030da', 'System Administrator',   1, ''),
(2, 2, 'reimb@dis.local',    'reimb.user',   '805693f0a06d183b5cfc4cee08d5e7f572a030da', 'Reimbursement Staff',    1, ''),
(3, 3, 'cs@dis.local',       'cs.user',      '805693f0a06d183b5cfc4cee08d5e7f572a030da', 'Customer Service Staff', 1, ''),
(4, 4, 'finance@dis.local',  'finance.user', '805693f0a06d183b5cfc4cee08d5e7f572a030da', 'Finance Officer',        1, ''),
(5, 1, 'inactive@dis.local', 'inactive.usr', '805693f0a06d183b5cfc4cee08d5e7f572a030da', 'Inactive Test User',     0, 'ac1234567890abcdef');


-- access_role
-- Prod schema: acc_id, acc_name (NOT NULL), acc_code (NOT NULL), acc_status (DEFAULT 0)
-- NOTE: dev schema has no acc_name column; must supply it for prod
INSERT INTO `access_role` (`acc_id`, `acc_name`, `acc_code`, `acc_status`) VALUES
(1, 'Transaction',  'transaction', 0),
(2, 'Process',      'process',     0),
(3, 'Summary',      'summary',     0),
(4, 'RS Process',   'rs_process',  0),
(5, 'Admin',        'admin',       0),
(6, 'RS Summary',   'rs_summary',  0),
(7, 'Conversion',   'conversion',  0);


-- access_permission
-- created_by / updated_by default to 0; def_page is NOT NULL (must supply)
INSERT INTO `access_permission` (`user_id`, `acc_id`, `acc_read_only`, `acc_all_access`, `def_page`) VALUES
-- Admin: all modules, full access, default page = admin (acc_id 5)
(1, 1, 0, 1, 0),
(1, 2, 0, 1, 0),
(1, 3, 0, 1, 0),
(1, 4, 0, 1, 0),
(1, 5, 0, 1, 1),
(1, 6, 0, 1, 0),
(1, 7, 0, 1, 0),
-- Reimbursement: process + rs_process full, rest read-only, default = process
(2, 1, 1, 0, 0),
(2, 2, 0, 1, 1),
(2, 3, 1, 0, 0),
(2, 4, 0, 1, 0),
(2, 6, 1, 0, 0),
(2, 7, 1, 0, 0),
-- Read-only: transaction + conversion read-only, default = transaction
(3, 1, 1, 0, 1),
(3, 7, 1, 0, 0),
-- Finance: summary read-only + rs_summary, default = summary
(4, 2, 1, 0, 0),
(4, 3, 1, 0, 1),
(4, 4, 1, 0, 0),
(4, 6, 1, 0, 0);


-- password_history
-- user_id is varchar(45) in prod schema
INSERT INTO `password_history` (`user_id`, `password_hash`, `last_password_change_date`) VALUES
('1', '805693f0a06d183b5cfc4cee08d5e7f572a030da', '2026-01-01 00:00:00'),
('2', '805693f0a06d183b5cfc4cee08d5e7f572a030da', '2026-01-15 00:00:00'),
('3', '805693f0a06d183b5cfc4cee08d5e7f572a030da', '2026-02-01 00:00:00'),
('4', '805693f0a06d183b5cfc4cee08d5e7f572a030da', '2026-02-10 00:00:00');


-- tbl_user (legacy lookup table)
INSERT INTO `tbl_user` (`pid`, `username`, `password`, `remarks`, `userid`) VALUES
(1, 'admin',       'p@55123', 'System admin',         1),
(2, 'reimb.user',  'p@55123', 'Reimbursement staff',  2),
(3, 'finance.user','p@55123', 'Finance officer',       4);


-- ============================================================
-- AUDIT TABLES
-- ============================================================

-- audit_action
-- Prod schema: auc_id, auc_desc (varchar 10), auc_name (varchar 50)
INSERT INTO `audit_action` (`auc_id`, `auc_desc`, `auc_name`) VALUES
(1, 'LOGIN',   'User Login'),
(2, 'LOGOUT',  'User Logout'),
(3, 'ADD',     'Record Added'),
(4, 'UPDATE',  'Record Updated'),
(5, 'DELETE',  'Record Deleted'),
(6, 'CANCEL',  'Record Cancelled'),
(7, 'SUBMIT',  'Record Submitted');


-- audit_trail
-- Prod schema: audit_id, action, user_id, pa_id, logist_id, dist_status, created_time
-- NOTE: dev schema has completely different columns (module_id, target_id, order_id, auc_id)
-- Using prod column names; logist_id=0 for non-logistics entries
INSERT INTO `audit_trail` (`action`, `user_id`, `pa_id`, `logist_id`, `dist_status`) VALUES
(0, 1, 0, 0, 0),   -- login: admin
(0, 2, 0, 0, 0),   -- login: reimb.user
(2, 1, 1, 0, 0),   -- add: PA 1 created
(2, 2, 2, 0, 0),   -- add: PA 2 created
(2, 2, 3, 0, 0),   -- add: PA 3 created
(3, 1, 1, 0, 1),   -- update: PA 1 status
(5, 1, 0, 0, 0);   -- submit: logout/submit action


-- audit_upload
INSERT INTO `audit_upload` (`module_name`, `file_name`) VALUES
('reconciliation', 'recon_upload_2026_01_15.csv'),
('refund',         'refund_upload_2026_01_17.csv');


-- ============================================================
-- PRODUCTS
-- ============================================================

INSERT INTO `cp_product` (`SERVICE_ID`, `SERVICE_NAME`) VALUES
(42, 'Pluxee Digital Voucher'),
(43, 'Pluxee Gift Certificate');

INSERT INTO `cp_product_backup` (`SERVICE_ID`, `SERVICE_NAME`) VALUES
(42, 'Pluxee Digital Voucher'),
(43, 'Pluxee Gift Certificate');


-- ============================================================
-- MERCHANTS
-- MERCHANT_ID mapping (used in branch_merchant, redemption, reconcilation, refund):
--   1 = McDonalds PH  (CP_ID '1001', cp_merchant.ID = 1)
--   2 = 7-Eleven PH   (CP_ID '1002', cp_merchant.ID = 2)
--   3 = Robinsons      (CP_ID '1003', cp_merchant.ID = 3)
-- ============================================================

-- cp_merchant
-- Prod schema requires PayeeId (NOT NULL) — missing from dev sample data
INSERT INTO `cp_merchant`
  (`CP_ID`, `TIN`, `LegalName`, `TradingName`, `GroupTIN`, `GroupName`, `Address`,
   `ContactPerson`, `ContactNumber`, `MeanofPayment`, `PayeeCode`, `BankName`,
   `BankBranchCode`, `BankAccountNumber`, `PayeeName`, `PayeeId`,
   `AffiliateGroupCode`, `PayeeQtyOfDays`, `PayeeDayType`, `PayeeComments`,
   `MerchantFee`, `Industry`, `VATCond`, `InsertType`,
   `MerchantType`, `DigitalSettlementType`)
VALUES
('1001', '123-456-789-000', 'Golden Arches Dev Corp', 'McDonalds PH',
 '123-456-789-000', 'QSR Group', 'Mckinley Pkwy, BGC Taguig',
 'Juan Dela Cruz', '+63-2-8888-0000', 'Bank Transfer',
 'PAYEE-MC-001', 'BDO', 'BDO-BGC', '1234-5678-9012', 'Golden Arches', 'PID-MC-001',
 'GADC', 3, '1', 'Standard 3 business days',
 0.020000, 'Food Service', 'Taxable', 'I', '', ''),

('1002', '987-654-321-000', '7-Eleven Philippines Inc', '7-Eleven PH',
 '987-654-321-000', 'CVS Group', '7-Eleven Bldg, Pasig City',
 'Maria Santos', '+63-2-7777-0000', 'Bank Transfer',
 'PAYEE-7E-001', 'BPI', 'BPI-ORTIGAS', '9876-5432-1098', '7-Eleven PH', 'PID-7E-001',
 'CVSG', 5, '0', 'Standard 5 calendar days',
 0.015000, 'Convenience Store', 'VAT-Exempt', 'I', '', ''),

('1003', '111-222-333-000', 'Robinsons Retail Holdings Inc', 'Robinsons Supermarket',
 '111-222-333-000', 'Robinsons Group', 'Robinson Galleria, Ortigas',
 'Pedro Cruz', '+63-2-6666-0000', 'Bank Transfer',
 'PAYEE-RR-001', 'Metrobank', 'MBK-ORTIGAS', '1111-2222-3333', 'Robinsons Retail', 'PID-RR-001',
 'RRHI', 3, '1', '',
 0.018000, 'Supermarket', 'Taxable', 'I', 'Merchant Dormancy', '');


-- cp_agreement (contract info per merchant-CP pair)
INSERT INTO `cp_agreement`
  (`AGREEMENT_ID`, `CP_ID`, `AffiliateGroupCode`, `Address`, `ContactPerson`, `ContactNumber`,
   `MeanofPayment`, `PayeeCode`, `BankName`, `BankBranchCode`, `BankAccountNumber`,
   `PayeeName`, `PayeeId`, `PayeeQtyOfDays`, `PayeeDayType`, `PayeeComments`,
   `MerchantFee`, `VATCond`, `InsertType`)
VALUES
(1, 1, 'GADC', 'Mckinley Pkwy, BGC Taguig', 'Juan Dela Cruz', '+63-2-8888-0000',
 'Bank Transfer', 'PAYEE-MC-001', 'BDO', 'BDO-BGC', '1234-5678-9012',
 'Golden Arches', 'PID-MC-001', 3, '1', 'Standard 3 business days',
 0.020000, 'Taxable', 'I'),

(2, 2, 'CVSG', '7-Eleven Bldg, Pasig City', 'Maria Santos', '+63-2-7777-0000',
 'Bank Transfer', 'PAYEE-7E-001', 'BPI', 'BPI-ORTIGAS', '9876-5432-1098',
 '7-Eleven PH', 'PID-7E-001', 5, '0', 'Standard 5 calendar days',
 0.015000, 'VAT-Exempt', 'I'),

(3, 3, 'RRHI', 'Robinson Galleria, Ortigas', 'Pedro Cruz', '+63-2-6666-0000',
 'Bank Transfer', 'PAYEE-RR-001', 'Metrobank', 'MBK-ORTIGAS', '1111-2222-3333',
 'Robinsons Retail', 'PID-RR-001', 3, '1', '',
 0.018000, 'Taxable', 'I');


-- merchant_fee
INSERT INTO `merchant_fee` (`MERCHANT_ID`, `MERCHANT_FEE`) VALUES
(1, 0.020000),
(2, 0.015000),
(3, 0.018000);


-- ============================================================
-- BRANCHES
-- FK: branch_merchant must exist first (FOREIGN_KEY_CHECKS=0 here)
-- branches.cp_id = cp_merchant.ID (auto-increment: 1=McD, 2=7E, 3=RR)
-- ============================================================

-- branch_merchant (must exist before branches/redemption/reconcilation/refund FK resolution)
INSERT INTO `branch_merchant` (`MERCHANT_ID`, `BRANCH_ID`) VALUES
(1, 'BR-MC-001'),
(1, 'BR-MC-002'),
(2, 'BR-7E-001'),
(2, 'BR-7E-002'),
(3, 'BR-RR-001');


-- branches
INSERT INTO `branches` (`BRANCH_ID`, `MERCHANT_ID`, `cp_id`, `BRANCH_NAME`, `affiliategroupcode`) VALUES
('BR-MC-001', 1, 1, 'McDo Makati Ayala',    'GADC'),
('BR-MC-002', 1, 1, 'McDo BGC High Street', 'GADC'),
('BR-7E-001', 2, 2, '7-Eleven Ortigas',     'CVSG'),
('BR-7E-002', 2, 2, '7-Eleven Pasig',       'CVSG'),
('BR-RR-001', 3, 3, 'Robinsons Galleria',   'RRHI');


-- payment_cutoff (MERCHANT_ID is UNIQUE KEY in prod schema)
INSERT INTO `payment_cutoff` (`MERCHANT_ID`, `TYPE`, `SPECIFIC_DAY`, `SPECIFIC_DATE`, `DigitalSettlementType`) VALUES
(1, 'weekly',     'Sunday', '',    ''),
(2, 'semi_month', '',       '[15]',''),
(3, 'weekly',     'Friday', '',    '');


-- ============================================================
-- DIGITAL TRANSACTION FLOW
-- redemption → reconcilation → refund → pa_header/pa_detail
-- ============================================================

-- REDEMPTIONS
-- Prod schema requires: MERCHANT_NAME, POS_TXN_ID, TRANSACTION_ID (all NOT NULL)
-- These were absent from the dev sample data — causes errors against prod schema
INSERT INTO `redemption`
  (`ID`, `REDEEM_ID`, `MERCHANT_ID`, `MERCHANT_NAME`, `BRANCH_ID`,
   `POS_ID`, `POS_TXN_ID`, `PROD_ID`, `VOUCHER_CODE`,
   `TRANSACTION_DATE_TIME`, `TRANSACTION_ID`, `TRANSACTION_VALUE`,
   `STAGE`, `PAYMENT_MODE`, `REFUND_ID`, `PA_ID`, `PA_TEMPID`)
VALUES
-- RECONCILED — under PA 1 (McDo Makati, MERCHANT_ID=1)
(1,  'RED-2026-001', 1, 'McDonalds PH', 'BR-MC-001', 'POS-MC-001', 'POSTXN-MC-001', 42, 'VOUCH-MC-001',
 '2026-01-05 10:00:00', 'TXN-2026-001',  500.00, 'RECONCILED', 'DIGITAL', 0, 1, 0),
(2,  'RED-2026-002', 1, 'McDonalds PH', 'BR-MC-001', 'POS-MC-001', 'POSTXN-MC-002', 42, 'VOUCH-MC-002',
 '2026-01-06 11:00:00', 'TXN-2026-002',  750.00, 'RECONCILED', 'DIGITAL', 0, 1, 0),
-- RECONCILED — under PA 2 (McDo BGC, MERCHANT_ID=1, BR-MC-002)
(3,  'RED-2026-003', 1, 'McDonalds PH', 'BR-MC-002', 'POS-MC-002', 'POSTXN-MC-003', 42, 'VOUCH-MC-003',
 '2026-01-07 09:30:00', 'TXN-2026-003',  300.00, 'RECONCILED', 'DIGITAL', 0, 2, 0),
(4,  'RED-2026-004', 1, 'McDonalds PH', 'BR-MC-002', 'POS-MC-002', 'POSTXN-MC-004', 42, 'VOUCH-MC-004',
 '2026-01-08 14:00:00', 'TXN-2026-004',  600.00, 'RECONCILED', 'DIGITAL', 0, 2, 0),
-- REVERSED — pre-PA (REFUND_ID=1, PA_ID=0)
(5,  'RED-2026-005', 1, 'McDonalds PH', 'BR-MC-001', 'POS-MC-001', 'POSTXN-MC-005', 42, 'VOUCH-MC-005',
 '2026-01-09 10:00:00', 'TXN-2026-005',  400.00, 'REVERSED',   'DIGITAL', 1, 0, 0),
-- REVERSED — post-PA (REFUND_ID=2, PA_ID=1)
(6,  'RED-2026-006', 1, 'McDonalds PH', 'BR-MC-001', 'POS-MC-001', 'POSTXN-MC-006', 42, 'VOUCH-MC-006',
 '2026-01-10 10:00:00', 'TXN-2026-006',  250.00, 'REVERSED',   'DIGITAL', 2, 1, 0),
-- RECONCILED — under PA 3 (7-Eleven, MERCHANT_ID=2)
(7,  'RED-2026-007', 2, '7-Eleven PH',  'BR-7E-001', 'POS-7E-001', 'POSTXN-7E-001', 42, 'VOUCH-7E-001',
 '2026-01-12 08:00:00', 'TXN-2026-007',  200.00, 'RECONCILED', 'DIGITAL', 0, 3, 0),
(8,  'RED-2026-008', 2, '7-Eleven PH',  'BR-7E-001', 'POS-7E-001', 'POSTXN-7E-002', 42, 'VOUCH-7E-002',
 '2026-01-13 09:00:00', 'TXN-2026-008',  350.00, 'RECONCILED', 'DIGITAL', 0, 3, 0),
-- REDEEMED — uploaded, not yet reconciled
(9,  'RED-2026-009', 1, 'McDonalds PH', 'BR-MC-001', 'POS-MC-001', 'POSTXN-MC-009', 42, 'VOUCH-MC-009',
 '2026-02-01 10:00:00', 'TXN-2026-009',  500.00, 'REDEEMED',   'DIGITAL', 0, 0, 0),
(10, 'RED-2026-010', 2, '7-Eleven PH',  'BR-7E-001', 'POS-7E-001', 'POSTXN-7E-010', 43, 'VOUCH-7E-010',
 '2026-02-02 11:00:00', 'TXN-2026-010', 1000.00, 'REDEEMED',   'DIGITAL', 0, 0, 0),
-- VOID — cancelled
(11, 'RED-2026-011', 2, '7-Eleven PH',  'BR-7E-002', 'POS-7E-002', 'POSTXN-7E-011', 42, 'VOUCH-7E-011',
 '2026-02-03 12:00:00', 'TXN-2026-011',  100.00, 'VOID',       'DIGITAL', 0, 0, 0);


-- RECONCILIATIONS
-- Prod schema requires: MERCHANT_NAME, POS_ID, POS_TXN_ID, VOUCHER_CODE (all NOT NULL)
-- These were absent from the dev sample data — causes errors against prod schema
INSERT INTO `reconcilation`
  (`ID`, `RECON_ID`, `REDEEM_ID`, `MERCHANT_NAME`, `MERCHANT_ID`, `BRANCH_ID`,
   `POS_ID`, `POS_TXN_ID`, `VOUCHER_CODE`,
   `TRANSACTION_DATE_TIME`, `PROD_ID`, `TRANSACTION_VALUE`,
   `RECON_DATE_TIME`, `PA_ID`, `REFUND_ID`, `payment_mode`,
   `REDEEM_TBL_ID`, `STAGE`, `PA_TEMPID`)
VALUES
-- Under PA 1 — McDo Makati
(1, 'RECON-2026-001', 'RED-2026-001', 'McDonalds PH', 1, 'BR-MC-001',
 'POS-MC-001', 'POSTXN-MC-001', 'VOUCH-MC-001',
 '2026-01-05 10:00:00', 42,  500.00, '2026-01-15 08:00:00', 1, 0, 'DIGITAL', 1, 'RECONCILED', 0),
(2, 'RECON-2026-002', 'RED-2026-002', 'McDonalds PH', 1, 'BR-MC-001',
 'POS-MC-001', 'POSTXN-MC-002', 'VOUCH-MC-002',
 '2026-01-06 11:00:00', 42,  750.00, '2026-01-15 08:05:00', 1, 0, 'DIGITAL', 2, 'RECONCILED', 0),
-- Under PA 2 — McDo BGC
(3, 'RECON-2026-003', 'RED-2026-003', 'McDonalds PH', 1, 'BR-MC-002',
 'POS-MC-002', 'POSTXN-MC-003', 'VOUCH-MC-003',
 '2026-01-07 09:30:00', 42,  300.00, '2026-01-16 08:00:00', 2, 0, 'DIGITAL', 3, 'RECONCILED', 0),
(4, 'RECON-2026-004', 'RED-2026-004', 'McDonalds PH', 1, 'BR-MC-002',
 'POS-MC-002', 'POSTXN-MC-004', 'VOUCH-MC-004',
 '2026-01-08 14:00:00', 42,  600.00, '2026-01-16 08:10:00', 2, 0, 'DIGITAL', 4, 'RECONCILED', 0),
-- REVERSED — pre-PA (PA_ID=0, REFUND_ID=1)
(5, 'RECON-2026-005', 'RED-2026-005', 'McDonalds PH', 1, 'BR-MC-001',
 'POS-MC-001', 'POSTXN-MC-005', 'VOUCH-MC-005',
 '2026-01-09 10:00:00', 42,  400.00, '2026-01-17 08:00:00', 0, 1, 'DIGITAL', 5, 'REVERSED',   0),
-- REVERSED — post-PA (PA_ID=1, REFUND_ID=2)
(6, 'RECON-2026-006', 'RED-2026-006', 'McDonalds PH', 1, 'BR-MC-001',
 'POS-MC-001', 'POSTXN-MC-006', 'VOUCH-MC-006',
 '2026-01-10 10:00:00', 42,  250.00, '2026-01-17 08:30:00', 1, 2, 'DIGITAL', 6, 'REVERSED',   0),
-- Under PA 3 — 7-Eleven
(7, 'RECON-2026-007', 'RED-2026-007', '7-Eleven PH',  2, 'BR-7E-001',
 'POS-7E-001', 'POSTXN-7E-001', 'VOUCH-7E-001',
 '2026-01-12 08:00:00', 42,  200.00, '2026-01-20 08:00:00', 3, 0, 'DIGITAL', 7, 'RECONCILED', 0),
(8, 'RECON-2026-008', 'RED-2026-008', '7-Eleven PH',  2, 'BR-7E-001',
 'POS-7E-001', 'POSTXN-7E-002', 'VOUCH-7E-002',
 '2026-01-13 09:00:00', 42,  350.00, '2026-01-20 08:05:00', 3, 0, 'DIGITAL', 8, 'RECONCILED', 0);


-- REFUNDS
-- Both MERCHANT_ID and BRANCH_ID must exist in branch_merchant (FK)
INSERT INTO `refund`
  (`REFUND_ID`, `REDEEM_ID`, `REVERSAL_TRANSACTION_ID`, `TRANSACTION_ID`, `RECON_ID`,
   `REVERSAL_MODE`, `PROD_ID`, `REDEEM_STATUS`, `MERCHANT_ID`, `BRANCH_ID`, `USER_ID`,
   `UPLOAD_ID`, `REVERSAL_DATE_TIME`, `DATE_CREATED`, `PA_ID`,
   `REDEEM_TBL_ID`, `RECON_TBL_ID`, `PA_TEMPID`)
VALUES
-- REFUND 1: pre-PA reversal (PA_ID=0)
(1, 'RED-2026-005', 'REV-TXN-2026-001', 'TXN-2026-005', 'RECON-2026-005',
 'FULL', 42, 'REVERSED', 1, 'BR-MC-001', 1,
 1, '2026-01-17 10:00:00', '2026-01-17 10:00:00', 0, 5, 5, 0),
-- REFUND 2: post-PA reversal (PA_ID=1)
(2, 'RED-2026-006', 'REV-TXN-2026-002', 'TXN-2026-006', 'RECON-2026-006',
 'FULL', 42, 'REVERSED', 1, 'BR-MC-001', 1,
 1, '2026-01-18 10:00:00', '2026-01-18 10:00:00', 1, 6, 6, 0);


-- TEMP REFUND (upload staging)
INSERT INTO `temp_refund`
  (`REFUND_ID`, `MERCHANT_NAME`, `MERCHANT_ID`, `BRANCH_ID`, `POS_ID`, `POS_TXN_ID`,
   `PROD_ID`, `TRANSACTION_DATE_TIME`, `TRANSACTION_ID`, `REDEMPTION_API_TRANSACTION_ID`,
   `REVERSAL_DATE_TIME`, `REVERSAL_TRANSACTION_ID`, `VOUCHER_CODE`, `TRANSACTION_VALUE`,
   `RECON_API_TRANSACTION_ID`, `PAYMENT_MODE`, `REVERSAL_MODE`, `UPLOAD_ID`, `ERROR_MESSAGE`)
VALUES
(0, 'McDonalds PH', '1', 'BR-MC-001', 'POS-MC-001', 'POSTXN-MC-NEW1',
 '42', '2026-02-01 10:00:00', 'TXN-2026-NEW-001', 'RED-API-NEW-001',
 '2026-02-05 09:00:00', 'REV-TXN-NEW-001', 'VOUCH-MC-NEW', '500.00',
 'RECON-API-NEW-001', 'DIGITAL', 'FULL', 2, '');


-- ============================================================
-- PAYMENT ADVICE (PA)
-- ============================================================

-- pa_header
-- Prod schema requires GENERATED (NOT NULL) — missing from dev sample data
INSERT INTO `pa_header`
  (`PA_ID`, `MERCHANT_ID`, `MERCHANT_FEE`, `vatcond`, `REIMBURSEMENT_DATE`,
   `ExpectedDueDate`, `USER_ID`, `GENERATED`)
VALUES
(1, 1, 0.02000, 'Taxable',    '2026-01-20 09:00:00', '2026-01-23', 2, 0),
(2, 1, 0.02000, 'Taxable',    '2026-01-21 09:00:00', '2026-01-24', 2, 0),
(3, 2, 0.01500, 'VAT-Exempt', '2026-01-25 09:00:00', '2026-01-30', 2, 0);


-- pa_detail
-- Prod schema requires TOTAL_REFUND (NOT NULL) — missing from dev sample data
-- DATE_CREATED defaults to '0000-00-00 00:00:00' via ON UPDATE CURRENT_TIMESTAMP
INSERT INTO `pa_detail`
  (`PA_DID`, `PA_ID`, `BRANCH_ID`, `RECON_ID`, `RATE`, `NUM_PASSES`,
   `TOTAL_FV`, `MARKETING_FEE`, `VAT`, `NET_DUE`, `TOTAL_REFUND`)
VALUES
-- PA 1 — McDo Makati
(1, 1, 'BR-MC-001', 'RECON-2026-001', 0.02, 1,  500.00, 10.00, 1.20,  488.80, 0.00000),
(2, 1, 'BR-MC-001', 'RECON-2026-002', 0.02, 1,  750.00, 15.00, 1.80,  733.20, 0.00000),
(3, 1, 'BR-MC-001', 'RECON-2026-006', 0.02, 1, -250.00, -5.00,-0.60, -244.40, 250.00000),  -- post-PA reversal
-- PA 2 — McDo BGC
(4, 2, 'BR-MC-002', 'RECON-2026-003', 0.02, 1,  300.00,  6.00, 0.72,  293.28, 0.00000),
(5, 2, 'BR-MC-002', 'RECON-2026-004', 0.02, 1,  600.00, 12.00, 1.44,  586.56, 0.00000),
-- PA 3 — 7-Eleven
(6, 3, 'BR-7E-001', 'RECON-2026-007', 0.015,1,  200.00,  3.00, 0.00,  197.00, 0.00000),
(7, 3, 'BR-7E-001', 'RECON-2026-008', 0.015,1,  350.00,  5.25, 0.00,  344.75, 0.00000);


-- ============================================================
-- REIMBURSEMENT SETTLEMENT (RS) & CONVERSION
-- ============================================================

-- rs_header
-- Prod schema requires GENERATED (NOT NULL) — missing from dev sample data
INSERT INTO `rs_header`
  (`RS_ID`, `RS_NUMBER`, `MERCHANT_ID`, `BRANCH_ID`, `MERCHANT_FEE`, `VATCOND`,
   `REIMBURSEMENT_DATE`, `ExpectedDueDate`, `USER_ID`, `GENERATED`, `DATE_CREATED`)
VALUES
(1, 'RS-2026-01-001', 1, 'BR-MC-001', 0.02000, 'Taxable',
 '2026-01-20 09:00:00', '2026-01-23', 1, 0, '2026-01-20 09:00:00');


-- conversion
-- Prod schema requires CHANNEL (NOT NULL) — missing from dev sample data
-- Prod schema AGENT_ID is int(11) — dev sample used string 'AGT-001' (type mismatch)
INSERT INTO `conversion`
  (`COV_ID`, `MERCHANT_ID`, `BRANCH_ID`, `BRANCH_NAME`, `USER_ID`, `NAME`,
   `TOTAL_AMOUNT`, `VOUCHER_CODES`, `DENO`, `STAGE`, `CHANNEL`,
   `CREATED_AT`, `AGENT_ID`, `PROD_ID`, `RS_ID`)
VALUES
(1, 1, 'BR-MC-001', 'McDo Makati Ayala',  1, 'Juan Dela Cruz',
 1000.00, 'VOUCH-MC-CONV-001,VOUCH-MC-CONV-002', 500.00, 'CONVERTED', 'DIGITAL',
 '2026-01-10 08:00:00', 1, 42, 1),
(2, 1, 'BR-MC-001', 'McDo Makati Ayala',  1, 'Juan Dela Cruz',
  500.00, 'VOUCH-MC-CONV-003', 500.00, 'CONVERTED', 'DIGITAL',
 '2026-01-10 09:00:00', 1, 42, 1),
(3, 2, 'BR-7E-001', '7-Eleven Ortigas',   1, 'Pedro Cruz',
  200.00, 'VOUCH-7E-CONV-001', 200.00, 'CONVERTED', 'DIGITAL',
 '2026-02-01 08:00:00', 2, 42, 0);  -- RS_ID=0 = pending settlement


-- rs_detail
INSERT INTO `rs_detail` (`RS_ID`, `COV_ID`, `RATE`, `TOTAL_FV`, `MARKETING_FEE`, `VAT`, `NET_DUE`, `DATE_CREATED`) VALUES
(1, 1, 2, 1000.00, 20.00, 2.40,  977.60, '2026-01-20 09:00:00'),
(1, 2, 2,  500.00, 10.00, 1.20,  488.80, '2026-01-20 09:00:00');


-- ============================================================
-- NAV (Navision/ERP) EXPORT
-- nav_header.CP_ID is int(11) → maps to cp_merchant.ID (auto-increment: 1,2,3)
-- ============================================================

INSERT INTO `nav_header`
  (`NAVH_ID`, `CP_ID`, `MERCHANT_ID`, `PA_ID`, `RECON_ID`, `PROD_ID`,
   `TotalAmount`, `DateofReceipt`, `ExpectedDueDate`)
VALUES
(1, 1, 1, 1, 'RECON-2026-001', 42, 1250.00, '2026-01-20', '2026-01-23'),
(2, 2, 2, 3, 'RECON-2026-007', 42,  550.00, '2026-01-25', '2026-01-30');


-- nav_detail
-- BillItem is varchar(20); VATCond is varchar(20) — supply as strings
INSERT INTO `nav_detail` (`NAVH_ID`, `PROD_ID`, `BillItem`, `FaceValue`, `OutputVAT`, `VATCond`) VALUES
(1, 42, 'Dvoucher', 1250.00, 0.00, 'Taxable'),
(2, 42, 'Dvoucher',  550.00, 0.00, 'VAT-Exempt');


SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- QUICK REFERENCE
-- ============================================================
-- Login credentials (username / password):
--   admin        / p@55123   (Admin — full access, default: admin)
--   reimb.user   / p@55123   (Reimbursement — PA + RS, default: process)
--   cs.user      / p@55123   (Read-only — transaction + conversion)
--   finance.user / p@55123   (Finance — summary read-only, default: summary)
--   inactive.usr / p@55123   (Inactive — login blocked until activated)
--
-- Key data points:
--   Merchants  : McDonalds PH (ID=1), 7-Eleven PH (ID=2), Robinsons (ID=3)
--   PA headers : PA 1 (McDo Makati), PA 2 (McDo BGC), PA 3 (7-Eleven)
--   Reversals  : REFUND 1 (pre-PA), REFUND 2 (post-PA, under PA 1)
--   RS         : RS 1 (McDo Makati settled), COV 3 (7-Eleven pending RS_ID=0)
--   NAV export : nav_header 1 & 2
--   Dormancy   : Robinsons (MerchantType='Merchant Dormancy')
-- ============================================================
