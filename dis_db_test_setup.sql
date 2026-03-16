-- ============================================================
-- DIS TEST SETUP — run this AFTER dis_db_sample_data.sql
-- Clears all transaction tables and configures one nrecon merchant
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- STEP 1: Clear transaction tables (keep master data intact)
-- ============================================================
TRUNCATE TABLE `audit_upload`;
TRUNCATE TABLE `temp_refund`;
TRUNCATE TABLE `refund`;
TRUNCATE TABLE `reconcilation`;
TRUNCATE TABLE `redemption`;
TRUNCATE TABLE `pa_detail`;
TRUNCATE TABLE `pa_header`;
TRUNCATE TABLE `branch_merchant`;

-- ============================================================
-- STEP 2: Make MERCHANT_ID=2 (McDo BGC, BR-MC-002) a nrecon
-- merchant so it appears in /process/nrecon/get_item
-- (DigitalSettlementType='' → wrecon, !='' → nrecon)
-- ============================================================
UPDATE `payment_cutoff`
SET `DigitalSettlementType` = 'EVOUCHER'
WHERE `MERCHANT_ID` = 2;

-- ============================================================
-- STEP 3: Re-insert merchant 677 data (wiped by truncates above)
-- Semi-Monthly {15,31} nrecon merchant for cutoff testing
-- ============================================================
INSERT INTO `branch_merchant` (`MERCHANT_ID`, `BRANCH_ID`) VALUES
(677, 'BR-SM-677');

INSERT INTO `redemption`
  (`ID`, `REDEEM_ID`, `MERCHANT_ID`, `MERCHANT_NAME`, `BRANCH_ID`, `PROD_ID`, `VOUCHER_CODE`,
   `TRANSACTION_VALUE`, `STAGE`, `TRANSACTION_DATE_TIME`, `POS_ID`, `POS_TXN_ID`,
   `TRANSACTION_ID`, `PAYMENT_MODE`, `REFUND_ID`, `PA_ID`, `PA_TEMPID`)
VALUES
(12, 'RED-2026-012', 677, 'SampleMerchant677', 'BR-SM-677', 42, 'VOUCH-SM-001',  800.00, 'RECONCILED', '2026-02-15 10:00:00', 'POS-SM-677', 'POSTXN-SM-001', 'TXN-2026-012', 'DIGITAL', 0, 0, 0),
(13, 'RED-2026-013', 677, 'SampleMerchant677', 'BR-SM-677', 42, 'VOUCH-SM-002', 1200.00, 'RECONCILED', '2026-02-20 14:00:00', 'POS-SM-677', 'POSTXN-SM-002', 'TXN-2026-013', 'DIGITAL', 0, 0, 0);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Result after running both SQLs:
--
-- cp_merchant:
--   id=1  CP_ID=1001  LegalName='Golden Arches Dev Corp'  MF=2%  VAT=Taxable
--   id=2  CP_ID=1002  LegalName='7-Eleven Philippines Inc'  MF=1.5%  VAT=VAT-Exempt
--   id=3  CP_ID=1003  LegalName='Robinsons Retail Holdings Inc'  MF=1.8%  VAT=Taxable
--
-- branches:
--   BR-MC-001  MERCHANT_ID=1  CP_ID=1001  (McDo Makati — wrecon)
--   BR-MC-002  MERCHANT_ID=2  CP_ID=1001  (McDo BGC — nrecon)
--   BR-7E-001  MERCHANT_ID=3  CP_ID=1002  (7-Eleven Ortigas — monthly)
--   BR-7E-002  MERCHANT_ID=4  CP_ID=1002  (7-Eleven Pasig — monthly)
--   BR-RR-001  MERCHANT_ID=5  CP_ID=1003  (Robinsons — weekly friday)
--
-- payment_cutoff:
--   MERCHANT_ID=1  Weekly  Sunday   DigitalSettlementType=''      → wrecon
--   MERCHANT_ID=2  Weekly  Sunday   DigitalSettlementType=EVOUCHER → nrecon
--   MERCHANT_ID=3  Monthly [15]     DigitalSettlementType=''
--   MERCHANT_ID=4  Monthly [15]     DigitalSettlementType=''
--   MERCHANT_ID=5    Weekly        Friday   DigitalSettlementType=''
--   MERCHANT_ID=677  Semi-Monthly  {15,31}  DigitalSettlementType=NRECON → nrecon
--
-- redemption (after re-insert):
--   RED-2026-012  merchant 677  RECONCILED  2026-02-15  PA_ID=0
--   RED-2026-013  merchant 677  RECONCILED  2026-02-20  PA_ID=0
-- ============================================================
