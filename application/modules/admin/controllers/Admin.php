<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MX_Controller {
	private $MODULE_ID;
	public function	__construct(){
	parent::__construct();
		$this->MODULE_ID = 5;
		if(!$this->auth->check_session()) redirect('login');
		$this->auth->check_admin();
		
		$this->load->model('User_model');
		$this->load->model('Action_model');	
		$this->load->model('Corepass_model');
		$this->form_validation->run($this);
	}
	public function test(){
		echo $this->sdx_email->test();
	}
	
	
	// TESTING FOR DM GENERATION (PHSD-4113, PHSD-3437)
	public function dmtest(){
		if(isset($_GET['cp_id']) && $_GET['cp_id'] != '' && isset($_GET['bankaccountno']) && $_GET['bankaccountno'] != ''){
			$ID = $_GET['cp_id'];
			$bankaccountno = $_GET['bankaccountno'];
		
			echo 'Mercant ID: ' . $ID . '<br>';

			$data = $this->Corepass_model->getBankAccountByCPID($bankaccountno, $ID);
			
			echo 'No. of bank account found in Corepass: ' . $data->num_rows() . '<br>';
			
			if($data->num_rows() !=0){
				echo '<pre>';
				echo json_encode($data->result(), JSON_PRETTY_PRINT);
				echo '</pre>';
			}else{
				echo 'No result found';
			}
		}else{
			echo 'cp_id is required';
		}
	}
	
	public function index(){
		$this->form_validation->set_rules('username', 'Username', 'required|callback_username_check');
		$this->form_validation->set_rules('email', 'Email', 'required|callback_email_check');	
		$this->form_validation->set_rules('type', 'User Type', 'required');	
		$this->form_validation->set_rules('full_name', 'Full Name', 'required');	
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		
		$data['input_user'] = array(
		  'name' => 'username',
		  'class' => 'required form-control',
		  'type' => 'text',
		  'value' =>  set_value('username')
		);
		$data['input_email'] = array(
		  'name' => 'email',
		  'class' => 'required form-control',
		  'type' => 'text',
		  'value' =>  set_value('email')
		);
		$data['input_name'] = array(
		  'name' => 'full_name',
		  'class' => 'required form-control',
		  'type' => 'text',
		  'value' =>  set_value('full_name')
		);
		if ($this->form_validation->run() !== FALSE){
			$newID = $this->Action_model->user_add($_POST, true, false);								
			//$this->Action_model->audit_save(0, 2, $newID);	
			/*CREATE PERMISSION*/
			for($i=1;$i<=5;$i++){
				$utype = $this->input->post('type', true);
					$insertAcc = $this->my_layout->user_permission($utype, $i);
					$insertAcc['user_id'] = $newID;
					$insertAcc['created_by'] = $this->auth->get_userid();
					$this->Action_model->access_add($insertAcc);
			}				
			$data['alert'] = $this->my_layout->alertMsg(7, 'Successfully Update Profile', true);
			/* save to db */
			$_POST = array();
		}		
		$data['css'][] = 'queue.css';
		$data['css'][] = 'admin.css';
		$data['js'][] = 'admin/mngt.js';
		
		$data['cssDT'][] = '1.10.16/css/jquery.dataTables.min.css';
		$data['jsDT'][] = '1.10.16/js/jquery.dataTables.min.js';		
		$this->my_layout->layout_nav('admin/index', $data);
	}
	function resetpass(){
		if(!$this->input->is_ajax_request())redirect(base_url());
		
		$userID = $_POST['id'];
		$checkRow = $this->User_model->user_info(array('user_id'=>$userID));
		if($userID && $checkRow->num_rows() !== 0){
			$passWORD = $this->Action_model->reset_pass($userID);	
				$toEmail = $checkRow->row('email');
				$toName = (empty($checkRow->row('full_name')) ? $checkRow->row('user_name') : $checkRow->row('full_name'));
				$this->sdx_email->reset_password($toEmail, $toName, $passWORD);			
			$this->Action_model->audit_save(0, 3, $userID);	
			$data['msg'] = 'SUCCESS RESET PASSWORD!';
		}else $data['msg'] = 'FAILED RESET PASSWORD!';
		
		echo json_encode($data);
		exit();
	}
	function edit(){
		$userID = $this->uri->segment(3);
		$checkRow = $this->User_model->user_info(array('user_id'=>$userID), true);
		if(!$userID || $checkRow == 0) redirect('admin');
		
		$this->form_validation->set_rules('username', 'Username', 'required|callback_username_check|strtolower');
		$this->form_validation->set_rules('fullname', 'Fullname', 'required');	
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');	
		
		if ($this->form_validation->run() !== FALSE){
			$this->Action_model->user_update($userID , $_POST);				
			$this->Action_model->audit_save(0, 3, $userID);				
			$utype = $this->input->post('type', true);
			//echo $utype.'<br />';
			for($i=1;$i<=5;$i++){
				$update = $this->my_layout->user_permission($utype , $i);
				$where['user_id'] = $userID;
				$where['acc_id'] = $i;
				$this->Action_model->access_update_user($where, $update);
				//echo '<pre>';print_r($update);echo '</pre>';
			}	
			$data['alert'] = $this->my_layout->alertMsg(7, 'Successfully Update Profile', true);
			/* save to db */
		}
		
		$data['user_id'] = $userID;
		$data['data_user'] = $row = $this->User_model->user_info(array('user_id'=>$userID));
		
		$set_user = $row->row('user_name');
		$set_fullname = $row->row('full_name');
		if(isset($_POST['submit'])){
			$set_user = $_POST['username'];
			$set_fullname = $_POST['fullname'];
		}
		
		$data['input_user'] = array(
		  'name' => 'username',
		  'class' => 'required form-control',
		  'type' => 'text',
		  'value' =>  $set_user
		);
		$data['input_fullname'] = array(
		  'name' => 'fullname',
		  'class' => 'required form-control',
		  'type' => 'text',
		  'value' =>  $set_fullname
		);
		$data['css'][] = 'account.css';
		$this->my_layout->layout_nav('admin/edit_user', $data);
	}	
			function email_check($str){
				/*
				* CHECK IF EMAIL ALREADY EXIST  AND IF VALID IN DB
				*/
				if(empty(valid_email($str))){
					$this->form_validation->set_message('email_check', 'Invalid Email');
					return FALSE;
				}
				$checkRow = $this->User_model->user_info(array('email'=>$str), true);				
				if ($checkRow != 0){
					$this->form_validation->set_message('email_check', 'Email Already Exists');
					return FALSE;
				}else{
					return TRUE;
				}
			}		
			function username_check($str){
				/*
				* CHECK IF USERNAME ALREADY EXIST IN DB
				*/
				if($this->uri->segment(3)) $arr['user_id !='] = $this->uri->segment(3);
				$arr['user_name'] = $str;
				$checkRow = $this->User_model->user_info($arr, true);
				if ($checkRow != 0){
					$this->form_validation->set_message('username_check', 'Username Already Exists');
					return FALSE;
				}else{
					return TRUE;
				}
			}
}
