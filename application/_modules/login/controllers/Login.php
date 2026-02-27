<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MX_Controller {
	private $activate_stat;
	public function	__construct(){
		parent::__construct();
		$this->form_validation->run($this);
		$this->load->model('User_model');
		$this->load->model('Action_model');		
		$this->activate_stat = false;
	}
	function _404(){
		$data['heading'] = 'Unauthorized Page Request!';
		$data['message'] = 'You do not have permission to access this page. Please contact admin to access this page!.';
		$this->my_layout->layout("errors/html/error_404", $data);
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
			}else $this->my_layout->layout('login/activate');
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
			$data['erros_msg'] = '<span class="error">Please select company</span>';	
			$this->my_layout->layout('login/index', $data);
		}else{	
			$str = $this->db->escape_str($this->input->post('username', true));
			if(empty(valid_email($str))) $where['user_name'] = $str;	
			else $where['email'] = $str;	
			
			$where['password'] = $this->db->escape_str($this->auth->encrypt_encode($this->input->post('password', true), true));
			$row = $this->User_model->user_info($where);
			$u_id = $row->row('user_id');
				
			$get_role = $this->User_model->available_role($u_id);	
				$get_role_id = array();  $def_module = '';
				foreach($get_role as $val){
					if($val->acc_read_only == 1 ||$val->acc_all_access == 1){
						$get_role_id[] = $val->acc_id;
						if($val->def_page == 1) $def_module = $val->acc_code;
					}
				}
			$ajax_session = $this->Action_model->ajax_update(); //update session for AJAX REQUEST
			$sess_array = array(
			 'id' =>  $u_id,
			 'name' => $row->row('user_name'),
			 'type' => $row->row('utype_id'),
			 'access_module' => $get_role_id,
			 'def_page' => $def_module,
			 'ajax_session' => $ajax_session
			);
			$this->auth->set_session($sess_array);
				$this->Action_model->audit_save(0);
			if($activate == true) $this->Action_model->activate_user($u_id);			
			redirect($def_module);
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
		
		if(empty(valid_email($str))) $arr['user.user_name'] = $this->db->escape_str($str);	
		else $arr['user.email'] = $this->db->escape_str($str);	
		
		$arr['user.password'] = $this->auth->encrypt_encode($this->db->escape_str($pass), true);
		if($this->activate_stat == false) $arr['user.status'] = 1;
		
		if($this->User_model->check_user($arr, true) == 0){
			$this->form_validation->set_message('account_check', 'Invalid username or password!');
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
				$email = $this->my_email->reset_password($toEmail, $toName);
				if($email != true)$this->my_layout->alertMsg(3, 'Email not sent!', true);
				else{	
					$this->Action_model->reset_pass($row->row('user_id'));				
					$data['alert'] = $this->my_layout->alertMsg(6, 'Email already sent!', true);
				}
			}else $data['alert'] = $this->my_layout->alertMsg(3, 'Email does not exists!', true);
		}	
		$data['css'][] = 'login.css';		
		$this->my_layout->layout('login/forgot', $data);
		
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
			$this->Action_model->audit_save(1); 
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
			$data['ajax_session'] = $this->auth->ajax_session();
			$data['stat'] = $this->Action_model->check_ajax();
			$data['log'] = true;		
		}else $data['log'] = false;
		echo json_encode($data);
	}
	
	/*
	*
	*/		
	function default_admin(){
		$insert['user_name'] = 'admin';
		$insert['full_name'] = 'admin';
		$insert['utype_id'] = 1;
		$insert['email'] = 'it.svc.ph@sodexo.com';
		$insert['password'] = $this->auth->default_pass();
		$insert['status'] = 1;
		$this->db->insert('user',$insert);
		$newID = $this->db->insert_id();
		
		if(!empty($newID)){
			/*CREATE PERMISSION*/
			for($i=1;$i<=5;$i++){
				$utype = 1;
				$insertAcc = $this->my_layout->user_permission($utype, $i);
				$insertAcc['user_id'] = $newID;
				$insertAcc['created_by'] = 0;
				$this->Action_model->access_add($insertAcc);
			}
		}
	}
}
