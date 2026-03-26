-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: dis_schema
-- ------------------------------------------------------
-- Server version	8.0.40

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
  `id` int NOT NULL AUTO_INCREMENT,
  `acc_id` int NOT NULL,
  `user_id` int NOT NULL,
  `acc_read_only` int NOT NULL DEFAULT '0',
  `acc_all_access` int NOT NULL DEFAULT '0',
  `def_page` int NOT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_by` int NOT NULL DEFAULT '0',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `access_role_id` (`acc_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_permission`
--

LOCK TABLES `access_permission` WRITE;
/*!40000 ALTER TABLE `access_permission` DISABLE KEYS */;
/*!40000 ALTER TABLE `access_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `access_role`
--

DROP TABLE IF EXISTS `access_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `access_role` (
  `acc_id` int NOT NULL AUTO_INCREMENT,
  `acc_name` varchar(255) NOT NULL,
  `acc_code` varchar(50) NOT NULL,
  `acc_status` int NOT NULL DEFAULT '0' COMMENT '0:active; 1:inactive',
  PRIMARY KEY (`acc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_role`
--

LOCK TABLES `access_role` WRITE;
/*!40000 ALTER TABLE `access_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `access_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ajax_update`
--

DROP TABLE IF EXISTS `ajax_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ajax_update` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `date_created` date NOT NULL,
  `last_update` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_created` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ajax_update`
--

LOCK TABLES `ajax_update` WRITE;
/*!40000 ALTER TABLE `ajax_update` DISABLE KEYS */;
/*!40000 ALTER TABLE `ajax_update` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_action`
--

DROP TABLE IF EXISTS `audit_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_action` (
  `auc_id` int NOT NULL AUTO_INCREMENT,
  `auc_desc` varchar(10) NOT NULL,
  `auc_name` varchar(50) NOT NULL,
  PRIMARY KEY (`auc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_action`
--

LOCK TABLES `audit_action` WRITE;
/*!40000 ALTER TABLE `audit_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_trail`
--

DROP TABLE IF EXISTS `audit_trail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_trail` (
  `audit_id` int NOT NULL AUTO_INCREMENT,
  `action` int NOT NULL COMMENT '0:login; 1;logout 2:add; 3:update; 4:delete; 5:cancel; 5:submit;',
  `user_id` int NOT NULL,
  `pa_id` int NOT NULL,
  `logist_id` int NOT NULL,
  `dist_status` int NOT NULL,
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`audit_id`),
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7830 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_trail`
--

LOCK TABLES `audit_trail` WRITE;
/*!40000 ALTER TABLE `audit_trail` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_trail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_upload`
--

DROP TABLE IF EXISTS `audit_upload`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_upload` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module_name` varchar(250) NOT NULL,
  `file_name` varchar(250) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `file_name` (`file_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21159 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_upload`
--

LOCK TABLES `audit_upload` WRITE;
/*!40000 ALTER TABLE `audit_upload` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_upload` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branch_merchant`
--

DROP TABLE IF EXISTS `branch_merchant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branch_merchant` (
  `MERCHANT_ID` int NOT NULL,
  `BRANCH_ID` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  UNIQUE KEY `mid_bid` (`MERCHANT_ID`,`BRANCH_ID`),
  KEY `MERCHANT_ID` (`MERCHANT_ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branch_merchant`
--

LOCK TABLES `branch_merchant` WRITE;
/*!40000 ALTER TABLE `branch_merchant` DISABLE KEYS */;
/*!40000 ALTER TABLE `branch_merchant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `BRANCH_ID` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `MERCHANT_ID` int NOT NULL,
  `cp_id` int NOT NULL,
  `BRANCH_NAME` varchar(250) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=31129 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversion`
--

DROP TABLE IF EXISTS `conversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversion` (
  `COV_ID` int NOT NULL AUTO_INCREMENT,
  `MERCHANT_ID` int NOT NULL,
  `BRANCH_ID` varchar(25) NOT NULL,
  `BRANCH_NAME` varchar(250) NOT NULL,
  `USER_ID` int NOT NULL,
  `NAME` varchar(100) NOT NULL,
  `TOTAL_AMOUNT` decimal(10,2) NOT NULL,
  `VOUCHER_CODES` varchar(25) NOT NULL,
  `DENO` decimal(10,2) NOT NULL,
  `STAGE` varchar(100) NOT NULL,
  `CHANNEL` varchar(100) NOT NULL,
  `CREATED_AT` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `AGENT_ID` int NOT NULL,
  `PROD_ID` int NOT NULL,
  `RS_ID` int NOT NULL,
  `DATE_CREATED` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`COV_ID`),
  UNIQUE KEY `VOUCHER_CODES` (`VOUCHER_CODES`),
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `PROD_ID` (`PROD_ID`) USING BTREE,
  KEY `RS_ID` (`RS_ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=459 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversion`
--

LOCK TABLES `conversion` WRITE;
/*!40000 ALTER TABLE `conversion` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cp_agreement`
--

DROP TABLE IF EXISTS `cp_agreement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cp_agreement` (
  `AGREEMENT_ID` int NOT NULL,
  `CP_ID` int NOT NULL,
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
  `PayeeQtyOfDays` int NOT NULL,
  `PayeeDayType` varchar(100) NOT NULL,
  `PayeeComments` varchar(250) NOT NULL,
  `MerchantFee` double NOT NULL,
  `VATCond` varchar(20) NOT NULL,
  `InsertType` varchar(1) NOT NULL COMMENT 'I , U , X - donot include for migration',
  UNIQUE KEY `AgreementID` (`AGREEMENT_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cp_agreement`
--

LOCK TABLES `cp_agreement` WRITE;
/*!40000 ALTER TABLE `cp_agreement` DISABLE KEYS */;
/*!40000 ALTER TABLE `cp_agreement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cp_merchant`
--

DROP TABLE IF EXISTS `cp_merchant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cp_merchant` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CP_ID` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `TIN` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `LegalName` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `TradingName` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `GroupTIN` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `GroupName` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Address` varchar(250) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ContactPerson` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ContactNumber` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `MeanofPayment` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL COMMENT 'Cash or Credit Client',
  `PayeeCode` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `BankName` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `BankBranchCode` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `BankAccountNumber` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `PayeeName` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `PayeeId` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `AffiliateGroupCode` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0',
  `PayeeQtyOfDays` int NOT NULL,
  `PayeeDayType` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `PayeeComments` varchar(250) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `MerchantFee` double DEFAULT NULL,
  `Industry` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `VATCond` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `InsertType` varchar(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL COMMENT 'I , U , X - donot include for migration',
  `MerchantType` varchar(250) NOT NULL,
  `DigitalSettlementType` varchar(250) DEFAULT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `CP_ID` (`CP_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1308 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cp_merchant`
--

LOCK TABLES `cp_merchant` WRITE;
/*!40000 ALTER TABLE `cp_merchant` DISABLE KEYS */;
/*!40000 ALTER TABLE `cp_merchant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cp_product`
--

DROP TABLE IF EXISTS `cp_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cp_product` (
  `SERVICE_ID` int NOT NULL,
  `SERVICE_NAME` varchar(100) NOT NULL,
  PRIMARY KEY (`SERVICE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cp_product`
--

LOCK TABLES `cp_product` WRITE;
/*!40000 ALTER TABLE `cp_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `cp_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cp_product_backup`
--

DROP TABLE IF EXISTS `cp_product_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cp_product_backup` (
  `SERVICE_ID` int NOT NULL,
  `SERVICE_NAME` varchar(100) NOT NULL,
  PRIMARY KEY (`SERVICE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cp_product_backup`
--

LOCK TABLES `cp_product_backup` WRITE;
/*!40000 ALTER TABLE `cp_product_backup` DISABLE KEYS */;
/*!40000 ALTER TABLE `cp_product_backup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merchant_fee`
--

DROP TABLE IF EXISTS `merchant_fee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `merchant_fee` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `MERCHANT_ID` int NOT NULL,
  `MERCHANT_FEE` double NOT NULL,
  `DATE_UPDATED` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merchant_fee`
--

LOCK TABLES `merchant_fee` WRITE;
/*!40000 ALTER TABLE `merchant_fee` DISABLE KEYS */;
/*!40000 ALTER TABLE `merchant_fee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nav_detail`
--

DROP TABLE IF EXISTS `nav_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nav_detail` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAVH_ID` int NOT NULL,
  `PROD_ID` int NOT NULL,
  `BillItem` varchar(20) NOT NULL,
  `FaceValue` double NOT NULL,
  `OutputVAT` double NOT NULL,
  `VATCond` varchar(20) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `NAVH_ID` (`NAVH_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nav_detail`
--

LOCK TABLES `nav_detail` WRITE;
/*!40000 ALTER TABLE `nav_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `nav_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nav_header`
--

DROP TABLE IF EXISTS `nav_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nav_header` (
  `NAVH_ID` int NOT NULL AUTO_INCREMENT,
  `CP_ID` int NOT NULL,
  `MERCHANT_ID` int NOT NULL,
  `PA_ID` int NOT NULL,
  `RECON_ID` int NOT NULL,
  `PROD_ID` int NOT NULL,
  `DateofReceipt` date NOT NULL,
  `ExpectedDueDate` date NOT NULL,
  `TotalAmount` double NOT NULL,
  PRIMARY KEY (`NAVH_ID`),
  KEY `CP_ID` (`CP_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nav_header`
--

LOCK TABLES `nav_header` WRITE;
/*!40000 ALTER TABLE `nav_header` DISABLE KEYS */;
/*!40000 ALTER TABLE `nav_header` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `old_reconcilation`
--

DROP TABLE IF EXISTS `old_reconcilation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `old_reconcilation` (
  `ID` int NOT NULL,
  `RECON_ID` varchar(50) NOT NULL,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `MERCHANT_ID` int NOT NULL,
  `BRANCH_ID` varchar(25) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` int NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PROD_ID` int NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `RECON_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PA_ID` int NOT NULL,
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
-- Dumping data for table `old_reconcilation`
--

LOCK TABLES `old_reconcilation` WRITE;
/*!40000 ALTER TABLE `old_reconcilation` DISABLE KEYS */;
/*!40000 ALTER TABLE `old_reconcilation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `old_redemption`
--

DROP TABLE IF EXISTS `old_redemption`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `old_redemption` (
  `ID` int NOT NULL,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(25) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` int NOT NULL,
  `PROD_ID` int NOT NULL,
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
-- Dumping data for table `old_redemption`
--

LOCK TABLES `old_redemption` WRITE;
/*!40000 ALTER TABLE `old_redemption` DISABLE KEYS */;
/*!40000 ALTER TABLE `old_redemption` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_detail`
--

DROP TABLE IF EXISTS `pa_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_detail` (
  `PA_DID` int NOT NULL AUTO_INCREMENT,
  `PA_ID` int NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `RECON_ID` varchar(50) NOT NULL,
  `RATE` decimal(10,2) NOT NULL,
  `NUM_PASSES` int NOT NULL,
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
-- Dumping data for table `pa_detail`
--

LOCK TABLES `pa_detail` WRITE;
/*!40000 ALTER TABLE `pa_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `pa_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_header`
--

DROP TABLE IF EXISTS `pa_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_header` (
  `PA_ID` int NOT NULL AUTO_INCREMENT,
  `MERCHANT_ID` int NOT NULL,
  `MERCHANT_FEE` decimal(10,5) DEFAULT NULL,
  `vatcond` varchar(100) NOT NULL,
  `REIMBURSEMENT_DATE` datetime NOT NULL,
  `ExpectedDueDate` date DEFAULT NULL,
  `USER_ID` int NOT NULL,
  `GENERATED` int NOT NULL COMMENT '0:GENERATED; 1:COPY',
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PA_ID`),
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE,
  KEY `USER_ID` (`USER_ID`) USING BTREE,
  KEY `DATE_CREATED` (`DATE_CREATED`) USING BTREE,
  KEY `REIMBURSEMENT_DATE` (`REIMBURSEMENT_DATE`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29166 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_header`
--

LOCK TABLES `pa_header` WRITE;
/*!40000 ALTER TABLE `pa_header` DISABLE KEYS */;
/*!40000 ALTER TABLE `pa_header` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_history`
--

DROP TABLE IF EXISTS `password_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` varchar(45) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `change_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_password_change_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_history`
--

LOCK TABLES `password_history` WRITE;
/*!40000 ALTER TABLE `password_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_cutoff`
--

DROP TABLE IF EXISTS `payment_cutoff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_cutoff` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `MERCHANT_ID` int DEFAULT NULL,
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
-- Dumping data for table `payment_cutoff`
--

LOCK TABLES `payment_cutoff` WRITE;
/*!40000 ALTER TABLE `payment_cutoff` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_cutoff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reconcilation`
--

DROP TABLE IF EXISTS `reconcilation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reconcilation` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `RECON_ID` varchar(50) NOT NULL,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `MERCHANT_ID` int NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PROD_ID` int NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `RECON_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PA_ID` int NOT NULL,
  `REFUND_ID` int NOT NULL,
  `payment_mode` text,
  `REDEEM_TBL_ID` int NOT NULL,
  `STAGE` varchar(50) NOT NULL,
  `PA_TEMPID` int NOT NULL,
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
-- Dumping data for table `reconcilation`
--

LOCK TABLES `reconcilation` WRITE;
/*!40000 ALTER TABLE `reconcilation` DISABLE KEYS */;
/*!40000 ALTER TABLE `reconcilation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redemption`
--

DROP TABLE IF EXISTS `redemption`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemption` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `PROD_ID` int NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `REFUND_ID` int NOT NULL,
  `PA_ID` int NOT NULL,
  `PA_TEMPID` int NOT NULL,
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
-- Dumping data for table `redemption`
--

LOCK TABLES `redemption` WRITE;
/*!40000 ALTER TABLE `redemption` DISABLE KEYS */;
/*!40000 ALTER TABLE `redemption` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redemption_20230608`
--

DROP TABLE IF EXISTS `redemption_20230608`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemption_20230608` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `PROD_ID` int NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `REFUND_ID` int NOT NULL,
  `PA_ID` int NOT NULL,
  `PA_TEMPID` int NOT NULL,
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
-- Dumping data for table `redemption_20230608`
--

LOCK TABLES `redemption_20230608` WRITE;
/*!40000 ALTER TABLE `redemption_20230608` DISABLE KEYS */;
/*!40000 ALTER TABLE `redemption_20230608` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redemption_backup2024_march_12`
--

DROP TABLE IF EXISTS `redemption_backup2024_march_12`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemption_backup2024_march_12` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `PROD_ID` int NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `REFUND_ID` int NOT NULL,
  `PA_ID` int NOT NULL,
  `PA_TEMPID` int NOT NULL,
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
-- Dumping data for table `redemption_backup2024_march_12`
--

LOCK TABLES `redemption_backup2024_march_12` WRITE;
/*!40000 ALTER TABLE `redemption_backup2024_march_12` DISABLE KEYS */;
/*!40000 ALTER TABLE `redemption_backup2024_march_12` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redemption_pa6524`
--

DROP TABLE IF EXISTS `redemption_pa6524`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemption_pa6524` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `PROD_ID` int NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `REFUND_ID` int NOT NULL,
  `PA_ID` int NOT NULL,
  `PA_TEMPID` int NOT NULL,
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
-- Dumping data for table `redemption_pa6524`
--

LOCK TABLES `redemption_pa6524` WRITE;
/*!40000 ALTER TABLE `redemption_pa6524` DISABLE KEYS */;
/*!40000 ALTER TABLE `redemption_pa6524` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redemption_paid8308_8098_7842`
--

DROP TABLE IF EXISTS `redemption_paid8308_8098_7842`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemption_paid8308_8098_7842` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `PROD_ID` int NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `REFUND_ID` int NOT NULL,
  `PA_ID` int NOT NULL,
  `PA_TEMPID` int NOT NULL,
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
-- Dumping data for table `redemption_paid8308_8098_7842`
--

LOCK TABLES `redemption_paid8308_8098_7842` WRITE;
/*!40000 ALTER TABLE `redemption_paid8308_8098_7842` DISABLE KEYS */;
/*!40000 ALTER TABLE `redemption_paid8308_8098_7842` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redemption_test_20230224`
--

DROP TABLE IF EXISTS `redemption_test_20230224`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemption_test_20230224` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `MERCHANT_ID` int NOT NULL,
  `MERCHANT_NAME` varchar(100) NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `POS_ID` varchar(25) NOT NULL,
  `POS_TXN_ID` varchar(100) NOT NULL,
  `PROD_ID` int NOT NULL,
  `VOUCHER_CODE` varchar(25) NOT NULL,
  `TRANSACTION_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `TRANSACTION_VALUE` decimal(10,2) NOT NULL,
  `STAGE` varchar(20) NOT NULL,
  `PAYMENT_MODE` text,
  `REFUND_ID` int NOT NULL,
  `PA_ID` int NOT NULL,
  `PA_TEMPID` int NOT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SOURCE_FILE` varchar(500) DEFAULT NULL,
  `COUNT` int DEFAULT NULL,
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
-- Dumping data for table `redemption_test_20230224`
--

LOCK TABLES `redemption_test_20230224` WRITE;
/*!40000 ALTER TABLE `redemption_test_20230224` DISABLE KEYS */;
/*!40000 ALTER TABLE `redemption_test_20230224` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refund`
--

DROP TABLE IF EXISTS `refund`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refund` (
  `REFUND_ID` int NOT NULL AUTO_INCREMENT,
  `REDEEM_ID` varchar(50) NOT NULL,
  `REVERSAL_TRANSACTION_ID` varchar(100) NOT NULL,
  `TRANSACTION_ID` varchar(50) NOT NULL,
  `RECON_ID` varchar(50) NOT NULL,
  `REVERSAL_MODE` varchar(50) NOT NULL,
  `PROD_ID` int NOT NULL,
  `REDEEM_STATUS` varchar(50) NOT NULL,
  `MERCHANT_ID` int NOT NULL,
  `BRANCH_ID` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `USER_ID` int NOT NULL,
  `UPLOAD_ID` int NOT NULL,
  `REVERSAL_DATE_TIME` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DATE_CREATED` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PA_ID` int NOT NULL COMMENT 'adjustment PA',
  `REDEEM_TBL_ID` int DEFAULT NULL,
  `RECON_TBL_ID` int NOT NULL,
  `PA_TEMPID` int NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=51069 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refund`
--

LOCK TABLES `refund` WRITE;
/*!40000 ALTER TABLE `refund` DISABLE KEYS */;
/*!40000 ALTER TABLE `refund` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rs_detail`
--

DROP TABLE IF EXISTS `rs_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rs_detail` (
  `RS_DID` int NOT NULL AUTO_INCREMENT,
  `RS_ID` int NOT NULL,
  `COV_ID` int NOT NULL,
  `RATE` int NOT NULL,
  `TOTAL_FV` double NOT NULL,
  `MARKETING_FEE` decimal(10,2) NOT NULL,
  `VAT` decimal(10,2) NOT NULL,
  `NET_DUE` decimal(10,2) NOT NULL,
  `DATE_CREATED` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`RS_DID`),
  UNIQUE KEY `COV_ID` (`COV_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rs_detail`
--

LOCK TABLES `rs_detail` WRITE;
/*!40000 ALTER TABLE `rs_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `rs_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rs_header`
--

DROP TABLE IF EXISTS `rs_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rs_header` (
  `RS_ID` int NOT NULL AUTO_INCREMENT,
  `RS_NUMBER` varchar(250) NOT NULL COMMENT 'DIS + "_" + BRANCH_ID + "_" + PAYMENTDUEDATE + "_" + RS_ID',
  `MERCHANT_ID` int NOT NULL,
  `BRANCH_ID` varchar(100) NOT NULL,
  `MERCHANT_FEE` decimal(10,2) DEFAULT NULL,
  `VATCOND` varchar(100) NOT NULL,
  `REIMBURSEMENT_DATE` datetime NOT NULL,
  `ExpectedDueDate` date NOT NULL,
  `USER_ID` int NOT NULL,
  `GENERATED` int NOT NULL COMMENT '0:GENERATED; 1:COPY',
  `DATE_CREATED` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`RS_ID`),
  UNIQUE KEY `RS_NUMBER` (`RS_NUMBER`),
  KEY `BRANCH_ID` (`BRANCH_ID`) USING BTREE,
  KEY `MERCHANT_ID` (`MERCHANT_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rs_header`
--

LOCK TABLES `rs_header` WRITE;
/*!40000 ALTER TABLE `rs_header` DISABLE KEYS */;
/*!40000 ALTER TABLE `rs_header` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user`
--

DROP TABLE IF EXISTS `tbl_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_user` (
  `pid` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(20) NOT NULL,
  `remarks` varchar(60) NOT NULL,
  `userid` int NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user`
--

LOCK TABLES `tbl_user` WRITE;
/*!40000 ALTER TABLE `tbl_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temp_refund`
--

DROP TABLE IF EXISTS `temp_refund`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `temp_refund` (
  `TEMP_REFUNDID` int NOT NULL AUTO_INCREMENT,
  `REFUND_ID` int NOT NULL,
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
  `UPLOAD_ID` int NOT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ERROR_MESSAGE` varchar(250) NOT NULL,
  PRIMARY KEY (`TEMP_REFUNDID`),
  KEY `REFUND_ID` (`REFUND_ID`),
  KEY `UPLOAD_ID` (`UPLOAD_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=59719 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temp_refund`
--

LOCK TABLES `temp_refund` WRITE;
/*!40000 ALTER TABLE `temp_refund` DISABLE KEYS */;
/*!40000 ALTER TABLE `temp_refund` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `utype_id` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `status` int NOT NULL,
  `activation_code` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (31,1,'arced.remollo@sodexo.com','arced.remollo','805693f0a06d183b5cfc4cee08d5e7f572a030da','Arced Remollo',0,'a6f4ddd7af556fb5536118500f79e319','2019-11-22 07:50:32'),(32,1,'alyssa.garcia@sodexo.com','alyssa.garcia','805693f0a06d183b5cfc4cee08d5e7f572a030da','Alyssa Garcia',0,'aa30bd45611295336584afcc70f0ed52','2019-11-22 07:52:11'),(33,2,'Elpidio.DOMINGO@sodexo.com','jun.domingo','805693f0a06d183b5cfc4cee08d5e7f572a030da','Elpidio Domingo',0,'e3f72030cb13abb9ea57f0f341eca74e','2019-11-22 07:54:42'),(34,4,'Geneveve.ramos@sodexo.com','gen.holgado','805693f0a06d183b5cfc4cee08d5e7f572a030da','Geneveve Ramos',0,'99618d753b74d50f8cae19d4e95d8f51','2019-11-22 08:10:07'),(35,1,'enrico.dominesjr@pluxeegroup.com','enrico.domines','805693f0a06d183b5cfc4cee08d5e7f572a030da','Enrico Domines',0,'99ab245e84c5bfd4567908ab6e0d0ad8','2019-11-22 10:32:37'),(36,4,'benson.durias@pluxeegroup.com','benson.durias','805693f0a06d183b5cfc4cee08d5e7f572a030da','Benson Durias',1,'59cd7090811a63a5d1df23a9ab8411c6','2019-11-22 10:36:26'),(37,4,'Jay.Geniston@sodexo.com','jay.geniston','805693f0a06d183b5cfc4cee08d5e7f572a030da','Jay Geniston',0,'450f6e22d7234712162461f6b82d7608','2019-11-22 10:37:51'),(38,3,'ginalyn.poserio@pluxeegroup.com','gina poserio','805693f0a06d183b5cfc4cee08d5e7f572a030da','Ginalyn Poserio',1,'81dba65210cfabb16263fdadda343791','2019-11-22 10:39:20'),(39,1,'arnel.grutas@sodexo.com','arnel.grutas','805693f0a06d183b5cfc4cee08d5e7f572a030da','Arnel Grutas',0,'7a91abc4c251bf684d23fd4f21e8f3ba','2020-01-07 07:31:31'),(40,1,'KimberlyAnne.HAYAG@sodexo.com','kim.hayag','805693f0a06d183b5cfc4cee08d5e7f572a030da','kim hayag',0,'26d66e960c6cacb25adef4efe52a8eaa','2020-01-24 03:24:59'),(41,2,'grace.quijado@pluxeegroup.com','grace.quijado','805693f0a06d183b5cfc4cee08d5e7f572a030da','Grace Quijado',1,'2ac61c9cfb689c5cf39ea5c54f312085','2021-07-19 06:19:46'),(42,1,'jhunnel.arosena@pluxeegroup.com','jhunnel.arosena','805693f0a06d183b5cfc4cee08d5e7f572a030da','Jhunnel Arosena',1,'31ac635404d63bd7feccd072b3285539','2021-09-07 06:29:40'),(43,2,'Jeffrey.ACEJO@sodexo.com','jeffrey.acejo','805693f0a06d183b5cfc4cee08d5e7f572a030da','Jeffrey Acejo',0,'2661283e726d30afe7af6aaa560a32ef','2021-09-17 01:47:39'),(44,1,'michael.parco@pluxeegroup.com','michael.parco','805693f0a06d183b5cfc4cee08d5e7f572a030da','Michael Parco',1,'e69caab85ac5dacd5f0833b64145c696','2022-11-15 06:14:33'),(45,1,'marc.delacruz@pluxeegroup.com','marc.delacruz','805693f0a06d183b5cfc4cee08d5e7f572a030da','Marc Dela Cruz',1,'ff48ad8ba254669352d68cda66926067','2023-01-11 08:50:13'),(46,2,'karenclarisse.marco@sodexo.com','karen.marco','805693f0a06d183b5cfc4cee08d5e7f572a030da','Karen Clarisse Marco',0,'2661283e726d30afe7af6aaa560a32ef','2023-01-23 01:56:46'),(47,2,'samanthaisabel.benosa@pluxeegroup.com','samantha.benosa','4b495d3356d6c433f66daa4b0cb80b6748b24030','Samantha Isabel Benosa',1,'d9af7d63c537bc46b4fecba4965313bb','2023-01-23 02:05:49'),(48,1,'johnpaulvincent.cabase@pluxeegroup.com','jpcabasee','805693f0a06d183b5cfc4cee08d5e7f572a030da','JP Cabase',1,'2661283e726d30afe7af6aaa560a32ef','2023-05-10 09:26:24'),(49,2,'karenclarisse.marco@pluxeegroup.com','clarisse.karen','65e4fe95b6dd8e8586bfe846393e6c72d5f8a2e7','Clarisse Karen Marco',0,'a6f4ddd7af556fb5536118500f79e319','2023-11-05 03:26:17'),(50,2,'jeffrey.acejo@pluxeegroup.com','jeffreyacejo','805693f0a06d183b5cfc4cee08d5e7f572a030da','Jeffrey Acejo',1,'a6f4ddd7af556fb5536118500f79e319','2024-11-05 03:27:18'),(51,2,'nicco.serrano@pluxeegroup.com','niccoserrano','805693f0a06d183b5cfc4cee08d5e7f572a030da','Nicco Serrano',0,'a6f4ddd7af556fb5536118500f79e319','2024-11-05 09:06:57'),(52,2,'jenny.evangelista@pluxeegroup.com','jennye','a12cc0f1049f079a069d49270edece67b64cd72a','Jenny Evangelista',1,'a6f4ddd7af556fb5536118500f79e319','2024-11-06 05:29:10'),(53,2,'jamela.galindez@pluxeegroup.com','jamela.galindez','805693f0a06d183b5cfc4cee08d5e7f572a030da','Jamela Galindez',1,'a6f4ddd7af556fb5536118500f79e319','2025-05-23 05:29:10'),(54,1,'JPJP@gmail.com','jpjp','805693f0a06d183b5cfc4cee08d5e7f572a030da','JPJP TEST ADD USER',1,'2efc176259fb6e5f23657a62d067f6e6','2025-06-30 06:36:10'),(55,1,'alyssamae.anabo@pluxeegroup.com','alyssamae.anabo','805693f0a06d183b5cfc4cee08d5e7f572a030da','Alyssa Mae Anabo',1,'b8f60fda8620bbaafc1da57c095ed291','2026-02-02 07:07:28'),(56,1,'neri.flores@pluxeegroup.com','neri.flores','805693f0a06d183b5cfc4cee08d5e7f572a030da','Neri Lou Flores',1,'a6f4ddd7af556fb5536118500f79e319','2026-02-02 10:06:44');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utype`
--

DROP TABLE IF EXISTS `utype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utype` (
  `utype_id` int NOT NULL AUTO_INCREMENT,
  `utype_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `utype_desc` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`utype_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utype`
--

LOCK TABLES `utype` WRITE;
/*!40000 ALTER TABLE `utype` DISABLE KEYS */;
/*!40000 ALTER TABLE `utype` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-23 13:42:27
