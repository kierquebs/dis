<?php
/**
 * AUTOMATED PROCESS
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
		echo 'CHECKING AUTOMATE REIMBURSEMENT';
	}
	
	/**
	 * DELETE ALL DATA FROM NAV INTERFACEFILE
	 */
	public function clearNavDIR(){
		$files = glob("C:/xampp/htdocs/nav_interface/*");
		array_map('unlink', array_filter( (array) array_merge($files))); 
		/*foreach($files as $file){ // iterate files
			if(is_file($file)) unlink($file); //delete file
		}*/
	}
	
	/**
	 * GET ALL DATA FROM CSV FILE
	 */
	 public function manual_branch(){
		 exit();
		 //441144,1907,455,Aura Premier,
			$BRANCH_ID = '441144';
			$MERCHANT_ID = '1907';
			$CP_ID = '455';
			$BRANCH_NAME = 'Aura Premier';
			$AFFILIATEGROUPCODE = '';
		 
				$where['BRANCH_ID'] =  $BRANCH_ID;
				$where['MERCHANT_ID'] =  $MERCHANT_ID;	
				$where['CP_ID'] =  $CP_ID;					
			$recordResult = $this->Sys_model->v_branches($where);	
			
			print_r($recordResult->result()); die();
			
			if(!empty($where['BRANCH_ID']) && !empty($where['MERCHANT_ID']) && !empty($where['CP_ID'])){					
					$insertDB['CP_ID'] = $where['CP_ID'];
					$insertDB['MERCHANT_ID'] = $updateCP['MERCHANT_ID'] = $where['MERCHANT_ID'];
					$insertDB['BRANCH_ID'] = $where['BRANCH_ID'];
					if(!empty($BRANCH_NAME)) $insertDB['BRANCH_NAME'] = $BRANCH_NAME;
					if(!empty($AFFILIATEGROUPCODE)) $insertDB['AFFILIATEGROUPCODE'] = $AFFILIATEGROUPCODE;
				
				if($recordResult == 0){
					echo "SUCCESS INSERT BRANCHES : <br /> <pre>";
					$this->Sys_model->i_branches($insertDB);
				}else{	
					echo "SUCCESS UPDATE BRANCHES : <br /> <pre>";					
					print_r($insertDB);
					$this->Sys_model->u_branches($where, $insertDB);
				}			
			 }
	 }
	
	public function upload_branches(){
		$map = directory_map('./to_upload/branches/', FALSE, TRUE);
		
		if(!is_array($map) || count($map) == 0){
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
		
				
				$where = '';
				for($i=0;$i<count($result_data);$i++){
						$where['BRANCH_ID'] =  $result_data[$i]['BRANCH_ID'];
						$where['MERCHANT_ID'] =  $result_data[$i]['MERCHANT_ID'];	
						$where['CP_ID'] =  $result_data[$i]['CP_ID'];					
					$recordResult = $this->Sys_model->v_branches($where, true);	
					
					//Additional Validation to MERCHANT_ID and COREPASS_ID not null / 0 value
					if(!empty($where['BRANCH_ID']) && !empty($where['MERCHANT_ID']) && !empty($where['CP_ID']) && $where['MERCHANT_ID'] != 0 && $where['CP_ID'] != 0){					
							$insertDB['CP_ID'] = $where['CP_ID'];
							$insertDB['MERCHANT_ID'] = $updateCP['MERCHANT_ID'] = $where['MERCHANT_ID'];
							$insertDB['BRANCH_ID'] = $where['BRANCH_ID'];
							if(!empty($result_data[$i]['BRANCH_NAME'])) $insertDB['BRANCH_NAME'] = $result_data[$i]['BRANCH_NAME'];
						
						if($recordResult == 0){
							if(!empty($result_data[$i]['AFFILIATEGROUPCODE'])) $insertDB['AFFILIATEGROUPCODE'] = $result_data[$i]['AFFILIATEGROUPCODE'];
							echo "SUCCESS INSERT BRANCHES : <br /> <pre>";
							$this->Sys_model->branch_merchant($insertDB);
							$this->Sys_model->i_branches($insertDB);
						}else{	
							$insertDB['AFFILIATEGROUPCODE'] = $result_data[$i]['AFFILIATEGROUPCODE'];//update affgroupcode
							echo "SUCCESS UPDATE BRANCHES : <br /> <pre>";					
							print_r($insertDB);
							$this->Sys_model->u_branches($where, $insertDB);
						}					
						//update cp_merchant table -> merchant_id 
						//if(!empty($where['MERCHANT_ID'])) $this->Sys_model->u_merchant(array('CP_ID' => $result_data[$i]['CP_ID']),$updateCP);	
					}else {
						echo "FAILED INSERT BRANCHES : <br /> <pre>";				
						print_r($where);
						echo "</pre> <br /> ";	
					}						
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
		
		if(!is_array($map) || count($map) == 0){
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
		
				echo "<pre>";
				for($i=0;$i<count($result_data);$i++){ 
					$where['MERCHANT_ID'] = $result_data[$i]['MERCHANT_ID'];
					
					if(!empty($result_data[$i]['MERCHANT_ID']) && !empty($result_data[$i]['TYPE'])){
						$recordResult = $this->Sys_model->v_cutoff($where, true);
						if($recordResult == 0){
							echo "SUCCESS INSERT PAYMENT CUT-OFF : <br />";
							$this->Sys_model->i_cutoff($result_data[$i]);
						}else{
							echo "SUCCESS UPDATE PAYMENT CUT-OFF : <br />";
							$this->Sys_model->u_cutoff($where['MERCHANT_ID'], $result_data[$i]);
						}
					}else echo "FAILED PAYMENT CUT-OFF : <br />";					
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
	 
	/**
	 * Regen PA details to correct the record 
	 * BE CAREFUL USING THIS MODULE AS IT CAN OVERWRITE THE EXISTING RECORD DIRECTLY TO THE DATABASE
	 */
	public function regen_padetail(){ 
		//echo 'BE CAREFUL USING THIS MODULE AS IT CAN OVERWRITE THE EXISTING RECORD'; die();
		if(!isset($_GET['pa']) || empty($_GET['pa'])){
			return 'ERROR'; exit();
		}
		//$whereBranch = "recon.PA_ID <> 0 ";		
		$whereBranch = "recon.PA_ID <> 0 and recon.PA_ID IN (".$_GET['pa'].")";	   
		$getPCBranchesRow =  $this->Sys_model->getPCBranch_PA_RE($whereBranch, false); 
		if($getPCBranchesRow->num_rows() != 0){
			$update_detail = $insert_detail = '';
			foreach($getPCBranchesRow->result() as $row){
				$PA_MerchantFee = $row->MerchantFee;
				$PA_PayeeDayType = $row->PayeeDayType;
				$PA_PayeeQtyOfDays = $row->PayeeQtyOfDays;
				$PA_VAT = $row->vatcond;
				
				if($row->merAFFCODE <> $row->brAFFCODE){
					$whereAFFCODE['CP_ID'] =  $row->CPID;
					$whereAFFCODE['AffiliateGroupCode'] = trim($row->brAFFCODE);
					$getAFFCODE =  $this->Sys_model->v_agreement($whereAFFCODE, false);	
					if($getAFFCODE->num_rows() <> 0){
						$rowAFFCODE = $getAFFCODE->row();													
						$PA_MerchantFee = $rowAFFCODE->MerchantFee;
						$PA_PayeeDayType = $rowAFFCODE->PayeeDayType;
						$PA_PayeeQtyOfDays = $rowAFFCODE->PayeeQtyOfDays;
						$PA_VAT = $rowAFFCODE->VATCond;						
						//echo $row->merAFFCODE.' - '.$row->brAFFCODE.' '.$row->MID.' '.$row->BRANCH_ID.' = '.$rowAFFCODE->AGREEMENT_ID.' '.$PA_MerchantFee.' <br />';
					}
				}
				
				$VAT = $this->my_lib->checkVAT($PA_VAT);				
				$totalFV = $row->totalAmount;
				$percentMF = $this->my_lib->convertMFRATE($PA_MerchantFee, true); 
				$MF = $this->my_lib->computeMF($totalFV, $percentMF);
				
				/*check if PA DETAILS already exist*/
				$whereD['RECON_ID'] = $row->RECON_ID;
				$whereD['BRANCH_ID'] = $row->BRANCH_ID;
				$PA_ID = $whereD['PA_ID'] = $row->PA_ID;	
				
				/*BUILD PA DETAIL INFO*/		
				$update_detail['RATE'] = $insert_detail['RATE'] = $percentMF;
				$update_detail['NUM_PASSES'] = $insert_detail['NUM_PASSES'] = $row->totalPasses;
				$update_detail['TOTAL_FV'] = $insert_detail['TOTAL_FV'] = $totalFV;
				$update_detail['MARKETING_FEE'] = $insert_detail['MARKETING_FEE'] = $MF; 
				$update_detail['VAT'] = $insert_detail['VAT'] = $this->my_lib->computeVAT($totalFV, $percentMF, $VAT);
				$update_detail['NET_DUE'] = $insert_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);
				
				$checkDetail = $this->Sys_model->v_paD($whereD);
				if($checkDetail->num_rows() == 0){				
					$insert_detail['PA_ID'] = $PA_ID;
					$insert_detail['RECON_ID'] = $row->RECON_ID;		
					$insert_detail['BRANCH_ID'] = $row->BRANCH_ID;						
					$insert_detail['DATE_CREATED'] = $this->my_lib->current_date();	
					$this->Sys_model->i_paD($insert_detail);
					echo 'ADDITIONAL ROW FOR:'.$PA_ID.'<br />';
				}else{
					$whereU['PA_DID'] = $checkDetail->row('PA_DID');
					$this->Sys_model->u_paD($whereU, $update_detail);
					echo 'UPDATE ROW FOR:'.$checkDetail->row('PA_DID').' '.$PA_ID.'<br />';
				}
			}	
			//update PA header
			$u_paH['MERCHANT_FEE']= $PA_MerchantFee;
			$u_paH['vatcond']= $PA_VAT;
			$this->Sys_model->u_paH(array('PA_ID'=>$PA_ID), $u_paH);
		}
	}

	/**
	 * uPermission
	 * will update all user role permission depending on the permission library
	 */
	public function uPermission(){	
		$this->load->model('User_model');
		$this->load->model('Action_model');
		
		$all_user = $this->User_model->user_all();
		foreach($all_user as $temp_row){ 	
			$user_name = $temp_row->user_name;		
			$user_id = $temp_row->user_id;
			$utype_id = $temp_row->utype_id;
					
			for($i=1;$i<=7;$i++){ 
				$where['access_permission.acc_id'] = $i; 	
				$where['user.user_id'] = $user_id;
				$insertAcc = $this->my_layout->user_permission($utype_id, $i); //get permission list
	
				echo $user_name.' - '. $i;	
				$checkNumRow = $this->User_model->useraccess($where, '', true);
				if($checkNumRow <> 0){	
					$checkRow = $this->User_model->useraccess($where, '', 	false);
					$this->Action_model->access_update($checkRow[0]->id, $insertAcc); 
					echo ' update ';
				}else{
					$insertAcc['user_id'] =  $user_id;
					$insertAcc['created_by'] = 1;
					$this->Action_model->access_add($insertAcc);
					echo ' insert ';
				}	
				echo '<br />';			
			}	
		}
		echo 'DONE';		
	}
	
}
