<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf; 
use Dompdf\Options; 
use Dompdf\FontMetrics; 

class Pdf_pa extends MX_Controller {
	
	public function __construct(){        
        // include autoloader
        require_once BASEPATH.'/dompdf/autoload.inc.php';	
		$this->load->model('Sys_model');
    }
	
	public function index(){
		echo 'hello'; DIE();
		$this->generate(1);
	}
	
	public function group_gen($PA_ARR){
		if(!is_array($PA_ARR)) return false;
		
		for($x = 0; $x < count($PA_ARR); $x++){
			$pa_id = $PA_ARR[$x];
			$filename = 'Payment_Advice_'.$pa_id;
			$this->generate($pa_id, $filename, false, true);
		}
		
	}
	
	public function generate($pa_id, $filename = 'Payment_Advice_', $copy = false, $download = false){
		if(empty($pa_id)) return false;
		
		
		$where = 'paH.PA_ID = '.$pa_id; 
		$merchantInfo =  $this->Sys_model->merchantPA($where, false);
		$temp_transac =  $this->Sys_model->getTransaction($where, false);

		if($temp_transac->num_rows() != 0 && $merchantInfo->num_rows() != 0){	
			$data['merchantInfo'] =  $merchantInfo->result();	
			$data['branchLi'] = $this->arr_result($temp_transac);
			
			$this->load->view('index', $data);		
			$html = $this->output->get_output(); // Get output html
			$this->loadPDF($html, $filename, $copy, $download);	
		}else echo 'INVALID TRANSACTION';
		
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
	
	private function loadPDF($html, $filename = 'Payment_Advice_', $copy = false, $download = false){
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
		 
		if($copy == true){
			// Instantiate canvas instance 
			$canvas = $dompdf->getCanvas(); 			 
			// Instantiate font metrics class 
			$fontMetrics = new FontMetrics($canvas, $options); 			 
			// Get height and width of page 
			$w = $canvas->get_width(); 
			$h = $canvas->get_height(); 			 
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
		}
        
		// Output the generated PDF (1 = download and 0 = preview)
		//$dompdf->stream("payment_advice.pdf", array('Attachment'=>1));		
		return $dompdf->stream($filename.".pdf", array("Attachment" => ($download == true ? 1 : 0) ));
	}
}
