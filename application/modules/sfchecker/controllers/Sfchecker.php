<?php
/**
 * MAIN CONTROLLER
 * modules that can update corepass records directly to its database records
 * as data being updated it is not log to the audit trail of corepass actions
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Sfchecker extends MX_Controller {
	public function	__construct(){

	parent::__construct();	
		$this->load->model('Corepass_model');
	}
	
	public function index(){		
		$resultRow = $data['resultRow'] = $data['result'] = '';
		
		// check for corepass agreement 
		if(isset($_POST['delpoint_id']) && $_POST['agr_input'] != null){
			$result = $this->Corepass_model->getQueryDelPoint($_POST['agr_input']); 
			$resultRow = $result->num_rows();
		}else if(isset($_POST['sproleag_id'])&& $_POST['agr_input'] != null){
			$result = $this->Corepass_model->getQueryAgrSpRole($_POST['agr_input']); 
			$resultRow = $result->num_rows();
		}else if(isset($_POST['people_id'])&& $_POST['acc_input'] != null){			
			$result = $this->Corepass_model->getQueryAcctPeopleID($_POST['acc_input']); 
			$resultRow = $result->num_rows();
		}else if(isset($_POST['sprole_id']) && $_POST['acc_input'] != null){			
			$result = $this->Corepass_model->getQueryAcctSpRoleID($_POST['acc_input']); 
			$resultRow = $result->num_rows();
		}else if(isset($_POST['contact_id']) && $_POST['acc_input'] != null){			
			$result = $this->Corepass_model->getQueryContactDataID($_POST['acc_input']); 
			$resultRow = $result->num_rows();
		}else if(isset($_POST['company_info']) && $_POST['acc_input'] != null){	
			//echo $_POST['company_info']; die();
			$result = $this->Corepass_model->getQueryCompanyInfo($_POST['acc_input']); 
			$resultRow = $result->num_rows();
		}
		
		
			
		if($resultRow != 0)$data['result'] = $result;
		$data['resultRow'] = $resultRow; 	
		
		$this->load->view('index', $data);	
	}
}
