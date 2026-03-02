<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MX_Controller{
	private $CI;
	public $REDIRECT_URL;

	function __construct(){
		$this->CI = &get_instance();
		$this->CI->load->config('config');
		$this->CI->load->library('session');
		$this->REDIRECT_URL = $this->CI->config->item('base_url');
	}
	function default_pass($textonly = false){
		$defTxt = 'p@55123';
		//$defTxt = 12345;

		if($textonly != false) return $defTxt;
		else return $this->encrypt_encode($defTxt, true);
	}
	function encrypt_encode($msg, $sha1 = false){
			if(empty($msg)) return false;
		if($sha1 == true) return sha1($msg);
		else return $this->encryption->encrypt($msg);
	}
	function ecrypt_decode($msg){
			if(empty($msg)) return false;
		$encrypted_string = $this->encryption->decrypt($msg);
		return $encrypted_string;
	}
	/**
     * Session Checking
	 * @param session_userdata
	 * @return session status
     */
	public function check_session(){
		$sodexo_dis = $this->CI->session->userdata('sodexo_dis');
		if($sodexo_dis)return true;
		else return false;
	}
	public function check_admin($redirect=true){
		if($this->get_usertype() != 1){
			if($redirect == true )redirect('login');
			else return false;
		}
		return true;
	}
	public function role_all($moduleID){
		$all_access = $this->user_allaccess();
			if(empty($all_access)) $all_access = array();
		if(in_array($moduleID, $all_access)) return true;
		else return false;
	}
	/**
     * Create Session
	 * @param $user_info
	 * @return set session
     */
	public function set_session($user_info){
		$session['sodexo_dis'] = true;
		$session['dis_username'] = $user_info['name'];
		$session['dis_userid'] = $user_info['id'];
		$session['dis_all_access'] = $user_info['access_module'];
		$session['dis_def_page'] = $user_info['def_page'];
		$session['dis_ajax_session'] = $user_info['ajax_session'];
		$session['pa_arr'] = '';
		$session['dis_usertype'] = $user_info['type'];
		$this->CI->session->set_userdata($session);
	}
	public function set_userdata($dataName, $newVal){
		if(empty($dataName) || empty($newVal)) return;
		$session[$dataName] = $newVal;
		$this->CI->session->set_userdata($session);
	}

	public function unset_userdata($dataName){
		$unset_array[$dataName] = '';
		$this->CI->session->unset_userdata($unset_array);
	}
	/**
     * Destroy Session
	 * clear all session userdata
	 * @return destroy session
     */
	function out_session(){
		$unset_array['sodexo_dis'] = false;
		$unset_array['dis_username'] = '';
		$unset_array['dis_userid'] = '';
		$unset_array['dis_all_access'] = '';
		$unset_array['dis_def_page'] = '';
		$unset_array['dis_ajax_session'] = '';
		$unset_array['pa_arr'] = '';
		$unset_array['dis_usertype'] = '';
		$this->CI->session->unset_userdata($unset_array);
		$this->CI->session->sess_destroy();
	}
	/**
     * GET SESSION USERNAME
	 * @return username
     */
	function get_usertype(){
		return $this->CI->session->userdata('dis_usertype');
	}
	function get_username(){
		return $this->CI->session->userdata('dis_username');
	}
	function get_userid(){
		return $this->CI->session->userdata('dis_userid');
	}
	function get_defpage(){
		return $this->CI->session->userdata('dis_def_page');
	}
	function user_allaccess(){
		return $this->CI->session->userdata('dis_all_access');
	}
	function ajax_session(){
		return $this->CI->session->userdata('dis_ajax_session');
	}
	function get_paarr(){
		return $this->CI->session->userdata('pa_arr');
	}
}
