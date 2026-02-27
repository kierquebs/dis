<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class My_layout extends MX_Controller{
    public function __construct() {
		$this->load->helper('number');
    }
	/**
     * GET MODULE
	* end(explode('/',$url))
     */
	public function asset_location(){
		switch (ENVIRONMENT) {
			case 'development':
				return 'C:/xampp/htdocs/mp_dis/assets/';
				break;
			default:
				return '/var/www/html/mp_dis/assets/';
		}
	}
    public function module($url){
		switch ($url) {
			case 'transaction':
				$module_id = 1; 
				break;
			case 'process':
				$module_id = 2; 
				break;
			case 'summary':
				$module_id = 3; 
				break;
			case 'admin':
				$module_id = 5; 
				break;
			default:
				$module_id = 0; 
		}
		return $module_id;
	}
	
	/**
     * PAGINATION SETUP
     */
    public function pagination($per_page = 20){
		$page['per_page'] = $per_page;
		$page['limit'] = $per_page;	
		$page['offset'] = 0;		
		if(isset($_GET['page'])){
			$pageNum = $_GET['page']; 
			if($pageNum) $page['offset'] = ($pageNum - 1) * $per_page;
			else $page['offset'] = 0;
		}
		return $page;
	}
	/**
     * SET Layout for HOMEPAGE
     */
    public function layout($page, $data = ''){
		$this->load->view('layout/header', $data);
		$this->load->view($page);
		$this->load->view('layout/footer', $data);
	}
	/**
     * SET Layout w/ Navigation Tab
     */
    public function layout_nav($page, $data = ''){
		$data['css'][] = 'queue.css';
		$data['nav_div'] = 1;		
		$nav_data = $this->set_nav();
		$this->load->view('layout/header', $data);
		$this->load->view('layout/nav-header', $nav_data);
		$this->load->view($page, $data);
		$this->load->view('layout/footer', $data);
	}		
		private function set_nav(){
			$module_page = $this->uri->segment(1);
			$module_class = $this->uri->segment(2);
			$page_filter = $data['backAr'] = '';
			$admin = $this->auth->check_admin(false);
			$allaccessArr = (is_array($this->auth->user_allaccess()) ? $this->auth->user_allaccess() : array());
			$access_arr = $allaccessArr;
			
			$page_active = 1; 
			$module_page = (empty($module_page) ? 'transaction' : $module_page);
			switch ($module_page) {
				case 'transaction':
					$page_title = 'Transaction Report';
					$page_active = 1; 
				break;
				case 'process':
					$page_title = 'PA Process';
					$page_active = 2; 
				break;
				case 'summary':
					$page_title = 'PA Summary';
					$page_active = 3; 
				break;
				case 'account':
					$page_title = 'My Account';
					$page_active = 8; 
				break;
				case 'admin':	
					$page_title = 'ADMIN MANAGEMENT';
					$page_active = 5;
				break;
				case 'report':	
					$page_title = 'REPORT MANAGEMENT';
					$page_active = 0;
				break;
			}
			switch ($module_class) {
				case 'audit':
					$page_title = 'AUDIT TRAIL';
				break;
				case 'edit':
					$page_title = 'ADMIN MANAGEMENT - EDIT USER PROFILE';
					$data['backAr'][] = 'edit';
				break;
				case 'access':
					$page_title = 'ADMIN MANAGEMENT - ACCESS CONTROL';
				break;
				case 'status':
					$page_title = 'ADMIN MANAGEMENT - ORDER STATUS';
				break;
			}	

			$data['access_arr'] = $access_arr;	
			$data['page_active'] = $page_active;
			$data['page_title'] = $page_title;
			$data['page_filter'] = $page_filter;
			$data['myUname'] = $this->auth->get_username();
			
			return $data;
		}
	/**
	* SET alert message
	* @param $class = alert-class (1-info, 2-warning, 3-danger, 4-link, 5-dismissible, 6-success)
	* @param $msg = alert-message
	* @param $set = show div
	* @return alert div
	*/
	public function alertMsg($class, $msg, $set, $close = true){
		if($set == false) return;
			switch ($class) {
				case 2:
					$className = 'warning';
					$strongName = 'Warning!';
					break;
				case 3:
					$className = 'danger';
					$strongName = 'Alert!';
					break;
				case 4:
					$className = 'link';
					$strongName = '';
					break;
				case 5:
					$className = 'dismissible';
					$strongName = 'Alert!';
					break;
				case 6:
					$className = 'success';
					$strongName = 'Well done!';
					break;
				default:
					$className = 'info';
					$strongName = 'Heads up!';
			}
			$reMsg = '<div class="alert alert-'.$className.'" role="alert">';
			if($close == true) $reMsg .= '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
			$reMsg .= '<strong>'.$strongName.'</strong> '. $msg;
			$reMsg .= '</div>';
		return $reMsg;
	}

	public function current_date(){
		return $this->setDate();
	}
	public function setDate($date = null, $dateOnly = false) {
		$time = now();	
		$format = ('%Y-%m-%d %H:%i:%s');		
		if($date != null) $time = strtotime($date); 		
		if($dateOnly == true) $format = ('%Y-%m-%d');
		
		return mdate($format , $time);
	}
	public function setMyDate($dateSET, $dateFORMAT = false) {
			if(!$dateSET) $dateSET = date("Y-m-d");
		$format = ('Y-m-d H:i:s');
		if($dateFORMAT  != false) $format = ($dateFORMAT);			
		return date($format, strtotime($dateSET));
	}
	
	/**
     * GET MODULE
	* end(explode('/',$url))
     */
	 function grp_gen($rID){
		return $this->set_barcode($rID, '000000');
	 }
	 function set_barcode($char, $def = '00000000'){
		$charLen = strlen($char);
		return substr_replace($def,$char,(strlen($def)-$charLen),$charLen); 
	 }
	/*
	* FORMULA
	*/
	public function order_amount($deno, $qty){
		return ($deno * $qty);
	}
	public function last_barcode($bfirst, $qty){
		return ($bfirst + $qty) - 1;
	}
	public function get_bal($qty, $used){
		$ans = ($used == 0 ? $qty : ($qty - $used));
		return ($ans > 0 ? $ans : 0);
	}		
    public function _authorizeAction($request){
		$arr = array();		
		$arr['acc_read'] = 1; 
		switch ($request) {
			case 'store':
				$arr['acc_add'] = $arr['acc_edit'] = 1;
			break;
			case 'delete':
				$arr['acc_delete'] = 1;
			break;
			case 'submit':
				$arr['acc_submit'] = 1;
			break;
			case 'pick':
				$arr['acc_pick'] = 1;
			break;
			case 'cancel':
				$arr['acc_cancel'] = 1;
			break;
		}
		return $arr;
    }
	
	
	/**
     * SET PERMISSION
     */
    public function user_permission($typeID, $moduleID){
		$arr = array();	
		$arr['acc_id'] = $moduleID;	 
		switch ($typeID) {
			case 1:	//admin	
				if($moduleID == 5) $arr['def_page'] = 1;
				else $arr['def_page'] = 0;
				
				$arr['acc_read_only'] = 0;
				$arr['acc_all_access'] = 1;
			break;
			case 2:	//reim	
				if($moduleID == 2){					
					$arr['acc_read_only'] = 0;
					$arr['acc_all_access'] = $arr['def_page'] = 1;
				}else if($moduleID == 5){
					$arr['acc_read_only'] = 1;
					$arr['acc_read_only'] = $arr['acc_all_access'] = $arr['def_page'] = 0;
				}else{
					$arr['acc_read_only'] = 1;
					$arr['acc_all_access'] = $arr['def_page'] = 0;
				}
			break;
			case 3:	//readonly	
				if($moduleID == 1){					
					$arr['acc_all_access'] = 0;
					$arr['acc_read_only'] = $arr['def_page'] = 1;
				}else $arr['acc_all_access'] = $arr['acc_read_only'] = $arr['def_page'] = 0;				
			break;
			case 4:	//finance	
				if($moduleID == 5 || $moduleID == 2){
					$arr['acc_read_only'] = 0;
					$arr['acc_read_only'] = $arr['acc_all_access'] = $arr['def_page'] = 0;
				}else{
					$arr['acc_read_only'] = 1;
					$arr['acc_all_access'] = $arr['def_page'] = 0;
					if($moduleID == 3)$arr['def_page'] = 1;
				}
			break;
		}
		return $arr;
	}
	public function permission_type($typeID){
		switch ($typeID) {
			case 1:	//admin	
				return 'admin';
			break;
			case 2:	//reim					
				return 'reimbursement';
			break;
			case 3:	//cs	
				return 'read only';
			break;
			case 4:	//finance	
				return 'finance';
			break;
		}
	}	
}
