<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf; 
use Dompdf\Options; 
use Dompdf\FontMetrics; 

class Gen_pdf extends MX_Controller {
	private $zipName;
	private $zipLocation;
	
	public function __construct(){        
        // include autoloader
			
        require_once BASEPATH.'/dompdf/autoload.inc.php';	
		$this->load->model('Sys_model');
		$this->load->model('User_model');
		$this->load->model('Action_model');
		$this->load->helper('download');
		$this->load->library('zip');
		$this->zipName = 'zipFIle_'.date('mdY',now()).'.zip';
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
		
	public function generate($pa_id, $filename = 'PA_', $copy = false, $download = false, $serverDL = false, $zip = false){
		if(empty($pa_id)) return false;
		
		$where = 'paH.PA_ID in ('.$pa_id.')'; 	
		//$whereView = 'PA_ID in ('.$pa_id.')'; 	
		$selectMerchant = 'mer.*, br.AFFILIATEGROUPCODE brAffCode, br.BRANCH_NAME, paH.*, u.*';		
		$result_merchantPA =  $this->Sys_model->merchantPA($where, false, $selectMerchant);
		$branchesPAROW =  $this->Sys_model->branchesPA($where, true); 
		$result_servicesPA =  $this->Sys_model->servicesPA($where, false);
		
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
				$result_branchesPA =  $this->Sys_model->branchesPA($where, false, $pagination);
				$branchPG[$x] = $this->arr_result($result_branchesPA);
			}
			$data['totalNewPage'] = $totalNewPage;
			$data['branchLi'] = $branchPG;	
			$data['branchNum'] = $branchesPAROW ;		
			
			$data['serviceLi'] = $this->arr_result($result_servicesPA);
				$whereU['user.user_id'] = $this->auth->get_userid();
			$data['data_user'] = $row_info = $this->User_model->user_info($whereU)->row();
			$data['copy'] = $copy;
			$data['date_printed'] = $this->my_lib->setDate(); 
			$html = $this->load->view('gen_pdf', $data, true); 
			$filename = $this->my_lib->paNumber($pa_id).'_'.trim($result_merchantPA->row('LegalName')).'_'.date("Ymd");
			return $this->loadPDF($html, $filename, $copy, $download, $serverDL, $zip);	
			//$this->load->view('index', $data);
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
