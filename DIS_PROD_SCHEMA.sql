-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 10.63.16.144    Database: dis_db
-- ------------------------------------------------------
-- Server version	5.5.5-10.1.38-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_permission`
--

DROP TABLE IF EXISTS `access_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `access_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acc_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `acc_read_only` int(11) NOT NULL DEFAULT '0',
  `acc_all_access` int(11) NOT NULL DEFAULT '0',
  `def_page` int(11) NOT NULL,
  `created_by` int(11) NOT NULL DEFAULT '0',
  `updated_by` int(11) NOT NULL DEFAULT '0',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `access_role_id` (`acc_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `access_role`
--

DROP TABLE IF EXISTS `access_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `access_role` (
  `acc_id` int(11) NOT NULL AUTO_INCREMENT,
  `acc_name` varchar(255) NOT NULL,
  `acc_code` varchar(50) NOT NULL,
  `acc_status` int(11) NOT NULL DEFAULT '0' COMMENT '0:active; 1:inactive',
  PRIMARY KEY (`acc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ajax_update`
--

DROP TABLE IF EXISTS `ajax_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ajax_update` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `date_created` date NOT NULL,
  `last_update` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_created` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit_action`
--

DROP TABLE IF EXISTS `audit_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_action` (
  `auc_id` int(11) NOT NULL AUTO_INCREMENT,
  `auc_desc` varchar(10) NOT NULL,
  `auc_name` varchar(50) NOT NULL,
  PRIMARY KEY (`auc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit_trail`
--

DROP TABLE IF EXISTS `audit_trail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_trail` (
  `audit_id` int(11) NOT NULL AUTO_INCREMENT,
  `action` int(11) NOT NULL COMMENT '0:login; 1;logout 2:add; 3:update; 4:delete; 5:cancel; 5:submit;',
  `user_id` int(11) NOT NULL,
  `pa_id` int(11) NOT NULL,
  `logist_id` int(11) NOT NULL,
  `dist_status` int(11) NOT NULL,
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`audit_id`),
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7830 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit_upload`
--

DROP TABLE IF EXISTS `audit_upload`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_upload` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(250) NOT NULL,
  `file_name` varchar(250) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `file_name` (`file_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21159 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `branch_merchant`
--

DROP TABLE IF EXISTS `branch_merchant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branch_merchant` (
  `MERCHANT_ID` int(11) NOT NULL,
  `BRANCH_ID` varchar(100) CHARACTER SET latin1 NOT NULL,
  UNIQUE KEY `mid_bid` (`MERCHANT_ID`,`BRANCH_ID`),
  KEY `MERCHANT_ID` (`MERCHANT_ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BRANCH_ID` varchar(100) CHARACTER SET latin1 NOT NULL,
  `MERCHANT_ID` int(11) NOT NULL,
  `cp_id` int(11) NOT NULL,
  `BRANCH_NAME` varchar(250) CHARACTER SET latin1 NOT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DATE_MODIFIED` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `affiliategroupcode` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `branches_cp_id_idx` (`cp_id`) USING BTREE,
  KEY `BRANCH_NAME` (`BRANCH_NAME`) USING BTREE,
  KEY `affiliategroupcode` (`affiliategroupcode`) USING BTREE,
  CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`MERCHANT_ID`) REFERENCES `branch_merchant` (`MERCHANT_ID`),
  CONSTRAINT `branches_ibfk_2` FOREIGN KEY (`BRANCH_ID`) REFERENCES `branch_merchant` (`BRANCH_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=31129 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conversion`
--

DROP TABLE IF EXISTS `conversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversion` (
  `COV_ID` int(11) NOT NULL AUTO_INCREMENT,
  `MERCHANT_ID` int(11) NOT NULL,
  `BRANCH_ID` varchar(25) NOT NULL,
  `BRANCH_NAME` varchar(250) NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `NAME` varchar(100) NOT NULL,
  `TOTAL_AMOUNT` decimal(10,2) NOT NULL,
  `VOUCHER_CODES` varchar(25) NOT NULL,
  `DENO` decimal(10,2) NOT NULL,
  `STAGE` varchar(100) NOT NULL,
  `CHANNEL` varchar(100) NOT NULL,
  `CREATED_AT` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `AGENT_ID` int(11) NOT NULL,
  `PROD_ID` int(11) NOT NULL,
  `RS_ID` int(11) NOT NULL,
  `DATE_CREATED` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`COV_ID`),
  UNIQUE KEY `VOUCHER_CODES` (`VOUCHER_CODES`),
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `PROD_ID` (`PROD_ID`) USING BTREE,
  KEY `RS_ID` (`RS_ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=459 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cp_agreement`
--

DROP TABLE IF EXISTS `cp_agreement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cp_agreement` (
  `AGREEMENT_ID` int(22) NOT NULL,
  `CP_ID` int(22) NOT NULL,
  `AffiliateGroupCode` varchar(100) NOT NULL,
  `Address` varchar(250) NOT NULL,
  `ContactPerson` varchar(50) NOT NULL,
  `ContactNumber` varchar(50) NOT NULL,
  `MeanofPayment` varchar(50) NOT NULL,
  `PayeeCode` varchar(10) NOT NULL,
  `BankName` varchar(50) NOT NULL,
  `BankBranchCode` varchar(50) NOT NULL,
  `BankAccountNumber` varchar(50) NOT NULL,
  `PayeeName` varchar(50) NOT NULL,
  `PayeeId` varchar(20) NOT NULL,
  `PayeeQtyOfDays` int(11) NOT NULL,
  `PayeeDayType` varchar(100) NOT NULL,
  `PayeeComments` varchar(250) NOT NULL,
  `MerchantFee` double NOT NULL,
  `VATCond` varchar(20) NOT NULL,
  `InsertType` varchar(1) NOT NULL COMMENT 'I , U , X - donot include for migration',
  UNIQUE KEY `AgreementID` (`AGREEMENT_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cp_merchant`
--

DROP TABLE IF EXISTS `cp_merchant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cp_merchant` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CP_ID` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `TIN` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `LegalName` varchar(150) CHARACTER SET latin1 DEFAULT NULL,
  `TradingName` varchar(150) CHARACTER SET latin1 DEFAULT NULL,
  `GroupTIN` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `GroupName` varchar(150) CHARACTER SET latin1 DEFAULT NULL,
  `Address` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `ContactPerson` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `ContactNumber` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `MeanofPayment` varchar(50) CHARACTER SET latin1 DEFAULT NULL COMMENT 'Cash or Credit Client',
  `PayeeCode` varchar(10) CHARACTER SET latin1 NOT NULL,
  `BankName` varchar(50) CHARACTER SET latin1 NOT NULL,
  `BankBranchCode` varchar(50) CHARACTER SET latin1 NOT NULL,
  `BankAccountNumber` varchar(50) CHARACTER SET latin1 NOT NULL,
  `PayeeName` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `PayeeId` varchar(20) CHARACTER SET latin1 NOT NULL,
  `AffiliateGroupCode` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '0',
  `PayeeQtyOfDays` int(11) NOT NULL,
  `PayeeDayType` varchar(100) CHARACTER SET latin1 NOT NULL,
  `PayeeComments` varchar(250) CHARACTER SET latin1 NOT NULL,
  `MerchantFee` double DEFAULT NULL,
  `Industry` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `VATCond` varchar(20) CHARACTER SET latin1 NOT NULL,
  `InsertType` varchar(1) CHARACTER SET latin1 DEFAULT NULL COMMENT 'I , U , X - donot include for migration',
  `MerchantType` varchar(250) NOT NULL,
  `DigitalSettlementType` varchar(250) DEFAULT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `CP_ID` (`CP_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1308 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cp_product`
--

DROP TABLE IF EXISTS `cp_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cp_product` (
  `SERVICE_ID` int(11) NOT NULL,
  `SERVICE_NAME` varchar(100) NOT NULL,
  PRIMARY KEY (`SERVICE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cp_product_backup`
--

DROP TABLE IF EXISTS `cp_product_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cp_product_backup` (
  `SERVICE_ID` int(11) NOT NULL,
  `SERVICE_NAME` varchar(100) NOT NULL,
  PRIMARY KEY (`SERVICE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `merchant_fee`
--

DROP TABLE IF EXISTS `merchant_fee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `merchant_fee` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MERCHANT_ID` int(11) NOT NULL,
  `MERCHANT_FEE` double NOT NULL,
  `DATE_UPDATED` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nav_detail`
--

DROP TABLE IF EXISTS `nav_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nav_detail` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAVH_ID` int(11) NOT NULL,
  `PROD_ID` int(11) NOT NULL,
  `BillItem` varchar(20) NOT NULL,
  `FaceValue` double NOT NULL,
  `OutputVAT` double NOT NULL,
  `VATCond` varchar(20) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `NAVH_ID` (`NAVH_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nav_header`
--

DROP TABLE IF EXISTS `nav_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nav_header` (
  `NAVH_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CP_ID` int(11) NOT NULL,
  `MERCHANT_ID` int(11) NOT NULL,
  `PA_ID` int(11) NOT NULL,
  `RECON_ID` int(11) NOT NULL,
  `PROD_ID` int(11) NOT NULL,
  `DateofReceipt` date NOT NULL,
  `ExpectedDueDate` date NOT NULL,
  `TotalAmount` double NOT NULL,
  PRIMARY KEY (`NAVH_ID`),
  KEY `CP_ID` (`CP_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `old_reconcilation`
--

DROP TABLE IF EXISTS `old_reconcilation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `old_reconcilation` (
  `ID` int(11) NOT NULL,
  `RECON_ID` varchar(50) NOT NULL,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `MERCHANT_ID` int(20) NOT NULL,
  `BRANCH_ID` varchar(25) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` int(20) NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PROD_ID` int(10) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `RECON_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PA_ID` int(11) NOT NULL,
  `PAYMENT_MODE` text,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `PA_ID` (`PA_ID`) USING BTREE,
  KEY `RECON_ID` (`RECON_ID`) USING BTREE,
  KEY `REDEEM_ID` (`REDEEM_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `old_redemption`
--

DROP TABLE IF EXISTS `old_redemption`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `old_redemption` (
  `ID` int(11) NOT NULL,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int(20) NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(25) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` int(20) NOT NULL,
  `PROD_ID` int(10) NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `REDEEM_ID` (`REDEEM_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pa_detail`
--

DROP TABLE IF EXISTS `pa_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_detail` (
  `PA_DID` int(11) NOT NULL AUTO_INCREMENT,
  `PA_ID` int(11) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `RECON_ID` varchar(50) NOT NULL,
  `RATE` decimal(10,2) NOT NULL,
  `NUM_PASSES` int(11) NOT NULL,
  `TOTAL_FV` double NOT NULL,
  `MARKETING_FEE` decimal(19,5) NOT NULL,
  `VAT` decimal(19,5) NOT NULL,
  `NET_DUE` decimal(19,5) NOT NULL,
  `TOTAL_REFUND` decimal(19,5) NOT NULL,
  `DATE_CREATED` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PA_DID`),
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `PA_ID` (`PA_ID`) USING BTREE,
  KEY `RECON_ID` (`RECON_ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1031191 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pa_header`
--

DROP TABLE IF EXISTS `pa_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_header` (
  `PA_ID` int(11) NOT NULL AUTO_INCREMENT,
  `MERCHANT_ID` int(20) NOT NULL,
  `MERCHANT_FEE` decimal(10,5) DEFAULT NULL,
  `vatcond` varchar(100) NOT NULL,
  `REIMBURSEMENT_DATE` datetime NOT NULL,
  `ExpectedDueDate` date DEFAULT NULL,
  `USER_ID` int(11) NOT NULL,
  `GENERATED` int(11) NOT NULL COMMENT '0:GENERATED; 1:COPY',
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PA_ID`),
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `USER_ID` (`USER_ID`) USING BTREE,
  KEY `DATE_CREATED` (`DATE_CREATED`) USING BTREE,
  KEY `REIMBURSEMENT_DATE` (`REIMBURSEMENT_DATE`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29166 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_history`
--

DROP TABLE IF EXISTS `password_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(45) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `change_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_password_change_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment_cutoff`
--

DROP TABLE IF EXISTS `payment_cutoff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_cutoff` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MERCHANT_ID` int(11) DEFAULT NULL,
  `TYPE` varchar(15) NOT NULL COMMENT 'weekly : semi_month',
  `SPECIFIC_DAY` varchar(250) NOT NULL COMMENT '(M, TUE, W, THU, FRI, SAT, SUN)',
  `SPECIFIC_DATE` varchar(250) NOT NULL COMMENT '{array of dates}',
  `DigitalSettlementType` varchar(250) NOT NULL,
  `DATE_CREATED` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `DATE_MODIFIED` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `MERCHANT_ID` (`MERCHANT_ID`),
  KEY `DigitalSettlementType` (`DigitalSettlementType`) USING BTREE,
  KEY `TYPE` (`TYPE`) USING BTREE,
  KEY `SPECIFIC_DAY` (`SPECIFIC_DAY`) USING BTREE,
  KEY `SPECIFIC_DATE` (`SPECIFIC_DATE`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1022 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reconcilation`
--

DROP TABLE IF EXISTS `reconcilation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reconcilation` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RECON_ID` varchar(50) NOT NULL,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `MERCHANT_ID` int(20) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PROD_ID` int(10) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `RECON_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PA_ID` int(11) NOT NULL,
  `REFUND_ID` int(11) NOT NULL,
  `payment_mode` text,
  `REDEEM_TBL_ID` int(11) NOT NULL,
  `STAGE` varchar(50) NOT NULL,
  `PA_TEMPID` int(11) NOT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SOURCE_FILE` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `PA_ID` (`PA_ID`) USING BTREE,
  KEY `RECON_ID` (`RECON_ID`) USING BTREE,
  KEY `REDEEM_ID` (`REDEEM_ID`) USING BTREE,
  KEY `REFUND_ID` (`REFUND_ID`),
  KEY `REDEEM_TBL_ID` (`REDEEM_TBL_ID`),
  KEY `PA_TEMPID` (`PA_TEMPID`),
  CONSTRAINT `reconcilation_ibfk_1` FOREIGN KEY (`MERCHANT_ID`) REFERENCES `branch_merchant` (`MERCHANT_ID`),
  CONSTRAINT `reconcilation_ibfk_2` FOREIGN KEY (`BRANCH_ID`) REFERENCES `branch_merchant` (`BRANCH_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2231578 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `redemption`
--

DROP TABLE IF EXISTS `redemption`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemption` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int(11) NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `PROD_ID` int(10) NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `REFUND_ID` int(11) NOT NULL,
  `PA_ID` int(11) NOT NULL,
  `PA_TEMPID` int(11) NOT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SOURCE_FILE` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `REDEEM_ID_STAGE_PROD_ID_PA_ID_TRXN_ID` (`REDEEM_ID`,`STAGE`,`PROD_ID`,`PA_ID`,`TRANSACTION_ID`,`TRANSACTION_VALUE`) USING BTREE,
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `REDEEM_ID` (`REDEEM_ID`) USING BTREE,
  KEY `REFUND_ID` (`REFUND_ID`),
  KEY `PA_ID` (`PA_ID`),
  KEY `PA_TEMPID` (`PA_TEMPID`),
  KEY `PROD_ID` (`PROD_ID`) USING BTREE,
  KEY `STAGE` (`STAGE`) USING BTREE,
  KEY `SOURCE_FILE` (`SOURCE_FILE`) USING BTREE,
  KEY `TRANSACTION_ID` (`TRANSACTION_ID`) USING BTREE,
  KEY `TRANSACTION_DATE_TIME` (`TRANSACTION_DATE_TIME`) USING BTREE,
  KEY `DATE_CREATED` (`DATE_CREATED`) USING BTREE,
  KEY `VOUCHER_CODE` (`VOUCHER_CODE`) USING BTREE,
  CONSTRAINT `redemption_ibfk_1` FOREIGN KEY (`MERCHANT_ID`) REFERENCES `branch_merchant` (`MERCHANT_ID`),
  CONSTRAINT `redemption_ibfk_2` FOREIGN KEY (`BRANCH_ID`) REFERENCES `branch_merchant` (`BRANCH_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=9323515 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `redemption_20230608`
--

DROP TABLE IF EXISTS `redemption_20230608`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemption_20230608` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int(11) NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `PROD_ID` int(10) NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `REFUND_ID` int(11) NOT NULL,
  `PA_ID` int(11) NOT NULL,
  `PA_TEMPID` int(11) NOT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SOURCE_FILE` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `REDEEM_ID` (`REDEEM_ID`) USING BTREE,
  KEY `REFUND_ID` (`REFUND_ID`),
  KEY `PA_ID` (`PA_ID`),
  KEY `PA_TEMPID` (`PA_TEMPID`),
  KEY `PROD_ID` (`PROD_ID`) USING BTREE,
  KEY `STAGE` (`STAGE`) USING BTREE,
  KEY `SOURCE_FILE` (`SOURCE_FILE`) USING BTREE,
  KEY `TRANSACTION_ID` (`TRANSACTION_ID`) USING BTREE,
  KEY `redemption_TRANSACTION_DATE_TIME_IDX` (`TRANSACTION_DATE_TIME`) USING BTREE,
  KEY `DATE_CREATED` (`DATE_CREATED`) USING BTREE,
  KEY `VOUCHER_CODE` (`VOUCHER_CODE`) USING BTREE,
  CONSTRAINT `redemption_20230608_ibfk_1` FOREIGN KEY (`MERCHANT_ID`) REFERENCES `branch_merchant` (`MERCHANT_ID`),
  CONSTRAINT `redemption_20230608_ibfk_2` FOREIGN KEY (`BRANCH_ID`) REFERENCES `branch_merchant` (`BRANCH_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3646311 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `redemption_backup2024_march_12`
--

DROP TABLE IF EXISTS `redemption_backup2024_march_12`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemption_backup2024_march_12` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int(11) NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `PROD_ID` int(10) NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `REFUND_ID` int(11) NOT NULL,
  `PA_ID` int(11) NOT NULL,
  `PA_TEMPID` int(11) NOT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SOURCE_FILE` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `REDEEM_ID_STAGE_PROD_ID_PA_ID_TRXN_ID` (`REDEEM_ID`,`STAGE`,`PROD_ID`,`PA_ID`,`TRANSACTION_ID`,`TRANSACTION_VALUE`) USING BTREE,
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `REDEEM_ID` (`REDEEM_ID`) USING BTREE,
  KEY `REFUND_ID` (`REFUND_ID`),
  KEY `PA_ID` (`PA_ID`),
  KEY `PA_TEMPID` (`PA_TEMPID`),
  KEY `PROD_ID` (`PROD_ID`) USING BTREE,
  KEY `STAGE` (`STAGE`) USING BTREE,
  KEY `SOURCE_FILE` (`SOURCE_FILE`) USING BTREE,
  KEY `TRANSACTION_ID` (`TRANSACTION_ID`) USING BTREE,
  KEY `TRANSACTION_DATE_TIME` (`TRANSACTION_DATE_TIME`) USING BTREE,
  KEY `DATE_CREATED` (`DATE_CREATED`) USING BTREE,
  KEY `VOUCHER_CODE` (`VOUCHER_CODE`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5806155 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `redemption_pa6524`
--

DROP TABLE IF EXISTS `redemption_pa6524`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemption_pa6524` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int(11) NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `PROD_ID` int(10) NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `REFUND_ID` int(11) NOT NULL,
  `PA_ID` int(11) NOT NULL,
  `PA_TEMPID` int(11) NOT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SOURCE_FILE` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `REDEEM_ID_STAGE_PROD_ID_PA_ID_TRXN_ID` (`REDEEM_ID`,`STAGE`,`PROD_ID`,`PA_ID`,`TRANSACTION_ID`) USING BTREE,
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `REDEEM_ID` (`REDEEM_ID`) USING BTREE,
  KEY `REFUND_ID` (`REFUND_ID`),
  KEY `PA_ID` (`PA_ID`),
  KEY `PA_TEMPID` (`PA_TEMPID`),
  KEY `PROD_ID` (`PROD_ID`) USING BTREE,
  KEY `STAGE` (`STAGE`) USING BTREE,
  KEY `SOURCE_FILE` (`SOURCE_FILE`) USING BTREE,
  KEY `TRANSACTION_ID` (`TRANSACTION_ID`) USING BTREE,
  KEY `TRANSACTION_DATE_TIME` (`TRANSACTION_DATE_TIME`) USING BTREE,
  KEY `DATE_CREATED` (`DATE_CREATED`) USING BTREE,
  KEY `VOUCHER_CODE` (`VOUCHER_CODE`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3774894 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `redemption_paid8308_8098_7842`
--

DROP TABLE IF EXISTS `redemption_paid8308_8098_7842`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemption_paid8308_8098_7842` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int(11) NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `PROD_ID` int(10) NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `REFUND_ID` int(11) NOT NULL,
  `PA_ID` int(11) NOT NULL,
  `PA_TEMPID` int(11) NOT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SOURCE_FILE` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `REDEEM_ID_STAGE_PROD_ID_PA_ID_TRXN_ID` (`REDEEM_ID`,`STAGE`,`PROD_ID`,`PA_ID`,`TRANSACTION_ID`,`TRANSACTION_VALUE`) USING BTREE,
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `REDEEM_ID` (`REDEEM_ID`) USING BTREE,
  KEY `REFUND_ID` (`REFUND_ID`),
  KEY `PA_ID` (`PA_ID`),
  KEY `PA_TEMPID` (`PA_TEMPID`),
  KEY `PROD_ID` (`PROD_ID`) USING BTREE,
  KEY `STAGE` (`STAGE`) USING BTREE,
  KEY `SOURCE_FILE` (`SOURCE_FILE`) USING BTREE,
  KEY `TRANSACTION_ID` (`TRANSACTION_ID`) USING BTREE,
  KEY `TRANSACTION_DATE_TIME` (`TRANSACTION_DATE_TIME`) USING BTREE,
  KEY `DATE_CREATED` (`DATE_CREATED`) USING BTREE,
  KEY `VOUCHER_CODE` (`VOUCHER_CODE`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3395274 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `redemption_test_20230224`
--

DROP TABLE IF EXISTS `redemption_test_20230224`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemption_test_20230224` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int(20) NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `PROD_ID` int(10) NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `REFUND_ID` int(11) NOT NULL,
  `PA_ID` int(11) NOT NULL,
  `PA_TEMPID` int(11) NOT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SOURCE_FILE` varchar(500) DEFAULT NULL,
  `COUNT` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `REDEEM_ID` (`REDEEM_ID`) USING BTREE,
  KEY `REFUND_ID` (`REFUND_ID`),
  KEY `PA_ID` (`PA_ID`),
  KEY `PA_TEMPID` (`PA_TEMPID`)
) ENGINE=InnoDB AUTO_INCREMENT=3161257 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `refund`
--

DROP TABLE IF EXISTS `refund`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refund` (
  `REFUND_ID` int(11) NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `REVERSAL_TRANSACTION_ID` varchar(100) NOT NULL,
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `RECON_ID` varchar(50) NOT NULL,
  `REVERSAL_MODE` varchar(50) NOT NULL,
  `PROD_ID` int(11) NOT NULL,
  `REDEEM_STATUS` varchar(50) NOT NULL,
  `MERCHANT_ID` int(11) NOT NULL,
  `BRANCH_ID` varchar(100) CHARACTER SET latin1 NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `UPLOAD_ID` int(11) NOT NULL,
  `REVERSAL_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DATE_CREATED` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PA_ID` int(11) NOT NULL COMMENT 'adjustment PA',
  `REDEEM_TBL_ID` int(11) DEFAULT NULL,
  `RECON_TBL_ID` int(11) NOT NULL,
  `PA_TEMPID` int(11) NOT NULL,
  `SOURCE_FILE` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`REFUND_ID`),
  KEY `upload_id` (`UPLOAD_ID`),
  KEY `recon_id` (`RECON_ID`),
  KEY `merchant_id` (`MERCHANT_ID`),
  KEY `branch_id` (`BRANCH_ID`),
  KEY `user_id` (`USER_ID`),
  KEY `PA_ID` (`PA_ID`),
  KEY `REVERSAL_TRANSACTION_ID` (`REVERSAL_TRANSACTION_ID`),
  KEY `TRANSACTION_ID` (`TRANSACTION_ID`),
  KEY `PROD_ID` (`PROD_ID`),
  KEY `RECON_ID_2` (`RECON_ID`),
  KEY `REDEEM_TBL_ID` (`REDEEM_TBL_ID`),
  KEY `RECON_TBL_ID` (`RECON_TBL_ID`),
  KEY `PA_TEMPID` (`PA_TEMPID`),
  KEY `refund_REVERSAL_DATE_TIME_IDX` (`REVERSAL_DATE_TIME`) USING BTREE,
  KEY `REVERSAL_MODE` (`REVERSAL_MODE`) USING BTREE,
  KEY `REDEEM_STATUS` (`REDEEM_STATUS`) USING BTREE,
  KEY `REVERSAL_DATE_TIME` (`REVERSAL_DATE_TIME`) USING BTREE,
  CONSTRAINT `refund_ibfk_1` FOREIGN KEY (`MERCHANT_ID`) REFERENCES `branch_merchant` (`MERCHANT_ID`),
  CONSTRAINT `refund_ibfk_2` FOREIGN KEY (`BRANCH_ID`) REFERENCES `branch_merchant` (`BRANCH_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=51069 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rs_detail`
--

DROP TABLE IF EXISTS `rs_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rs_detail` (
  `RS_DID` int(11) NOT NULL AUTO_INCREMENT,
  `RS_ID` int(11) NOT NULL,
  `COV_ID` int(11) NOT NULL,
  `RATE` int(11) NOT NULL,
  `TOTAL_FV` double NOT NULL,
  `MARKETING_FEE` decimal(10,2) NOT NULL,
  `VAT` decimal(10,2) NOT NULL,
  `NET_DUE` decimal(10,2) NOT NULL,
  `DATE_CREATED` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`RS_DID`),
  UNIQUE KEY `COV_ID` (`COV_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rs_header`
--

DROP TABLE IF EXISTS `rs_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rs_header` (
  `RS_ID` int(11) NOT NULL AUTO_INCREMENT,
  `RS_NUMBER` varchar(250) NOT NULL COMMENT 'DIS + "_" + BRANCH_ID + "_" + PAYMENTDUEDATE + "_" + RS_ID',
  `MERCHANT_ID` int(11) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `MERCHANT_FEE` decimal(10,2) DEFAULT NULL,
  `VATCOND` varchar(100) NOT NULL,
  `REIMBURSEMENT_DATE` datetime NOT NULL,
  `ExpectedDueDate` date NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `GENERATED` int(11) NOT NULL COMMENT '0:GENERATED; 1:COPY',
  `DATE_CREATED` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`RS_ID`),
  UNIQUE KEY `RS_NUMBER` (`RS_NUMBER`),
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_user`
--

DROP TABLE IF EXISTS `tbl_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_user` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(20) NOT NULL,
  `remarks` varchar(60) NOT NULL,
  `userid` int(11) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `temp_refund`
--

DROP TABLE IF EXISTS `temp_refund`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `temp_refund` (
  `TEMP_REFUNDID` int(11) NOT NULL AUTO_INCREMENT,
  `REFUND_ID` int(11) NOT NULL,
  `MERCHANT_NAME` varchar(250) NOT NULL,
  `MERCHANT_ID` varchar(250) NOT NULL,
  `BRANCH_ID` varchar(250) NOT NULL,
  `POS_ID` varchar(250) NOT NULL,
  `POS_TXN_ID` varchar(250) NOT NULL,
  `PROD_ID` varchar(250) NOT NULL,
  `TRANSACTION_DATE_TIME` varchar(250) NOT NULL,
  `TRANSACTION_ID` varchar(250) NOT NULL,
  `REDEMPTION_API_TRANSACTION_ID` varchar(250) NOT NULL,
  `REVERSAL_DATE_TIME` varchar(250) NOT NULL,
  `REVERSAL_TRANSACTION_ID` varchar(250) NOT NULL,
  `VOUCHER_CODE` varchar(250) NOT NULL,
  `TRANSACTION_VALUE` varchar(250) NOT NULL,
  `RECON_API_TRANSACTION_ID` varchar(250) NOT NULL,
  `PAYMENT_MODE` varchar(250) NOT NULL,
  `REVERSAL_MODE` varchar(250) NOT NULL,
  `UPLOAD_ID` int(11) NOT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ERROR_MESSAGE` varchar(250) NOT NULL,
  PRIMARY KEY (`TEMP_REFUNDID`),
  KEY `REFUND_ID` (`REFUND_ID`),
  KEY `UPLOAD_ID` (`UPLOAD_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=59719 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `utype_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `activation_code` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `utype`
--

DROP TABLE IF EXISTS `utype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utype` (
  `utype_id` int(11) NOT NULL AUTO_INCREMENT,
  `utype_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `utype_desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`utype_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-12 14:31:37
