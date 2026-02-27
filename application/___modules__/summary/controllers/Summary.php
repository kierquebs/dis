<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Summary extends MX_Controller {
	private $MODULE_ID;
	public function	__construct(){
	parent::__construct();
		$this->MODULE_ID = 3;
		//if(!$this->auth->check_session()) redirect('login');		
		$this->load->model('Sys_model');
		$this->form_validation->run($this);
		//if($this->auth->role_all($this->MODULE_ID) == false) redirect('404_override');
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
		$data['result'] = ''; 
		$DateToday = $this->my_layout->setDate('', true);
		//*FILTER SEARCH		
		$where = ''; 		
		if(isset($_GET['search'])  && $_GET['search'] != '' ){
			$SEARCH = htmlentities($this->input->get('search', true));
			$where .= (empty($where) ? '' : ' AND ').'(oc.po_no = "'.$SEARCH.'" OR oc.order_id = '.$SEARCH.' OR oc.deno = '.$SEARCH.')';
		}
		//*END FILTER SEARCH
		
		$data['where'] = $where;	
		$page = $this->my_layout->pagination();	
		$data['per_page'] = $page['per_page'];	
		$data['offset'] = $page['offset'];	
		$data['total'] =  $this->Sys_model->getTransaction($where, true)->num_rows();
		if($data['total'] != 0){	
			$temp_transac =  $this->Sys_model->getTransaction($where, false, $page);	
			$data['result'] = $this->arr_result($temp_transac);
		}
		echo json_encode($data); 
		exit();
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
				/*
				$newRow->access_edit = $this->User_model->check_access($this->MODULE_ID, array_merge($this->my_layout->_authorizeAction('store'), $this->my_layout->_authorizeAction('delete')));
				*/					
				$arr[] = $newRow;
			}
			return $arr;
		}
}




