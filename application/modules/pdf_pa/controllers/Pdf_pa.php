<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf; 
use Dompdf\Options; 
use Dompdf\FontMetrics; 

class Pdf_pa extends MX_Controller {
	private $zipName;
	private $zipLocation;
	
	public function __construct(){        
        // include autoloader
		if(!$this->auth->check_session()) redirect('login');	
		
        require_once BASEPATH.'/dompdf/autoload.inc.php';	
		$this->load->model('Sys_model');
		$this->load->model('User_model');
		$this->load->model('Action_model');
		$this->load->helper('download');
		$this->load->library('zip');
		$this->zipName = 'zipFIle_'.$this->auth->get_userid().'.zip';
		$this->zipLocation = 'C:/xampp/htdocs/generated_payment_advice/';
    }
	
	public function index(){
		if(isset($_GET['pa']) && !empty($_GET['pa'])){
			$this->generate($_GET['pa'],'',false,false,false,false); //276
		}
	}
	
	public function load_orig(){
		if(isset($_GET['pa']) && !empty($_GET['pa'])){
			$this->generate($_GET['pa'], '', false, true, true);
		}
	}
	
	public function regen_pa(){
		$group_pa = array(4282,4281,4285,4291,4303,4302,4301,4300,4299,4298,4297,4296,4295,4308
		,4307,4306,4315,4314,4313,4312,4311,4310,4317,4322,4321,4320,4319,4334,4333,4332,4331,4330
		,4329,4328,4327,4326,4325,4324,4337,4336,4362,4361,4360,4359,4358,4357,4356,4355,4354,4353
		,4352,4351,4350,4349,4348,4347,4346,4345,4344,4343,4342,4374,4373,4372,4371,4370,4369,4368
		,4367,4365,4364,4377,4376,4382,4381,4380,4379,4386,4384,4389,4388);	
		for($x = 0; $x <= count($group_pa); $x++){	
			$this->generate($group_pa[$x], '', false, true, true);
			echo $group_pa[$x].'<br />';
		}
		echo 'Done regeneration PA';
	}
	
	public function export(){
		log_message('error', 'Exporting to Excel......');
		
		// Include XLSX generator library 
		require_once 'PhpXlsxGenerator.php'; 
		// Include and initialize ZipArchive class
		require_once 'ZipArchiver.php';

		$paDate = $this->Sys_model->getLastPaDate();
		$PA_IDs = $this->Sys_model->getPaIdFromLastPaDate($paDate[0]->date);

		foreach($PA_IDs as $key => $val) {
			
			$transactions = $this->Sys_model->getExportData($val->PA_ID);

			$excelData = array();

			$excelData[] = array(
				'DIGITAL ID', 
				'MERCHANT ID', 
				'MERCHANT NAME', 
				'TIN NUMBER', 
				'BRANCH ID', 
				'BRANCH NAME', 
				'VOUCHER CODE', 
				'PRODUCT',
				'POS ID',
				'AMOUNT',
				'REDEEM ID',
				'POS TXN ID',
				'REDEEM STATUS',
				'REDEEM DATE',
				'PAYMENT ADVICE ID',
				'PA DATE',
				'PA DUE DATE',
			); 

			foreach($transactions as $val){
				$lineData = array(
					$val->DIGITALID, 
					$val->MERCHANTID, 
					$val->MERCHANTNAME, 
					$val->TINNUMBER, 
					$val->BRANCHID,
					$val->BRANCHNAME,
					$val->VOUCHERCODE,
					$val->PRODUCT,
					$val->POSID,
					$val->AMOUNT,
					$val->REDEEMID,
					$val->POSTXNID,
					$val->REDEEMSTATUS,
					$val->REDEEMDATE,
					$val->PAYMENTADVICEID,
					$val->PADATE,
					$val->PADUEDATE,
				);
				$excelData[] = $lineData; 
			}
			
			$fileName = 'Z' . str_pad($val->PAYMENTADVICEID, 6, '0', STR_PAD_LEFT) . '_' . $val->MERCHANTNAME . '_' . date('Ymd') .".xlsx"; 
			log_message('error', $fileName);
			// Export data to excel and download as xlsx file 
			$xlsx = CodexWorld\PhpXlsxGenerator::fromArray( $excelData ); 
			$xlsx->saveAs($fileName); 

			// rename("C:\laragon\www\mp_dis/".$fileName, "C:/xampp/htdocs/generated_payment_advice/". date('Ymd') . '/' . $fileName); // local
			rename("C:/xampp/htdocs/mp_dis/".$fileName, "C:/xampp/htdocs/generated_payment_advice/". date('Ymd') . '/' . $fileName); // uat
		
		}

		return true;
	}
	
	public function getReimbursementUserIds(){
		$where['utype_id'] = 2; 

		$this->db->from('user');	
		$this->db->where($where);	
		$this->db->select('user_id');
		$result = $this->db->get();	

		$json = json_encode($result->result(), JSON_PRETTY_PRINT);

		// Decode JSON to PHP array
		$array = json_decode($json, true);

		// Extract user_id values and join them into a comma-separated string
		$user_ids = array_column($array, 'user_id');
		$comma_separated = implode(',', $user_ids);

		return $comma_separated;
	}
	
	public function group_gen(){	
		log_message('error', 'Starting export PDF......');
	
		//$whereU['user_id'] = $uid = $this->auth->get_userid();
		$uid = $this->auth->get_userid();
		$whereU['generated'] = 0;
		$whereU['MERCHANT_FEE !='] = '';
		$userIds = $this->getReimbursementUserIds();	
		$generatedPA = $this->Sys_model->v_paH_new($whereU, false, 'PA_ID', $userIds);
		//$generatedPA =  $this->Sys_model->v_paH($whereU, false, 'pa_id');
		
		if($generatedPA->num_rows() != 0){
			$PA_ARR = [];
			foreach($generatedPA->result() as $row){
				$PA_ARR[] = $PA_ID= $row->PA_ID;
				$this->generate($row->PA_ID, '', false, true, true);	
			}		

			if(!empty($PA_ARR)){
				//$whereUpdate = 'generated = 0 AND user_id = '.$uid.' AND MERCHANT_FEE <> "" and pa_id in ('.implode(',',array_filter($PA_ARR)).')';
				$whereUpdate = 'GENERATED = 0 AND USER_ID in ('.$userIds.') AND MERCHANT_FEE <> "" and PA_ID in ('.implode(',',array_filter($PA_ARR)).')';
				$this->Sys_model->u_paH($whereUpdate, array('generated'=>1));	
				//$this->Action_model->audit_save(7, array('PA_ID'=>implode(',',array_filter($PA_ARR))));//AUDIT TRAIL HERE						
			}
		}	
		
		log_message('error', 'Calling Export Excel function......');
		$this->export();			
		log_message('error', 'Done generation......');
		redirect('process');
	}		

	public function print_copy(){
		if(!isset($_POST['process'])) redirect('summary');		
		/*
		* GET ALL CHECK ITEMS
		*/
		$toProcess = $_POST['process'];
		$countProcess = count($toProcess);
		if($countProcess <> 0){
			if($countProcess == 1){
				$newPAID = $this->my_lib->paNumber($toProcess[0], true);		
				$this->generate($newPAID, '', true, true, false, false);
			}else{
				$return = array(); 		
				for($x = 0; $x <= $countProcess; $x++){		
					$newPAID = $this->my_lib->paNumber($toProcess[$x], true);
					$returnGen = $this->generate($newPAID, '', true, false, false, true);
					$return[$returnGen['filename']] = $returnGen['filedata'];	
				}
				/**
				 * GENERATE ZIP FILE FOR MULTIPLE DOWNLOAD
				 */		
				$this->zip->add_data($return);
				$this->zip->download($this->zipName); 					
				$this->Action_model->audit_save(7, array('PA_ID'=>$PA_ID));//AUDIT TRAIL HERE	
			}
		}
		redirect('summary');
	}
	
	public function check_pa(){
		$pa_id = $this->my_lib->paNumber('Z000030', true);
		$where = 'paH.PA_ID in ('.$pa_id.')';
		$result_branchesPA =  $this->Sys_model->branchesPA2($where, false);
		echo '<pre>'; print_r($result_branchesPA->result()); echo '</pre>';
	}
	
	public function generate($pa_id, $filename = 'PA_', $copy = false, $download = false, $serverDL = false, $zip = false){
		if(empty($pa_id)) return false;
		
		$where = 'paH.PA_ID in ('.$pa_id.')'; 	
		$selectMerchant = 'mer.*, br.AFFILIATEGROUPCODE brAffCode, br.BRANCH_NAME, paH.*, u.*';		
		$result_merchantPA =  $this->Sys_model->merchantPA($where, false, $selectMerchant);
			$whereBranchRow = $where;//.' AND paD.TOTAL_REFUND = 0';		
		$branchesPAROW =  $this->Sys_model->branchesPA($whereBranchRow, true);

		$row_merchantPA = $result_merchantPA->row();
		
		//check if PA is processed with recon
		$wherePAD = 'paH.PA_ID in ('.$pa_id.')'; 	
		$check_pad_wrecon =  $this->Sys_model->pad_wrecon($wherePAD);
		$norecon = ($check_pad_wrecon->num_rows() == 0 ? "Nrecon" : "");


		$result_servicesPA =  ($norecon <> "" ? $this->Sys_model->servicesPANrecon($where, false) : $this->Sys_model->servicesPA($where, false));		
		/*
		* check_numrows
		*/
		$merchantPAROW = $result_merchantPA->num_rows();

		if($branchesPAROW != 0 && $merchantPAROW != 0){	
			$data['merchantInfo'] =  $result_merchantPA->result();
			
			$totalPage = $branchesPAROW;
			$perpage = 15;		
			$totalNewPage = 1;
			if($branchesPAROW > $perpage){
				$totalNewPage = round($branchesPAROW/$perpage) + 1;		
			} 
			$branchPG = array();		
			for ($x = 1; $x <= $totalNewPage; $x++) {
				$currentPage = $x;
				
				$startPage = ($currentPage-1)*$perpage;
				if($startPage < 0) $startPage = 0;			
				$pagination =  " limit " . $startPage . "," . $perpage; 							
				$result_branchesPA =  $this->Sys_model->branchesPA($whereBranchRow, false, $pagination);
				$branchPG[$x] = $this->arr_result($result_branchesPA);
				
				//echo '<pre>'; print_r($branchPG[$x]); echo '</pre>';
			}
			//die();
			$data['totalNewPage'] = $totalNewPage;
			$data['branchLi'] = $branchPG;	
			$data['branchNum'] = $branchesPAROW ;		
			
			$data['serviceLi'] = $this->arr_result($result_servicesPA);

			//REVERSAL PART 
			$result_refundPA = ($norecon <> "" ? $this->Sys_model->refundPANrecon($where, false) : $this->Sys_model->refundPA($where, false)) ;
			$data['refundRow'] = $result_refundPA->num_rows();
			$data['refundLi'] = $this->arr_result($result_refundPA);
			if($data['refundRow'] <> 0){				
					$result_servicesREF =  ($norecon <> "" ? $this->Sys_model->servicesREFNrecon($where, false) : $this->Sys_model->servicesREF($where, false));
				$data['serviceREF'] = $this->arr_result($result_servicesREF);		
			}
				$whereU['user.user_id'] = $this->auth->get_userid();
			$data['data_user'] = $row_info = $this->User_model->user_info($whereU)->row();
			$data['copy'] = $copy;
			$data['date_printed'] = $this->my_lib->setDate(); 
			$html = $this->load->view('index2', $data, true); //index
			$filename = $this->my_lib->paNumber($pa_id).'_'.trim($result_merchantPA->row('LegalName')).'_'.date("Ymd");
			return $this->loadPDF($html, $filename, $copy, $download, $serverDL, $zip);	
			//echo $html; die();
		}
	}	
	
	private function arr_result($temp_transac, $export = false){
		$arr = array();			
		$fields = $temp_transac->list_fields(); 
		foreach($temp_transac->result() as $temp_row){ 
			$newRow = new stdClass(); 
			foreach ($fields as $field){
				$newRow->$field =  $temp_row->$field;
			}					
			$newRow->get_userid = $this->auth->get_userid(); 									
			$arr[] = $newRow;
		}
		return $arr;
	}
	
	private function loadPDF($html, $filename = 'PA_', $copy = false, $download = false, $serverDL = false, $zip = false){
		if(empty($html)) return false;

		// instantiate and use the dompdf class
		$options = new Options(); 
		$options->set('isPhpEnabled', 'true'); 

        $dompdf = new Dompdf($options); 		
        // Load HTML content
        $dompdf->loadHtml($html);    
		// (Optional) Setup the paper size and orientation 
		$dompdf->setPaper('A4', 'portrait'); 		 
		// Render the HTML as PDF 
		$dompdf->render(); 
		 
		/*if($copy == true){
			// Instantiate canvas instance 
			$canvas = $dompdf->getCanvas(); 	
			// Instantiate font metrics class 
			$fontMetrics = new FontMetrics($canvas, $options); 			 
			// Get height and width of page 
			$w = $canvas->get_width(); //595.28
			$h = $canvas->get_height(); //841.89			 
			// Get font family file 
			$font = $fontMetrics->getFont('times'); 			 
			// Specify watermark text 
			$text = "COPY"; 			 
			// Get height and width of text 
			$txtHeight = $fontMetrics->getFontHeight($font, 100); 
			$textWidth = $fontMetrics->getTextWidth($text, $font, 100); 

			// Set text opacity 
			$canvas->set_opacity(.2); 			 
			// Specify horizontal and vertical position 
			$x = (($w-$textWidth)/2); 
			$y = (($h-$txtHeight)/2); 			 
			// Writes text at the specified x and y coordinates 
			$canvas->text($x, $y, $text, $font, 100); 
		}*/
        
		// Output the generated PDF (1 = download and 0 = preview)
		if($serverDL == true){
			$filename = $this->my_lib->makeDIR($this->zipLocation, date("Ymd")).$filename;
			return file_put_contents($filename.".pdf", $dompdf->output()); 
		}else if($zip == true){
			$output = $dompdf->output();
			unset($dompdf);		
			return array('filename'=>$filename.".pdf", 'filedata'=>$output);
		}else{	
			return $dompdf->stream($filename.".pdf", array("Attachment" => ($download == true ? 1 : 0) )); 
		}
	}
}
