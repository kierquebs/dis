<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sdx_email extends MX_Controller{	
	private $FR_EMAIL;
	private $FR_NAME;
	public function __construct(){
		parent::__construct();
		$this->load->library('email');
		$this->FR_EMAIL = getenv('MAIL_FROM') !== false ? getenv('MAIL_FROM') : 'gift@notifications.pluxee.ph';
		$this->FR_NAME = getenv('MAIL_FROM_NAME') !== false ? getenv('MAIL_FROM_NAME') : 'Pluxee Admin';
	}
	function test(){
		$this->email->from($this->FR_EMAIL, $this->FR_NAME);
		//$this->email->to('arced.remollo@sodexo.com');
		$this->email->subject('Email Test');
		$this->email->message('Testing the email class.');

		$this->email->send();

		echo $this->email->print_debugger();
	}
	function reset_password($toEmail, $toName, $passWORD = ''){	
		if(empty($toEmail) || empty($toName)) return false;
		$link = base_url().'login';
		if(empty($passWORD)) $passWORD = $this->auth->default_pass(true); //AhYqAZ
		
		$message = "Hello ".$toName.", <br /><br /><br />
			Thank you for verification!  <br /><br />
			Here is your new generated password. <br /><br />
			Password: ".$passWORD." <br />
			Email Address: ".$toEmail." <br /><br />
			You may log in here: <a href='".$link."' target='_blank'>GO TO LOGIN PAGE</a> <br /><br />
			Regards, <br />
			Administrator <br />
			<br /><br /><br />
			----------------------------------------------------------------------
			<br /><br /><br />
			System generated email, please do not reply.";
		
		$this->email->from($this->FR_EMAIL, $this->FR_NAME);
		$this->email->to($toEmail, $toName);
		//$this->email->bcc('arced.remollo@sodexo.com');
		$this->email->subject('Pluxee Account Information - Digital Interface Service (DIS)');
		$this->email->message($message);		
		$this->email->send();	
		echo $this->email->print_debugger();
		return true;
	}
	function email_account($toEmail, $toName, $activation, $passWORD = ''){	
		if(empty($toEmail) || empty($toName) || empty($activation)) return false;
		if(empty($passWORD)) $passWORD = $this->auth->default_pass(true); //AhYqAZ		
		$link = base_url().'login/activate/'.$activation;
		
		$message = "Pluxee Digital Interface Service (DIS) <br /><br /> 
			Your account has been successfully created.<br /><br />
			Log on to ".$link." and use your credentials below to start using the system.<br /><br />
			Username: ".$toName." <br />
			Password: ".$passWORD." <br /><br /><br />
			After successful Login, please proceed to change the default password.<br /><br /><br />	
			Thank you.<br /><br />
			Administrator <br />
			<br /><br />
			----------------------------------------------------------------------
			<br /><br />
			System generated email, please do not reply.";
		
		$this->email->to($toEmail, $toName);
		//$this->email->bcc('arced.remollo@sodexo.com');
		$this->email->from($this->FR_EMAIL, $this->FR_NAME);
		//$this->email->from($this->FR_EMAIL, $this->FR_NAME);
		$this->email->subject('Pluxee Account Information - Digital Interface Service (DIS)');
		$this->email->message($message);		
		$this->email->send();	
		echo $this->email->print_debugger(); die(); 
		return true;
	}
	
}
