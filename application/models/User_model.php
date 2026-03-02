<?php
class User_model extends CI_Model{
	public function __construct(){
		parent::__construct();
	}
	/*
	/*
	** QUERY FOR TBL user
	* @param $where [array]
	* @param $count [get total count]
	* @param $page [array limit & offset]
	*/
	function user_all($where = '', $count = false, $page = ''){
		$this->db->from('user');	
		if(!empty($where))$this->db->where($where);
		if($count == true){
			$this->db->select('user_id');
			$data =  $this->db->count_all_results();
		}else{
			if(!empty($page)) $this->db->limit($page['limit'], $page['offset']);
			$data =  $this->db->get()->result();
		}			
		return $data;
	}
	function check_user($arr, $count = false, $select = ''){
		$this->db->where($arr)
				->from('user');	
		if($count == true){
			$this->db->select('user_id');
			$data =  $this->db->count_all_results();
		}else{
			if(!empty($select))$this->db->select($select);
			$data =  $this->db->get();
		}			
		return $data;
	}
	function check_password($user_id, $password, $count = false){
		$arr['user_id']  = $user_id;
		$arr['password']  = $this->auth->encrypt_encode($password, true);
		return $this->check_user($arr, $count);
	}
	
	/*
	** QUERY FOR TBL user join TBL utype
	* @param $where [array]
	* @param $count [get total count]
	* @param $page [array limit & offset]
	*/
	function user_info($where, $count = false){
		$this->db->from('user')
			->join('utype', 'utype.utype_id  = user.utype_id')
			->where($where);		
		if($count == true){
			$this->db->select('user.user_id');
			$data = $this->db->count_all_results();
		}else{
			$data = $this->db->get();
		}	
		return $data;
	}
	function search_user($where = '', $count = false, $page = ''){
		$this->db->from('user');
		if(!empty($where))$this->db->where($where);
		if($count == true){
			$this->db->select('user_id');
			$data =  $this->db->count_all_results();
		}else{
			$this->db->select('user.*');	
			if(!empty($page)) $this->db->limit($page['limit'], $page['offset']);
			$data =  $this->db->get()->result();
		}			
		return $data;
	}

	/*
	** QUERY FOR TBL user join TBL access_role
	* @param $where [array]
	* @param $count [get total count]
	* @param $page [array limit & offset]
	*/
	function module_all($where = '', $count = false, $page = '', $select = ''){
		$this->db->from('access_role');	
		if(!empty($where))$this->db->where($where);
		if($count == true){
			$this->db->select('acc_id');
			$data =  $this->db->count_all_results();
		}else{
			if(!empty($page)) $this->db->limit($page['limit'], $page['offset']);
			if(!empty($select)) $this->db->select($select);
			$data =  $this->db->get()->result();
		}
		return $data;
	}
	/*
	** QUERY FOR TBL user join TBL access_role
	* @param $where [array]
	* @param $count [get total count]
	* @param $page [array limit & offset]
	*/
	public function useraccess($where = '', $groupBy = '', $count = false, $select = ''){
		if(!empty($groupBy)) $groupBy = 'access_permission.user_id';
		$this->db->from('access_permission')
			->join('user', 'user.user_id  = access_permission.user_id')
			->group_by($groupBy);
		if(!empty($where))$this->db->where($where);
		if($count == true){
			$this->db->select('access_permission.user_id');
			$data =  $this->db->count_all_results();
		}else{
			if(!empty($select)) $this->db->select($select);
			else $this->db->select('access_permission.*');
			$data =  $this->db->get()->result();
		}			
		return $data;
	}
		/* CHECK MODULE ACCESS
		* @param $where [array]
		* @param $count [get total count]
		* @param $page [array limit & offset]
		*/
		public function check_access($moduleID, $where_action = ''){	
			$where['user.user_id'] = $this->auth->get_userid();			
			$where['access_permission.acc_id'] = $moduleID; 
			if(!empty($where_action))$this->db->where($where_action);
				$checkRow = $this->useraccess($where, '', true);
			if($checkRow !=0) return true;
			else return false;
		}
		public function available_role($uID, $where = array(), $select = '', $count = false){
			if (!is_array($where)) {
				$where = array();
			}
			$where['access_permission.user_id'] = $uID;
			$this->db->from('access_permission')
				->join('access_role', 'access_role.acc_id  = access_permission.acc_id')
				->join('user', 'user.user_id  = access_permission.user_id');
			if(!empty($where))$this->db->where($where);
			
			if($count == true){
				$this->db->select('access_permission.id');
				$data =  $this->db->count_all_results();
			}else{
				if(!empty($select)) $this->db->select($select);
				else $this->db->select('access_role.acc_code, access_permission.*');
				$data =  $this->db->get()->result();
			}			
			return $data;			
		}
}

