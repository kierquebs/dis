<?php
/**
 * NAVISION INTERFACE CONTROLLER
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Dormancy extends MX_Controller {
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
		$this->_interface_dormancy();
	}	
		
	 /**
	 * ---------------------------------------------------------------------------
	 * DORMANCY FEE
	 * ---------------------------------------------------------------------------
	 */	  
	 private function _interface_dormancy($serverDl = true){
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
		$result =  $this->Sys_model->v_navH($where, false, "", true); //$where = null, $count = false, $select = null, $dormancy = false	
		if($result->num_rows() != 0 ){
			$module['filename'] = 'DR_'.date('mdY',now()).'_01'; //DR_MMDDYYYY_01.csv			
			$arr_pa = $this->_interface_dormancy_result($result); 
			
			$result_reversal =  $this->Sys_model->v_navH_reversal($where, false, "", true);	//$where = null, $count = false, $select = null, $dormancy = false
				$arr_reversal = $this->_interface_dormancy_result($result_reversal, false, true); 

			$arr = array_merge( $arr_pa, $arr_reversal );
			
			//echo $where; 
			//echo '<pre>';print_r($arr); echo '</pre>';die();
			return $this->download_file->_dormancy_fee($module, $arr, $serverDl);
		}
	 }
		private function _interface_dormancy_result($temp_transac, $export = false, $refund = false){
			$arr = array();			
			foreach($temp_transac->result() as $temp_row){ 
				$newRow = new stdClass();
				$newRow->RECORD_TYPE = 'H';
				$newRow->paymentAdvice = ($refund == true ? 'SFeesRev' : 'SFees');//$this->my_lib->paNumber($temp_row->paymentAdvice);
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
			
	
}
