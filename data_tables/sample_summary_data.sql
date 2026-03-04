-- Sample data for PA Summary Page
-- To display data for PA ID 200

-- 1. Insert Sample Merchant
INSERT IGNORE INTO `cp_merchant` (`CP_ID`, `TIN`, `LegalName`, `TradingName`, `MerchantFee`, `VATCond`, `PayeeQtyOfDays`, `PayeeDayType`, `DigitalSettlementType`)
VALUES (1001, '123-456-789-000', 'Pluxee Sample Merchant', 'Pluxee Sample', 0.02, 'Taxable', 3, 1, '');

-- 2. Insert Sample Branch
-- Note: MERCHANT_ID in branches table should match MERCHANT_ID in pa_header
INSERT IGNORE INTO `branches` (`BRANCH_ID`, `MERCHANT_ID`, `BRANCH_NAME`, `CP_ID`)
VALUES ('BR001', 1001, 'Main Branch', 1001);

-- 3. Insert PA Header for PA ID 200
-- Aligned with joins in Sys_model::getTransactionSummary_part3
INSERT IGNORE INTO `pa_header` (`PA_ID`, `MERCHANT_ID`, `REIMBURSEMENT_DATE`, `ExpectedDueDate`, `DATE_CREATED`, `USER_ID`, `GENERATED`)
VALUES (200, 1001, '2026-03-01', '2026-03-04', '2026-03-01 10:00:00', 1, 1);

-- 4. Insert PA Detail
-- Aligned with joins in Sys_model::getTransactionSummary_part3
INSERT IGNORE INTO `pa_detail` (`PA_ID`, `BRANCH_ID`, `RECON_ID`, `RATE`, `NUM_PASSES`, `TOTAL_FV`, `MARKETING_FEE`, `VAT`, `NET_DUE`, `DATE_CREATED`)
VALUES (200, 'BR001', 'REC200', '2.00%', 1, 1000.00, 20.00, 2.40, 977.60, '2026-03-01 10:00:00');
