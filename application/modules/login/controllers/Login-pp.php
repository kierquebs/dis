<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MX_Controller {
	private $activate_stat;
	public function	__construct(){
		parent::__construct();
		$this->form_validation->run($this);
		$this->load->model('User_model');
		$this->activate_stat = false; 
	}
	
	function test(){		
		$this->load->library('queue_email');
		$email = $this->queue_email->test_email('arced.remollo@sodexo.com');
	}
	
	function _404(){
		$data['heading'] = 'Unauthorized Page Request!';
		$data['message'] = 'You do not have permission to access this page. Please contact admin to access this page!.';
		$this->lib_order->layout("errors/html/error_404", $data);
		//$this->load->view("errors/html/error_404", $data);
	}
	public function activate(){
		if($this->auth->check_session() == true) redirect($this->auth->get_defpage());	
		
		if(!$this->uri->segment(3)) redirect('login');	
		$where['activation_code'] = $this->uri->segment(3);
		$checkCode = $this->User_model->user_info($where, '', 'status');
		if(count($checkCode) != 0){
			$stat = $checkCode->row('status');
			if($stat == 0){
				$this->activate_stat = true;
				$this->index(true, $stat);
			}else $this->lib_order->layout('login/activate');
		}
		
	}
	public function index($activate = false){		
		if($this->auth->check_session() == true) redirect($this->auth->get_defpage());				
			if($activate == false) $where['status'] = 1;	
		$this->form_validation->set_rules('username', 'Username', 'required|callback_account_check');
		$this->form_validation->set_rules('password', 'Password', 'required');	
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		if ($this->form_validation->run() == FALSE){
			$data['username'] = array(
			  'name' => 'username',
			  'class' => 'required form-control',
			  'type' => 'text',
			  'value' =>  set_value('username')
			);
			$data['password'] = array(
			  'name' => 'password',
			  'class' => 'required form-control',
			  'type' => 'password'
			);				
			$data['css'][] = 'login.css';
			$data['activate'] = $activate;				
			$this->lib_order->layout('login/index', $data);
		}else{	
			$str = $this->db->escape_str($this->input->post('username', true));
			if(empty(valid_email($str))) $where['user_name'] = $str;	
			else $where['email'] = $str;	
			
			$where['password'] = $this->db->escape_str($this->auth->encrypt_encode($this->input->post('password', true), true));
			$row = $this->User_model->user_info($where);
				$utype_id = $row->row('utype_id');
				$get_viewaccess = $get_allaccess = $this->User_model->getall_access($utype_id, array('all_access'=>1)); 
				if( $utype_id != 1) $get_viewaccess = $this->User_model->getall_access($utype_id, array('view'=>1)); 
				$ajax_session = $this->Action_model->ajax_update(); //update session for AJAX REQUEST

			$sess_array = array(
			 'id' =>  $row->row('user_id'),
			 'name' => $row->row('user_name'),
			 'type' => $utype_id,
			 'type_name' => $row->row('utype_desc'),
			 'reupdate_stat' => $row->row('re_update'),
			 'all_access' => $get_allaccess['arr'],
			 'all_view' => $get_viewaccess['arr'],
			 'def_page' => $get_allaccess['default'],
			 'ajax_session' => $ajax_session
			);

			$this->auth->set_session($sess_array);
			$this->Action_model->audit_save(0, 5);


			//DEV
			$u_id = $row->row('user_id');
			$last_password_change_date = $this->Action_model->get_last_password_change_date($u_id);
			// Calculate time difference in days
           
			// if ($last_password_change_date) {
            // Calculate time difference in days
            $days_since_last_change = (strtotime(date('Y-m-d')) - strtotime($last_password_change_date)) / (60 * 60 * 24);


            if ($days_since_last_change >= 90) {				
				$data['change_pass'] = 'forcechange';	
                redirect('account/password?changepass=true' , $data);
				}
			// }	

			if($activate == true) $this->Action_model->activate_user($row->row('user_id'));
			
			if($utype_id == 1) redirect('admin');
			else redirect($get_allaccess['default']);
		}
	}
    /**
     * Check username and password 
     * @param  var  $str = username
     * @param  input password
     * @return form validation
     */
	public function account_check($str){ 
		$pass = $this->input->post('password', true);
		if(empty(valid_email($str))) $arr['user_name'] = $this->db->escape_str($str);	
		else $arr['email'] = $this->db->escape_str($str);	
		
		$arr['password'] = $this->auth->encrypt_encode($this->db->escape_str($pass), true);
		if($this->activate_stat == false) $arr['status'] = 1;
		if($this->User_model->check_user($arr, true) == 0){
			$this->form_validation->set_message('account_check', 'Invalid username or password');
			return FALSE;
		}else{
			return TRUE;
		}
	}
	public function forgot(){
		if($this->auth->check_session() == true) redirect($this->auth->get_defpage());
		
		$this->form_validation->set_rules('email', 'Email Addres', 'required|callback_email_check');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		
		$data['email'] = array(
		  'name' => 'email',
		  'class' => 'required form-control',
		  'type' => 'text',
		  'value' =>  set_value('email')
		);	
		if ($this->form_validation->run() !== FALSE){
			$where['email'] = $this->input->post('email', true);
			$row = $this->User_model->user_info($where);
			if(count($row) != 0 ){			
				$toEmail = $row->row('email');
				$toName = (empty($row->row('full_name')) ? $row->row('user_name') : $row->row('full_name'));
				$this->load->library('queue_email');
				$email = $this->queue_email->reset_password($toEmail, $toName);
				if($email != true)$this->lib_order->alertMsg(3, 'Email not sent!', true);
				else{	
					$this->Action_model->reset_pass($row->row('user_id'));				
					$data['alert'] = $this->lib_order->alertMsg(6, 'Email already sent!', true);
				}
			}else $data['alert'] = $this->lib_order->alertMsg(3, 'Email does not exists!', true);
		}	
		$data['css'][] = 'login.css';		
		$this->lib_order->layout('login/forgot', $data);
		
	}
    /**
     * Check email address 
     * @param  var  $str = email
     * @return form validation
     */
	public function email_check($str){ 	
		if(empty(valid_email($str))){
			$this->form_validation->set_message('email_check', 'Invalid Email');
			return FALSE;
		}		
		if ($this->User_model->check_user(array('email'=>$str, 'status' => 1), true) == 0){
			$this->form_validation->set_message('email_check', "We can't find a user with that e-mail address.");
			return FALSE;
		}else{
			return TRUE;
		}
				
	}
	/**
     * LOG-OUT USER
     */
	public function out(){
		if($this->auth->check_session() == true){
			$this->Action_model->audit_save(0, 6);
			$this->auth->out_session();
		}
		redirect('login');
	}
	/*
	*
	*/	
	function db_update(){
		if(!$this->input->is_ajax_request()) exit('No direct script access allowed');
		if($this->auth->check_session() == true){
			//$data['ajax_session'] = $this->auth->ajax_session();
			$data['stat'] = $this->Action_model->check_ajax();
			$data['log'] = true;			
		}else $data['log'] = false;
		echo json_encode($data);
	}
}
