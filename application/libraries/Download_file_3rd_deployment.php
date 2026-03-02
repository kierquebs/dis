<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Download_file extends MX_Controller{
	public $navLocation;
	public function __construct(){
		parent::__construct();
		$this->load->library('classes/PHPExcel.php');
		//$this->load->library('classes/PHPExcel.php');
		$this->navLocation = "C:/xampp/htdocs/nav_interface/";
	}
	
	public function file_path(){
		return $this->my_lib->asset_location();
	}
	
	/**
	 * load_file function
	 *
	 * @param array $module
	 * @param array $query
	 */
	public function load_file($module, $query){	
		if(empty($query)) return false;
		
		/* LOAD PHPCLASS FUNCTION*/			
		$objPHPExcel = $this->_callPHPCLASS($module);
		
		// Field names in the first row
		$fields = $query->list_fields();
		$col = 0;
		foreach ($fields as $field){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $field);
			$col++;
		}	 
		// Fetching the table data
		$row = 2;
		foreach($query->result() as $data){
			$col = 0;
			foreach ($fields as $field){
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $data->$field);
				$col++;
			}
			$row++;
		}	 
		$objPHPExcel->setActiveSheetIndex(0);	 
		
		/***
		* handle download file 
		*/		
		$this->_callDownload($objPHPExcel, $module);
		return true;
	}
	
/**
 * ---------------------------------------------------------------------------
 * PRIVATE FUNCTIONS
 * ---------------------------------------------------------------------------
 */

	 /**
	  * _callPHPCLASS function
	  *
	  * @param array $module
	  * @return object_array
	  */
	private function _callPHPCLASS($module){	
		$objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle($module['filename'])->setDescription("");        
		$objPHPExcel->setActiveSheetIndex(0);	
		return $objPHPExcel;
	}	
	/**
	 * _callDownload function
	 *
	 * @param object_array $arrOBJ
	 * @param array $module
	 * @param string $type {'Excel5' , 'CSV'}
	 * @return void
	 */
	private function _callDownload($arrOBJ, $module, $type = 'Excel5'){	
		$filename = $module['filename'];			
		header('Content-Type: application/force-download;');
		header('Content-Transfer-Encoding: binary'); //no cache	
		header('Cache-Control: max-age=0'); //no cache			
		header('Content-Disposition: attachment;filename="'.$filename.'"'); 		
		
		if($type == 'CSV'){
			$objWriter = new PHPExcel_Writer_CSV($arrOBJ);
			$objWriter->setUseBOM(true);
			$objWriter->setEnclosure('');  
		}else $objWriter = PHPExcel_IOFactory::createWriter($arrOBJ, $type);

		return $objWriter->save('php://output'); 
	}

	private function _callDownloadNew($arrOBJ, $module, $type = 'Excel5'){	
		$filename = $module['filename'];			
		header('Content-Type: application/force-download;');
		header('Content-Transfer-Encoding: binary'); //no cache	
		header('Cache-Control: max-age=0'); //no cache			
		header('Content-Disposition: attachment;filename="'.$filename.'"'); 		
		
		if($type == 'CSV'){
			$objWriter = new PHPExcel_Writer_CSV($arrOBJ);
			$objWriter->setUseBOM(false);
			$objWriter->setEnclosure('');  
		}else $objWriter = PHPExcel_IOFactory::createWriter($arrOBJ, $type);
		
		// Start output buffering to capture CSV content
		ob_start();
		$objWriter->save('php://output');
		$csvContent = ob_get_clean(); // Get the content from the buffer

		// $csvContent = preg_replace('/,\s*$/m', '', $csvContent); 
		
		$lines = explode("\n", $csvContent);  // Split CSV into lines


		$modifiedLines = [];
		$lineNumber = 0;  // Initialize line number counter
		
		foreach ($lines as $line) {
			$lineNumber++; 
			
			$firstLetter = substr(trim($line), 0, 1);  // Get the first letter after trimming any spaces
		
			if($firstLetter == 'H' || $lineNumber === 1){
				$modifiedLine = preg_replace('/,\s*$/m', '', $line);
				$modifiedLines[] = $modifiedLine;
			}else{
				$modifiedLines[] = $line;
		
			}
			//$modifiedLine = preg_replace('/,\s*$/m', '', $line);
			//$modifiedLines[] = $modifiedLine;
		}

		$csvContent = implode("\n", $modifiedLines);
		
		echo $csvContent;
	}
	
	private function _callDownloadServer($arrOBJ, $module, $type = 'Excel5', $serverDL = ''){
		$filename = $serverDL.$module['filename'];				
		if($type == 'CSV'){
			$objWriter = new PHPExcel_Writer_CSV($arrOBJ);
			$objWriter->setUseBOM(true);
			$objWriter->setEnclosure('');
		}else $objWriter = PHPExcel_IOFactory::createWriter($arrOBJ, $type);
		return $objWriter->save($filename);
	}
 /**
 * ---------------------------------------------------------------------------
 * ---------------------------------------------------------------------------
 */
				
	/**
	 * transaction_report function
	 */	
	public function transaction_report($module, $result){
		if(empty($result)) return false;	
		$styleArray = array(
			'font' => array(
				'bold' => true,
			)
		);
		$objPHPExcel = $this->_callPHPCLASS($module);
		$title = $module['filename'];
		$table_columns = array("DIGITAL ID", "MERCHANT ID", "MERCHANT NAME", "TIN NUMBER", "BRANCH ID", "BRANCH NAME", "VOUCHER CODE", "PRODUCT", "POS ID", "AMOUNT", "REDEEM ID", "REDEEM STATUS", "REDEEM DATE"
		, "RECON ID", "RECON DATE", "PAYMENT ADVICE ID", "PA STATUS", "PA DATE", "PA DUE DATE" );		
		$column = 0;
		foreach($table_columns as $field){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$column++;
		}	
		$objPHPExcel->getActiveSheet()->getStyle("A1:S1")->getAlignment()->setWrapText(true);	
		$objPHPExcel->getActiveSheet()->getStyle("A1:S1")->applyFromArray($styleArray);
		
		$x= 2; $i = 1;		
		foreach($result as $row){
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()
				->setCellValue("A$x",$row->cp_id)
				->setCellValue("B$x",$row->m_id)
				->setCellValue("C$x",$row->legalname)
				->setCellValue("D$x",$row->tin)
				->setCellValue("E$x",$row->br_id)
				->setCellValue("F$x",$row->br_name)
				->setCellValue("G$x",$row->voucher_code)
				->setCellValue("H$x",$row->prod_name)
				->setCellValue("I$x",$row->pos_id)
				->setCellValue("J$x",$row->redeem_fv)
				->setCellValue("K$x",$row->redeem_id)
				->setCellValue("L$x",$row->redeem_status)
				->setCellValue("M$x",$row->redeem_date)
				->setCellValue("N$x",$row->recon_id)
				->setCellValue("O$x",$row->recon_date)
				->setCellValue("P$x",$row->pa_id)
				->setCellValue("Q$x",(!empty($row->pa_id)? 'BILLED' : ''))
				->setCellValue("R$x",(!empty($row->pa_id)? $row->pa_date : ''))
				->setCellValue("S$x",(!empty($row->pa_id)? $row->pa_duedate : ''));
				
			$objPHPExcel->getActiveSheet()->getRowDimension($x)->setRowHeight(-1);	
			$objPHPExcel->getActiveSheet()->getStyle("A$x:S$x")->getAlignment()->setWrapText(true);
			$x++;
		}
		foreach(range('A','S') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
		}		
		$module['filename'] = $module['filename'].'_'.$this->my_lib->setDate('', TRUE).'.xls';	
		$this->_callDownload($objPHPExcel, $module);		
	}
	
	/**
	 * summary_report function
	 */	
	public function summary_report($module, $result){
		if(empty($result)) return false;	
		$styleArray = array(
			'font' => array(
				'bold' => true,
			)
		);
		$objPHPExcel = $this->_callPHPCLASS($module);
		$title = $module['filename'];
		$table_columns = array("PAYMENT ADVICE ID", "MERCHANT ID", "MERCHANT NAME", "TOTAL AMOUNT", "PAYMENT DUE DATE");		
		$column = 0;
		foreach($table_columns as $field){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$column++;
		}	
		$objPHPExcel->getActiveSheet()->getStyle("A1:E1")->getAlignment()->setWrapText(true);	
		$objPHPExcel->getActiveSheet()->getStyle("A1:E1")->applyFromArray($styleArray);
		
		$x= 2; $i = 1;		
		foreach($result as $row){
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()
				->setCellValue("A$x",$row->pa_id)
				->setCellValue("B$x",$row->m_id)
				->setCellValue("C$x",$row->legalname)
				->setCellValue("D$x",$row->TOTAL_FV)
				->setCellValue("E$x",$row->pa_duedate);
				
			$objPHPExcel->getActiveSheet()->getRowDimension($x)->setRowHeight(-1);	
			$objPHPExcel->getActiveSheet()->getStyle("A$x:E$x")->getAlignment()->setWrapText(true);
			$x++;
		}
		foreach(range('A','D') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
		}
		$module['filename'] = $module['filename'].'_'.$this->my_lib->setDate('', TRUE).'.xls';		
		$this->_callDownload($objPHPExcel, $module);		
	}
	
	/**
	 * _nav_merchant function
	 */	
	public function _nav_merchant($module, $result, $serverDl = true){
		if(empty($result)) return false;	
		
		$objPHPExcel = $this->_callPHPCLASS($module);
		$title = $module['filename'];		
		$x= 1;		
		foreach($result as $row){
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()
				->setCellValue("A$x",$row->TIN)				
				->setCellValue("B$x",'"'.$row->LegalName.'"')
				->setCellValue("C$x",'"'.$row->TradingName.'"')
				->setCellValue("D$x",'"'.$row->GroupName.'"')
				->setCellValue("E$x",'"'.$row->Address.'"')
				->setCellValue("F$x",$row->ContactPerson) 
				->setCellValue("G$x",$row->ContactNumber) 
				->setCellValue("H$x",$row->MeanofPayment)
				->setCellValue("I$x",$row->GroupTIN)
				->setCellValue("J$x",$row->CP_ID)
				->setCellValue("K$x",$row->PayeeCode)
				->setCellValue("L$x",$row->BankName)
				->setCellValue("M$x",$row->BankBranchCode)
				->setCellValue("N$x",$row->BankAccountNumber)
				->setCellValue("O$x",'"'.$row->PayeeName.'"')
				->setCellValue("P$x",$row->MerchantFee)
				->setCellValue("Q$x",$row->Industry) 
				->setCellValue("R$x",$row->InsertType);
				/*			
				->setCellValue("B$x",$row->LegalName)
				->setCellValue("C$x",$row->TradingName)
				->setCellValue("D$x",$row->GroupName)
				->setCellValue("E$x",$row->Address)
				->setCellValue("O$x",$row->PayeeName)
				*/
				
			$objPHPExcel->getActiveSheet()->getRowDimension($x)->setRowHeight(-1);	
			$objPHPExcel->getActiveSheet()->getStyle("A$x:R$x")->getAlignment()->setWrapText(true);
			$x++;
		}
		foreach(range('A','R') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
		}		
		$module['filename'] = $module['filename'].'.csv';		
		if($serverDl == true) $this->_callDownloadServer($objPHPExcel, $module, 'CSV', $this->navLocation);
		else $this->_callDownload($objPHPExcel, $module, 'CSV');	
	}
	
	/**
	 * _nav_remittance function
	 */	
	public function _nav_remittance($module, $result, $serverDl = true){
		if(empty($result)) return false;	
		
		$objPHPExcel = $this->_callPHPCLASS($module);
		$title = $module['filename'];		
		$x= 1;		
		foreach($result as $row){
			
			//log_message('error', json_encode($row->paymentAdvice, JSON_PRETTY_PRINT));
			
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()
				->setCellValue("A$x",$row->RECORD_TYPE)
				->setCellValue("B$x",$row->PA_TYPE)
				->setCellValue("C$x",$row->TIN)
				->setCellValue("D$x",'"'.$row->LegalName.'"') //'"'.$row->LegalName.'"'
				->setCellValue("E$x",$row->RECON_ID)
				->setCellValue("F$x",$row->RECONDATE) 
				->setCellValue("G$x",$row->paymentAdvice) 
				->setCellValue("H$x",$row->paGenDate)
				->setCellValue("I$x",$row->ExpectedDueDate)
				->setCellValue("J$x",str_replace(',','',$row->TOTAL_FV))
				->setCellValue("K$x",$row->PROD_ID)
				->setCellValue("L$x",$row->PayeeCode)
				->setCellValue("M$x",'"'.$row->PayeeName.'"')//$row->PayeeName
				->setCellValue("N$x",$row->BankAccountNumber)
				->setCellValue("O$x",$row->CP_ID)
				->setCellValue("P$x",$row->drBankCode)
				->setCellValue("Q$x",$row->drBankNo)
				->setCellValue("R$x",$row->drBankAccName)
				->setCellValue("S$x",$row->drBankName)
				->setCellValue("T$x",$row->drAgreementId);		 		
			$objPHPExcel->getActiveSheet()->getRowDimension($x)->setRowHeight(-1);	
			$objPHPExcel->getActiveSheet()->getStyle("A$x:O$x")->getAlignment()->setWrapText(true);
			
			$navDetail = $row->nav_detail;
			//CHECK IF NAV DETAILS IS NOT NULL
			if(!empty($navDetail)){
				$y = $x;
				foreach($navDetail as $detail){ //FACE VALUE
					$y++;
					$objPHPExcel->setActiveSheetIndex(0);
					$objPHPExcel->getActiveSheet()
						->setCellValue("A$y",$detail->RECORD_TYPE)
						->setCellValue("B$y",$detail->PROD_ID)
						->setCellValue("C$y",'') //Face value (Credits)
						->setCellValue("D$y",str_replace(',','',$detail->FV));
						/*->setCellValue("E$y",$detail->VAT_OUTPUT)
						->setCellValue("F$y",$detail->VAT_COND);*/				
					$objPHPExcel->getActiveSheet()->getRowDimension($y)->setRowHeight(-1);	
					$objPHPExcel->getActiveSheet()->getStyle("A$y:D$y")->getAlignment()->setWrapText(true);					
				}				
				$y += 1;	
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()
					->setCellValue("A$y",'D')
					->setCellValue("B$y",$detail->PROD_ID)
					->setCellValue("C$y",'Marketing fee')
					->setCellValue("D$y",str_replace(',','',$row->MERCHANT_FEE))
					->setCellValue("E$y",str_replace(',','',$row->VAT_OUTPUT))
					->setCellValue("F$y",$row->VAT_COND);				
				$objPHPExcel->getActiveSheet()->getRowDimension($y)->setRowHeight(-1);	
				$objPHPExcel->getActiveSheet()->getStyle("A$y:F$y")->getAlignment()->setWrapText(true);
				
				/*$y += 1;	
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()
					->setCellValue("A$y",'D')
					->setCellValue("B$y",'')
					->setCellValue("C$y",'Adjustment fee')
					->setCellValue("D$y", 0);				
				$objPHPExcel->getActiveSheet()->getRowDimension($y)->setRowHeight(-1);	
				$objPHPExcel->getActiveSheet()->getStyle("A$y:D$y")->getAlignment()->setWrapText(true);*/
				$x = $y;
			}			
			$x++;
		}
		foreach(range('A','R') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
		}
		$module['filename'] = $module['filename'].'.csv';		
		if($serverDl == true) $this->_callDownloadServer($objPHPExcel, $module, 'CSV', $this->navLocation);
		else $this->_callDownload($objPHPExcel, $module, 'CSV');		
	}

	/**
	 * _cic_client function
	 * 
	 */	
	public function _cic_client($module, $result, $serverDl = true){
		if(empty($result)) return false;	
		
		$objPHPExcel = $this->_callPHPCLASS($module);
		$title = $module['filename'];		
		$x= 1;		
		foreach($result as $row){
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()
				->setCellValue("A$x",$row->TIN)
				->setCellValue("B$x",'"'.$row->LEGALNAME.'"')
				->setCellValue("C$x",'"'.$row->GROUPNAME.'"')
				->setCellValue("D$x",'"'.$row->TRADINGNAME.'"')
				->setCellValue("E$x",'"'.$row->ADDRESS.'"')
				->setCellValue("F$x",$row->ContactPerson) 
				->setCellValue("G$x",$row->ContactNumber) 
				->setCellValue("H$x",$row->PAYMENTTERMSNAME)
				->setCellValue("I$x",$row->MEANOFPAYMENT)
				->setCellValue("J$x",$row->GROUPTIN)
				->setCellValue("K$x",$row->CP_ID)
				->setCellValue("L$x",$row->BANKBRANCHCODE)
				->setCellValue("M$x",$row->BANKNAME)
				->setCellValue("N$x","")
				->setCellValue("O$x",$row->BANKACCOUNTNUMBER)
				->setCellValue("P$x",$row->CREDITLIMIT)
				->setCellValue("Q$x",$row->INDUSTRY) 
				->setCellValue("R$x",$row->InsertType);
				
			$objPHPExcel->getActiveSheet()->getRowDimension($x)->setRowHeight(-1);	
			$objPHPExcel->getActiveSheet()->getStyle("A$x:R$x")->getAlignment()->setWrapText(true);
			$x++;
		}
		foreach(range('A','R') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
		}
		$module['filename'] = $module['filename'].'.csv';		
		if($serverDl == true) $this->_callDownloadServer($objPHPExcel, $module, 'CSV', $this->navLocation);
		else $this->_callDownload($objPHPExcel, $module, 'CSV');	
	}

	public function _cic_si_remittance($module, $result, $serverDl = true){
		if(empty($result)) return false;	
			
		$objPHPExcel = $this->_callPHPCLASS($module);
		$title = $module['filename'];		
		$x= 1;		

		
		$newArr = array();
		foreach($result as $row){
			$navDetail = $row->nav_detail;
			//CHECK IF NAV DETAILS IS NOT NULL
			
			//log_message('error', json_encode($row, JSON_PRETTY_PRINT));		
			if(count($navDetail) > 0){
				foreach($navDetail as $key => $detail){ //FACE VALUE

					//if($key == 1){

						
						if (strpos($detail->BILLABLE_ITEM, "Fee") !== false) {
							$y++;
							$vat = 0;
							
							
							
							if($detail->VAT_COND == "Exempt"){
								$row->AMOUNT = $row->TOTAL_AMOUNT;
							}
						
						}

						
					//}				
				}	
				$newArr[] = $row; 				
			}		
		}
		
		foreach($newArr as $row){
			
			//log_message('error', json_encode($row, JSON_PRETTY_PRINT));		
			
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()
				->setCellValue("A$x",$row->RECORD_TYPE)
				->setCellValue("B$x",$row->SOA)
				->setCellValue("C$x",$row->TIN)
				->setCellValue("D$x",'"'.$row->LegalName.'"')
				->setCellValue("E$x",$row->nav_detail[0]->SI_NUMBER)
				->setCellValue("F$x",$row->ORDER_ID) 
				->setCellValue("G$x",$row->ORDER_DATE) 
				->setCellValue("H$x",$row->DELIVERED_DATE)
				->setCellValue("I$x",$row->AMOUNT)
				->setCellValue("J$x",str_replace(',','',$row->DISCOUNT))
				->setCellValue("K$x",str_replace(',','',$row->TOTAL_AMOUNT))
				->setCellValue("L$x",$row->CUSTOMER_TYPE)
				->setCellValue("M$x",$row->SERVICE_ID)
				->setCellValue("S$x",$row->ACCOUNT_MANAGER)
				->setCellValue("T$x",$row->PO)
				->setCellValue("U$x",$row->CP_ID)
				->setCellValue("V$x",$row->DUE_DATE);				
			$objPHPExcel->getActiveSheet()->getRowDimension($x)->setRowHeight(-1);	
			$objPHPExcel->getActiveSheet()->getStyle("A$x:Q$x")->getAlignment()->setWrapText(true);
			
			$navDetail = $row->nav_detail;
			//CHECK IF NAV DETAILS IS NOT NULL
			if(!empty($navDetail)){
				$y = $x;
				foreach($navDetail as $key => $detail){ //FACE VALUE	
				
					if (strpos($detail->BILLABLE_ITEM, "Fee") !== false || strpos($detail->BILLABLE_ITEM, "(credit)") !== false) {
						$y++;
						$vat = 0;
						
								
						
						$unit_cost = $detail->CREDIT_VALUE - $detail->VAT_OUTPUT;
						
						//log_message('error', json_encode($unit_cost, JSON_PRETTY_PRINT));
						
						$detail->CREDIT_VALUE = $row->TOTAL_AMOUNT;
						
						if($detail->VAT_COND !== "Exempt"){
							$vat = str_replace(',','',$detail->VAT_OUTPUT);
							$detail->CREDIT_VALUE = $detail->CREDIT_VALUE;
						}
						
						$objPHPExcel->setActiveSheetIndex(0);
						$objPHPExcel->getActiveSheet()
							// ->setCellValue("A$y",$detail->RECORD_TYPE)
							// ->setCellValue("B$y",$detail->SERVICE_ID)
							// ->setCellValue("C$y",$detail->ISSUANCE_DATE)
							// ->setCellValue("D$y",$detail->BILLABLE_ITEM)
							// ->setCellValue("E$y",str_replace(',','',$detail->CREDIT_VALUE))
							// ->setCellValue("F$y",str_replace(',','',$detail->VAT_OUTPUT))
							// ->setCellValue("G$y",$detail->VAT_COND)
							// ->setCellValue("H$y",$detail->ACCOUNT_MANAGER);	
							->setCellValue("A$y",$detail->RECORD_TYPE)
							->setCellValue("C$y",$detail->SERVICE_ID)			
							->setCellValue("B$y", 0)
							->setCellValue("D$y",$detail->ISSUANCE_DATE)
							->setCellValue("E$y", $detail->ISSUANCE_DATE)
							->setCellValue("F$y",$detail->BILLABLE_ITEM)
							->setCellValue("G$y",str_replace(',','',$unit_cost))
							->setCellValue("H$y", $vat)
							->setCellValue("I$y",$detail->ISSUANCE_DATE)
							->setCellValue("J$y",$detail->ISSUANCE_DATE)
							->setCellValue("K$y",$detail->VAT_COND)
							->setCellValue("L$y",$detail->ACCOUNT_MANAGER)
							->setCellValue("M$y", "")
							->setCellValue("N$y", "")
							->setCellValue("O$y", "")
							->setCellValue("P$y", "")
							->setCellValue("Q$y", "")
							->setCellValue("R$y", "")
							->setCellValue("S$y", "")
							->setCellValue("T$y", "")
							->setCellValue("U$y", "")
							->setCellValue("V$y", "")
							->setCellValue("W$y", "");
						$objPHPExcel->getActiveSheet()->getRowDimension($y)->setRowHeight(-1);	
					//	$objPHPExcel->getActiveSheet()->getStyle("A$y:Q$y")->getAlignment()->setWrapText(false);	
					}				
				}				
				$x = $y;
				
			}			
			$x++;
		}

		// foreach(range('A','P') as $columnID) {
		// 	$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
		// 			->setAutoSize(true);
		// }
		$module['filename'] = $module['filename'].'.csv';		
		if($serverDl == true) $this->_callDownloadServer($objPHPExcel, $module, 'CSV', $this->navLocation);
		else $this->_callDownloadNew($objPHPExcel, $module, 'CSV');		
	}

	/**
	 * _cic_soa_remittance function
	 */	
	public function _cic_soa_remittance($module, $result, $serverDl = true){
		if(empty($result)) return false;	
		
		$objPHPExcel = $this->_callPHPCLASS($module);
		$title = $module['filename'];		
		$x= 1;		

		// log_message('error', json_encode($result, JSON_PRETTY_PRINT));		

		foreach($result as $row){
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()
				->setCellValue("A$x",$row->RECORD_TYPE)
				->setCellValue("B$x",$row->SOA)
				->setCellValue("C$x",$row->TIN)
				->setCellValue("D$x",'"'.$row->LegalName.'"')
				->setCellValue("E$x",$row->SOA_NUMBER)
				->setCellValue("F$x",$row->ORDER_ID) 
				->setCellValue("G$x",$row->ORDER_DATE) 
				->setCellValue("H$x",$row->DELIVERED_DATE)
				->setCellValue("I$x",$row->AMOUNT)
				->setCellValue("J$x",str_replace(',','',$row->DISCOUNT))
				->setCellValue("K$x",str_replace(',','',$row->TOTAL_AMOUNT))
				->setCellValue("L$x",$row->CUSTOMER_TYPE)
				->setCellValue("M$x",$row->SERVICE_ID)
				->setCellValue("N$x",$row->ACCOUNT_MANAGER)
				->setCellValue("O$x",'"'.$row->PO.'"')
				->setCellValue("P$x",$row->CP_ID)
				->setCellValue("Q$x",$row->DUE_DATE);				
			$objPHPExcel->getActiveSheet()->getRowDimension($x)->setRowHeight(-1);	
			$objPHPExcel->getActiveSheet()->getStyle("A$x:Q$x")->getAlignment()->setWrapText(true);
			
			$navDetail = $row->nav_detail;
			//CHECK IF NAV DETAILS IS NOT NULL
			if(!empty($navDetail)){
				$y = $x;
				foreach($navDetail as $key => $detail){ //FACE VALUE
					
					// log_message('error', $detail->BILLABLE_ITEM);

					if (!strpos($detail->BILLABLE_ITEM, "Fee") !== false) {
						
						$y++;
						$objPHPExcel->setActiveSheetIndex(0);
						$objPHPExcel->getActiveSheet()
							->setCellValue("A$y",$detail->RECORD_TYPE)
							->setCellValue("B$y",$detail->SERVICE_ID)
							->setCellValue("C$y",$detail->ISSUANCE_DATE)
							->setCellValue("D$y",$detail->BILLABLE_ITEM)
							->setCellValue("E$y",str_replace(',','',$detail->CREDIT_VALUE))
							->setCellValue("F$y",str_replace(',','',$detail->VAT_OUTPUT))
							->setCellValue("G$y",$detail->VAT_COND)
							->setCellValue("H$y",$detail->ACCOUNT_MANAGER);				
						$objPHPExcel->getActiveSheet()->getRowDimension($y)->setRowHeight(-1);	
						$objPHPExcel->getActiveSheet()->getStyle("A$y:H$y")->getAlignment()->setWrapText(true);	
					}				
				}				
				$x = $y;
			}			
			$x++;
		}
		foreach(range('A','R') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
		}
		$module['filename'] = $module['filename'].'.csv';		
		if($serverDl == true) $this->_callDownloadServer($objPHPExcel, $module, 'CSV', $this->navLocation);
		else $this->_callDownload($objPHPExcel, $module, 'CSV');		
	}

	/**
	 * rs_txtfile function
	 */	
	public function rs_txtfile($module, $result, $serverDl = true, $servLoc = ''){
		if(empty($result)) return false;	
		$objPHPExcel = $this->_callPHPCLASS($module);
		$x= 1; 	
		foreach($result as $row){
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()
				->setCellValue("A$x",$row->barcode);								
			//$objPHPExcel->getActiveSheet()->getRowDimension($x)->setRowHeight(-1);	
			//$objPHPExcel->getActiveSheet()->getStyle("A$x:A$x")->getAlignment()->setWrapText(true);
			$x++;
		}

		$module['filename'] = $module['filename'].'.txt';		
		if($serverDl == true && !empty($servLoc)) $this->_callDownloadServer($objPHPExcel, $module, 'CSV', $servLoc);
		else $this->_callDownload($objPHPExcel, $module, 'CSV');			
	}

	
	/**
	 * _cic_merchant function
	 * 
	 */	
	public function _cic_merchant($module, $result, $serverDl = true){
		if(empty($result)) return false;	
		
		$objPHPExcel = $this->_callPHPCLASS($module);
		$title = $module['filename'];		
		$x= 1;
		//foreach($result as $row){
		for($y=0;$y < count($result);$y++){
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()
				->setCellValue("A$x",$result[$y]->TIN)
				->setCellValue("B$x",'"'.$result[$y]->LEGALNAME.'"')
				->setCellValue("C$x",'"'.$result[$y]->GROUPNAME.'"')
				->setCellValue("D$x",'"'.$result[$y]->TRADINGNAME.'"')
				->setCellValue("E$x",'"'.$result[$y]->ADDRESS.'"')
				->setCellValue("F$x",$result[$y]->ContactPerson) 
				->setCellValue("G$x",$result[$y]->ContactNumber) 
				->setCellValue("H$x",$result[$y]->PAYMENTTERMSNAME) 
				->setCellValue("I$x",$result[$y]->MEANOFPAYMENT) 
				->setCellValue("J$x",$result[$y]->GROUPTIN)
				->setCellValue("K$x",$result[$y]->CP_ID)
				->setCellValue("L$x",$result[$y]->BANKBRANCHCODE)
				->setCellValue("M$x",$result[$y]->BANKNAME)
				->setCellValue("N$x",$result[$y]->PAYEECODE) 
				->setCellValue("O$x",$result[$y]->BANKACCOUNTNUMBER)
				->setCellValue("P$x",$result[$y]->CREDITLIMIT) 
				->setCellValue("Q$x",$result[$y]->INDUSTRY) 
				->setCellValue("R$x",$result[$y]->InsertType);
				
			$objPHPExcel->getActiveSheet()->getRowDimension($x)->setRowHeight(-1);	
			$objPHPExcel->getActiveSheet()->getStyle("A$x:R$x")->getAlignment()->setWrapText(true);
			$x++;
		} 
		foreach(range('A','R') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
		}
		$module['filename'] = $module['filename'].'.csv';		
		if($serverDl == true) $this->_callDownloadServer($objPHPExcel, $module, 'CSV', $this->navLocation);
		else $this->_callDownload($objPHPExcel, $module, 'CSV');	
	}

	/**
	 * _cic_soa_remittance function
	 */	
	public function _cic_mer_remittance($module, $result, $serverDl = true){
		if(empty($result)) return false;	
		/*
			Record Type
			CI
			TIN
			Legal Name
			Remittance ID
			RS Number
			Creation Date
			Credited date
			Amount
			Discount
			Total Amount
			Customer Type
			Service ID
			Account Manager
			PA Number
			Zeta Internal ID
			Due Date
			*/
		$objPHPExcel = $this->_callPHPCLASS($module);
		$title = $module['filename'];		
		$x= 1;		
		foreach($result as $row){
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()
				->setCellValue("A$x",$row->RECORD_TYPE)
				->setCellValue("B$x",$row->CI)
				->setCellValue("C$x",$row->TIN)
				->setCellValue("D$x",'"'.$row->LegalName.'"')
				->setCellValue("E$x",$row->REMITTANCE_ID)
				->setCellValue("F$x",$row->RS_NUMBER) 
				->setCellValue("G$x",$row->CREATION_DATE) 
				->setCellValue("H$x",$row->CREDITED_DATE)
				->setCellValue("I$x",$row->AMOUNT)
				->setCellValue("J$x",str_replace(',','',$row->DISCOUNT))
				->setCellValue("K$x",str_replace(',','',$row->TOTAL_AMOUNT))
				->setCellValue("L$x",$row->CUSTOMER_TYPE)
				->setCellValue("M$x",$row->SERVICE_ID)
				->setCellValue("N$x",$row->ACCOUNT_MANAGER)
				->setCellValue("O$x",$row->PA_NUMBER)
				->setCellValue("P$x",$row->CP_ID)
				->setCellValue("Q$x",$row->DUE_DATE);				
			$objPHPExcel->getActiveSheet()->getRowDimension($x)->setRowHeight(-1);	
			$objPHPExcel->getActiveSheet()->getStyle("A$x:Q$x")->getAlignment()->setWrapText(true);
			
			$navDetail = $row->nav_detail;
			//CHECK IF NAV DETAILS IS NOT NULL
			if(!empty($navDetail)){
				$y = $x;
				foreach($navDetail as $detail){ //FACE VALUE
					$y++;
					$objPHPExcel->setActiveSheetIndex(0);
					$objPHPExcel->getActiveSheet()
						->setCellValue("A$y",$detail->RECORD_TYPE)
						->setCellValue("B$y",$detail->SERVICE_ID)
						->setCellValue("C$y",'')//$detail->ISSUANCE_DATE
						->setCellValue("D$y",'')//$detail->BILLABLE_ITEM
						->setCellValue("E$y",str_replace(',','',$detail->CREDIT_VALUE))
						->setCellValue("F$y",str_replace(',','',$detail->VAT_OUTPUT))
						->setCellValue("G$y",$detail->VAT_COND)
						->setCellValue("H$y",$detail->ACCOUNT_MANAGER);				
					$objPHPExcel->getActiveSheet()->getRowDimension($y)->setRowHeight(-1);	
					$objPHPExcel->getActiveSheet()->getStyle("A$y:H$y")->getAlignment()->setWrapText(true);					
				}				
				$x = $y;
			}			
			$x++; 
		}
		foreach(range('A','R') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
		}
		$module['filename'] = $module['filename'].'.csv';		
		if($serverDl == true) $this->_callDownloadServer($objPHPExcel, $module, 'CSV', $this->navLocation);
		else $this->_callDownload($objPHPExcel, $module, 'CSV');		
	}

	
	/**
	 * _dormancy_fee function
	 */	
	 public function _dormancy_fee($module, $result, $serverDl = true){
		if(empty($result)) return false;	
		
		$objPHPExcel = $this->_callPHPCLASS($module);
		$title = $module['filename'];		
		$x= 1;		
		foreach($result as $row){
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()
				->setCellValue("A$x",$row->RECORD_TYPE)
				->setCellValue("B$x",$row->paymentAdvice)
				->setCellValue("C$x",$row->TIN)
				->setCellValue("D$x",'"'.$row->LegalName.'"') //'"'.$row->LegalName.'"'
				->setCellValue("E$x",$row->RECON_ID)
				->setCellValue("F$x",$row->RECONDATE) 
				->setCellValue("G$x",$row->paymentAdvice) 
				->setCellValue("H$x",$row->paGenDate)
				->setCellValue("I$x",$row->ExpectedDueDate)
				->setCellValue("J$x",str_replace(',','',$row->TOTAL_FV))
				->setCellValue("K$x",$row->PROD_ID)
				->setCellValue("L$x",$row->PayeeCode)
				->setCellValue("M$x",'"'.$row->PayeeName.'"')//$row->PayeeName
				->setCellValue("N$x",$row->BankAccountNumber)
				->setCellValue("O$x",$row->CP_ID);				
			$objPHPExcel->getActiveSheet()->getRowDimension($x)->setRowHeight(-1);	
			$objPHPExcel->getActiveSheet()->getStyle("A$x:O$x")->getAlignment()->setWrapText(true);
			
			$navDetail = $row->nav_detail;
			//CHECK IF NAV DETAILS IS NOT NULL
			if(!empty($navDetail)){
				$y = $x;
				foreach($navDetail as $detail){ //FACE VALUE
					$y++;
					$objPHPExcel->setActiveSheetIndex(0);
					$objPHPExcel->getActiveSheet()
						->setCellValue("A$y",$detail->RECORD_TYPE)
						->setCellValue("B$y",$detail->PROD_ID)
						->setCellValue("C$y",'') //Face value (Credits)
						->setCellValue("D$y",str_replace(',','',$detail->FV));
						/*->setCellValue("E$y",$detail->VAT_OUTPUT)
						->setCellValue("F$y",$detail->VAT_COND);*/				
					$objPHPExcel->getActiveSheet()->getRowDimension($y)->setRowHeight(-1);	
					$objPHPExcel->getActiveSheet()->getStyle("A$y:D$y")->getAlignment()->setWrapText(true);					
				}				
				$y += 1;	
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()
					->setCellValue("A$y",'D')
					->setCellValue("B$y",$detail->PROD_ID)
					->setCellValue("C$y",'Marketing fee')
					->setCellValue("D$y",str_replace(',','',$row->MERCHANT_FEE))
					->setCellValue("E$y",str_replace(',','',$row->VAT_OUTPUT))
					->setCellValue("F$y",$row->VAT_COND);				
				$objPHPExcel->getActiveSheet()->getRowDimension($y)->setRowHeight(-1);	
				$objPHPExcel->getActiveSheet()->getStyle("A$y:F$y")->getAlignment()->setWrapText(true);
				
				/*$y += 1;	
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()
					->setCellValue("A$y",'D')
					->setCellValue("B$y",'')
					->setCellValue("C$y",'Adjustment fee')
					->setCellValue("D$y", 0);				
				$objPHPExcel->getActiveSheet()->getRowDimension($y)->setRowHeight(-1);	
				$objPHPExcel->getActiveSheet()->getStyle("A$y:D$y")->getAlignment()->setWrapText(true);*/
				$x = $y;
			}			
			$x++;
		}
		foreach(range('A','R') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
		}
		$module['filename'] = $module['filename'].'.csv';		
		if($serverDl == true) $this->_callDownloadServer($objPHPExcel, $module, 'CSV', $this->navLocation);
		else $this->_callDownload($objPHPExcel, $module, 'CSV');		
	}
	
	
} // END CLASS


