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
		$this->generate(2,'',false,false,false,false);
	}
	
	public function group_gen(){			
		$whereU['user_id'] = $uid = $this->auth->get_userid();
		$whereU['generated'] = 0;
		$generatedPA =  $this->Sys_model->v_paH($whereU, false, 'pa_id');
		
		if($generatedPA->num_rows() != 0){
			$PA_ARR = '';
			foreach($generatedPA->result() as $row){
				$PA_ARR[] = $PA_ID= $row->pa_id;
				$this->generate($row->pa_id, '', false, true, true);	
			}		

			if(!empty($PA_ARR)){
				$whereUpdate = 'generated = 0 AND user_id = '.$uid.' and pa_id in ('.implode(',',$PA_ARR).')';
				$this->Sys_model->u_paH($whereUpdate, array('generated'=>1));	
				$this->Action_model->audit_save(7, array('PA_ID'=>implode(',',$PA_ARR)));//AUDIT TRAIL HERE						
			}
		}		
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
	
	public function generate($pa_id, $filename = 'PA_', $copy = false, $download = false, $serverDL = false, $zip = false){
		if(empty($pa_id)) return false;
		
		$where = 'paH.PA_ID in ('.$pa_id.')'; 		
		$result_merchantPA =  $this->Sys_model->merchantPA($where, false);
		$result_branchesPA =  $this->Sys_model->branchesPA($where, false);
		$result_servicesPA =  $this->Sys_model->servicesPA($where, false);

		/*
		* check_numrows
		*/
		$branchesPAROW = $result_branchesPA->num_rows();
		$merchantPAROW = $result_merchantPA->num_rows();

		if($branchesPAROW != 0 && $merchantPAROW != 0){	
			$data['merchantInfo'] =  $result_merchantPA->result();	
			$data['branchLi'] = $this->arr_result($result_branchesPA);
			$data['serviceLi'] = $this->arr_result($result_servicesPA);
				$whereU['user.user_id'] = $this->auth->get_userid();
			$data['data_user'] = $row_info = $this->User_model->user_info($whereU)->row();
			$data['copy'] = $copy;
			$data['date_printed'] = $this->my_lib->setDate(); 
			$html = $this->load->view('index', $data, true); 
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
