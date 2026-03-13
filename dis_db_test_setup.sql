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
--   MERCHANT_ID=5  Weekly  Friday   DigitalSettlementType=''
-- ============================================================
