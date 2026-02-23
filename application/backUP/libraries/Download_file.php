<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Download_file extends MX_Controller{
	public $navLocation;
	public function __construct(){
		parent::__construct();
		$this->load->library('classes/PHPExcel.php');
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
		header('Content-Type: application/force-download');
		header('Cache-Control: max-age=0'); //no cache		
		header('Content-Disposition: attachment;filename="'.$filename.'"'); 

		if($type == 'CSV'){
			$objWriter = new PHPExcel_Writer_CSV($arrOBJ);
			$objWriter->setUseBOM(true);
			$objWriter->setEnclosure('');
		}else $objWriter = PHPExcel_IOFactory::createWriter($arrOBJ, $type);

		return $objWriter->save('php://output'); 
	}
	private function _callDownloadServer($arrOBJ, $module, $type = 'Excel5', $serverDL){
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
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()
				->setCellValue("A$x",$row->RECORD_TYPE)
				->setCellValue("B$x",$row->paymentAdvice)
				->setCellValue("C$x",$row->TIN)
				->setCellValue("D$x",'"'.$row->LegalName.'"')
				->setCellValue("E$x",$row->RECON_ID)
				->setCellValue("F$x",$row->RECONDATE) 
				->setCellValue("G$x",$row->paymentAdvice) 
				->setCellValue("H$x",$row->paGenDate)
				->setCellValue("I$x",$row->ExpectedDueDate)
				->setCellValue("J$x",$row->TOTAL_FV)
				->setCellValue("K$x",$row->PROD_ID)
				->setCellValue("L$x",$row->PayeeCode)
				->setCellValue("M$x",'"'.$row->PayeeName.'"')
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
						->setCellValue("C$y",'Face value (Credits)')
						->setCellValue("D$y",$detail->FV);
						/*->setCellValue("E$y",$detail->VAT_OUTPUT)
						->setCellValue("F$y",$detail->VAT_COND);*/				
					$objPHPExcel->getActiveSheet()->getRowDimension($y)->setRowHeight(-1);	
					$objPHPExcel->getActiveSheet()->getStyle("A$y:D$y")->getAlignment()->setWrapText(true);					
				}				
				$y += 1;	
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()
					->setCellValue("A$y",'D')
					->setCellValue("B$y",'')
					->setCellValue("C$y",'Marketing fee')
					->setCellValue("D$y",$row->MERCHANT_FEE)
					->setCellValue("E$y",$row->VAT_OUTPUT)
					->setCellValue("F$y",$row->VAT_COND);				
				$objPHPExcel->getActiveSheet()->getRowDimension($y)->setRowHeight(-1);	
				$objPHPExcel->getActiveSheet()->getStyle("A$y:F$y")->getAlignment()->setWrapText(true);
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


	/// testing code
	
} // END CLASS


