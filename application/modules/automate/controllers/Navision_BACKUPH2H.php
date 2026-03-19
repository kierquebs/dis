<?php
/**
 * NAVISION INTERFACE CONTROLLER
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Navision extends MX_Controller {
	private $DATE_NOW;

	public function	__construct(){
		parent::__construct();	
			$this->DATE_NOW = $this->my_lib->current_date();
			$this->load->model('Sys_model');
			$this->load->library('download_file');
	}
			
	/**
	 * ---------------------------------------------------------------------------
	 * PUBLIC FUNCTIONS
	 * ---------------------------------------------------------------------------
	 */
	public function index(){
		$this->_interface_merchant();
		$this->_interface_remittance();
		$this->_interface_remittance_norecon();
	}	
	public function merchant(){
		$this->_interface_merchant(false);
	}	
	public function remittance(){
		$this->_interface_remittance(false);
	}
	public function remittance_norecon(){
		$this->_interface_remittance_norecon(false);
	}
		
	/**
	 * ---------------------------------------------------------------------------
	 * MERCHANT CREATION
	 * ---------------------------------------------------------------------------
	 */
	 
	 private function _interface_merchant($serverDl = true){
		$date = new DateTime();		
		$previousDate = $date->modify("-1 days")->format('Y-m-d');
			if(isset($_GET['date'])) $previousDate = $_GET['date'];
		$where = "date_format(DATE_CREATED, '%Y-%m-%d') = '".$previousDate."' and InsertType= 'I'";
		//$where = "date_format(DATE_CREATED, '%Y-%m-%d') >= '2020-09-14' and date_format(DATE_CREATED, '%Y-%m-%d') <= '2020-09-16' and InsertType= 'I'";
		if(isset($_GET['month'])){
			$previousDate = $_GET['month'];  
			$where = "date_format(DATE_CREATED, '%Y-%m') = '".$previousDate."'";
		}
		if(isset($_GET['cpid'])) $where = "CP_ID = '".$_GET['cpid']."'"; //SPECIFIC COMPANY ID
		
		$result =  $this->Sys_model->v_merchant($where);
		if($result->num_rows() != 0 ){
			$arr = $this->_interface_merchant_result($result);
			$module['filename'] = 'DM_'.date('mdY',time()).'_01'; //DM_MMDDYYYY_01.csv
			//echo '<pre>';print_r($arr); echo '</pre>';die();
			return $this->download_file->_nav_merchant($module, $arr, $serverDl);
		}
	 }
		private function _interface_merchant_result($temp_transac, $export = false){
			$arr = array();			
			$fields = $temp_transac->list_fields(); 
			foreach($temp_transac->result() as $temp_row){ 
				$newRow = new stdClass(); 
				foreach ($fields as $field){
					if($field == 'pa_id' && $temp_row->$field != '') $newRow->$field = $this->my_lib->paNumber($temp_row->$field);
					else if($field == 'CP_ID' && $temp_row->$field != '') $newRow->$field = $this->my_lib->digitalID($temp_row->$field);
					else if($field == 'MerchantFee' && $temp_row->$field != '') $newRow->$field = str_replace('%','',$this->my_lib->convertMFRATE($temp_row->$field, true)); 
					else if(($field == 'TIN' || $field == 'GroupTIN') && $temp_row->$field != '') $newRow->$field = $this->my_lib->setTin($temp_row->$field);
					else $newRow->$field =  $temp_row->$field;
				}													
				$arr[] = $newRow;
			}
			return $arr;
		}
	  
	 /**
	 * ---------------------------------------------------------------------------
	 * Affiliate Remittance with Recon Transaction
	 * ---------------------------------------------------------------------------
	 */	 
	 private function _interface_remittance($serverDl = true){
		 /**
			reimbursement date coverage -> all transaction of previous day
		 */		
		$date = new DateTime();
		$previousDate = $date->modify("-1 days")->format('Y-m-d'); 
			if(isset($_GET['date'])) $previousDate = $_GET['date'];
			$where = "AND date_format(paH.DATE_CREATED, '%Y-%m-%d') = '".$previousDate."'";			
			//$where = " AND date_format(paH.DATE_CREATED, '%Y-%m-%d') >= '2020-09-14' AND date_format(paH.DATE_CREATED, '%Y-%m-%d') <= '2020-09-16'";
			
			if(isset($_GET['month'])){
				$previousDate = $_GET['month'];  
				$where = " AND date_format(paH.DATE_CREATED, '%Y-%m')='".$previousDate."'";
			}
			
			if(isset($_GET['paid'])){
				$paid = $_GET['paid'];  
				$where = " AND  paH.PA_ID ='".$paid."'";
			}	

			if(isset($_GET['grp_pa'])){
				$where = " AND  paH.PA_ID IN (4367)";
			}		
			
			/*if(isset($_GET['grp_pa2'])){
				$where = " AND  paH.PA_ID IN (2546,2547)";
			}*/		
		
		$where .= " AND paH.vatcond <> ''";		
		$result =  $this->Sys_model->v_navH($where);	
		if($result->num_rows() != 0 ){
			$module['filename'] = 'DR_'.date('mdY',time()).'_01'; //DR_MMDDYYYY_01.csv			
			$arr_pa = $this->_interface_remittance_result($result); 
			
			/*$result_reversal =  $this->Sys_model->v_navH_reversal($where);	
				$arr_reversal = $this->_interface_remittance_result($result_reversal, false, true); 

			$arr = array_merge( $arr_pa, $arr_reversal );*/
			return $this->download_file->_nav_remittance($module, $arr_pa, $serverDl);
		}else{
			echo 'NO Result Found! Check No Recon DR Generation';
		}
	 }
		private function _interface_remittance_result($temp_transac, $export = false, $refund = false){
			$arr = array();			
			foreach($temp_transac->result() as $temp_row){ 
				$newRow = new stdClass();
				$newRow->RECORD_TYPE = 'H';
				$newRow->paymentAdvice = $this->my_lib->paNumber($temp_row->paymentAdvice);
				//$newRow->PA_TYPE = ($refund == true ? 'DM' : 'Payment Advise');//$this->my_lib->paNumber($temp_row->paymentAdvice);
				$newRow->PA_TYPE = $newRow->paymentAdvice;
				$newRow->CP_ID = $this->my_lib->digitalID($temp_row->CP_ID);
				$newRow->RECON_ID = $temp_row->RECON_ID;
				$newRow->RECONDATE = $temp_row->RECONDATE;
				$newRow->PROD_ID = $temp_row->PROD_ID; 
				$newRow->paGenDate = $temp_row->paGenDate;
				$newRow->MERCHANT_ID = $temp_row->MERCHANT_ID;
				$newRow->TIN = $this->my_lib->setTin($temp_row->TIN);
				$newRow->LegalName = $temp_row->LegalName;
				$newRow->PayeeCode = $temp_row->PayeeCode;
				$newRow->PayeeName = $temp_row->PayeeName;
				$newRow->BankAccountNumber = $temp_row->BankAccountNumber;
				$newRow->ExpectedDueDate = $this->my_lib->convertDate($temp_row->ExpectedDueDate); 
				//check billable marketing fee with vat condition and vat output
					$percentMF = $this->my_lib->convertMFRATE($temp_row->MERCHANT_FEE, true);
					$VAT = $this->my_lib->checkVAT($temp_row->vatCond);
					$totalFV = $temp_row->TOTAL_FV;
				$newRow->VAT_COND = $temp_row->vatCond;		
				$newRow->VAT_OUTPUT = $this->my_lib->computeVAT($totalFV, $percentMF, $VAT);	
				$newRow->TOTAL_FV = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);	
				$newRow->MERCHANT_FEE = $this->my_lib->computeMFVATINCL($totalFV, $percentMF, $newRow->VAT_OUTPUT);		
				/*
				navision detail
				*/
				$whereD = 'AND paH.PA_ID = '.$temp_row->paymentAdvice.' AND recon.PROD_ID = '.$temp_row->PROD_ID.' AND recon.RECON_ID = "'.$temp_row->RECON_ID.'"';	
				
				if($refund == true){
					$resultDetail = $this->Sys_model->v_navD_reversal($whereD);
				}else{					
					$resultDetail = $this->Sys_model->v_navD($whereD);
				}	
				$newRow->nav_detail = $this->_detail_result($resultDetail);												
				$arr[] = $newRow;
			}
			return $arr;
		}
			private function _detail_result($result){
				if($result->num_rows() == 0) return "";				
				$arr = array();			
				foreach($result->result() as $temp_row){ 
					$newRow = new stdClass(); 
					$newRow->RECORD_TYPE = 'D';	
					$newRow->PROD_ID = $temp_row->PROD_ID;
					$newRow->FV = $temp_row->FV;							
						$percentMF = $this->my_lib->convertMFRATE($temp_row->MERCHANT_FEE, true);
					$newRow->VAT_COND = $temp_row->vatCond;		
					$newRow->VAT_OUTPUT = $this->my_lib->computeVAT($temp_row->FV, $percentMF, $this->my_lib->checkVAT($newRow->VAT_COND));	
					$arr[] = $newRow;
				}
				return $arr;
			}
	
		/**
	 * ---------------------------------------------------------------------------
	 * Affiliate Remittance with Recon Transaction
	 * ---------------------------------------------------------------------------
	 */	 
	private function _interface_remittance_norecon($serverDl = true){
		/**
		   reimbursement date coverage -> all transaction of previous day
		*/		
	   $date = new DateTime();
	   $previousDate = $date->modify("-1 days")->format('Y-m-d'); 
		   if(isset($_GET['date'])) $previousDate = $_GET['date'];
		   $where = "AND date_format(paH.DATE_CREATED, '%Y-%m-%d') = '".$previousDate."'";			
		   //$where = " AND date_format(paH.DATE_CREATED, '%Y-%m-%d') >= '2020-09-14' AND date_format(paH.DATE_CREATED, '%Y-%m-%d') <= '2020-09-16'";
		   
		   if(isset($_GET['month'])){
			   $previousDate = $_GET['month'];  
			   $where = " AND date_format(paH.DATE_CREATED, '%Y-%m')='".$previousDate."'";
		   }
		   
		   if(isset($_GET['paid'])){
			   $paid = $_GET['paid'];  
			   $where = " AND  paH.PA_ID ='".$paid."'";
		   }	

		   if(isset($_GET['grp_pa'])){
			   $where = " AND  paH.PA_ID IN (4674,4673,4672,4671)"; 
		   }			
	   
	   $where .= " AND paH.vatcond <> ''";		
	   $result =  $this->Sys_model->v_navH_NRecon($where);	
	   if($result->num_rows() != 0 ){
		   $module['filename'] = 'DR_'.date('mdY',time()).'_001'; //DR_MMDDYYYY_01.csv			
		   $arr_pa = $this->_interface_remittance_result_norecon($result);
		   
		   /*$result_reversal =  $this->Sys_model->v_navH_reversal_NRecon($where);	
			   $arr_reversal = $this->_interface_remittance_result_norecon($result_reversal, false, true);
		   $arr = array_merge( $arr_pa, $arr_reversal );*/
		   
		   return $this->download_file->_nav_remittance($module, $arr_pa, $serverDl);
	   }else{
			echo 'NO Result Found! Check With Recon DR Generation';
		}
	}
	
		private function _interface_remittance_result_norecon($temp_transac, $export = false, $refund = false){
			$arr = array();			
			foreach($temp_transac->result() as $temp_row){ 
				$newRow = new stdClass();
				$newRow->RECORD_TYPE = 'H';
				$newRow->paymentAdvice = $this->my_lib->paNumber($temp_row->paymentAdvice);
				//$newRow->PA_TYPE = ($refund == true ? 'DM' : 'Payment Advise');//$this->my_lib->paNumber($temp_row->paymentAdvice);
				$newRow->PA_TYPE = $newRow->paymentAdvice;
				$newRow->CP_ID = $this->my_lib->digitalID($temp_row->CP_ID);
				$newRow->RECON_ID = "AUTO-".$temp_row->PROD_ID."-".$temp_row->MERCHANT_ID."-".$temp_row->paymentAdvice;  //ADJUSTMENT FOR NO RECON MERCHANTS 
				$newRow->RECONDATE = $temp_row->paGenDate; //ADJUSTMENT FOR NO RECON MERCHANTS
				$newRow->PROD_ID = $temp_row->PROD_ID; 
				$newRow->paGenDate = $temp_row->paGenDate;

				$newRow->MERCHANT_ID = $temp_row->MERCHANT_ID;
				$newRow->TIN = $this->my_lib->setTin($temp_row->TIN);
				$newRow->LegalName = $temp_row->LegalName;
				$newRow->PayeeCode = $temp_row->PayeeCode;
				$newRow->PayeeName = $temp_row->PayeeName;
				$newRow->BankAccountNumber = $temp_row->BankAccountNumber;
				$newRow->ExpectedDueDate = $this->my_lib->convertDate($temp_row->ExpectedDueDate); 
				//check billable marketing fee with vat condition and vat output
					$percentMF = $this->my_lib->convertMFRATE($temp_row->MERCHANT_FEE, true);
					$VAT = $this->my_lib->checkVAT($temp_row->vatCond);
					$totalFV = $temp_row->TOTAL_FV;
				$newRow->VAT_COND = $temp_row->vatCond;		
				$newRow->VAT_OUTPUT = $this->my_lib->computeVAT($totalFV, $percentMF, $VAT);	
				$newRow->TOTAL_FV = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);	
				$newRow->MERCHANT_FEE = $this->my_lib->computeMFVATINCL($totalFV, $percentMF, $newRow->VAT_OUTPUT);		
				/*
				navision detail
				*/			
				$whereD = 'AND paH.PA_ID = '.$temp_row->paymentAdvice.' AND redeem.PROD_ID = '.$temp_row->PROD_ID.'';	 // AND redeem.BRANCH_ID = "'.$temp_row->BRANCH_ID.'"
				
				if($refund == true){
					$resultDetail = $this->Sys_model->v_navD_reversal_NRecon($whereD);
				}else{					
					$resultDetail = $this->Sys_model->v_navD_NRecon($whereD);
				}	
				$newRow->nav_detail = $this->_detail_result($resultDetail);	

				$arr[] = $newRow;
			}
			return $arr;
		}

}//end controller
