-- ============================================================
-- DIS (Digital Interface Service) - Full Database Schema
-- Generated from model analysis: User_model, Action_model,
-- Sys_model, Process_model + SQL files in data_tables/
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================
-- USER MANAGEMENT TABLES
-- Source: User_model.php, Action_model.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `utype` (
  `utype_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `utype_name` VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`utype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `user` (
  `user_id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `utype_id`        INT(11)      NOT NULL DEFAULT 0,
  `user_name`       VARCHAR(100) NOT NULL DEFAULT '',
  `full_name`       VARCHAR(200) NOT NULL DEFAULT '',
  `email`           VARCHAR(200) NOT NULL DEFAULT '',
  `password`        VARCHAR(255) NOT NULL DEFAULT '',
  `status`          TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '0=inactive, 1=active',
  `activation_code` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`),
  KEY `utype_id` (`utype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `access_role` (
  `acc_id`   INT(11)     NOT NULL AUTO_INCREMENT,
  `acc_code` VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`acc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `access_permission` (
  `id`             INT(11)    NOT NULL AUTO_INCREMENT,
  `user_id`        INT(11)    NOT NULL DEFAULT 0,
  `acc_id`         INT(11)    NOT NULL DEFAULT 0 COMMENT 'FK to access_role.acc_id',
  `acc_read_only`  TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=can view only',
  `acc_all_access` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=can view+edit',
  `def_page`       TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=this is the default landing module',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `acc_id` (`acc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- access table referenced by Action_model::access_update()
CREATE TABLE IF NOT EXISTS `access` (
  `access_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `access_name` VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`access_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `password_history` (
  `id`                       INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`                  INT(11)      NOT NULL,
  `password_hash`            VARCHAR(255) NOT NULL,
  `last_password_change_date` DATE         NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ============================================================
-- AUDIT & SESSION TABLES
-- Source: Action_model.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `audit_trail` (
  `id`        INT(11)  NOT NULL AUTO_INCREMENT,
  `user_id`   INT(11)  NOT NULL DEFAULT 0,
  `module_id` INT(11)  NOT NULL DEFAULT 0,
  `target_id` INT(11)  NOT NULL DEFAULT 0,
  `order_id`  INT(11)  NOT NULL DEFAULT 0,
  `auc_id`    INT(11)  NOT NULL DEFAULT 0 COMMENT 'action/audit code ID',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `module_id` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `ajax_update` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `session_id`   VARCHAR(255) NOT NULL DEFAULT '',
  `date_created` DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_update`  DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_created` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `audit_upload` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `module_name` VARCHAR(100) NOT NULL DEFAULT '',
  `file_name`   VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `file_upload` (
  `file_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `file_name` VARCHAR(255) NOT NULL DEFAULT '',
  `module_id` INT(11)      NOT NULL DEFAULT 0,
  `utype_id`  INT(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`file_id`),
  KEY `module_id` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ============================================================
-- ORDER MANAGEMENT TABLES
-- Source: Action_model.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `companies` (
  `company_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `company_name` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`company_id`),
  UNIQUE KEY `company_name` (`company_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `location` (
  `location_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `location_name` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `location_name` (`location_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `statorder` (
  `statorder_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `statorder_name` VARCHAR(255) NOT NULL DEFAULT '',
  `module_id`      INT(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`statorder_id`),
  KEY `module_id` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `statcategory` (
  `statcategory_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `statcategory_name` VARCHAR(255) NOT NULL DEFAULT '',
  `module_id`         INT(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`statcategory_id`),
  KEY `module_id` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `statreason` (
  `statreason_id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `statreason_name` VARCHAR(255) NOT NULL DEFAULT '',
  `module_id`       INT(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`statreason_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `order_list` (
  `order_id`   INT(11) NOT NULL,
  `company_id` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_company` (`order_id`, `company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `binloc` (
  `binloc_id`    INT(11)  NOT NULL AUTO_INCREMENT,
  `order_id`     INT(11)  NOT NULL DEFAULT 0,
  `location_id`  INT(11)  NOT NULL DEFAULT 0,
  `statorder_id` INT(11)  NOT NULL DEFAULT 0,
  `date_release` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_pickup`  DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`binloc_id`),
  UNIQUE KEY `order_loc` (`order_id`, `location_id`),
  KEY `order_id` (`order_id`),
  KEY `location_id` (`location_id`),
  KEY `statorder_id` (`statorder_id`)
  -- Note: statorder_id=7 = PICKUP (locked), location_id=3 = production vault (locked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `binloc_log` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `binloc_id`    INT(11)      NOT NULL DEFAULT 0,
  `order_id`     INT(11)      NOT NULL DEFAULT 0,
  `location_id`  INT(11)      NOT NULL DEFAULT 0,
  `statorder_id` INT(11)      NOT NULL DEFAULT 0,
  `date_release` DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_pickup`  DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `action`       VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'add or update',
  `user_id`      INT(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `binloc_id` (`binloc_id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `delsched` (
  `delsched_id`     INT(11)        NOT NULL AUTO_INCREMENT,
  `company_id`      INT(11)        NOT NULL DEFAULT 0,
  `order_id`        INT(11)        NOT NULL DEFAULT 0,
  `amount`          DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
  `account_manager` VARCHAR(255)   NOT NULL DEFAULT '',
  `del_instruc`     TEXT           NOT NULL,
  `del_mode`        INT(11)        NOT NULL DEFAULT 0 COMMENT 'FK to statcategory',
  `p_term`          VARCHAR(255)   NOT NULL DEFAULT '',
  `p_mode`          VARCHAR(255)   NOT NULL DEFAULT '',
  `statorder_id`    INT(11)        NOT NULL DEFAULT 0,
  `del_date`        DATETIME       NOT NULL DEFAULT '0000-00-00 00:00:00',
  `remarks`         TEXT           NOT NULL,
  `created_by`      INT(11)        NOT NULL DEFAULT 0,
  PRIMARY KEY (`delsched_id`),
  KEY `company_id` (`company_id`),
  KEY `order_id` (`order_id`),
  KEY `statorder_id` (`statorder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `delsched_string` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `mode`          TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '0=unknown,1=payment_mode,2=payment_term,3=delivery_mode',
  `default_value` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mode_value` (`mode`, `default_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `transac` (
  `transac_id`     INT(11)  NOT NULL AUTO_INCREMENT,
  `order_id`       INT(11)  NOT NULL DEFAULT 0,
  `company_id`     INT(11)  NOT NULL DEFAULT 0,
  `contact_person` VARCHAR(255) NOT NULL DEFAULT '',
  `created_by`     INT(11)  NOT NULL DEFAULT 0,
  `prod_stat`      INT(11)  NOT NULL DEFAULT 0,
  `location_id`    INT(11)  NOT NULL DEFAULT 0,
  `prod_time`      DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fin_stat`       INT(11)  NOT NULL DEFAULT 0,
  `fin_time`       DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_received`  DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_release`   DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`transac_id`),
  KEY `order_id` (`order_id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `transac_temp` (
  `transac_tempid` INT(11)      NOT NULL AUTO_INCREMENT,
  `transac_id`     INT(11)      NOT NULL DEFAULT 0,
  `order_id`       INT(11)      NOT NULL DEFAULT 0,
  `company_id`     INT(11)      NOT NULL DEFAULT 0,
  `contact_person` VARCHAR(255) NOT NULL DEFAULT '',
  `prod_stat`      INT(11)      NOT NULL DEFAULT 0,
  `location_id`    INT(11)      NOT NULL DEFAULT 0,
  `prod_time`      DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fin_stat`       INT(11)      NOT NULL DEFAULT 0,
  `fin_time`       DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`     INT(11)      NOT NULL DEFAULT 0,
  `user_id`        INT(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`transac_tempid`),
  KEY `transac_id` (`transac_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `transac_comment` (
  `id`         INT(11)   NOT NULL AUTO_INCREMENT,
  `transac_id` INT(11)   NOT NULL DEFAULT 0,
  `comment`    TEXT      NOT NULL,
  `user_id`    INT(11)   NOT NULL DEFAULT 0,
  `date_added` DATETIME  NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `transac_id` (`transac_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `resoa` (
  `resoa_id`      INT(11)  NOT NULL AUTO_INCREMENT,
  `transac_id`    INT(11)  NOT NULL DEFAULT 0,
  `order_id`      INT(11)  NOT NULL DEFAULT 0,
  `date_return`   DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_received` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`resoa_id`),
  KEY `transac_id` (`transac_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `release_order` (
  `reorder_id`    INT(11)  NOT NULL AUTO_INCREMENT,
  `order_id`      INT(11)  NOT NULL DEFAULT 0,
  `served_stat`   INT(11)  NOT NULL DEFAULT 0,
  `date_received` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_release`  DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`reorder_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `advance_soa` (
  `adsoa_id`       INT(11)      NOT NULL AUTO_INCREMENT,
  `order_id`       INT(11)      NOT NULL DEFAULT 0,
  `company_id`     INT(11)      NOT NULL DEFAULT 0,
  `contact_person` VARCHAR(255) NOT NULL DEFAULT '',
  `created_by`     INT(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`adsoa_id`),
  KEY `order_id` (`order_id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `co_orderinfo` (
  `co_orderinfo_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id`        INT(11) NOT NULL DEFAULT 0,
  `company_id`      INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`co_orderinfo_id`),
  KEY `order_id` (`order_id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `co_transac` (
  `co_transac_id`   INT(11) NOT NULL AUTO_INCREMENT,
  `order_id`        INT(11) NOT NULL DEFAULT 0,
  `company_id`      INT(11) NOT NULL DEFAULT 0,
  `co_orderinfo_id` INT(11) NOT NULL DEFAULT 0,
  `created_by`      INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`co_transac_id`),
  KEY `order_id` (`order_id`),
  KEY `co_orderinfo_id` (`co_orderinfo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `cor_transac` (
  `cor_transac_id`  INT(11) NOT NULL AUTO_INCREMENT,
  `order_id`        INT(11) NOT NULL DEFAULT 0,
  `company_id`      INT(11) NOT NULL DEFAULT 0,
  `co_orderinfo_id` INT(11) NOT NULL DEFAULT 0,
  `created_by`      INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cor_transac_id`),
  KEY `order_id` (`order_id`),
  KEY `co_orderinfo_id` (`co_orderinfo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ============================================================
-- MERCHANT / PRODUCT TABLES
-- Source: Sys_model.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `cp_merchant` (
  `id`                   INT(11)        NOT NULL AUTO_INCREMENT,
  `CP_ID`                INT(11)        NOT NULL DEFAULT 0 COMMENT 'Corepass Merchant ID',
  `TIN`                  VARCHAR(50)    NOT NULL DEFAULT '',
  `LegalName`            VARCHAR(255)   NOT NULL DEFAULT '',
  `TradingName`          VARCHAR(255)   NOT NULL DEFAULT '',
  `GroupTIN`             VARCHAR(50)    NOT NULL DEFAULT '',
  `GroupName`            VARCHAR(255)   NOT NULL DEFAULT '',
  `Address`              TEXT           NOT NULL,
  `MeanofPayment`        VARCHAR(100)   NOT NULL DEFAULT '',
  `PayeeCode`            VARCHAR(100)   NOT NULL DEFAULT '',
  `BankName`             VARCHAR(255)   NOT NULL DEFAULT '',
  `BankAccountNumber`    VARCHAR(100)   NOT NULL DEFAULT '',
  `PayeeName`            VARCHAR(255)   NOT NULL DEFAULT '',
  `MerchantFee`          DECIMAL(10,6)  NOT NULL DEFAULT 0.000000 COMMENT 'e.g. 0.02 = 2%',
  `Industry`             VARCHAR(100)   NOT NULL DEFAULT '',
  `VATCond`              VARCHAR(50)    NOT NULL DEFAULT '' COMMENT 'e.g. Taxable, VAT-Exempt',
  `InsertType`           VARCHAR(50)    NOT NULL DEFAULT '',
  `BankBranchCode`       VARCHAR(50)    NOT NULL DEFAULT '',
  `PayeeQtyOfDays`       INT(11)        NOT NULL DEFAULT 0,
  `PayeeDayType`         INT(11)        NOT NULL DEFAULT 0 COMMENT '0=calendar,1=business',
  `PayeeComments`        TEXT           NOT NULL,
  `AffiliateGroupCode`   VARCHAR(100)   NOT NULL DEFAULT '',
  `MerchantType`         VARCHAR(100)   NOT NULL DEFAULT '' COMMENT 'e.g. Merchant Dormancy',
  `DIGITALSETTLEMENTTYPE` VARCHAR(100)  NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `CP_ID` (`CP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `cp_agreement` (
  `id`           INT(11) NOT NULL AUTO_INCREMENT,
  `CP_ID`        INT(11) NOT NULL DEFAULT 0,
  `AGREEMENT_ID` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `CP_ID` (`CP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `cp_product` (
  `SERVICE_ID`   INT(11)      NOT NULL AUTO_INCREMENT,
  `SERVICE_NAME` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`SERVICE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `branches` (
  `BRANCH_ID`          VARCHAR(25)  NOT NULL DEFAULT '',
  `MERCHANT_ID`        INT(11)      NOT NULL DEFAULT 0,
  `BRANCH_NAME`        VARCHAR(255) NOT NULL DEFAULT '',
  `CP_ID`              INT(11)      NOT NULL DEFAULT 0,
  `AFFILIATEGROUPCODE` VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`BRANCH_ID`),
  KEY `MERCHANT_ID` (`MERCHANT_ID`),
  KEY `CP_ID` (`CP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `branch_merchant` (
  `MERCHANT_ID` INT(11)     NOT NULL DEFAULT 0,
  `BRANCH_ID`   VARCHAR(25) NOT NULL DEFAULT '',
  UNIQUE KEY `mid_bid` (`MERCHANT_ID`, `BRANCH_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `payment_cutoff` (
  `MERCHANT_ID`          INT(11)      NOT NULL DEFAULT 0,
  `TYPE`                 VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'e.g. Weekly',
  `SPECIFIC_DAY`         VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'e.g. Sunday',
  `SPECIFIC_DATE`        VARCHAR(255) NOT NULL DEFAULT '',
  `DigitalSettlementType` VARCHAR(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`MERCHANT_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ============================================================
-- TRANSACTION TABLES
-- Source: Sys_model.php, Process_model.php, sample_wrecon_data.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `redemption` (
  `ID`                   INT(11)        NOT NULL AUTO_INCREMENT,
  `REDEEM_ID`            VARCHAR(50)    NOT NULL DEFAULT '' COMMENT 'Redemption transaction ID from API',
  `MERCHANT_ID`          INT(11)        NOT NULL DEFAULT 0,
  `BRANCH_ID`            VARCHAR(25)    NOT NULL DEFAULT '',
  `PROD_ID`              INT(11)        NOT NULL DEFAULT 0,
  `VOUCHER_CODE`         VARCHAR(100)   NOT NULL DEFAULT '',
  `TRANSACTION_VALUE`    DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
  `STAGE`                VARCHAR(50)    NOT NULL DEFAULT '' COMMENT 'REDEEMED, RECONCILED, REVERSED, VOID',
  `TRANSACTION_DATE_TIME` DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
  `POS_ID`               VARCHAR(100)   NOT NULL DEFAULT '',
  `POS_TXN_ID`           VARCHAR(100)   NOT NULL DEFAULT '',
  `PAYMENT_MODE`         VARCHAR(50)    NOT NULL DEFAULT '',
  `REFUND_ID`            INT(11)        NOT NULL DEFAULT 0,
  `PA_ID`                INT(11)        NOT NULL DEFAULT 0,
  `PA_TEMPID`            INT(11)        NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `REDEEM_ID` (`REDEEM_ID`),
  KEY `MERCHANT_ID` (`MERCHANT_ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`),
  KEY `PROD_ID` (`PROD_ID`),
  KEY `PA_ID` (`PA_ID`),
  KEY `REFUND_ID` (`REFUND_ID`),
  KEY `PA_TEMPID` (`PA_TEMPID`),
  KEY `STAGE` (`STAGE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `reconcilation` (
  -- Note: intentional typo in table name matches codebase spelling
  `ID`                   INT(11)        NOT NULL AUTO_INCREMENT,
  `RECON_ID`             VARCHAR(50)    NOT NULL DEFAULT '' COMMENT 'Reconciliation ID from API',
  `REDEEM_ID`            VARCHAR(50)    NOT NULL DEFAULT '',
  `MERCHANT_ID`          INT(11)        NOT NULL DEFAULT 0,
  `BRANCH_ID`            VARCHAR(25)    NOT NULL DEFAULT '',
  `PROD_ID`              INT(11)        NOT NULL DEFAULT 0,
  `TRANSACTION_VALUE`    DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
  `RECON_DATE_TIME`      DATETIME       NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TRANSACTION_DATE_TIME` DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
  `REFUND_ID`            INT(11)        NOT NULL DEFAULT 0,
  `PA_ID`                INT(11)        NOT NULL DEFAULT 0,
  `payment_mode`         VARCHAR(50)    NOT NULL DEFAULT '',
  `REDEEM_TBL_ID`        INT(11)        NOT NULL DEFAULT 0 COMMENT 'FK to redemption.ID',
  `STAGE`                VARCHAR(50)    NOT NULL DEFAULT '' COMMENT 'RECONCILED, REVERSED',
  `PA_TEMPID`            INT(11)        NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`),
  KEY `RECON_ID` (`RECON_ID`),
  KEY `REDEEM_ID` (`REDEEM_ID`),
  KEY `MERCHANT_ID` (`MERCHANT_ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`),
  KEY `PROD_ID` (`PROD_ID`),
  KEY `REFUND_ID` (`REFUND_ID`),
  KEY `PA_ID` (`PA_ID`),
  KEY `REDEEM_TBL_ID` (`REDEEM_TBL_ID`),
  KEY `PA_TEMPID` (`PA_TEMPID`),
  KEY `STAGE` (`STAGE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Defined in data_tables/prod_refund.sql + ALTER from sample_wrecon_data.sql
CREATE TABLE IF NOT EXISTS `refund` (
  `REFUND_ID`              INT(11)       NOT NULL AUTO_INCREMENT,
  `REDEEM_ID`              VARCHAR(50)   NOT NULL DEFAULT '',
  `REVERSAL_TRANSACTION_ID` VARCHAR(100) NOT NULL DEFAULT '',
  `TRANSACTION_ID`         VARCHAR(50)   NOT NULL DEFAULT '',
  `RECON_ID`               VARCHAR(50)   NOT NULL DEFAULT '',
  `REVERSAL_MODE`          VARCHAR(50)   NOT NULL DEFAULT '',
  `PROD_ID`                INT(11)       NOT NULL DEFAULT 0,
  `REDEEM_STATUS`          VARCHAR(50)   NOT NULL DEFAULT '',
  `MERCHANT_ID`            INT(11)       NOT NULL DEFAULT 0,
  `BRANCH_ID`              VARCHAR(25)   NOT NULL DEFAULT '',
  `USER_ID`                INT(11)       NOT NULL DEFAULT 0,
  `UPLOAD_ID`              INT(11)       NOT NULL DEFAULT 0,
  `REVERSAL_DATE_TIME`     DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DATE_CREATED`           DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PA_ID`                  INT(11)       NOT NULL DEFAULT 0 COMMENT 'adjustment PA',
  `REDEEM_TBL_ID`          INT(11)       NOT NULL DEFAULT 0 COMMENT 'FK to redemption.ID',
  `RECON_TBL_ID`           INT(11)       NOT NULL DEFAULT 0 COMMENT 'FK to reconcilation.ID',
  `PA_TEMPID`              INT(11)       NOT NULL DEFAULT 0,
  PRIMARY KEY (`REFUND_ID`),
  UNIQUE KEY `redeem_id` (`REDEEM_ID`),
  KEY `upload_id` (`UPLOAD_ID`),
  KEY `recon_id` (`RECON_ID`),
  KEY `merchant_id` (`MERCHANT_ID`),
  KEY `branch_id` (`BRANCH_ID`),
  KEY `user_id` (`USER_ID`),
  KEY `PA_ID` (`PA_ID`),
  KEY `REVERSAL_TRANSACTION_ID` (`REVERSAL_TRANSACTION_ID`),
  KEY `TRANSACTION_ID` (`TRANSACTION_ID`),
  KEY `PROD_ID` (`PROD_ID`),
  KEY `REDEEM_TBL_ID` (`REDEEM_TBL_ID`),
  KEY `RECON_TBL_ID` (`RECON_TBL_ID`),
  KEY `PA_TEMPID` (`PA_TEMPID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Defined in data_tables/prod_temp_refund.sql
CREATE TABLE IF NOT EXISTS `temp_refund` (
  `TEMP_REFUNDID`                  INT(11)      NOT NULL AUTO_INCREMENT,
  `REFUND_ID`                      INT(11)      NOT NULL DEFAULT 0,
  `MERCHANT_NAME`                  VARCHAR(250) NOT NULL DEFAULT '',
  `MERCHANT_ID`                    VARCHAR(250) NOT NULL DEFAULT '',
  `BRANCH_ID`                      VARCHAR(250) NOT NULL DEFAULT '',
  `POS_ID`                         VARCHAR(250) NOT NULL DEFAULT '',
  `POS_TXN_ID`                     VARCHAR(250) NOT NULL DEFAULT '',
  `PROD_ID`                        VARCHAR(250) NOT NULL DEFAULT '',
  `TRANSACTION_DATE_TIME`          VARCHAR(250) NOT NULL DEFAULT '',
  `TRANSACTION_ID`                 VARCHAR(250) NOT NULL DEFAULT '',
  `REDEMPTION_API_TRANSACTION_ID`  VARCHAR(250) NOT NULL DEFAULT '',
  `REVERSAL_DATE_TIME`             VARCHAR(250) NOT NULL DEFAULT '',
  `REVERSAL_TRANSACTION_ID`        VARCHAR(250) NOT NULL DEFAULT '',
  `VOUCHER_CODE`                   VARCHAR(250) NOT NULL DEFAULT '',
  `TRANSACTION_VALUE`              VARCHAR(250) NOT NULL DEFAULT '',
  `RECON_API_TRANSACTION_ID`       VARCHAR(250) NOT NULL DEFAULT '',
  `PAYMENT_MODE`                   VARCHAR(250) NOT NULL DEFAULT '',
  `REVERSAL_MODE`                  VARCHAR(250) NOT NULL DEFAULT '',
  `UPLOAD_ID`                      INT(11)      NOT NULL DEFAULT 0,
  `DATE_CREATED`                   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ERROR_MESSAGE`                  VARCHAR(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`TEMP_REFUNDID`),
  KEY `REFUND_ID` (`REFUND_ID`),
  KEY `UPLOAD_ID` (`UPLOAD_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ============================================================
-- PAYMENT ADVICE (PA) TABLES
-- Source: Sys_model.php, Automate.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `pa_header` (
  `PA_ID`              INT(11)        NOT NULL AUTO_INCREMENT,
  `MERCHANT_ID`        INT(11)        NOT NULL DEFAULT 0,
  `USER_ID`            INT(11)        NOT NULL DEFAULT 0,
  `DATE_CREATED`       DATETIME       NOT NULL DEFAULT '0000-00-00 00:00:00',
  `REIMBURSEMENT_DATE` DATETIME       NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ExpectedDueDate`    DATETIME       NOT NULL DEFAULT '0000-00-00 00:00:00',
  `MERCHANT_FEE`       DECIMAL(10,6)  NOT NULL DEFAULT 0.000000,
  `vatCond`            VARCHAR(50)    NOT NULL DEFAULT '',
  `generated`          TINYINT(1)     NOT NULL DEFAULT 0,
  PRIMARY KEY (`PA_ID`),
  KEY `MERCHANT_ID` (`MERCHANT_ID`),
  KEY `USER_ID` (`USER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `pa_detail` (
  `PA_DID`        INT(11)       NOT NULL AUTO_INCREMENT,
  `PA_ID`         INT(11)       NOT NULL DEFAULT 0,
  `RECON_ID`      VARCHAR(50)   NOT NULL DEFAULT '',
  `RATE`          DECIMAL(10,6) NOT NULL DEFAULT 0.000000 COMMENT 'merchant fee rate',
  `NUM_PASSES`    INT(11)       NOT NULL DEFAULT 0,
  `TOTAL_FV`      DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'total face value',
  `MARKETING_FEE` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `VAT`           DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `NET_DUE`       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `BRANCH_ID`     VARCHAR(25)   NOT NULL DEFAULT '',
  PRIMARY KEY (`PA_DID`),
  KEY `PA_ID` (`PA_ID`),
  KEY `RECON_ID` (`RECON_ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ============================================================
-- NAV (Navision/ERP Export) TABLES
-- Source: Automate.php, Sys_model.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `nav_header` (
  `NAVH_ID`         INT(11)       NOT NULL AUTO_INCREMENT,
  `CP_ID`           INT(11)       NOT NULL DEFAULT 0,
  `MERCHANT_ID`     INT(11)       NOT NULL DEFAULT 0,
  `PA_ID`           INT(11)       NOT NULL DEFAULT 0,
  `RECON_ID`        VARCHAR(50)   NOT NULL DEFAULT '',
  `PROD_ID`         INT(11)       NOT NULL DEFAULT 0,
  `TotalAmount`     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `DateofReceipt`   DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ExpectedDueDate` DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`NAVH_ID`),
  KEY `CP_ID` (`CP_ID`),
  KEY `MERCHANT_ID` (`MERCHANT_ID`),
  KEY `PA_ID` (`PA_ID`),
  KEY `RECON_ID` (`RECON_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `nav_detail` (
  `NAVD_ID`  INT(11)       NOT NULL AUTO_INCREMENT,
  `NAVH_ID`  INT(11)       NOT NULL DEFAULT 0,
  `PROD_ID`  INT(11)       NOT NULL DEFAULT 0,
  `BillItem` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'maps to MARKETING_FEE',
  `FaceValue` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'maps to TOTAL_FV',
  `OutputVAT` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `VATCond`  DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'maps to VAT amount',
  PRIMARY KEY (`NAVD_ID`),
  KEY `NAVH_ID` (`NAVH_ID`),
  KEY `PROD_ID` (`PROD_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ============================================================
-- CONVERSION / REIMBURSEMENT SETTLEMENT (RS) TABLES
-- Source: Sys_model.php, Rs.php controller
-- ============================================================

CREATE TABLE IF NOT EXISTS `conversion` (
  `COV_ID`        INT(11)        NOT NULL AUTO_INCREMENT,
  `MERCHANT_ID`   INT(11)        NOT NULL DEFAULT 0,
  `BRANCH_ID`     VARCHAR(25)    NOT NULL DEFAULT '',
  `BRANCH_NAME`   VARCHAR(255)   NOT NULL DEFAULT '',
  `PROD_ID`       INT(11)        NOT NULL DEFAULT 0,
  `VOUCHER_CODES` TEXT           NOT NULL COMMENT 'comma-separated voucher codes',
  `AGENT_ID`      VARCHAR(100)   NOT NULL DEFAULT '',
  `DENO`          DECIMAL(15,2)  NOT NULL DEFAULT 0.00 COMMENT 'denomination / amount per transaction',
  `TOTAL_AMOUNT`  DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
  `STAGE`         VARCHAR(50)    NOT NULL DEFAULT '' COMMENT 'e.g. CONVERTED',
  `RS_ID`         INT(11)        NOT NULL DEFAULT 0,
  `USER_ID`       INT(11)        NOT NULL DEFAULT 0,
  `NAME`          VARCHAR(255)   NOT NULL DEFAULT '',
  `CREATED_AT`    DATETIME       NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`COV_ID`),
  KEY `MERCHANT_ID` (`MERCHANT_ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`),
  KEY `RS_ID` (`RS_ID`),
  KEY `STAGE` (`STAGE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `rs_header` (
  `RS_ID`              INT(11)       NOT NULL AUTO_INCREMENT,
  `BRANCH_ID`          VARCHAR(25)   NOT NULL DEFAULT '',
  `MERCHANT_ID`        INT(11)       NOT NULL DEFAULT 0,
  `DATE_CREATED`       DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
  `REIMBURSEMENT_DATE` DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
  `USER_ID`            INT(11)       NOT NULL DEFAULT 0,
  `MERCHANT_FEE`       DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
  `VATCOND`            VARCHAR(50)   NOT NULL DEFAULT '',
  `ExpectedDueDate`    DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
  `RS_NUMBER`          VARCHAR(100)  NOT NULL DEFAULT '',
  `generated`          TINYINT(1)    NOT NULL DEFAULT 0,
  PRIMARY KEY (`RS_ID`),
  KEY `MERCHANT_ID` (`MERCHANT_ID`),
  KEY `BRANCH_ID` (`BRANCH_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `rs_detail` (
  `RS_DID`        INT(11)       NOT NULL AUTO_INCREMENT,
  `RS_ID`         INT(11)       NOT NULL DEFAULT 0,
  `COV_ID`        INT(11)       NOT NULL DEFAULT 0,
  `RATE`          DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
  `TOTAL_FV`      DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `MARKETING_FEE` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `VAT`           DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `NET_DUE`       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `DATE_CREATED`  DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`RS_DID`),
  KEY `RS_ID` (`RS_ID`),
  KEY `COV_ID` (`COV_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ============================================================
-- ARCHIVE / BACKUP TABLES (read-only, same schema as redemption)
-- Source: Sys_model.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `redemption_20230608` LIKE `redemption`;
CREATE TABLE IF NOT EXISTS `old_redemption` LIKE `redemption`;
