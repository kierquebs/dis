<?php
/**
 * DIGITAL CLIENTS INTERFACE CONTROLLER
 * CIC ISSUANCE FILE - data record will not be stored in DIS database
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Digital extends MX_Controller {
	private $DATE_NOW;

	public function	__construct(){
		parent::__construct();	
			$this->DATE_NOW = $this->my_lib->current_date();
			$this->load->model('Corepass_model'); 
			$this->load->library('download_file');
	}
			
	/**
	 * ---------------------------------------------------------------------------
		DIGITAL ISSUANCE  = Client Credit Order Issuance (client , soa)
	 * ---------------------------------------------------------------------------
	 */
	public function index(){
		$this->_interface_client();
		$this->_interface_soa();
	}	

	public function client(){
		$this->_interface_client(false); 
	}

	public function soa(){
		$this->_interface_soa(false);
	}	

	public function si(){
		$this->_interface_si(false);
	}

	private function _interface_si($serverDl = true){
		/**
		   date coverage -> all transaction of previous day
		*/		
	   $date = new DateTime(); 
	   $previousDate = $date->modify("-1 days")->format('m/d/yy'); 
		   if(isset($_GET['date'])) $previousDate = $_GET['date']; 
	   $where = " AND TO_CHAR(cs.START_DATE, 'mm/dd/yyyy') ='".$previousDate."'";
		   if(isset($_GET['month'])){
			   $previousDate = $_GET['month'];  
			   $where = " AND TO_CHAR(cs.START_DATE, 'mm/yyyy') ='".$previousDate."'";
		   }
	   
	   $result =  $this->Corepass_model->getDigitalSOAOrder($where);
	   
	   if($result && $result->num_rows() != 0 ){
		   $arr = $this->_interface_si_result($result); 		
		   
		   $module['filename'] = 'SI_'.date('mdY',time()).'_001'; //CIC_SOA_MMDDYYYY_01.csv 
		   return $this->download_file->_cic_si_remittance($module, $arr, $serverDl);
	   }
	} 

	private function _interface_si_result($temp_transac, $export = false){
		$arr = array();		
		foreach($temp_transac->result() as $temp_row){ 
			$newRow = new stdClass();
			$newRow->RECORD_TYPE = 'H';
			$newRow->SOA = 'SI';
			$newRow->TIN = $this->my_lib->setTin($temp_row->TIN) ?: $temp_row->TIN;
			$newRow->LegalName = $temp_row->LEGALNAME;
			$newRow->SOA_NUMBER = $temp_row->SOA_NUMBER;
			$newRow->ORDER_ID = $this->my_lib->paNumber($temp_row->ORDER_ID, false, ''); 
			$newRow->ORDER_DATE = $temp_row->ORDER_DATE; //created date									
			$newRow->DELIVERED_DATE = $temp_row->DELIVERED_DATE; //credited date
			$newRow->CUSTOMER_TYPE = $temp_row->CLIENT_TYPE;	
			$newRow->SERVICE_ID = $temp_row->SERVICE_ID;	
			$newRow->ACCOUNT_MANAGER = $temp_row->ACCOUNT_MANAGER;	
			$newRow->PO = $temp_row->PO;		
			$newRow->CP_ID = $this->my_lib->digitalID($temp_row->CP_ID);		 
			$newRow->DUE_DATE = $temp_row->DUE_DATE;
			
			//$whereD = 'AND ECD.N_ACCOUNTINGDOCUMENT = '.$temp_row->SOA_NUMBER.' AND TCO.N_CREDITORDER = '.$temp_row->ORDER_ID.' AND EA.SERVICE_ID = '.$temp_row->SERVICE_ID;	
			$whereD = "AND ECD.RELATEDENTITIESIDS = " . $temp_row->ORDER_ID ;
			
			//log_message('error', json_encode($whereD, JSON_PRETTY_PRINT));
			
			//$resultDetail = $this->Corepass_model->getDigitalSOAOrderBillable($whereD);				
			$resultDetail = $this->Corepass_model->cptestquery($whereD);				
			
			//log_message('error', json_encode($resultDetail->result(), JSON_PRETTY_PRINT));
			
			$return_detail = $this->_detail_result($resultDetail, $temp_row->SERVICE_ID,  $temp_row->ACCOUNT_MANAGER, $newRow->DELIVERED_DATE);	
			
			//log_message('error', json_encode($return_detail, JSON_PRETTY_PRINT));
			
			$newRow->nav_detail = array();
			if($return_detail){
				//log_message('error', 'With SI ' . $newRow->ORDER_ID);
				$newRow->nav_detail = $return_detail['nav_detail'];
				/*
				* get computation from the _detail_result
				* NET_BILLABLE sumOfBillablItem  = without tax
				  * GROSS_BILLABLE sumOfBillablItem = with tax 
				*/	
				$newRow->DISCOUNT = $return_detail['TOTAL_DISCOUNT']; //DISCOUNT CALCULATION  -- change to total amount of rebate billable item
				$newRow->AMOUNT =  ($newRow->DISCOUNT <> 0 ? $return_detail['X_GROSS_BILLABLE'] : $return_detail['GROSS_BILLABLE']); //TOTAL PAYMENT (SUM of base amount per billable ITEM less Discount Billable)
				$newRow->TOTAL_AMOUNT = ($newRow->DISCOUNT <> 0 ? $return_detail['X_NET_BILLABLE'] : $return_detail['NET_BILLABLE']); //NET AMOUNT CALCULATION
			}else{
				//log_message('error', 'WITHOUT SI ' . $newRow->ORDER_ID . ' <<<<');
			}
			
			
			$arr[] = $newRow; 
		}
		//log_message('error',  json_encode($arr, JSON_PRETTY_PRINT) . ' <<<<');
		return $arr;
	}
		
	/**
	 * ---------------------------------------------------------------------------
	 * DIGITAL CLIENT Master Info
	 * ---------------------------------------------------------------------------
	 */ 
	 
	 private function _interface_client($serverDl = true){
		$date = new DateTime();
		$previousDate = $date->modify("-1 days")->format('m/d/yy');
			if(isset($_GET['date'])) $previousDate = $_GET['date'];
			
			if(isset($_GET['cpid'])) $where = " AND EC.COMPANY_ID = '".$_GET['cpid']."'";
			else if(isset($_GET['grpcpid'])) {
				$dm_groupid = getenv('dm_groupid') !== false ? getenv('dm_groupid') : '';
				$where = " AND EC.COMPANY_ID IN (".$dm_groupid.")";
			}
			else $where = " AND TO_CHAR(ECGD.CREATION_DATE, 'mm/dd/yyyy') = '".$previousDate."'";
			
			if(isset($_GET['month'])){
				$previousDate = $_GET['month'];  
				$where = " AND TO_CHAR(ECGD.CREATION_DATE, 'mm/yyyy') ='".$previousDate."'";
			}		
		//$where = " AND TO_CHAR(ECGD.CREATION_DATE, 'mm/dd/yyyy') >= '09/28/2020' AND TO_CHAR(ECGD.CREATION_DATE, 'mm/dd/yyyy') <= '09/16/2020'";
		$result =  $this->Corepass_model->getQueryDigitalClients($where);	
		if($result->num_rows() != 0 ){
			$arr = $this->_interface_client_result($result);			
			$module['filename'] = 'DC_'.date('mdY',time()).'_01'; //CIC_CLIENT_MMDDYYYY_01.csv
			return $this->download_file->_cic_client($module, $arr, $serverDl);
		}
	 }

	private function _interface_client_result($temp_transac, $export = false){
		$arr = array();
		/***/		
		if( $temp_transac->num_rows() != 0){
			$fields = $temp_transac->list_fields(); 
			foreach($temp_transac->result() as $data){
				$newRow = new stdClass();
				foreach ($fields as $field){
					if($field <> 'AGREEMENT_ID'){
						if($field == 'CP_ID' && $data->$field != '') $newRow->$field =  $this->my_lib->digitalID($data->$field);
						else if(($field == 'TIN' || $field == 'GROUPTIN') && $data->$field != '') $newRow->$field = $this->my_lib->setTin($data->$field);
						else $newRow->$field =  $data->$field;	
					}else{
						$AGREEMENT_ID = $newRow->$field = $data->$field;	
					}				
				}
				$ContactPerson = 'ContactPerson';
				$ContactNumber = 'ContactNumber';
				$InsertType = 'InsertType';
				$contact = $this->Corepass_model->getQueryAgreementDigitalRole($AGREEMENT_ID); //Digital POC
				if($contact->num_rows() <> 0){
					$contact = $contact->result();
					$newRow->$ContactPerson = $contact[0]->FULLNAME;
					$newRow->$ContactNumber = $contact[0]->CONTACT;
				}else $newRow->$ContactPerson = $newRow->$ContactNumber = "";
				
				$newRow->$InsertType = "I";
				$arr[] = $newRow;
			}	
		}	
		return $arr;
	}
		
		
	/**
	 * ---------------------------------------------------------------------------
	 * DIGITAL CLIENT Order Issuance - SOA
	 * ---------------------------------------------------------------------------
	 */	 
	 private function _interface_soa($serverDl = true){
		 /**
			date coverage -> all transaction of previous day
		 */		
		$date = new DateTime(); 
		$previousDate = $date->modify("-1 days")->format('m/d/yy'); 
			if(isset($_GET['date'])) $previousDate = $_GET['date']; 
		$where = " AND TO_CHAR(cs.START_DATE, 'mm/dd/yyyy') ='".$previousDate."'";
			if(isset($_GET['month'])){
				$previousDate = $_GET['month'];  
				$where = " AND TO_CHAR(cs.START_DATE, 'mm/yyyy') ='".$previousDate."'";
			}
		//$where = " AND TO_CHAR(cs.START_DATE, 'mm/dd/yyyy') >= '09/14/2020' AND TO_CHAR(cs.START_DATE, 'mm/dd/yyyy') <= '09/16/2020'";
		$result =  $this->Corepass_model->getDigitalSOAOrder($where);

		// log_message('error', json_encode($result->result(), JSON_PRETTY_PRINT));
		
		// log_message('error','----------');
		
		// return true;
		
		if($result->num_rows() != 0 ){
			$arr = $this->_interface_soa_result($result); 
			
			// log_message('error', json_encode($arr, JSON_PRETTY_PRINT));		
			
			$module['filename'] = 'DO_'.date('mdY',time()).'_001'; //CIC_SOA_MMDDYYYY_01.csv 
			//echo '<pre>';print_r($arr); echo '</pre>';die();
			return $this->download_file->_cic_soa_remittance($module, $arr, $serverDl);
		}
	 } 
	private function _interface_soa_result($temp_transac, $export = false){
		$arr = array();		
		foreach($temp_transac->result() as $temp_row){ 
			$newRow = new stdClass();
			$newRow->RECORD_TYPE = 'H';
			$newRow->SOA = 'SOA';
			$newRow->TIN = $this->my_lib->setTin($temp_row->TIN) ?: $temp_row->TIN;
			$newRow->LegalName = $temp_row->LEGALNAME;
			$newRow->SOA_NUMBER = $temp_row->SOA_NUMBER;
			$newRow->ORDER_ID = $this->my_lib->paNumber($temp_row->ORDER_ID, false, ''); 
			$newRow->ORDER_DATE = $temp_row->ORDER_DATE; //created date									
			$newRow->DELIVERED_DATE = $temp_row->DELIVERED_DATE; //credited date
			$newRow->CUSTOMER_TYPE = $temp_row->CLIENT_TYPE;	
			$newRow->SERVICE_ID = $temp_row->SERVICE_ID;	
			$newRow->ACCOUNT_MANAGER = $temp_row->ACCOUNT_MANAGER;	
			$newRow->PO = $temp_row->PO;		
			$newRow->CP_ID = $this->my_lib->digitalID($temp_row->CP_ID);		 
			$newRow->DUE_DATE = $temp_row->DUE_DATE;
			
			$whereD = 'AND ECD.N_ACCOUNTINGDOCUMENT = '.$temp_row->SOA_NUMBER.' AND TCO.N_CREDITORDER = '.$temp_row->ORDER_ID.' AND EA.SERVICE_ID = '.$temp_row->SERVICE_ID;	
			$resultDetail = $this->Corepass_model->getDigitalSOAOrderBillable($whereD);				
			$return_detail = $this->_detail_result($resultDetail, $temp_row->SERVICE_ID,  $temp_row->ACCOUNT_MANAGER, $newRow->DELIVERED_DATE);	
			$newRow->nav_detail = $return_detail['nav_detail'];
			/*
			* get computation from the _detail_result
			* NET_BILLABLE sumOfBillablItem  = without tax
			* GROSS_BILLABLE sumOfBillablItem = with tax 
			*/	
			$newRow->DISCOUNT = $return_detail['TOTAL_DISCOUNT']; //DISCOUNT CALCULATION  -- change to total amount of rebate billable item
			$newRow->AMOUNT =  ($newRow->DISCOUNT <> 0 ? $return_detail['X_GROSS_BILLABLE'] : $return_detail['GROSS_BILLABLE']); //TOTAL PAYMENT (SUM of base amount per billable ITEM less Discount Billable)
			$newRow->TOTAL_AMOUNT = ($newRow->DISCOUNT <> 0 ? $return_detail['X_NET_BILLABLE'] : $return_detail['NET_BILLABLE']); //NET AMOUNT CALCULATION
			$arr[] = $newRow; 
		}
		return $arr;
	}
			private function _detail_result($result, $SERVICE_ID, $ACCOUNT_MANAGER, $DELIVERED_DATE){
				if(!$result || $result->num_rows() == 0) return [];				
				
				$GROSS_BILLABLE = $NET_BILLABLE = $TOTAL_DISCOUNT = 0;
				$X_GROSS_BILLABLE = $X_NET_BILLABLE = 0;
				$arr = array();			
				foreach($result->result() as $temp_row){ 
					//if($temp_row->BILLABLE_CATEG_ID == 600){	
					if(in_array($temp_row->BILLABLE_CATEG_ID, array(600, 356, 355))){	
						$TOTAL_DISCOUNT += $temp_row->BILLABLE_AMOUNT; 
						$X_GROSS_BILLABLE -= $temp_row->BILLABLE_AMOUNT; 
						$X_NET_BILLABLE -= ($temp_row->BILLABLE_AMOUNT + $temp_row->BILLABLE_AMOUNT);  //deduct twice the rebate amount
					}else{
						$newRow = new stdClass(); 
						$newRow->RECORD_TYPE = 'D';	
						$newRow->SERVICE_ID = $SERVICE_ID;
						$newRow->ACCOUNT_MANAGER = $ACCOUNT_MANAGER;
						$newRow->SI_NUMBER = $temp_row->SI_NUMBER;
						
						$newRow->ISSUANCE_DATE = $newRow->VAT_COND =  $newRow->VAT_OUTPUT = '';					
						
						if($temp_row->B_FACEVALUE <> 'T'){
							$newRow->BILLABLE_ITEM = $temp_row->BILLABLE_CATEG_NAME;//.'_'.$temp_row->BILLABLE_CATEG_ID;
							
							$BILLABLE_AMOUNT = $temp_row->BILLABLE_AMOUNT;
							if($temp_row->B_CREDIT == 'T'){ //DISCOUNT BILLABLES
								$newRow->CREDIT_VALUE = -1 * (float)$BILLABLE_AMOUNT;
								$GROSS_BILLABLE -= $BILLABLE_AMOUNT;
								$NET_BILLABLE -= $BILLABLE_AMOUNT;		
								$X_GROSS_BILLABLE -= $BILLABLE_AMOUNT;	
								$X_NET_BILLABLE -= $BILLABLE_AMOUNT;									
							}else{							
								$newRow->VAT_COND = $temp_row->VATCON_TYPENAME;
								$newRow->VAT_OUTPUT = $this->my_lib->computeBillVAT($BILLABLE_AMOUNT, $temp_row->VAT_PERCENT ?? 0); 						
								$newRow->CREDIT_VALUE = $this->my_lib->computeBillIncVAT($BILLABLE_AMOUNT, $newRow->VAT_OUTPUT);
								
								$GROSS_BILLABLE += $newRow->CREDIT_VALUE;
								$NET_BILLABLE += $BILLABLE_AMOUNT;
								$X_GROSS_BILLABLE += $newRow->CREDIT_VALUE;		
								$X_NET_BILLABLE += $newRow->CREDIT_VALUE;				
							} 
						}else{						
							$newRow->ISSUANCE_DATE = $DELIVERED_DATE; 
							$newRow->BILLABLE_ITEM = '';//$this->my_lib->validateAM_Billable($ACCOUNT_MANAGER, $temp_row->BILLABLE_CATEG_NAME); 
							$newRow->CREDIT_VALUE = $temp_row->BILLABLE_AMOUNT;	
							
							$GROSS_BILLABLE += $newRow->CREDIT_VALUE;
							$NET_BILLABLE += $newRow->CREDIT_VALUE;								
							$X_GROSS_BILLABLE += $newRow->CREDIT_VALUE;
							$X_NET_BILLABLE += $newRow->CREDIT_VALUE;
						}						
						$arr[] = $newRow;
					}
				}
				$data['TOTAL_DISCOUNT'] = $TOTAL_DISCOUNT ; 
				$data['GROSS_BILLABLE'] = $GROSS_BILLABLE;
				$data['NET_BILLABLE'] = $NET_BILLABLE;
				$data['X_GROSS_BILLABLE'] = $X_GROSS_BILLABLE;
				$data['X_NET_BILLABLE'] = $X_NET_BILLABLE;
				$data['nav_detail'] = $arr;
				return $data;
			}
	
	/**
	 * ---------------------------------------------------------------------------
		CONVERSION ISSUANCE  = Merchant Conversion Issuance 
	 * ---------------------------------------------------------------------------
	 */	
	public function digital_merchant(){
		$this->_interface_merchant();
		$this->_interface_soa();
	}

	public function merchant(){
		$this->_interface_merchant(false); 
	}

	public function remittance(){
		$this->_interface_remittance(false);
	}

	 /**
	 * ---------------------------------------------------------------------------
	 * DIGITAL Merchant Conversion Master Info
	 * ---------------------------------------------------------------------------
	 */
	 
	 private function _interface_merchant($serverDl = true){
		//$where = "AND ECG.COMPANYGROUPTYPE_ID = 307 AND ECG.COMPANYGROUP_ID = 1228"; //UAT 
		$where = "AND ECG.COMPANYGROUPTYPE_ID = 308 AND ECG.COMPANYGROUP_ID = 1262"; //PROD 
		
		$date = new DateTime();
		$previousDate = $date->modify("-1 days")->format('m/d/yy');
			if(isset($_GET['date'])) $previousDate = $_GET['date'];
		$where .= " AND to_char(ECGD.CREATION_DATE, 'mm/dd/yyyy') ='".$previousDate."'";	 
		//$where .= " AND to_char(ECGD.CREATION_DATE, 'mm/dd/yyyy') >= '09/14/2020' AND to_char(ECGD.CREATION_DATE, 'mm/dd/yyyy') <= '09/16/2020'";
		$result = $this->Corepass_model->getQueryMerchantConv($where);	
		
		if($result->num_rows() != 0 ){
			$arr = $this->_interface_merchant_result($result);			
			$module['filename'] = 'CM_'.date('mdY',time()).'_01'; //DM_MMDDYYYY_01.csv
			//echo '<pre>'; print_r($arr); echo '</pre>'; die();
			return $this->download_file->_cic_merchant($module, $arr, $serverDl);
		}
	 }

	private function _interface_merchant_result($temp_transac, $export = false){	
		$arrMerchant = array();
		$fields = $temp_transac->list_fields(); 
		foreach($temp_transac->result() as $data){
			$arrMerchantData = new stdClass();
			foreach ($fields as $field){
				if($field == 'CP_ID' && $data->$field != '') $arrMerchantData->$field =  $this->my_lib->digitalID($data->$field);
				else if(($field == 'TIN' || $field == 'GROUPTIN') && $data->$field != '') $arrMerchantData->$field = $this->my_lib->setTin($data->$field);
				else $arrMerchantData->$field =  $data->$field;	

				if($field == 'CP_ID'){
					$CP_ID = $data->$field;	
					/** GET AGREEMENT **/
					$whereArg = 'AND EA.COMPANY_ID = '.$CP_ID; 
					$getAgreement = $this->Corepass_model->getQueryMerConvAgr($whereArg);
					if($getAgreement->num_rows() != 0){
						$fields2 = $getAgreement->list_fields(); 							
						foreach($getAgreement->result() as $data2){
							foreach ($fields2 as $field2){
								if($field2 <> 'AGREEMENT_ID'){
									if($field2 == 'ADDRESS'){
										$address = $this->Corepass_model->getQueryAddress($data2->$field2)->result();
										$arrMerchantData->$field2 = $address[0]->ADDRESS;
									}else if($field2 == 'VATCOND'){
										$arrMerchantData->$field2= ($data2->$field2 == 2 ? 'Exempt' : 'Taxable');
									}else $arrMerchantData->$field2 = $data2->$field2;	
								}else $AGREEMENT_ID = $data2->$field2;				
							}						
						}	
						$contact = $this->Corepass_model->getQueryAgreementRole($AGREEMENT_ID);
						if($contact->num_rows() <> 0){
							$ContactPerson = 'ContactPerson';
							$ContactNumber = 'ContactNumber';
							$contact = $contact->result();
							$arrMerchantData->$ContactPerson = $contact[0]->FULLNAME;
							$arrMerchantData->$ContactNumber= $contact[0]->CONTACT;
						}
						$arrMerchantData->InsertType = "I";
						$arrMerchantData->PAYMENTTERMSNAME = 'CONVERSION';
						$W_AGREEMENT = TRUE;
					}else $W_AGREEMENT = FALSE;
				}						
			}				
			if($W_AGREEMENT == true) $arrMerchant[] = $arrMerchantData;  
		}
		return $arrMerchant;
	}


	/**
	 * ---------------------------------------------------------------------------
	 * DIGITAL Merchant Issuance Details - Remittance 
	 * ---------------------------------------------------------------------------
	 */
	private function _interface_remittance($serverDl = true){
		/**
		   date coverage -> all transaction of previous day
		*/
	   
	   $date = new DateTime();
	   $previousDate = $date->modify("-1 days")->format('m/d/yy');
			if(isset($_GET['date'])) $previousDate = $_GET['date'];
	   $where = "to_char(remrhph.d_expected, 'mm/dd/yyyy') ='".$previousDate."'";  
	   //$where = "to_char(remrhph.d_expected, 'mm/dd/yyyy') >= '09/14/2020' AND to_char(remrhph.d_expected, 'mm/dd/yyyy') <= '09/16/2020'";
	   $result =  $this->Corepass_model->getQueryMerRemittance($where);	
	   if($result->num_rows() != 0 ){
		   $arr = $this->_interface_remittance_result($result); 
		   $module['filename'] = 'CI_'.date('mdY',time()).'_01'; //DR_MMDDYYYY_01.csv 
		//echo '<pre>';print_r($arr); echo '</pre>';die();
		   return $this->download_file->_cic_mer_remittance($module, $arr, $serverDl);
	   }
	} 

	private function _interface_remittance_result($temp_transac, $export = false){
		$arr = array();		
		foreach($temp_transac->result() as $temp_row){ 
			$newRow = new stdClass();
			$newRow->RECORD_TYPE = 'H';
			$newRow->CI = 'CI';
			$newRow->TIN = $this->my_lib->setTin($temp_row->TIN) ?: $temp_row->TIN;
			$newRow->LegalName = $temp_row->LEGALNAME;
			$newRow->REMITTANCE_ID = $temp_row->REMITTANCE_ID;
			$newRow->RS_NUMBER = $temp_row->RS_NUMBER; 
			$newRow->CREATION_DATE = $temp_row->CREATION_DATE; 						
			$newRow->CREDITED_DATE = $temp_row->CREDITED_DATE; //credited date dd/mm/yyyy
			$newRow->CUSTOMER_TYPE = $temp_row->CLIENT_TYPE;	
			$newRow->SERVICE_ID = $this->my_lib->product_mapping($temp_row->SERVICE_ID);	
			$newRow->ACCOUNT_MANAGER = $temp_row->ACCOUNT_MANAGER;	
			$newRow->PA_NUMBER = $temp_row->PA_NUMBER;		
			$newRow->CP_ID = $this->my_lib->digitalID($temp_row->CP_ID);		 
			$newRow->DUE_DATE = $temp_row->PAYMENT_DUEDATE;
			
			$whereD = 'AND RT.n_remittancerequest = '.$temp_row->REMITTANCE_ID.' AND EA.SERVICE_ID = '.$temp_row->SERVICE_ID;	
			$resultDetail = $this->Corepass_model->getDigitalRemittanceBillable($whereD);				
			$return_detail = $this->_remittance_detail_result($resultDetail, $newRow->SERVICE_ID,  $temp_row->ACCOUNT_MANAGER, $newRow->CREDITED_DATE);	
			$newRow->nav_detail = $return_detail['nav_detail'];
			
			/*
			* get computation from the _detail_result
			* NET_BILLABLE sumOfBillablItem  = without tax
			* GROSS_BILLABLE sumOfBillablItem = with tax
			*/	
			$newRow->DISCOUNT = $return_detail['TOTAL_DISCOUNT']; //DISCOUNT CALCULATION
			$newRow->AMOUNT = $return_detail['GROSS_BILLABLE']; //TOTAL PAYMENT (SUM of base amount per billable ITEM less Discount Billable)
			$newRow->TOTAL_AMOUNT = $return_detail['NET_BILLABLE']; //NET AMOUNT CALCULATION
			$arr[] = $newRow; 
		}
		return $arr;
	}

	private function _remittance_detail_result($result, $SERVICE_ID, $ACCOUNT_MANAGER, $DELIVERED_DATE){
		if($result->num_rows() == 0) return [];				
		
		/*
		Record Type
		Service ID
		Credit Issuance Date
		Billable Item
		Credit Value
		VAT Amount
		VAT Condition
		Account Manager
		*/
		$GROSS_BILLABLE = $NET_BILLABLE = $TOTAL_DISCOUNT = 0;
		$arr = array();			
		foreach($result->result() as $temp_row){ 
			$newRow = new stdClass(); 
			$newRow->RECORD_TYPE = 'D';	
			$newRow->SERVICE_ID = $SERVICE_ID;
			$newRow->ACCOUNT_MANAGER = $ACCOUNT_MANAGER;
			
			$newRow->ISSUANCE_DATE = $newRow->VAT_COND =  $newRow->VAT_OUTPUT = '';					
			
			if($temp_row->B_FACEVALUE <> 'T'){
				$newRow->BILLABLE_ITEM = str_replace('(remittance)','',$temp_row->BILLABLE_CATEG_NAME);  //$temp_row->BILLABLE_CATEG_NAME;
				
				$BILLABLE_AMOUNT = $temp_row->BILLABLE_AMOUNT;
				if($temp_row->B_CREDIT == 'T'){ //DISCOUNT BILLABLES
					$newRow->CREDIT_VALUE = $BILLABLE_AMOUNT;
					$TOTAL_DISCOUNT += $BILLABLE_AMOUNT;
				}else{							
					$newRow->VAT_COND = $temp_row->VATCON_TYPENAME;
					$newRow->VAT_OUTPUT = $this->my_lib->computeBillVAT($BILLABLE_AMOUNT, $temp_row->VAT_PERCENT ?? 0); 						
					$newRow->CREDIT_VALUE = $this->my_lib->computeBillIncVAT($BILLABLE_AMOUNT, $newRow->VAT_OUTPUT);							
					$GROSS_BILLABLE += $newRow->CREDIT_VALUE;
					$NET_BILLABLE += $BILLABLE_AMOUNT;
				} 
			}else{						
				$newRow->ISSUANCE_DATE = $DELIVERED_DATE; 
				$newRow->BILLABLE_ITEM = $this->my_lib->validateAM_Billable($ACCOUNT_MANAGER, $temp_row->BILLABLE_CATEG_NAME);  //facevalue and other billable item
				$newRow->CREDIT_VALUE = $temp_row->BILLABLE_AMOUNT;
				$GROSS_BILLABLE += $newRow->CREDIT_VALUE;
				$NET_BILLABLE += $newRow->CREDIT_VALUE;
			}						
			$arr[] = $newRow;
		}
		$data['TOTAL_DISCOUNT'] = $TOTAL_DISCOUNT ; 
		$data['GROSS_BILLABLE'] = $GROSS_BILLABLE;
		$data['NET_BILLABLE'] = $NET_BILLABLE;
		$data['nav_detail'] = $arr;
		return $data;
	}


}
