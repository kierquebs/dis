<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MX_Controller {
	private $activate_stat;
	private $configEmail;
	public function	__construct(){
		parent::__construct();
		$this->form_validation->run($this);
		$this->load->model('User_model');
		$this->load->model('Action_model');		
		$this->activate_stat = false;
		
		
		$config['useragent'] = 'PHPMailer'; // Mail engine switcher: 'CodeIgniter' or 'PHPMailer'
		$config['mailpath']         = '/usr/sbin/sendmail';
		$config['protocol']    = (getenv('MAIL_DRIVER')    ?: 'smtp');
		$config['smtp_host']   = (getenv('MAIL_HOST')      ?: 'smtp.gmail.com');
		$config['smtp_port']   = (getenv('MAIL_PORT')      ?: '587');
		$config['smtp_crypto'] = (getenv('MAIL_ENCRYPTION') ?: 'tls');
		$config['smtp_user']   = (getenv('MAIL_USERNAME')  ?: '');
		$config['smtp_pass']   = (getenv('MAIL_PASSWORD')  ?: '');
		$config['smtp_timeout']     = 5;                        // (in seconds)
		$config['wordwrap']         = true;
		$config['wrapchars']        = 76;
		$config['mailtype']         = 'html';                   // 'text' or 'html'
		$config['charset']          = 'iso-8859-1';
		$config['validate']         = true;
		$config['priority']         = 3;                        // 1, 2, 3, 4, 5
		$config['crlf']             = "\n";                     // "\r\n" or "\n" or "\r"
		$config['newline']          = "\n";                     // "\r\n" or "\n" or "\r"
		$config['bcc_batch_mode']   = false;
		$config['bcc_batch_size']   = 200;
		
		$this->configEmail = $config;
	}
	
	function test(){	
		print_r($this->configEmail);
		$this->email->initialize($this->configEmail);
		$this->email->from('sodexo.notification@sodexo.ph', 'SOdexo Test');
		$this->email->to('arced.remollo@sodexo.com');
		$this->email->subject('Email Test');
		$this->email->message('Test 2');
		$this->email->send();

		echo $this->email->print_debugger();
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
		if($checkCode->num_rows() != 0){
			$stat = $checkCode->row('status');
			if($stat == 0){
				$this->activate_stat = true;
				$this->index(true, $stat);
			}else $this->my_layout->layout('login/activate');
		}
		
	}
	public function index($activate = false){	
		//echo '<center><h2>UNDER MAINTENANCE</h2></center>'; die();
		
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
			$str = $this->input->post('username', true);
			if(empty(valid_email($str))) $where['user_name'] = $str;	
			else $where['email'] = $str;	
			
			$where['password'] = $this->auth->encrypt_encode($this->input->post('password', true), true);
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
			// $this->Action_model->audit_save(0);
			
				//DEV
			$u_id = $row->row('user_id');
			// Start: comment out password policy
			$last_password_change_date = $this->Action_model->get_last_password_change_date($u_id);
			// Calculate time difference in days
           
			// if ($last_password_change_date) {
            // Calculate time difference in days
            // $days_since_last_change = (strtotime(date('Y-m-d')) - strtotime($last_password_change_date)) / (60 * 60 * 24);
			$days_since_last_change = 1;

            if ($days_since_last_change >= 90) {				
				$data['change_pass'] = 'forcechange';	
                redirect('account/password?changepass=true' , $data);
				}
			// End: comment out password policy
			// }	
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
		
		if(empty(valid_email($str))) $arr['user_name'] = $str;
		else $arr['email'] = $str;
		
		$arr['password'] = $this->auth->encrypt_encode($pass, true);
		if($this->activate_stat == false) $arr['status'] = 1;
		
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
			if($row->num_rows() != 0 ){
				$toEmail = $row->row('email');
				$toName = (empty($row->row('full_name')) ? $row->row('user_name') : $row->row('full_name'));
				$email = $this->sdx_email->reset_password($toEmail, $toName); 
				if($email !== true)$this->my_layout->alertMsg(3, 'Email not sent!', true);
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
		if ($str === null || $str === '') {
			$this->form_validation->set_message('email_check', 'Invalid Email');
			return FALSE;
		}
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
			//$this->Action_model->audit_save(1); 
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
	
	public function test_email(){
		echo $this->sdx_email->test();
	}
}
