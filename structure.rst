###################
DATABASE STRUCTURE  DIS(DIGITAL INTERFACE SERVER)
DBNAME: DIS_DB
###################

// ---- Provision Merchant Data from COREPASS ----

*******************
TABLE : CP_MERCHANT (MOBILE VOUCHER PARTNER)
--- Get AGREEMENT INFO (MOBILE PASS PRODUCT) ---
UNIQUE: MERCHANT_ID
*******************
ID*
CP_ID* -> COREPASS or ZETA ID
MERCHANT_ID* -> from MOBILE 
TIN
LegalName
TradingName
GroupTIN
GroupName
Address
ContactPerson
ContactNumber
MeanofPayment
PayeeCode
BankName
BankBranchCode
BankAccountNumber
PayeeName (Merchant Payee legal name)
MerchantFee
Industry
VATCond
InsertType -> I , U , X - donot include for migration

*******************
TABLE : MERCHANT_FEE 
*******************
ID*
MERCHANT_ID*
MERCHANT_FEE*
DATE_UPDATED


*******************
TABLE : CP_PRODUCT
*******************
PRODUCT_ID*
PRODUCT_DESCRIPTION

// ---- Provision Merchant Branch fom AFFILIATION ----

*******************
TABLE : BRANCHES
UNIQUE: BRANCH_ID
*******************
BRANCH_ID*
MERCHANT_ID*
BRANCH_NAME

// ---- Provision Merchant PAYMENT CUTOFF fom REIMBURSEMENT ----

*******************
TABLE : PAYMENT_CUTOFF
UNIQUE: MERCHANT_ID
*******************
MERCHANT_ID*
TYPE  - weekly, semi_month
SPECIFIC_DAY - (available only for WEEKLY)
SPECIFIC_DATE - (available only for SEMI MONTHLY) {5, 10, 15, 25}

*******************
PAYMENT_TERMS
*******************
weekly - Get days of the week (M, TUE, W, THU, FRI, SAT, SUN)
semi_month - array of dates {5, 10, 15, 25}


// ---- Migrate DATA from ZETA SFTP ----

*******************
TABLE : REDEMPTION
UNIQUE: REDEEM_ID - PROD_ID
*******************
REDEEM_ID* -> API_TRANSACTION_ID
MERCHANT_ID*
MERCHANT_NAME	
BRANCH_ID*	
POS_ID	
POS_TXN_ID	
PROD_ID*	
VOUCHER_CODE
TRANSACTION_DATE_TIME	
TRANSACTION_ID	
TRANSACTION_VALUE	
STAGE	

*******************
TABLE : RECONCILATION
UNIQUE: RECON_ID - TRANSACTION_ID
*******************
RECON_ID* -> API_TRANSACTION_ID
REDEEM_ID* -> TRANSACTION_ID
MERCHANT_NAME	
MERCHANT_ID*	
BRANCH_ID*	
POS_ID	
POS_TXN_ID
VOUCHER_CODE		
TRANSACTION_DATE_TIME	
TRANSACTION_VALUE	
RECON_DATE_TIME	

// ---- PAYMENT ADVICE data from   ----

*******************
TABLE : PA_HEADER
UNIQUE: PA_ID
*******************
PA_ID*
MERCHANT_ID*
REIMBURSEMENT_DATE
DATE_CREATED

*******************
TABLE : PA_DETAIL
UNIQUE: PA_DID
*******************
PA_DID -> DETAIL ID
PA_ID* -> HEADER
BRANCH_ID*
RECON_ID*
RATE
NUM_PASSES
TOTAL_FV
MARKETING_FEE
VAT
NET_DUE
DATE_CREATED


// ---- For compilation of AFFILIATIE REMITTACE  (from PA TABLE)----

*******************
TABLE : NAV_HEADER
*******************
NAVH_ID*
CP_ID* -> COREPASS or ZETA ID | Zeta Internal ID
MERCHANT_ID*
PA_ID
RECON_ID*
PROD_ID*
DateofReceipt
ExpectedDueDate
TotalAmount

*******************
TABLE : NAV_DETAIL
*******************
NAVH_ID*
PROD_ID*
BillItem
FaceValue
OutputVAT
VATCond



----------------------
GENERATE JASPER REPORT
----------------------
* NAVISION MERCHANT INFO
    SCHEDULE: daily
    SOURCE TABLE : CP_MERCHANT 
    FILTER CONDITION:
        - get all STATUS = [I , U]
        - update all filtered record CP_MERCHANT STATUS = X
    SEND : via SFTP -> NAV 

* SM Department Store RECON TRANSACTIONS 
    SEND : via JASPER SFTP -> SM EFT
    SCHEDULE: 16th of the month
    SOURCE TABLE : CP_MERCHANT 
    FILTER CONDITION:
        - date range every 1-15th of the month

*  SM Retail Affiliates and Supermarket  RECON TRANSACTIONS
    SOURCE TABLE : CP_MERCHANT 
    SEND : via EMAIL
    SCHEDULE 1: 16th of the "month"
    FILTER CONDITION 1:
        - date range every 1-15th of the month
    SCHEDULE 2: 1st day of "next month"
    FILTER CONDITION 2:
        - date range every 16 - end of the month

----------------------
FUNCTIONS
----------------------
* Provision Merchants Account from Corepass with MobilePass Agreement & as MobilePass PARTNER (cp_merchant) -- DONE
* Import all records from ZETA SFTP csv files to DIS DATABASE -- DONE
* Module that can IMPORT all SERVICE/PRODUCT from COREPASS to DIS (cp_product) -- DONE
* Module that will upload PAYMENT CUT OFF csv file to DIS (payment_cutoff) -- DONE
* Module that will upload BRANCHES csv file to DIS (branches) -- DONE
* Module that will upload MERCHANT FEE csv file to DIS (merchant_fee) -- DONE
* Module that will import daily PA to Nav tables (nav_header & nav_detail)
