<?php
class Action_model extends CI_Model{
	protected $default_pass;
	public function __construct(){
		parent::__construct();
	}
	/*
	** QUERY FOR TBL audit
	* @param $where [array]
	*/
	public function audit_batch($data){
		if(!$data) return false;
		/*$data = array(
			array(
					'user_id' => 'user_id',
					'module_id' => 'module_id',
					'target_id' => 'target_id'
			),
			array(
					'user_id' => 'Another title',
					'module_id' => 'Another Name',
					'target_id' => 'Another date'
			)
		);*/
		$this->db->insert_batch('audit_trail', $data);
	}
	/*
	** 0:login; 1;logout 2:add; 3:update; 4:delete; 5:cancel; 6:submit; 7:download;
	*/
	public function audit_save($actionID, $insert = ''){
		if(!$actionID) return false;
		$insert['user_id'] = $this->auth->get_userid();
		$insert['action'] = $actionID;
		$this->db->insert('audit_trail',$insert);
	}
	/*
	** QUERY FOR TBL ajax_update
	* @param $where [array]
	*/
	public function ajax_where($where = '', $count = false){
		if(!empty($where))$this->db->where($where);
		$this->db->from('ajax_update');
		if($count == true){
			$this->db->select('id');
			return $this->db->count_all_results();	
		}else return  $this->db->get();
	}
	function ajax_update($fromHOME = false){
		return false;
		//set new dis_ajax_session after database change	
		$dateTIME = $this->my_layout->current_date();
		$date = $this->my_layout->setDate($dateTIME, true);
		$session_id =  $this->auth->encrypt_encode($date);
				
		$this->db->query('INSERT INTO ajax_update (session_id, date_created, last_update)
							VALUES ("'.$session_id.'",  "'.$date.'", "'.$dateTIME.'")
							ON DUPLICATE KEY UPDATE
								date_created = VALUES(date_created) ,
								last_update = IF(last_update < VALUES(last_update), VALUES(last_update), last_update),
								session_id = VALUES(session_id)');			
		if($this->auth->check_session() && $fromHOME == false) $this->auth->set_userdata('dis_ajax_session', $session_id);
		return $session_id;
	}
	function check_ajax(){			
		$whereNew['date_created'] = $where['date_created'] = $this->my_layout->setDate('', true);		
		$checkAjaxToday = $this->ajax_where($where);
		if($checkAjaxToday->num_rows() != 0){
			//get new ajax_session to update session_auth
			$mySession = $this->auth->ajax_session();	
			$session_id = $checkAjaxToday->row('session_id');
			if($session_id != $mySession){
				$this->auth->set_userdata('dis_ajax_session', $session_id);
				return true;
			}else return false;		
		}else{
			/* NO EXISTING SESSION TODAY*/
			$session_id = $this->ajax_update();
				$this->auth->set_userdata('dis_ajax_session', $session_id);
			return true;
		}
	}
	/*
	** QUERY ACTION FOR TBL access_permission
	*/
	function access_add($insert, $returnID = false){		
		if(empty($insert)) return false;
			$this->db->insert('access_permission',$insert);
		if($returnID == true) return $this->db->insert_id();
	}
	function access_update($access_id, $update){		
		$this->db->where('id',$access_id);
		$this->db->update('access_permission',$update);
	}
	function access_update_user($where, $update){		
		$this->db->where($where);
		$this->db->update('access_permission',$update);
	}
	
	/*
	** QUERY ACTION FOR TBL user
	*/
	function user_add($post, $returnID = false, $sendEmail = false){
		$insert['user_name'] = $this->input->post('username', true);
		$insert['full_name'] = $this->input->post('full_name', true);
		$insert['utype_id'] = $this->input->post('type', true);
		$insert['email'] = $this->input->post('email', true);
		$insert['password'] = $this->auth->default_pass();
		$insert['activation_code'] = md5($this->auth->encrypt_encode($insert['email'].$this->my_layout->current_date())); 
		$insert['status'] = 0;
		$this->db->insert('user',$insert);
		if($sendEmail == true){
			$toEmail = $insert['email'];
			$toName = $insert['user_name'];
			$this->my_email->email_account($toEmail, $toName, $insert['activation_code'], $this->auth->default_pass(true));	
		}
		if($returnID == true) return $this->db->insert_id();		
	}
	function user_update($uid, $post){
		$update['user_name'] = $this->input->post('username', true);
		$update['full_name'] = $this->input->post('fullname', true);
		$update['utype_id'] = $this->input->post('type', true);
		if(isset($_POST['status'])) $update['status'] = $this->input->post('status', true);
		$this->db->where('user_id',$uid);
		$this->db->update('user',$update);
	}
	function activate_user($uid){
		$update['status'] = 1;
		$this->db->where('user_id',$uid);
		$this->db->update('user',$update);
	}
	function change_pass($uid, $new_pass){
		$update['password'] = $this->auth->encrypt_encode($new_pass, true);
		$this->db->where('user_id',$uid);
		$this->db->update('user',$update);
	}
	function reset_pass($uid){
		$update['password'] = $this->auth->default_pass();
		$this->db->where('user_id',$uid);
		$this->db->update('user',$update);
		return $this->auth->default_pass(true);
	}
	
}

