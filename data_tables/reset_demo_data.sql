-- ============================================================
-- DEMO RESET — clears all transactional data, keeps master data
-- Run ONCE before each client demo session
-- ============================================================
-- Master data kept: users, merchants, branches, payment_cutoff,
--                  products, access roles
-- Transactional data cleared: redemption, reconcilation, refund,
--                              PA, RS, NAV, conversion, audit logs
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- PA / RS
TRUNCATE TABLE `pa_detail`;
TRUNCATE TABLE `pa_header`;
TRUNCATE TABLE `rs_detail`;
TRUNCATE TABLE `rs_header`;
TRUNCATE TABLE `nav_detail`;
TRUNCATE TABLE `nav_header`;

-- Transactions
TRUNCATE TABLE `refund`;
TRUNCATE TABLE `temp_refund`;
TRUNCATE TABLE `reconcilation`;
TRUNCATE TABLE `redemption`;
TRUNCATE TABLE `conversion`;

-- Upload / audit logs
TRUNCATE TABLE `audit_upload`;
TRUNCATE TABLE `file_upload`;
TRUNCATE TABLE `audit_trail`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DEMO MASTER DATA (merchants aligned to dcj_test_to_upload)
-- Only insert if not already present
-- ============================================================

INSERT IGNORE INTO `cp_product` (`SERVICE_ID`, `SERVICE_NAME`) VALUES
(42, 'Pluxee Digital Voucher'),
(44, 'Pluxee Gift Certificate');

-- Merchants from dcj_test_to_upload CSV files
INSERT IGNORE INTO `cp_merchant`
  (`CP_ID`, `TIN`, `LegalName`, `TradingName`, `GroupTIN`, `GroupName`, `Address`,
   `MeanofPayment`, `PayeeCode`, `BankName`, `BankAccountNumber`, `PayeeName`,
   `MerchantFee`, `Industry`, `VATCond`, `InsertType`, `BankBranchCode`,
   `PayeeQtyOfDays`, `PayeeDayType`, `PayeeComments`,
   `AffiliateGroupCode`, `MerchantType`, `DIGITALSETTLEMENTTYPE`)
VALUES
(33,   '001-000-033-000', 'EPICUREAN PARTNERS EXCHANGE INC.', 'Epicurean Partners', '001-000-033-000', 'Epicurean Group',  'Makati City', 'Bank Transfer', 'PAYEE-033', 'BDO', '0330000001', 'Epicurean Partners', 0.020000, 'Food Service',    'Taxable',    'AUTO', 'BDO-MKT',    3, 1, '', 'EPXI', '', ''),
(3913, '001-000-391-000', 'Decathlon Philippines Inc.',        'Decathlon PH',      '001-000-391-000', 'Decathlon Group', 'BGC Taguig',   'Bank Transfer', 'PAYEE-391', 'BPI', '3913000001', 'Decathlon PH',      0.018000, 'Retail',         'Taxable',    'AUTO', 'BPI-BGC',    3, 1, '', 'DCPH', '', ''),
(4771, '001-000-477-000', 'Sodexo Test Merchant - Legal Name', 'Sodexo PH',         '001-000-477-000', 'Sodexo Group',    'Ortigas',      'Bank Transfer', 'PAYEE-477', 'Metrobank', '4771000001', 'Sodexo PH', 0.015000, 'Food Service',    'VAT-Exempt', 'AUTO', 'MBK-ORT',    5, 0, '', 'SDXO', '', ''),
(4772, '001-000-477-200', 'Yellow Cab Pizza Co.',              'Yellow Cab PH',     '001-000-477-200', 'Yellow Cab',      'Pasig City',   'Bank Transfer', 'PAYEE-472', 'BDO', '4772000001', 'Yellow Cab PH',     0.020000, 'Food Service',    'Taxable',    'AUTO', 'BDO-PSG',    3, 1, '', 'YCPH', '', ''),
(5512, '001-000-551-000', 'The Real American Doughnut Company Inc.', 'Krispy Kreme PH', '001-000-551-000', 'KK Group', 'Mandaluyong', 'Bank Transfer', 'PAYEE-551', 'BPI', '5512000001', 'Krispy Kreme', 0.020000, 'Food Service',    'Taxable',    'AUTO', 'BPI-MDL',    3, 1, '', 'KKPH', '', ''),
(6233, '001-000-623-000', 'Power Mac Center Inc.',             'Power Mac Center',  '001-000-623-000', 'PMC Group',       'Quezon City',  'Bank Transfer', 'PAYEE-623', 'BDO', '6233000001', 'Power Mac Center',  0.020000, 'Retail',         'Taxable',    'AUTO', 'BDO-QC',     3, 1, '', 'PMCI', '', ''),
(6252, '001-000-625-000', 'All Day Marts Inc',                 'All Day Supermarket','001-000-625-000','All Day Group',   'Parañaque',    'Bank Transfer', 'PAYEE-625', 'BDO', '6252000001', 'All Day Marts',     0.020000, 'Supermarket',    'Taxable',    'AUTO', 'BDO-PAR',    3, 1, '', 'ADMI', '', ''),
(7338, '001-000-733-000', 'MAX''S GROUP INC.',                 'Max''s Restaurant', '001-000-733-000', 'Max''s Group',    'Quezon City',  'Bank Transfer', 'PAYEE-733', 'Metrobank', '7338000001', 'Max''s Group', 0.020000, 'Food Service',    'Taxable',    'AUTO', 'MBK-QC',     3, 1, '', 'MGPH', '', ''),
(7475, '001-000-747-000', 'Max''s Kitchen Inc.',               'Max''s Kitchen',    '001-000-747-000', 'Max''s Group',    'Quezon City',  'Bank Transfer', 'PAYEE-747', 'Metrobank', '7475000001', 'Max''s Kitchen', 0.020000, 'Food Service',    'Taxable',    'AUTO', 'MBK-QC',     3, 1, '', 'MGPH', '', ''),
(8246, '001-000-824-000', 'Red Ribbon Foods Corporation',      'Red Ribbon',        '001-000-824-000', 'Red Ribbon Grp',  'Mandaluyong',  'Bank Transfer', 'PAYEE-824', 'BDO', '8246000001', 'Red Ribbon',        0.020000, 'Food Service',    'Taxable',    'AUTO', 'BDO-MDL',    3, 1, '', 'RRFC', '', ''),
-- Reversal merchants
(44,   '001-000-044-000', 'Madison Shopping Plaza Inc. - Cagayan De Oro', 'Madison CDO', '001-000-044-000', 'Madison Grp', 'CDO', 'Bank Transfer', 'PAYEE-044', 'BDO', '0440000001', 'Madison CDO', 0.020000, 'Retail', 'Taxable', 'AUTO', 'BDO-CDO', 3, 1, '', '', '', ''),
(45,   '001-000-045-000', 'Market Strategic Firm Inc. - Calamba',          'Market Strategic', '001-000-045-000', 'Market Grp', 'Calamba', 'Bank Transfer', 'PAYEE-045', 'BPI', '0450000001', 'Market Strategic', 0.020000, 'Retail', 'Taxable', 'AUTO', 'BPI-CAL', 3, 1, '', '', '', ''),
(3918, '001-000-391-800', 'SM Supermarket',        'SM Supermarket',  '001-000-391-800', 'SM Group', 'Pasay City',    'Bank Transfer', 'PAYEE-391-8', 'BDO', '3918000001', 'SM Supermarket', 0.020000, 'Supermarket', 'Taxable', 'AUTO', 'BDO-PSY', 3, 1, '', 'SMPH', '', ''),
(4398, '001-000-439-000', 'Robinsons Supermarket', 'Robinsons SM',    '001-000-439-000', 'RLC Group', 'Quezon City',  'Bank Transfer', 'PAYEE-439', 'Metrobank', '4398000001', 'Robinsons SM', 0.020000, 'Supermarket', 'Taxable', 'AUTO', 'MBK-QC',  3, 1, '', 'RLCG', '', '');


-- Branches from redemption CSV
INSERT IGNORE INTO `branches` (`BRANCH_ID`, `MERCHANT_ID`, `BRANCH_NAME`, `CP_ID`, `AFFILIATEGROUPCODE`) VALUES
('M806',    6252, 'All Day Mart - Branch M806',    6252, 'ADMI'),
('M820',    6252, 'All Day Mart - Branch M820',    6252, 'ADMI'),
('M835',    6252, 'All Day Mart - Branch M835',    6252, 'ADMI'),
('2126',    3913, 'Decathlon - Branch 2126',        3913, 'DCPH'),
('B1',      4771, 'Sodexo - Branch B1',             4771, 'SDXO'),
('YCLSD',   4772, 'Yellow Cab - YCLSD',             4772, 'YCPH'),
('YCSMLI',  4772, 'Yellow Cab - YCSMLI',            4772, 'YCPH'),
('KK8967',  5512, 'Krispy Kreme - KK8967',          5512, 'KKPH'),
('BR-101',  6233, 'Power Mac Center - BR-101',      6233, 'PMCI'),
('BR-125',  6233, 'Power Mac Center - BR-125',      6233, 'PMCI'),
('EPS105',  33,   'Epicurean - EPS105',              33,   'EPXI'),
('EPS116',  33,   'Epicurean - EPS116',              33,   'EPXI'),
('EPS130',  33,   'Epicurean - EPS130',              33,   'EPXI'),
('EPS146',  33,   'Epicurean - EPS146',              33,   'EPXI'),
('EPS711',  33,   'Epicurean - EPS711',              33,   'EPXI'),
('PC0084',  7338, 'Max''s Group - PC0084',           7338, 'MGPH'),
('0049',    7475, 'Max''s Kitchen - 0049',           7475, 'MGPH'),
('RR0058',  8246, 'Red Ribbon - RR0058',             8246, 'RRFC'),
('RR0298',  8246, 'Red Ribbon - RR0298',             8246, 'RRFC'),
('RR3183',  8246, 'Red Ribbon - RR3183',             8246, 'RRFC'),
-- Reversal merchants
('00733',   4398, 'Robinsons Supermarket - 00733',  4398, 'RLCG'),
('00147',   4398, 'Robinsons Supermarket - 00147',  4398, 'RLCG'),
('00742',   4398, 'Robinsons Supermarket - 00742',  4398, 'RLCG');


-- branch_merchant (MERCHANT_ID = CP_ID for these merchants)
INSERT IGNORE INTO `branch_merchant` (`MERCHANT_ID`, `BRANCH_ID`) VALUES
(6252, 'M806'), (6252, 'M820'), (6252, 'M835'),
(3913, '2126'),
(4771, 'B1'),
(4772, 'YCLSD'), (4772, 'YCSMLI'),
(5512, 'KK8967'),
(6233, 'BR-101'), (6233, 'BR-125'),
(33,   'EPS105'), (33, 'EPS116'), (33, 'EPS130'), (33, 'EPS146'), (33, 'EPS711'),
(7338, 'PC0084'),
(7475, '0049'),
(8246, 'RR0058'), (8246, 'RR0298'), (8246, 'RR3183'),
(4398, '00733'), (4398, '00147'), (4398, '00742');


-- Payment cutoffs — Monthly on the 29th (matches recon CSV dates from Dec 29 2022)
-- Use SPECIFIC_DATE=15 so it shows up easily when searching "Monthly + 15"
INSERT IGNORE INTO `payment_cutoff` (`MERCHANT_ID`, `TYPE`, `SPECIFIC_DAY`, `SPECIFIC_DATE`, `DigitalSettlementType`) VALUES
(33,   'Semi-Monthly', '', '{15,29}', ''),
(3913, 'Monthly',      '', '{15}',    ''),
(4771, 'Monthly',      '', '{15}',    ''),
(4772, 'Monthly',      '', '{15}',    ''),
(5512, 'Monthly',      '', '{15}',    ''),
(6233, 'Monthly',      '', '{15}',    ''),
(6252, 'Monthly',      '', '{15}',    ''),
(7338, 'Monthly',      '', '{15}',    ''),
(7475, 'Monthly',      '', '{15}',    ''),
(8246, 'Monthly',      '', '{15}',    ''),
(44,   'Monthly',      '', '{15}',    ''),
(45,   'Monthly',      '', '{15}',    ''),
(3918, 'Monthly',      '', '{15}',    ''),
(4398, 'Monthly',      '', '{15}',    '');

-- ============================================================
-- END OF RESET SCRIPT
-- ============================================================
