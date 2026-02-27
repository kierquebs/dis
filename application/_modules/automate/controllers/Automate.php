<?php
/**
 * REDEEMPTION CONTROLLER
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Automate extends MX_Controller {
	private $DATE_NOW;

	public function	__construct(){
		parent::__construct();	
			$this->DATE_NOW = $this->my_lib->current_date();
			$this->load->model('Sys_model');
	}

	/**
	 * ---------------------------------------------------------------------------
	 * PUBLIC FUNCTIONS
	 * ---------------------------------------------------------------------------
	 */
	public function index(){
		//echo 'REIMBURSEMENT';
	}
	
	/**
	 * GET ALL DATA FROM CSV FILE
	 */
	public function upload_branches(){
		$map = directory_map('./to_upload/branches/', FALSE, TRUE);
		
		if(count($map) == 0){
			log_message('info', 'REDEEM:: EMPTY FOLDER');
			echo 'EMPTY FOLDER'; exit();
		}
		
		for($x=0; $x<count($map); $x++){
			$fileArr = array();	$return = false;
			$fileArr['filname']  = $map[$x];
			$fileArr['module']  = 'branches'; 		
			$this->load->library('upload_file');

			/**
			 * @todo  get all data insert to DB
			 */
			$result_data = $this->upload_file->read_file($fileArr['filname'], $fileArr['module']);	
			if(is_array($result_data)){	
		
				echo "SUCCESS INSERT BRANCHES : <br /> <pre>";
				$where = '';
				for($i=0;$i<count($result_data);$i++){
						$where['BRANCH_ID'] =  $result_data[$i]['BRANCH_ID'];
						$where['MERCHANT_ID'] =  $result_data[$i]['MERCHANT_ID'];	
						$where['CP_ID'] =  $result_data[$i]['CP_ID'];					
					$recordResult = $this->Sys_model->v_branches($where, true);	
					
						$insertDB['CP_ID'] = $where['CP_ID'];
						$insertDB['MERCHANT_ID'] = $updateCP['MERCHANT_ID'] = $where['MERCHANT_ID'];
						$insertDB['BRANCH_ID'] = $where['BRANCH_ID'];
						if(!empty($result_data[$i]['BRANCH_NAME'])) $insertDB['BRANCH_NAME'] = $result_data[$i]['BRANCH_NAME'];
					
					if($recordResult == 0) $this->Sys_model->i_branches($insertDB);
					else{				
						print_r($insertDB);
						$this->Sys_model->u_branches($where, $insertDB);
					}					
					//update cp_merchant table -> merchant_id
					//$this->Sys_model->u_merchant(array('CP_ID' => $result_data[$i]['CP_ID']),$updateCP);					
				}	
				echo '</pre>';
				//delete file
				unlink(dirname($_SERVER["SCRIPT_FILENAME"])."/to_upload/".$fileArr['module'] ."/".$fileArr['filname']);
				$this->Sys_model->i_auditUpload($fileArr['module'], $fileArr['filname']); //save audit
			}else echo "INSERT STATUS : FAILED";
		}	
	}
	
	/**
	 * GET ALL DATA FROM CSV FILE
	 */
	public function upload_cutoff(){
		$map = directory_map('./to_upload/cutoff/', FALSE, TRUE);
		
		if(count($map) == 0){
			log_message('info', 'REDEEM:: EMPTY FOLDER');
			echo 'EMPTY FOLDER'; exit();
		}
		
		for($x=0; $x<count($map); $x++){
			$fileArr = array();	$return = false;
			$fileArr['filname']  = $map[$x];
			$fileArr['module']  = 'cutoff'; 		
			$this->load->library('upload_file');

			/**
			 * @todo  get all data insert to DB
			 */
			$result_data = $this->upload_file->read_file($fileArr['filname'], $fileArr['module']);	
			if(is_array($result_data)){	
		
				echo "SUCCESS INSERT PAYMENT CUT-OFF : <br /> <pre>";
				for($i=0;$i<count($result_data);$i++){
					$where['MERCHANT_ID'] = $result_data[$i]['MERCHANT_ID'];
					
					$recordResult = $this->Sys_model->v_cutoff($where, true);
					if($recordResult == 0) $this->Sys_model->i_cutoff($result_data[$i]);
					else $this->Sys_model->u_cutoff($where['MERCHANT_ID'], $result_data[$i]);
					
					print_r($result_data[$i]);
				}	
				echo '</pre>';
				//delete file
				unlink(dirname($_SERVER["SCRIPT_FILENAME"])."/to_upload/".$fileArr['module'] ."/".$fileArr['filname']);
				$this->Sys_model->i_auditUpload($fileArr['module'], $fileArr['filname']); //save audit
			}else echo "INSERT STATUS : FAILED";
		}	
	}	
	
	/**
	 * UPDATE NAV DATA FROM PA TABLE
	 */
	 public function update_nav(){
		/*
		* GET ALL DATA PA HEADER AND DETAILS 
		*/
		$paResult = $this->Sys_model->getPA();
		if($paResult->num_rows() != 0){
			foreach($paResult->result() as $data){
				$whereNav['CP_ID'] = $data->CP_ID;
				$whereNav['MERCHANT_ID'] = $data->MERCHANT_ID;
				$whereNav['PA_ID'] = $data->PA_ID;
				$whereNav['RECON_ID'] = $data->RECON_ID;
				$whereNav['PROD_ID'] = $data->PROD_ID;
				
				//check if NAV HEADER is already existing
				$navResult = $this->Sys_model->v_navH($whereNav, '', 'NAVH_ID');				
				if($navResult->num_rows() == 0){					
					$insertNavH['CP_ID'] = $row->CP_ID;
					$insertNavH['MERCHANT_ID'] = $row->MERCHANT_ID;
					$insertNavH['PA_ID'] = $row->PA_ID;
					$insertNavH['RECON_ID'] = $row->RECON_ID;
					$insertNavH['PROD_ID'] = $row->PROD_ID;
					$insertNavH['TotalAmount'] = $row->TotalAmount;
					
					$insertNavH['DateofReceipt'] = '';//unknown source
					$insertNavH['ExpectedDueDate'] = '';//unknown source
					
					$NAVH_ID = $this->Sys_model->i_navH($insertNavH); //insert data
				}else $NAVH_ID = $navResult->row('NAVH_ID');
				
				$this->_loadNavDetail($NAVH_ID, $data); //process NAV DETAIL
			}
		}
	}	
		private function _loadNavDetail($NAVH_ID, $arrResult){
			$whereNavD['NAVH_ID'] = $NAVH_ID;
			//check if NAV DETAIL is already existing
			$navNumRow = $this->Sys_model->v_navD($whereNavD, true);
			if($navNumRow == 0){
				//insert data here
				print_r($arrResult); //check array output				
				
				$insertNavD['NAVH_ID'] = $NAVH_ID;
				$insertNavD['PROD_ID'] = $arrResult->PROD_ID;
				$insertNavD['BillItem'] = $arrResult->MARKETING_FEE;
				$insertNavD['FaceValue'] = $arrResult->TOTAL_FV;
				$insertNavD['OutputVAT'] = ''; //unknown source
				$insertNavD['VATCond'] = $arrResult->VAT;
				$this->Sys_model->i_navD($insertNavD); //insert data
			}
		}
	 
	
}
