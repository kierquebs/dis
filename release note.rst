###################
What is DIS
###################



*******************
Server Requirements
*******************

PHP version 5.6 or newer is recommended.
Mysql Database
Oracle Listener

*******************
Release Information
*******************


**************************
Changelog and New Features
**************************

FIXES:
- Generation for Payment Advise with different RECON ID

CHANGE:
- NAVISION interface file naming convention

***********
UPDATE - APRIL 2020
***********
	1. Client details interface 
		○ fix logic for the insert type field value
	2. NEW MODULE:: Credit order issuance 
		○ Discount logic
		○ include billable items added only upon order processing 
	3. NEW MODULE::  Conversion Module: additional module in DIS UI
		○ automatic import of conversion data from ZETA SFT to DIS
		○ generation of conversion interface file
		○ REQUIREMENT:
			§ ON-BOARD merchants from CP where "GROUP  : MERCHANT TYPE = merchant conversion"
			§ Get data from ZETA CSV file ( only branchname will be provided)
			§  New table - conversion
				□ Data from CSV
				□ MERCHANT ID
			§ NEW PAGE FOR REIM : to generate RS ( 1 merchant = 1 rs number )
				□ TBL : rs_header
					® TBL : rs_detail
			§ RS Summary file - (filename format: legal name +_+ rs number +_+ datetime)

***********
UPDATE - JULY 2020
***********
	1. NEW MODULE:: Reversal Module
		○ handles reversal transactions to be processed in reimbursement processed

***********
UPDATE - NOV 2020
***********	

-- CLEAN-UP --
	Ø Create a new copy table (include data) for: 
		○ REDEEM as old_redeem (garbage data)
		○ RECON as old_recon (garbage data)
	Ø Additional field for both REDEEM & RECON tables
		○ PAYMENT_METHOD (datatype: text)
	Ø Filter all necessary parameters:
		○ Remove redeem_id with QR & RP
	Ø File Upload  - Validate / Check filename consist of:
			§ MerchantPortalReconciliationReport
			§ QRReconciliationReport