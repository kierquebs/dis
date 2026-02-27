<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf; 
use Dompdf\Options; 
use Dompdf\FontMetrics; 

class Pdf_rs extends MX_Controller {
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
		$this->zipLocation = 'C:/xampp/htdocs/generated_rs_doc/';
    }
	
	/**
	 * index function
	 *
	 * @return void
	 */
	public function index(){
		$this->generate(1,'',false,false,false,false);
	}
	
	/**
	 * group_gen function
	 *
	 * @return void
	 */
	public function group_gen(){			
		$whereU['user_id'] = $uid = $this->auth->get_userid();
		$whereU['generated'] = 0;
		$generatedRS =  $this->Sys_model->v_rsH($whereU, false, 'rs_id');
		
		if($generatedRS->num_rows() != 0){
			$RS_ARR = '';
			foreach($generatedRS->result() as $row){
				$RS_ARR[] = $RS_ID= $row->rs_id;
				$this->generate($row->rs_id, '', false, true, true);	
			}		

			if(!empty($RS_ARR)){
				$whereUpdate = 'generated = 0 AND user_id = '.$uid.' and rs_id in ('.implode(',',$RS_ARR).')';
				$this->Sys_model->u_rsH($whereUpdate, array('generated'=>1));	
				$this->Action_model->audit_save(7, array('RS_ID'=>implode(',',$RS_ARR)));//AUDIT TRAIL HERE						
			}
		}		
		redirect('process/rs');
	}		

	/**
	 * print_copy function
	 *
	 * @return void
	 */
	public function print_copy(){
		if(!isset($_POST['process'])) redirect('summary/rs');		
		/*
		* GET ALL CHECK ITEMS
		*/
		$toProcess = $_POST['process'];
		$countProcess = count($toProcess);
		if($countProcess <> 0){
			if($countProcess == 1){
				$newRSID = $toProcess[0];		
				$this->generate($newRSID, '', true, true, false, false);
			}else{
				$return = array(); 		
				for($x = 0; $x <= $countProcess; $x++){		
					$newRSID = $toProcess[$x];
					$returnGen = $this->generate($newRSID, '', true, false, false, true);
					$return[$returnGen['filename']] = $returnGen['filedata'];	
				}
				/**
				 * GENERATE ZIP FILE FOR MULTIPLE DOWNLOAD
				 */		
				$this->zip->add_data($return);
				$this->zip->download($this->zipName); 		 			
				$this->Action_model->audit_save(7, array('RS_ID'=>$PA_ID));//AUDIT TRAIL HERE	
			}
		}
		//redirect('summary/rs');
	}
	
	/**
	 * generate function
	 *
	 * @param [type] $rs_id
	 * @param string $filename = rs number + "_" +  legal name
	 * @param boolean $copy
	 * @param boolean $download
	 * @param boolean $serverDL
	 * @param boolean $zip
	 * @return void
	 */
	public function generate($rs_id, $filename = '', $copy = false, $download = false, $serverDL = false, $zip = false){
		if(empty($rs_id)) return false;
		
		$where = 'rsH.RS_ID in ('.$rs_id.')'; 	
		$result_branchInfoRS =  $this->Sys_model->branchInfoRS($where, false);
		$result_branchRSList =  $this->Sys_model->branchRSList($where, false);

		/*
		* check_numrows
		*/
		$branchInfoRSROW = $result_branchInfoRS->num_rows();
		$branchRSListROW = $result_branchRSList->num_rows();

		if($branchInfoRSROW != 0 && $branchRSListROW != 0){	
			$data['branchInfoRS'] =  $result_branchInfoRS->result();	
			$data['rsLi'] = $this->arr_result($result_branchRSList);	
				$whereU['user.user_id'] = $this->auth->get_userid();
			$data['data_user'] = $row_info = $this->User_model->user_info($whereU)->row();
			$data['copy'] = $copy;
			$data['date_printed'] = $this->my_lib->setDate(); 		
					
			//$this->load->view('index', $data); //TESTING 
			$html = $this->load->view('index', $data, true); 
			$filename = $result_branchInfoRS->row('RS_NUMBER').'_'.trim($result_branchInfoRS->row('LegalName'));
			
			$result_voucherRSList =  $this->Sys_model->voucherRSList($where, false);
			$voucherLi = $this->arr_result($result_voucherRSList);	
			
			//if($serverDl == true){
				$this->load->library('download_file');
				$this->download_file->rs_txtfile(array('filename'=>$filename), $voucherLi, true, $this->my_lib->makeDIR($this->zipLocation, date("Ymd")));
			//}

			return $this->loadPDF($html, $filename, $copy, $download, $serverDL, $zip); // GENERATE PDF DOC
		}
	}	
	
	/**
	 * arr_result function
	 *
	 * @param [type] $temp_transac
	 * @param boolean $export
	 * @return void
	 */
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
	
	/**
	 * loadPDF function
	 *
	 * @param [type] $html
	 * @param string $filename
	 * @param boolean $copy
	 * @param boolean $download
	 * @param boolean $serverDL
	 * @param boolean $zip
	 * @return void
	 */
	private function loadPDF($html, $filename = '', $copy = false, $download = false, $serverDL = false, $zip = false){
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
