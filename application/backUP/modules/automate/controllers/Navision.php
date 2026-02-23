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
	}	
	public function merchant(){
		$this->_interface_merchant(false);
	}	
	public function remittance(){
		$this->_interface_remittance(false);
	}
		
	/**
	 * ---------------------------------------------------------------------------
	 * MERCHANT CREATION
	 * ---------------------------------------------------------------------------
	 */
	 
	 private function _interface_merchant($serverDl = true){
		$result =  $this->Sys_model->v_merchant();	
		if($result->num_rows() != 0 ){
			$arr = $this->_interface_merchant_result($result);
			$module['filename'] = 'DM_'.str_replace('-','',$this->my_lib->setDate('', TRUE)).'_01'; //DM_MMDDYYYY_01.csv
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
	 * Affiliate Remittance 
	 * ---------------------------------------------------------------------------
	 */	 
	 private function _interface_remittance($serverDl = true){
		 /**
			reimbursement date coverage -> all transaction of previous day
		 */
		 $where = '';
		$result =  $this->Sys_model->v_navH($where);	
		if($result->num_rows() != 0 ){
			$arr = $this->_interface_remittance_result($result); 
			$module['filename'] = 'DR_'.str_replace('-','',$this->my_lib->setDate('', TRUE)).'_01'; //DR_MMDDYYYY_01.csv
			return $this->download_file->_nav_remittance($module, $arr, $serverDl);
		}
	 }
		private function _interface_remittance_result($temp_transac, $export = false){
			$arr = array();			
			foreach($temp_transac->result() as $temp_row){ 
				$newRow = new stdClass();
				$newRow->RECORD_TYPE = 'H';
				$newRow->paymentAdvice = $this->my_lib->paNumber($temp_row->paymentAdvice);
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
				$newRow->ExpectedDueDate = $temp_row->ExpectedDueDate;
				//check billable marketing fee with vat condition and vat output
					$percentMF = $this->my_lib->convertMFRATE($temp_row->MERCHANT_FEE, true);
					$VAT = $this->my_lib->checkVAT($temp_row->vatCond);
					$totalFV = $temp_row->TOTAL_FV;
				$newRow->VAT_COND = $temp_row->vatCond;		
				$newRow->VAT_OUTPUT = $this->my_lib->computeVAT($totalFV, $percentMF, $VAT);	
				$newRow->TOTAL_FV = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);	
				$newRow->MERCHANT_FEE = $this->my_lib->computeMF($totalFV, $percentMF);		
				/*
				navision detail
				*/
				$whereD = 'AND paH.PA_ID = '.$temp_row->paymentAdvice.' AND recon.PROD_ID = '.$temp_row->PROD_ID.' AND recon.RECON_ID = "'.$temp_row->RECON_ID.'"';	
				$resultDetail = $this->Sys_model->v_navD($whereD);				
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
