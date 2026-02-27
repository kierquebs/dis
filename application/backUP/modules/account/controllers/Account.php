<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends MX_Controller {
	public function	__construct(){
		parent::__construct();
		if(!$this->auth->check_session()) redirect('login');
		$this->load->model('User_model');
		$this->load->model('Action_model');
		$this->form_validation->run($this);
	}
	public function index(){		
		$this->form_validation->set_rules('username', 'Username', 'required|callback_username_check');
		$this->form_validation->set_rules('fullname', 'Fullname', 'required');	
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		
		$where['user.user_id'] = $uid = $this->auth->get_userid();
		if ($this->form_validation->run() !== FALSE){
			/* save to db */
			$data['alert'] = $this->lib_order->alertMsg(6, 'Successfully Update Profile', true);
			$this->Action_model->user_update($uid, $_POST);
			$this->Action_model->audit_save(0, 3);
		}
		
		$data['data_user'] = $row_info = $this->User_model->user_info($where)->row();
		
		$data['input_user'] = array(
		  'name' => 'username',
		  'class' => 'required form-control',
		  'type' => 'text',
		  'value' => $row_info->user_name
		);
		$data['input_fullname'] = array(
		  'name' => 'fullname',
		  'class' => 'required form-control',
		  'type' => 'text',
		  'value' => $row_info->full_name
		);
		$data['css'][] = 'account.css';		
		$data['js'][] = 'account.js';			
		$this->my_layout->layout_nav('account/index', $data);
	}
	
		function username_check($str){
			/*
			* CHECK IF USERNAME ALREADY EXIST IN DB
			*/
			$arr['user_name ='] = $str;
			$arr['user_id <>'] = $this->auth->get_userid();
			if ($this->User_model->check_user($arr, true) <> 0){
				$this->form_validation->set_message('username_check', 'Already Exists');
				return FALSE;
			}else{
				return TRUE;
			}
		}
	
    /**
     * Change Password 
     * @param  var  $str = passold
     * @param  input password
     * @return form success
     */
	public function password(){
		$this->form_validation->set_rules('passold', 'Old Password', 'required|callback_checkOldPass');
		$this->form_validation->set_rules('passnew', 'New Password', 'required|min_length[7]');	
		$this->form_validation->set_rules('passconf', 'Password Confirmation', 'required|matches[passnew]');	
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		
		$data['input_passold'] = array(
			'name' => 'passold',
			'class' => 'required form-control',
			'type' => 'password'
		);
		$data['input_passnew'] = array(	
			'name' => 'passnew',
			'class' => 'required form-control',
			'type' => 'password'
		);
		$data['input_passconf'] = array(
			'name' => 'passconf',
			'class' => 'required form-control',
			'type' => 'password'
		);
		if ($this->form_validation->run() !== FALSE){
			/* save to db */
			$data['alert'] = $this->my_layout->alertMsg(7, 'Successfully Change Password', true);
			$this->Action_model->change_pass($this->auth->get_userid(), $this->input->post('passnew', true));
			$this->Action_model->audit_save(0, 3);
			$_POST = array();
		}
		$data['curr_pass'] = $this->User_model->check_password($this->auth->get_userid(), $this->auth->default_pass(true), true);
		$data['data_user'] = $this->auth->get_username();
		$data['css'][] = 'account.css';		
		$data['js'][] = 'account.js';			
		$this->my_layout->layout_nav('account/password', $data);
	}
		/**
		 * Check Password 
		 * @param  var  $str = passold
		 * @param  input password
		 * @return form validation
		 */
		function checkOldPass($str){
			if ($this->User_model->check_password($this->auth->get_userid(), $str, true) == 0){
				$this->form_validation->set_message('checkOldPass', 'Invalid Password');
				return FALSE;
			}else{
				return TRUE;
			}
		}
}
