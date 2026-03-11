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
	
	public function read_file($filename, $module, $otherFolder = ''){	
		/* check if there is an existing new file*/
		if(!empty($otherFolder)) $folderName = $otherFolder;
		else $folderName = str_replace("_temp","",$module);
		
		$sourceFile = dirname($_SERVER["SCRIPT_FILENAME"])."/to_upload/".$folderName."/".$filename;		
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
	private function checkSyncDate($date){
		$date = trim(str_replace("/","-",$date));
		if(empty($date)){
			return '0000-00-00 00:00:00';
		}  
		return date("Y-m-d H:i:s", strtotime($date));
	}
	
	private function _redemption($arrayCount, $allDataInSheet){
		$entry = array();
		for($i=2;$i<=$arrayCount;$i++){
			if(trim($allDataInSheet[$i]["L"]) != ''){
				$entry[] = array(
					'MERCHANT_NAME' => $this->my_lib->xss_filter(trim($allDataInSheet[$i]["A"])),
					'MERCHANT_ID' => trim($allDataInSheet[$i]["B"]),
					'BRANCH_ID' => $this->my_lib->xss_filter(trim($allDataInSheet[$i]["C"])),
					'POS_ID' => trim($allDataInSheet[$i]["D"]),
					'POS_TXN_ID' => trim($allDataInSheet[$i]["E"]),
					'PROD_ID' => trim($allDataInSheet[$i]["F"]),
					'TRANSACTION_DATE_TIME' => $this->checkSyncDate($allDataInSheet[$i]["G"]),
					'TRANSACTION_ID' => trim($allDataInSheet[$i]["H"]),
					'VOUCHER_CODE' => trim($allDataInSheet[$i]["I"]),
					'TRANSACTION_VALUE' => trim($allDataInSheet[$i]["J"]),
					'STAGE' => trim($allDataInSheet[$i]["K"]),
					'REDEEM_ID' => trim($allDataInSheet[$i]["L"]),
					'PAYMENT_MODE' => trim($allDataInSheet[$i]["M"])
				);
			}
		}
		return $entry;
	}
	
	private function _reconciliation($arrayCount, $allDataInSheet){
		$entry = array();
		for($i=2;$i<=$arrayCount;$i++){
			if(trim($allDataInSheet[$i]["L"]) != ''){
				$entry[] = array(
					'MERCHANT_NAME' => $this->my_lib->xss_filter(trim($allDataInSheet[$i]["A"])),
					'MERCHANT_ID' => trim($allDataInSheet[$i]["B"]),
					'BRANCH_ID' => $this->my_lib->xss_filter(trim($allDataInSheet[$i]["C"])),
					'POS_ID' => trim($allDataInSheet[$i]["D"]),
					'POS_TXN_ID' => trim($allDataInSheet[$i]["E"]),
					'TRANSACTION_DATE_TIME' => $this->checkSyncDate($allDataInSheet[$i]["F"]),
					'PROD_ID' => trim($allDataInSheet[$i]["G"]),
					'REDEEM_ID' => trim($allDataInSheet[$i]["H"]),
					'VOUCHER_CODE' => trim($allDataInSheet[$i]["I"]),
					'TRANSACTION_VALUE' => trim($allDataInSheet[$i]["J"]),
					'RECON_DATE_TIME' => $this->checkSyncDate($allDataInSheet[$i]["K"]),
					'RECON_ID' => trim($allDataInSheet[$i]["L"]),
					'PA_ID' => 0,
					'PAYMENT_MODE' => trim($allDataInSheet[$i]["M"])
				);
			}
		}
		return $entry;
	}
	
	private function _branches($arrayCount, $allDataInSheet){
		$entry = array();
		for($i=2;$i<=$arrayCount;$i++){
			$AFFILIATEGROUPCODE = '';
			if(trim($allDataInSheet[$i]["A"]) != '' && trim($allDataInSheet[$i]["D"]) != ''){
				if(isset($allDataInSheet[$i]["E"])) $AFFILIATEGROUPCODE = $allDataInSheet[$i]["E"];
				$entry[] = array(
					'BRANCH_ID' => trim($allDataInSheet[$i]["A"]),
					'MERCHANT_ID' => trim($allDataInSheet[$i]["B"]),
					'BRANCH_NAME' => $allDataInSheet[$i]["C"],
					'CP_ID' => trim($allDataInSheet[$i]["D"]),
					'AFFILIATEGROUPCODE' => trim($AFFILIATEGROUPCODE) 
				);
			}
		} 
		return $entry;
	}
	
	private function _cutoff($arrayCount, $allDataInSheet){
		$entry = array();
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
		$entry = array();
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

	private function _conversion($arrayCount, $allDataInSheet){
		$entry = array();
		for($i=2;$i<=$arrayCount;$i++){
			if(trim($allDataInSheet[$i]["E"]) != ''){		
				//check if VOUCHER CODE is MULTI
				$VOUCHER_ID = trim($allDataInSheet[$i]["E"]);
				$resultVoucher = $this->my_lib->explodeVoucher($VOUCHER_ID);		
				if($resultVoucher['count'] <> 0){
					for($y=0;$y<$resultVoucher['count'];$y++){
						if($resultVoucher['result'][$y] != ''){	
							$entry[] = array(
								'USER_ID' => trim($allDataInSheet[$i]["A"]),
								'NAME' => $this->my_lib->xss_filter(trim($allDataInSheet[$i]["B"])),
								'BRANCH_NAME' => $this->my_lib->xss_filter(trim($allDataInSheet[$i]["C"])),
								'TOTAL_AMOUNT' => trim($allDataInSheet[$i]["D"]),
								'VOUCHER_CODES' => trim($resultVoucher['result'][$y]), 
								'STAGE' => trim($allDataInSheet[$i]["F"]),
								'CHANNEL' => trim($allDataInSheet[$i]["G"]),
								'CREATED_AT' => $this->checkSyncDate($allDataInSheet[$i]["H"]),
								'AGENT_ID' => trim($allDataInSheet[$i]["I"]), 
							);
						}
					}
				}else{				
					$entry[] = array(
						'USER_ID' => trim($allDataInSheet[$i]["A"]),
						'NAME' => $this->my_lib->xss_filter(trim($allDataInSheet[$i]["B"])),
						'BRANCH_NAME' => $this->my_lib->xss_filter(trim($allDataInSheet[$i]["C"])),
						'TOTAL_AMOUNT' => trim($allDataInSheet[$i]["D"]),
						'VOUCHER_CODES' => trim($allDataInSheet[$i]["E"]), 
						'STAGE' => trim($allDataInSheet[$i]["F"]),
						'CHANNEL' => trim($allDataInSheet[$i]["G"]),
						'CREATED_AT' => $this->checkSyncDate($allDataInSheet[$i]["H"]),
						'AGENT_ID' => trim($allDataInSheet[$i]["I"]),
					);
				}
			}
		}
		return $entry;
	}
	
	private function _reversal($arrayCount, $allDataInSheet){
		$entry = array(); 
		for($i=2;$i<=$arrayCount;$i++){
			$entry[] = array(
				'MERCHANT_NAME' => $this->my_lib->xss_filter(trim($allDataInSheet[$i]["A"])),
				'MERCHANT_ID' => trim($allDataInSheet[$i]["B"]),
				'BRANCH_ID' => $this->my_lib->xss_filter(trim($allDataInSheet[$i]["C"])),
				'POS_ID' => trim($allDataInSheet[$i]["D"]),
				'POS_TXN_ID' => trim($allDataInSheet[$i]["E"]),
				'PROD_ID' => trim($allDataInSheet[$i]["F"]),
				'TRANSACTION_DATE_TIME' => trim($allDataInSheet[$i]["G"]),
				'TRANSACTION_ID' => trim($allDataInSheet[$i]["H"]),
				'REDEMPTION_API_TRANSACTION_ID' => trim($allDataInSheet[$i]["I"]),
				'REVERSAL_DATE_TIME' => trim($allDataInSheet[$i]["J"]),
				'REVERSAL_TRANSACTION_ID' => trim($allDataInSheet[$i]["K"]),
				'VOUCHER_CODE' => trim($allDataInSheet[$i]["L"]),
				'TRANSACTION_VALUE' => trim($allDataInSheet[$i]["M"]),
				'RECON_API_TRANSACTION_ID' => trim($allDataInSheet[$i]["N"]),
				'PAYMENT_MODE ' => trim($allDataInSheet[$i]["O"]),
				'REVERSAL_MODE' => trim($allDataInSheet[$i]["P"])
			);
		}
		return $entry;
	}

	/*****
	******* Temporary solution for SFTP Recon Files
	*****/
	private function _reconciliation_temp($arrayCount, $allDataInSheet){
		$entry = array();
		for($i=2;$i<=$arrayCount;$i++){
			if(trim($allDataInSheet[$i]["L"]) != ''){
				$entry[] = array(
					'MERCHANT_NAME' => $this->my_lib->xss_filter(trim($allDataInSheet[$i]["A"])),
					'MERCHANT_ID' => trim($allDataInSheet[$i]["B"]),
					'BRANCH_ID' => $this->my_lib->xss_filter(trim($allDataInSheet[$i]["C"])),
					'POS_ID' => trim($allDataInSheet[$i]["D"]),
					'POS_TXN_ID' => trim($allDataInSheet[$i]["E"]),
					'PROD_ID' => trim($allDataInSheet[$i]["F"]),
					'TRANSACTION_DATE_TIME' => $this->checkSyncDate($allDataInSheet[$i]["G"]),
					'REDEEM_ID' => trim($allDataInSheet[$i]["H"]),
					'VOUCHER_CODE' => trim($allDataInSheet[$i]["I"]),
					'TRANSACTION_VALUE' => trim($allDataInSheet[$i]["J"]),
					'RECON_DATE_TIME' => $this->checkSyncDate($allDataInSheet[$i]["K"]),
					'RECON_ID' => trim($allDataInSheet[$i]["L"]),
					'PA_ID' => 0,
					'PAYMENT_MODE' => trim($allDataInSheet[$i]["M"])
				);
			}
		}
		return $entry;
	}

}

