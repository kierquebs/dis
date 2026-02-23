<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Upload_file extends MX_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->library('classes/PHPExcel.php');
	}
	public function file_path(){
		return $this->my_layout->asset_location();
	}
	
	public function read_rawfile($filename, $module){	
		/* check if there is an existing new file*/
		$sourceFile = dirname($_SERVER["SCRIPT_FILENAME"])."/to_upload/".$module."/".$filename;		
		if(!file_exists($sourceFile)) return false;		
		
		$allDataInSheet = $this->_callPHPCLASS($sourceFile);		
		$arrayCount = count($allDataInSheet);  // Here get total count of row in that Excel sheet		
	
		if($arrayCount == 0) return false;
		else return  $allDataInSheet;
	}
	
	public function read_file($filename, $module){	
		/* check if there is an existing new file*/
		$sourceFile = dirname($_SERVER["SCRIPT_FILENAME"])."/to_upload/".$module."/".$filename;		
		if(!file_exists($sourceFile)) return false;		
		
		$allDataInSheet = $this->_callPHPCLASS($sourceFile);		
		$arrayCount = count($allDataInSheet);  // Here get total count of row in that Excel sheet		
	
		if($arrayCount == 0) return false;
		else{
			$module = '_'.$module;
			return  $this->$module($arrayCount, $allDataInSheet);	
		}
	}
	
	/*
	** PRIVATE FUNCTION INCLUDE HERE
	*/
	private function _callPHPCLASS($sourceFile){		
		$objPHPExcel = PHPExcel_IOFactory::load($sourceFile);		
		/*
		$totalrows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow(); //Count Number of rows avalable in excel      	 
		$objWorksheet = $objPHPExcel->setActiveSheetIndex(0); 
		*/
		return $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
	}
	
	/*
	* csv file source content
	*/
	private function _redemption($arrayCount, $allDataInSheet){
		$entry = '';
		for($i=2;$i<=$arrayCount;$i++){
			if(trim($allDataInSheet[$i]["L"]) != ''){
				$entry[] = array(
					'MERCHANT_NAME' => trim($allDataInSheet[$i]["A"]),
					'MERCHANT_ID' => trim($allDataInSheet[$i]["B"]),
					'BRANCH_ID' => trim($allDataInSheet[$i]["C"]),
					'POS_ID' => trim($allDataInSheet[$i]["D"]),
					'POS_TXN_ID' => trim($allDataInSheet[$i]["E"]),
					'PROD_ID' => trim($allDataInSheet[$i]["F"]),
					'TRANSACTION_DATE_TIME' => date("Y-m-d H:i:s", strtotime(trim($allDataInSheet[$i]["G"]))),
					'TRANSACTION_ID' => trim($allDataInSheet[$i]["H"]),
					'VOUCHER_CODE' => trim($allDataInSheet[$i]["I"]),
					'TRANSACTION_VALUE' => trim($allDataInSheet[$i]["J"]),
					'STAGE' => trim($allDataInSheet[$i]["K"]),
					'REDEEM_ID' => trim($allDataInSheet[$i]["L"])
				);
			}
		}
		return $entry;
	}
	
	private function _reconciliation($arrayCount, $allDataInSheet){
		$entry = '';
		for($i=2;$i<=$arrayCount;$i++){
			if(trim($allDataInSheet[$i]["L"]) != ''){
				$entry[] = array(
					'MERCHANT_NAME' => trim($allDataInSheet[$i]["A"]),
					'MERCHANT_ID' => trim($allDataInSheet[$i]["B"]),
					'BRANCH_ID' => trim($allDataInSheet[$i]["C"]),
					'POS_ID' => trim($allDataInSheet[$i]["D"]),
					'POS_TXN_ID' => trim($allDataInSheet[$i]["E"]),
					'TRANSACTION_DATE_TIME' => date("Y-m-d H:i:s", strtotime(trim($allDataInSheet[$i]["F"]))),
					'PROD_ID' => trim($allDataInSheet[$i]["G"]),
					'REDEEM_ID' => trim($allDataInSheet[$i]["H"]),
					'VOUCHER_CODE' => trim($allDataInSheet[$i]["I"]),
					'TRANSACTION_VALUE' => trim($allDataInSheet[$i]["J"]),
					'RECON_DATE_TIME' => date("Y-m-d H:i:s", strtotime(trim($allDataInSheet[$i]["K"]))),
					'RECON_ID' => trim($allDataInSheet[$i]["L"]),
					'PA_ID' => 0
				);
			}
		}
		return $entry;
	}
	
	private function _branches($arrayCount, $allDataInSheet){
		$entry = '';
		for($i=2;$i<=$arrayCount;$i++){
			if(trim($allDataInSheet[$i]["A"]) != ''){
				$entry[] = array(
					'BRANCH_ID' => trim($allDataInSheet[$i]["A"]),
					'MERCHANT_ID' => trim($allDataInSheet[$i]["B"]),
					'BRANCH_NAME' => $allDataInSheet[$i]["C"],
					'CP_ID' => trim($allDataInSheet[$i]["D"])
				);
			}
		}
		return $entry;
	}
	
	private function _cutoff($arrayCount, $allDataInSheet){
		$entry = '';
		for($i=2;$i<=$arrayCount;$i++){
			if(trim($allDataInSheet[$i]["A"]) != ''){
				$entry[] = array(
					'MERCHANT_ID' => trim($allDataInSheet[$i]["A"]),
					'TYPE' => trim($allDataInSheet[$i]["B"]),
					'SPECIFIC_DAY' => trim($allDataInSheet[$i]["C"]),
					'SPECIFIC_DATE' => trim($allDataInSheet[$i]["D"])
				);
			}
		}
		return $entry;
	}
	
	private function _merchant_fee($arrayCount, $allDataInSheet){
		$entry = '';
		for($i=2;$i<=$arrayCount;$i++){
			if(trim($allDataInSheet[$i]["A"]) != ''){
				$entry[] = array(
					'MERCHANT_ID' => trim($allDataInSheet[$i]["A"]),
					'MERCHANT_FEE' => trim($allDataInSheet[$i]["B"])
				);
			}
		}
		return $entry;
	}
}

