<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Filter extends MX_Controller {
	private $MODULE_ID;
	public function	__construct(){
	parent::__construct();
		$this->MODULE_ID = 0;
	//if(!$this->auth->check_session()) redirect('login');
	//$this->auth->check_admin();

	$this->load->model('User_model');		
	}
	public function get_mngt(){
		if(!$this->input->is_ajax_request()) exit('No direct script access allowed');
		$data['result'] = $where = '';
		//$where = '(user.user_id <> '.$this->auth->get_userid().')';
		if(isset($_GET['search'])){
			$SEARCH = htmlentities($this->input->get('search', true));
			$where = '(user.user_name like "%'.$SEARCH.'%" OR user.email like "%'.$SEARCH.'%")';
		}	
		if(isset($_GET['type'])){
			$type = htmlentities($this->input->get('type', true));
			if(!empty($where)) $where .= ' AND ';
			$where = '(user.utype_id = '.$type.')';
		}		
		if(isset($_GET['status'])){
			$status = htmlentities($this->input->get('status', true));
			if(!empty($where)) $where .= ' AND ';
			$where = '(user.status = '.$status.')';
		}	
		$data['get'] = $where;
		$page = $this->my_layout->pagination();	
		$data['per_page'] = $page['per_page'];	
		$data['offset'] = $page['offset'];
		$data['total'] =  $this->User_model->search_user($where, true);		
		if($data['total'] != 0){		
			$temp_transac =  $this->User_model->search_user($where, false, $page);
			$data['result'] = $this->arr_result($temp_transac);
		}		
		echo json_encode($data); 
		exit();
	}
		
		private function arr_result($temp_transac, $export = false){
			$arr = array();
			foreach($temp_transac as $temp_row){ 
				$newRow = new stdClass(); 	
					//logs
					$newRow->user_id = $temp_row->user_id;	
					$newRow->user_name = $temp_row->user_name;	
					$newRow->email = $temp_row->email;					
					$newRow->status = $temp_row->status;
					
					
				$arr[] = $newRow;
			}
			return $arr;
		}
}
