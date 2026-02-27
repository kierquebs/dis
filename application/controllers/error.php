<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Error extends CI_Controller 
{
    public function __construct() 
    {
        parent::__construct(); 
    } 

    public function index() 
    { 
        $this->output->set_status_header('404'); 
		$data['content'] =  'error_404'; // View name 
        $this->load->view('error/html/error_404', $data);//loading in my template 
    } 
} 
