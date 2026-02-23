<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Summary extends MX_Controller {
	private $MODULE_ID;
	public function	__construct(){
	parent::__construct();
		$this->MODULE_ID = 3;
		if(!$this->auth->check_session()) redirect('login');		
		$this->load->model('Sys_model');
		$this->form_validation->run($this);
		if($this->auth->role_all($this->MODULE_ID) == false) redirect('404_override');
	}
	
	public function index(){ 
		$data['css'][] = 'queue.css';
		$data['js'][] = 'main.js';
		$data['js'][] = 'home.js';
		$data['js'][] = 'summary/main.js';			
				
		$data['cssDT'][] = '1.10.16/css/jquery.dataTables.min.css';
		$data['jsDT'][] = '1.10.16/js/jquery.dataTables.min.js';	
		$this->my_layout->layout_nav('summary/index', $data);
	}
/*
* ajax form request
*/	

	public function get_item(){
		if(!$this->input->is_ajax_request()) exit('No direct script access allowed');
		$data['total'] = 0; $data['result'] = ''; 
		$DateToday = $this->my_layout->setDate('', true);
		if(isset($_GET['search'])  || isset($_GET['mid'])  != '' ||isset($_GET['mname']) != ''){
			//*FILTER SEARCH		
			$where = 'paH.PA_ID <> ""'; 		
			if(isset($_GET['search'])  && $_GET['search'] != '' ){
				$SEARCH = htmlentities($this->input->get('search', true));
				$where .= (empty($where) ? '' : ' AND paH.PA_ID in ('.$this->my_lib->multiPANUM($SEARCH).')');
			}
			if(isset($_GET['mid'])  && $_GET['mid'] != '' ){
				$SEARCH = htmlentities($this->input->get('mid', true));
				$where .= (empty($where) ? '' : ' AND (paH.MERCHANT_ID = "'.$SEARCH.'")');
			}
			if(isset($_GET['mname'])  && $_GET['mname'] != '' ){
				$SEARCH = $this->input->get('mname', true);
				$where .= ' AND (mer.LegalName like "%'.$SEARCH.'%")';
			}
			//*END FILTER SEARCH
			
			$data['where'] = $where;	
			$page = $this->my_layout->pagination();	
			$data['per_page'] = $page['per_page'];	
			$data['offset'] = $page['offset'];	
			$GROUP_BY =  'paD.PA_ID';
			$data['total'] =  $this->Sys_model->getTransactionSummary_part3($where, true, '', $GROUP_BY)->num_rows();
			if($data['total'] != 0){	
				$temp_transac =  $this->Sys_model->getTransactionSummary_part3($where, false, $page,  $GROUP_BY);	
				$data['result'] = $this->arr_result($temp_transac);
			}
		}
		echo json_encode($data); 
		exit();
	}
	
	public function export(){	
		//if(!$this->input->is_ajax_request()) exit('No direct script access allowed');
		//*FILTER SEARCH		
		$where = 'paH.PA_ID <> ""'; 		
		if(isset($_GET['search'])  && $_GET['search'] != '' ){
			$SEARCH = htmlentities($this->input->get('search', true));
			$where .= (empty($where) ? '' : ' AND paH.PA_ID in ('.$this->my_lib->multiPANUM($SEARCH).')');
		}
		if(isset($_GET['mid'])  && $_GET['mid'] != '' ){
			$SEARCH = htmlentities($this->input->get('mid', true));
			$where .= (empty($where) ? '' : ' AND (paH.MERCHANT_ID = "'.$SEARCH.'")');
		}
		if(isset($_GET['mname'])  && $_GET['mname'] != '' ){
			$SEARCH = $this->input->get('mname', true);
			$where .= ' AND (mer.LegalName like "%'.$SEARCH.'%")';
		}
		//*END FILTER SEARCH
		$GROUP_BY =  'paD.PA_ID';
		$result =  $this->Sys_model->getTransactionSummary_part3($where, false, '',  $GROUP_BY);	
		if($result->num_rows() != 0 ){
			$arr = $this->arr_result($result);
			$this->load->model('Action_model');								
			$this->Action_model->audit_save(7, '');	
			$this->load->library('download_file');
			return $this->download_file->summary_report(array('filename'=>'PA SUMMARY'), $arr);
		}
	}
	
		private function arr_result($temp_transac, $export = false){
			$arr = array();			
			$fields = $temp_transac->list_fields(); 
			foreach($temp_transac->result() as $temp_row){ 
				$newRow = new stdClass(); 
				foreach ($fields as $field){
					if($field == 'pa_id' && $temp_row->$field != '') $newRow->$field = $this->my_lib->paNumber($temp_row->$field);
					else if($field == 'TOTAL_FV' && $temp_row->$field != '') $newRow->$field = number_format($temp_row->$field, 2);
					else $newRow->$field =  $temp_row->$field;
					
				}					
				$newRow->get_userid = $this->auth->get_userid(); 									
				$arr[] = $newRow;
			}
			return $arr;
		}
}




