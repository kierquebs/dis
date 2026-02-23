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
	public function audit_save($moduleID, $actionID, $targetID = 0, $orderID = 0){
		if(!$actionID) return false;
		$insert['user_id'] = $this->auth->get_userid();
		$insert['module_id'] = $moduleID;
		$insert['target_id'] = $targetID;
		$insert['order_id'] = $orderID;
		$insert['auc_id'] = $actionID;
		$this->db->insert('audit_trail',$insert);
		$this->ci_pusher->callTrigger();
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
		//set new ajax_session after database change	
		$dateTIME = $this->lib_order->current_date();
		$date = $this->lib_order->setDate($dateTIME, true);
		$session_id =  $this->auth->encrypt_encode($date);
				
		$this->db->query('INSERT INTO ajax_update (session_id, date_created, last_update)
							VALUES ("'.$session_id.'",  "'.$date.'", "'.$dateTIME.'")
							ON DUPLICATE KEY UPDATE
								date_created = VALUES(date_created) ,
								last_update = IF(last_update < VALUES(last_update), VALUES(last_update), last_update),
								session_id = VALUES(session_id)');			
		if($this->auth->check_session() && $fromHOME == false) $this->auth->set_userdata('ajax_session', $session_id);
		return $session_id;
	}
	function check_ajax(){			
		$whereNew['date_created'] = $where['date_created'] = $this->lib_order->setDate('', true);		
		$checkAjaxToday = $this->ajax_where($where);
		
		if($checkAjaxToday->num_rows() != 0){
			//get new ajax_session to update session_auth
			$mySession = $this->auth->ajax_session();	
			$session_id = $checkAjaxToday->row('session_id');
			if($session_id != $mySession){
				$this->auth->set_userdata('ajax_session', $session_id);
				return true;
			}else return false;		
		}else{
			/* NO EXISTING SESSION TODAY*/
			$session_id = $this->ajax_update();
				$this->auth->set_userdata('ajax_session', $session_id);
			return true;
		}
	}
	/*
	** QUERY ACTION FOR TBL access
	*/
	function access_update($access_id, $update){		
	$this->db->where('access_id',$access_id);
	$this->db->update('access',$update);
	}
	/*
	** QUERY ACTION FOR TBL file_upload
	*/
	function file_upload_add($file_name, $module_id, $utype_id){
		$insert['file_name'] = $file_name;
		$insert['module_id'] = $module_id;
		$insert['utype_id'] = $utype_id;
		$this->db->insert('file_upload',$insert);
		return $this->db->insert_id();
	}
	function file_upload_del($file_id){
		$this->db->where('file_id', $file_id);
		$this->db->delete('file_upload');
	}
	/*
	** QUERY ACTION FOR TBL user
	*/
	function user_add($post, $returnID = false, $sendEmail = false){
		$insert['utype_id'] = $this->input->post('type', true);
		$insert['user_name'] = $this->input->post('username', true);
		$insert['full_name'] = $this->input->post('full_name', true);
		$insert['email'] = $this->input->post('email', true);
		$insert['password'] = $this->auth->default_pass();
		// $insert['activation_code'] = md5($this->auth->encrypt_encode($insert['email'].$this->lib_order->current_date())); 
		$insert['activation_code'] = md5($this->auth->encrypt_encode($insert['email'] . date('Y-m-d H:i:s')));
		$insert['status'] = 0;
		
		$this->db->insert('user',$insert);
		if($sendEmail == true){
			$toEmail = $insert['email'];
			$toName = $insert['user_name'];
			$this->load->library('queue_email');
			$this->queue_email->email_account($toEmail, $toName, $insert['activation_code'], $this->auth->default_pass(true));	
		}
		if($returnID == true) return $this->db->insert_id();		
	}
	
	function access_update_user($where, $update){		
		$this->db->where($where);
		$this->db->update('access_permission',$update);
	}
	
	function user_update($uid, $post){
		if(isset($_POST['type'])) $update['utype_id'] = $this->input->post('type', true);
		$update['user_name'] = $this->input->post('username', true);
		$update['full_name'] = $this->input->post('fullname', true);
		// $update['re_update'] = (!empty($_POST['reupdate']) ? $_POST['reupdate'] : 0);
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
	/*
	** QUERY ACTION FOR TBL statorder
	*/
	function statorder_add($insert, $returnID = false){
		if(empty($insert)) return false;
			$this->db->insert('statorder',$insert);
		if($returnID == true) return $this->db->insert_id();
	}
	function bin_statorder($statorder_name, $moduleID){
		if(empty($statorder_name)) return false;
		$this->db->from('statorder')
				->select('statorder_id')
				->like('statorder_name', $statorder_name, 'both')
				->where('module_id', $moduleID); 
		$data = $this->db->get();
		if($data->num_rows() == 0){
			$insert['statorder_name'] = $statorder_name;
			$insert['module_id'] = $moduleID;
			return $this->statorder_add($insert, true);
		}else{
			return $data->row('statorder_id');
		}
	}
	
	/*
	** QUERY ACTION FOR TBL statcategory
	*/
	function statcategory_add($insert, $returnID = false){
		if(empty($insert)) return false;
			$this->db->insert('statcategory',$insert);
		if($returnID == true) return $this->db->insert_id();
	}
	function bin_statcategory($statcategory_name, $moduleID){
		if(empty($statcategory_name)) return false;
		$statcategory_name = strtolower($statcategory_name);
		$this->db->from('statcategory')
				->select('statcategory_id')
				->like('statcategory_name', $statcategory_name, 'both')
				->where('module_id', $moduleID); 
		$data = $this->db->get();
		if($data->num_rows() == 0){
			$insert['statcategory_name'] = $statcategory_name;
			$insert['module_id'] = $moduleID;
			return $this->statcategory_add($insert, true);
		}else{
			return $data->row('statcategory_id');
		}
	}
	/*
	** QUERY ACTION FOR TBL order_list
	*/
	function orderlist_add($order_id, $company_id){
		$order_id = $order_id;		
		$company_id = $company_id;	
		$this->db->query('INSERT INTO order_list (order_id, company_id) VALUES ('.$order_id.', '.$company_id.')
		ON DUPLICATE KEY UPDATE order_id = VALUES(order_id), company_id = VALUES(company_id)');
		return $order_id;
	}
	/*
	** QUERY ACTION FOR TBL companies
	*/
	function companies_add($company_name){
		//company_name
		$company_name = $this->db->escape_str($company_name);		
		$this->db->query('INSERT INTO companies (company_name) VALUES ("'.$company_name.'")
		ON DUPLICATE KEY UPDATE company_name = VALUES(company_name)');
		$new_id =  $this->db->insert_id();
		
		if($new_id == 0) return $this->db->query('SELECT company_id FROM companies WHERE company_name = "'.$company_name.'"')->row('company_id');
		else return $new_id;
	}
	/*
	** QUERY ACTION FOR TBL location
	*/
	function location_add($location_name){
		//location_name
		$location_name = $this->db->escape_str($location_name);
		$this->db->query('INSERT INTO location (location_name) VALUES ("'.$location_name.'")
		ON DUPLICATE KEY UPDATE location_name = VALUES(location_name)');
		$new_id =  $this->db->insert_id();
		
		if($new_id == 0) return $this->db->query('SELECT location_id FROM location WHERE location_name = "'.$location_name.'"')->row('location_id');
		else return $new_id;
	}
	/*
	** QUERY ACTION FOR TBL binloc_log
	*/
	function binloc_log_add($insert, $returnID  = false){
		if(empty($insert)) return false;
		$binloc_id = $insert['binloc_id'];
		$order_id = $insert['order_id'];
		$location_id = $insert['location_id'];
		$statorder_id = $insert['statorder_id'];
		$date_released = $insert['date_release'];
		$date_pickup = $insert['date_pickup'];
		$action = $insert['action'];
		$user_id = $this->auth->get_userid();
		
		$this->db->query('INSERT INTO binloc_log
		  (binloc_id, order_id, location_id, statorder_id, date_release, date_pickup, action, user_id)
		VALUES
		  ('.$binloc_id.', '.$order_id.', '.$location_id.', '.$statorder_id.', "'.$date_released.'", "'.$date_pickup.'", "'.$action.'", '.$user_id.')
			ON DUPLICATE KEY UPDATE
			binloc_id = VALUES(binloc_id),
			order_id = VALUES(order_id),
			location_id = VALUES(location_id),
			statorder_id = VALUES(statorder_id),
			date_release = VALUES(date_release),
			date_pickup = VALUES(date_pickup),
			action = VALUES(action),
			user_id = VALUES(user_id)
		');
		if($returnID == true) return $this->db->insert_id();
	}
	/*
	** QUERY ACTION FOR TBL binloc
	*/		
	function binloc_save($arrval, $frmUpload = false){
		if(empty($arrval)) return false;
		$company_id = $this->companies_add($arrval['company_name']);
		$order_id = $this->orderlist_add($arrval['order_id'], $company_id);
		$location_id = $arrval['location'];
		$statorder_id = $arrval['status'];
		
		if($frmUpload == true){
			if($location_id == 'returned to prod') $location_id = 'production vault';
			$location_id = $this->location_add($location_id);
			$statorder_id = $this->bin_statorder($statorder_id, 4);
		}

		if($location_id && $order_id && $statorder_id){			
		$arrval['date_released'] = $arrval['date_released'];
		$arrval['date_pickup'] = $arrval['date_pickup'];
		
		$order_id = $order_id;
		$location_id = $location_id;
		$arrval['date_released'] = $arrval['date_released'];
		$arrval['date_pickup'] = $arrval['date_pickup'];
		$this->db->query('INSERT INTO binloc
		  (order_id, location_id, statorder_id, date_release, date_pickup)
		VALUES
		  ('.$order_id.', '.$location_id.', '.$statorder_id.', "'.$arrval['date_released'].'", "'.$arrval['date_pickup'].'")
		ON DUPLICATE KEY UPDATE
			order_id = VALUES(order_id),
			location_id = IF(location_id = 3, location_id, VALUES(location_id)),
			statorder_id = IF(statorder_id = 7, statorder_id, VALUES(statorder_id)),
			date_release = VALUES(date_release),
			date_pickup = VALUES(date_pickup)
		'); //ITEM CANNOT BE UPDATED IF STATUS IS ALREADY PICKUP
		$insert['binloc_id'] = $this->db->insert_id();
		$insert['order_id'] = $order_id;
		$insert['location_id'] = $location_id;
		$insert['statorder_id'] = $statorder_id;
		$insert['date_release'] = $arrval['date_released'];
		$insert['date_pickup'] = $arrval['date_pickup'];
		$insert['action'] = 'add';
			$this->binloc_log_add($insert);
			$this->audit_save(4, 2, $insert['binloc_id'], $order_id);
		return true;
		}else return false;
	}
	function binloc_update($arrval){
		if(empty($arrval)) return false;
		if(empty($arrval['order_id']) || empty($arrval['id'])) return false;
		
			$update['order_id'] = $arrval['order_id'];
			if($arrval['location'] != '' && $arrval['location'] != 0) $update['location_id'] = $arrval['location'];
			if($arrval['status'] != '' && $arrval['status'] != 0) $update['statorder_id'] = $arrval['status'];
			if($arrval['date_released'] != '' && $arrval['date_released'] != 0) $update['date_release'] = $arrval['date_released'];
			if($arrval['date_pickup'] != '' && $arrval['date_pickup'] != 0) $update['date_pickup'] = $arrval['date_pickup'];
			$this->db->where('binloc_id',$arrval['id']);
		$this->db->update('binloc',$update);
		
		/* ADD BINLOC_LOG*/		
		$insert['binloc_id'] =  $arrval['id'];
		$insert['order_id'] =  $arrval['order_id'];
		$insert['location_id'] = $arrval['location'];
		$insert['statorder_id'] = $arrval['status'];
		$insert['date_release'] = $arrval['date_released'];
		$insert['date_pickup'] = $arrval['date_pickup'];
		$insert['action'] = 'update';
			$this->binloc_log_add($insert);			
			$this->audit_save(4, 3, $insert['binloc_id'], $insert['order_id']);
		return true;
	}
	/*
	** QUERY ACTION FOR TBL delsched
	*/
	function delsched_add($arrval, $date, $returnID  = false, $statorder_id = '', $statcategory_id = ''){
		$company_id = $this->companies_add($arrval['company_name']);		
		if($statorder_id == '') $statorder_id = $this->bin_statorder($arrval['status_order'], 5);
		if($statcategory_id == '') $statcategory_id = $this->bin_statcategory($arrval['del_mode'], 5);
		
		if($company_id && $statorder_id && $statcategory_id){
			$insert['company_id'] = $company_id;
			$insert['order_id'] = $order_id = $arrval['order_id'];
			$insert['amount'] = $arrval['amount'];
			$insert['account_manager'] = $arrval['account_manager'];
			$insert['del_instruc'] = $arrval['del_instruc'];
			$insert['del_mode'] = $statcategory_id;
			$insert['p_term'] = $arrval['p_term'];
			$insert['p_mode'] = $arrval['p_mode'];
			$insert['statorder_id'] = $statorder_id;						
			$insert['del_date'] = $arrval['del_date'];						
			$insert['remarks'] = $arrval['remarks'];				
			$insert['created_by'] = $this->auth->get_userid();
			
			$this->db->insert('delsched',$insert);
			$newID = $this->db->insert_id();
				if($returnID == true) return $newID;
				$this->audit_save(5, 2, $newID, $order_id); 
				/** insert delsched term */
				$this->delstring_add(1, $arrval['p_mode']); 
				$this->delstring_add(2, $arrval['p_term']); 
			return true; 
		}else return false;
	}	
	/*
	** QUERY ACTION FOR TBL delsched_string
	** mode [ 0: unknown, 1: payment_mode, 2: payment_term, 3: delivery_mode ]
	*/
	function delstring_add($mode = 0, $string){	
		if(empty($string)) return false;
		if($this->db->query('SELECT mode FROM delsched_string WHERE mode = '.$mode.' AND default_value = "'.$string.'"')->num_rows() == 0){
			$insert['mode'] = $mode;
			$insert['default_value'] = $string;
			$this->db->insert('delsched_string',$insert);
		}
		return $string;
	}
	/*
	** QUERY ACTION FOR TBL transac_temp
	*/
	function transac_temp_add($insert, $returnID = false){
		if(empty($insert)) return false;
		$this->db->insert('transac_temp',$insert);
		if($returnID == true) return $this->db->insert_id();
	}
	function transac_temp_update($uid, $update){
		if(empty($update)) return false;
		$this->db->where('transac_tempid',$uid);
		$this->db->update('transac_temp',$update);
	}
	/*
	** QUERY ACTION FOR TBL transac
	*/
	function transac_add($arrval, $returnID = false){
		if(empty($arrval)) return false;
		
		$company_id = $this->companies_add($arrval['company_name']);
		$order_id = $this->orderlist_add($arrval['order_id'], $company_id);
		if($company_id && $order_id){
			$insert['order_id'] = $order_id;
			$insert['company_id'] = $company_id;
			$insert['contact_person'] =	$arrval['contact_person'];
			$insert['created_by'] = $arrval['user_id'];
			
			/*CHECK BIN LOCATION STATUS*/
			if($arrval['prod_stat'] != 0){
				$insert['prod_stat'] = $arrval['prod_stat'];
				$insert['location_id'] = $arrval['location_id'];
				$insert['prod_time'] = $arrval['prod_time'];
			}
			
			/*CHECK IF RESOA IS NOT YET RETURNED STATUS*/
			if($arrval['fin_stat'] != 0){
				$insert['fin_stat'] = $arrval['fin_stat'];
				$insert['fin_time'] = $arrval['fin_time'];
			}
			
			$this->db->insert('transac',$insert);
			if($returnID == true) return $this->db->insert_id();
		}
	}
	function transac_update($transid, $update){
		if(empty($update)) return false;
		$this->db->where('transac_id',$transid);
		$this->db->update('transac',$update);
	}
	/*
	** QUERY ACTION FOR TBL advance_soa
	*/
	function adsoa_add($arrval, $returnID = false){
		if(empty($arrval)) return false;
		
		$company_id = $this->companies_add($arrval['company_name']);
		$order_id = $this->orderlist_add($arrval['order_id'], $company_id);
		if($company_id && $order_id){
			$insert['order_id'] = $order_id;
			$insert['company_id'] = $company_id;
			$insert['contact_person'] =	$arrval['contact_person'];
			$insert['created_by'] = $arrval['user_id'];
			
			$this->db->insert('advance_soa',$insert);
			if($returnID == true) return $this->db->insert_id();
		}
	}
	function adsoa_update($adsoa_id, $update){
		if(empty($update)) return false;
		$this->db->where('adsoa_id',$adsoa_id);
		$this->db->update('advance_soa',$update);
	}
	/*
	** QUERY ACTION FOR TBL release_order
	*/
	function reorder_add($transac_id, $returnID = false){
		$transac_id = $transac_id;
		$result = $this->db->query('SELECT date_received, order_id, date_release FROM transac WHERE transac_id = "'.$transac_id.'"');
		if($result->num_rows() != 0){
			$insert['order_id'] = $result->row('order_id');
			$insert['served_stat'] = 0;
			$insert['date_received'] = $result->row('date_received');
			$insert['date_release'] = $result->row('date_release');
			
			$this->db->insert('release_order',$insert);
			if($returnID == true) return $this->db->insert_id();
		}
	}
		function released_binloc($order_id, $date_release){
			if(empty($order_id)) return false;
			
			$order_id = $order_id;
			$result = $this->db->query('SELECT binloc_id, order_id, date_release, date_pickup FROM binloc WHERE order_id = "'.$order_id.'"');
			if($result->num_rows() != 0){
				$update['statorder_id'] = 7;
				$update['location_id'] = 3;
				$update['date_pickup'] = $date_release;
				$this->db->where('order_id',$order_id);
				$this->db->update('binloc',$update);
				
				/* ADD BINLOC_LOG*/		
				$insert['binloc_id'] =  $result->row('binloc_id');
				$insert['order_id'] =  $order_id;
				$insert['location_id'] = 3;
				$insert['statorder_id'] = 7;
				$insert['date_release'] = $result->row('date_release');
				$insert['date_pickup'] = $date_release;
				$insert['action'] = 'update';
					$this->binloc_log_add($insert);			
					$this->audit_save(4, 3, $insert['binloc_id'], $insert['order_id']);
			}
			return true;
		}
	function reorder_update($reorder_id, $update){
		if(empty($update)) return false;
		$this->db->where('reorder_id',$reorder_id);
		$this->db->update('release_order',$update);
	
		$this->ajax_update(); //update session for AJAX REQUEST
	}
	/*
	** QUERY ACTION FOR TBL transac_comment
	*/
	function transac_comment_add($insert){
		$this->db->insert('transac_comment',$insert);
	}
	/*
	** QUERY ACTION FOR TBL resoa
	*/
	function resoa_add($insert, $returnID  = false){
		$checkSOA = $this->db->query('SELECT resoa_id FROM resoa WHERE transac_id = '.$insert['transac_id'].' AND order_id = '.$insert['order_id'].' ')->num_rows();
		if($checkSOA == 0){
			$insert['date_return'] = '0000-00-00 00:00:00';
			$insert['date_received'] = '0000-00-00 00:00:00';		
			$this->db->insert('resoa',$insert);
			if($returnID == true) return $this->db->insert_id();
		}
	}
	function resoa_update($resoa_id, $update){
		if(empty($update)) return false;
		$this->db->where('resoa_id',$resoa_id);
		$this->db->update('resoa',$update);
	}
	
	/*
	** QUERY ACTION FOR TBL co_orderinfo
	*/
	function co_orderinfo_add($insert, $returnID = false){
		if(empty($insert)) return false;
		$this->db->insert('co_orderinfo',$insert);
		if($returnID == true) return $this->db->insert_id();
	}	
	function co_orderinfo_update($co_id, $update){
		if(empty($update)) return false;
		$this->db->where('co_orderinfo_id',$co_id);
		$this->db->update('co_orderinfo',$update);
	}
	
	/*
	** QUERY ACTION FOR TBL co_transac
	*/
	function cotransac_add($arrval, $returnID = false){
		if(empty($arrval)) return false;
		
		$company_id = $this->companies_add($arrval['company_name']);
		$order_id = $this->orderlist_add($arrval['order_id'], $company_id);
		if($company_id && $order_id){
			$insert['order_id'] = $order_id;
			$insert['company_id'] = $company_id;
			$insert['co_orderinfo_id'] = $arrval['co_orderinfo_id'];
			$insert['created_by'] = $arrval['user_id'];
			
			$this->db->insert('co_transac',$insert);
			if($returnID == true) return $this->db->insert_id();
		}
	}
	function cotransac_update($cotransid, $update){
		if(empty($update)) return false;
		$this->db->where('co_transac_id',$cotransid);
		$this->db->update('co_transac',$update);
	}
	/*
	** QUERY ACTION FOR TBL statreason
	*/
	function statreason_add($insert, $returnID = false){
		if(empty($insert)) return false;
			$this->db->insert('statreason',$insert);
		if($returnID == true) return $this->db->insert_id();
	}	
	function status_add_data($insert, $tblName, $returnID = false){
		if(empty($insert) || empty($tblName)) return false;
			$this->db->insert($tblName,$insert);
		if($returnID == true) return $this->db->insert_id();
	}
	
	/*
	** QUERY ACTION FOR TBL cor_transac
	*/
	function cortransac_add($arrval, $returnID = false){
		if(empty($arrval)) return false;
		
		$company_id = $this->companies_add($arrval['company_name']);
		$order_id = $this->orderlist_add($arrval['order_id'], $company_id);
		if($company_id && $order_id){
			$insert['order_id'] = $order_id;
			$insert['company_id'] = $company_id;
			$insert['co_orderinfo_id'] = $arrval['co_orderinfo_id'];
			$insert['created_by'] = $arrval['user_id'];
			
			$this->db->insert('cor_transac',$insert);
			if($returnID == true) return $this->db->insert_id();
		}
	}
	function cortransac_update($cotransid, $update){
		if(empty($update)) return false;
		$this->db->where('cor_transac_id',$cotransid);
		$this->db->update('cor_transac',$update);
	}

	public function is_password_recently_used($uid, $new_pass)
    {

        $this->db->select('*');
        $this->db->from('password_history');
        $this->db->where('user_id', $uid);
		$this->db->order_by('id', 'desc'); 
		$this->db->limit(12); 
        $query = $this->db->get();

		$result = $query->result();

        foreach ($result as $row) {
			log_message('error', 'pass: ' . $row->password_hash . ' | ' . $this->auth->encrypt_encode($new_pass, true));
            if ($row->password_hash == $this->auth->encrypt_encode($new_pass, true)) {
                return true;
            }
        }
        return false;
    }
	
	function access_add($insert, $returnID = false){		
		if(empty($insert)) return false;
			$this->db->insert('access_permission',$insert);
		if($returnID == true) return $this->db->insert_id();
	}

	public function update_password($uid, $new_pass)
    {
        // Add new password to password history
        $password_data = [
            'user_id' => $uid,
            'password_hash' => $this->auth->encrypt_encode($new_pass, true),
			'last_password_change_date' => date('Y-m-d')
        ];
        $this->db->insert('password_history', $password_data);
    }

	public function get_last_password_change_date($uid)
    {
        $this->db->select('last_password_change_date');
        $this->db->from('password_history');
        $this->db->where('user_id', $uid);
		$this->db->order_by('id', 'desc'); // Order by id in descending order
		$this->db->limit(1); // Limit the result to 1 row
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->last_password_change_date;
        }

        return null;
    }
}

