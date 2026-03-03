<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** This module handles all reversal (refund) transactions */

class Reversal extends MX_Controller {
	//private $MODULE_ID;	
	private $DATE_NOW;

	public function	__construct(){
		parent::__construct();	
		//$this->MODULE_ID = 9;
		//if(!$this->auth->check_session()) redirect('login');
		//$this->form_validation->run($this);
		//if($this->auth->role_all($this->MODULE_ID) == false) redirect('404_override');
			$this->DATE_NOW = $this->my_lib->current_date();
			$this->load->model('Sys_model');
	}

	public function index(){
		echo 'Reversal Module';
	}
	
	/**
	 * INSERT RECORD - TEMP REVERSAL
	 */
	private function i_tempReverse($arr_data, $UploadID, $filename){
		if(empty($arr_data)) {
			log_message('error', 'REVERSAL:: Fail to insert record from filename - '.$filename);
			return false;
		}
		$temp_insert = $arr_data;
		$temp_insert['UPLOAD_ID'] = $UploadID;
		$TEMP_REFUND_ID = $this->Sys_model->i_tmprefund($temp_insert); //insert temp record
		return $TEMP_REFUND_ID;
	}

	private function u_tempReverse($TEMP_REFUND_ID, $message, $update = ''){
		if(empty($TEMP_REFUND_ID))  return false;

		$update['ERROR_MESSAGE'] = $message;
		$this->Sys_model->u_tmprefund(array('TEMP_REFUNDID'=>$TEMP_REFUND_ID), $update); //update temp record
		
		log_message('error', 'REVERSAL :: '.$message);
	}

	/**
	 * AUTOMATED UPLOAD FROM CSV FILE
	*/
	public function upload_reversal(){
		$map = directory_map('./to_upload/reversal/', FALSE, TRUE);
		if(!is_array($map) || count($map) == 0){
			log_message('info', 'REVERSAL:: EMPTY FOLDER');
			echo 'EMPTY FOLDER'; exit();
		}
		$cronMessage = '';
		for($x=0; $x<count($map); $x++){			
			$return = false;			
			$fileArr = array();

			$fileArr['filname']  = $map[$x];
			$fileArr['module']  = 'reversal'; 		

			/**
			 * Check if filename exist
			 */
			$get_AuditUpload = $this->Sys_model->v_auditUpload(array('module_name'=>$fileArr['module'], 'file_name'=>$fileArr['filname']), true);			
			
			if($get_AuditUpload == 0){
				/**
				 * @todo read file - uploaded file
				 */
				$this->load->library('upload_file');			
				$result_data = $this->upload_file->read_file($fileArr['filname'], $fileArr['module']);
				
				if(is_array($result_data) && COUNT($result_data) != 0){	
					/**
					 * @todo insert audit upload name
					 */					
					$get_UploadID = $this->Sys_model->i_auditUpload($fileArr['module'], $fileArr['filname']); //save audit
					
					if(!empty($get_UploadID)){					
		
						echo "Do not close this window - ONGOING INSERT RECORD : <br />";
						
						for($i=0;$i<COUNT($result_data);$i++){
							$DigitalSettlementType = $u_refund = $i_refund = '';	
							$arrData = $result_data[$i]; 			
							$get_TempID = $this->i_tempReverse($arrData, $get_UploadID, $fileArr['filname']); //insert temp reverse

							if($get_TempID <> false){
								// $check_Refund = $this->Sys_model->v_refund(array('REDEEM_ID'=>$arrData['REDEMPTION_API_TRANSACTION_ID']), TRUE); 
								
								// July 9, 2024 - Fix for https://pluxee.atlassian.net/browse/PHSD-2481
								$check_Refund = $this->Sys_model->v_refund(array(
													'REDEEM_ID'=>$arrData['REDEMPTION_API_TRANSACTION_ID'],
													'PROD_ID'=>$arrData['PROD_ID'],
													), TRUE); 
								
								//CHECK REFUND TBL :: if below list "Exist"
								if($check_Refund == 0){	
									//CHECK PAYMENT_CUTOFF DigitalSettlementType
									$check_pcf = $this->Sys_model->v_cutoff(array('MERCHANT_ID'=>$arrData['MERCHANT_ID']), false, 'MERCHANT_ID, DigitalSettlementType', 'MERCHANT_ID');
									if($check_pcf->num_rows() <> 0){
										$rowPCF = $check_pcf->row();
										$DigitalSettlementType = $rowPCF->DigitalSettlementType;
									}

										$check_ReconWhere['REDEEM_ID'] = $arrData['REDEMPTION_API_TRANSACTION_ID'];
										$check_ReconWhere['PROD_ID'] = $arrData['PROD_ID'];
										$check_ReconWhere['RECON_ID'] = $arrData['RECON_API_TRANSACTION_ID'];
										$check_ReconWhere['MERCHANT_ID'] = $arrData['MERCHANT_ID'];
										$check_ReconWhere['BRANCH_ID'] = $arrData['BRANCH_ID'];
									$check_Recon = $this->Sys_model->v_checkRecon($check_ReconWhere, false, 'ID, RECON_ID, REDEEM_ID, REFUND_ID, MERCHANT_ID, BRANCH_ID, PA_ID');
									$check_ReconNum = $check_Recon->num_rows();

										
									$this->Sys_model->branch_merchant($arrData);//add branch_merchant to the list
									//CHECK RECON TBL :: REDEEM_ID & PROD_ID & RECON_ID "Exist"  
									if($check_ReconNum <> 0 && $DigitalSettlementType == ''){
										//check if num_rows has more than 1 record
										if($check_ReconNum > 1){ 
											//update temp_reverse error message
											$this->u_tempReverse($get_TempID, 'Has more than 1 record of recon'); 
										}else{
											$rowRecon = $check_Recon->row();
											//CHECK IF Record REFUND_ID (RECON TBL) <> 0
											if($rowRecon->REFUND_ID <> 0){												
												//update temp_reverse error message
												$this->u_tempReverse($get_TempID, 'Reversal details is already exist'); 
											}else{	
												//Check if fields matches to the uploaded (CSV) temp records												
												if($rowRecon->RECON_ID == $arrData['RECON_API_TRANSACTION_ID'] && $rowRecon->MERCHANT_ID == $arrData['MERCHANT_ID'] && $rowRecon->BRANCH_ID == $arrData['BRANCH_ID']){						
												/*
												-- !! condition  !!
													* REDEEM : STAGE -> REDEEMED -> REVERSED
													* REFUND : STAGE -> REVERSED
													* RECON  : NOT NULL
												*/
													//insert refund record
													$i_refund['REDEEM_ID'] 					= $rowRecon->REDEEM_ID;
													$i_refund['REVERSAL_TRANSACTION_ID'] 	= $arrData['REVERSAL_TRANSACTION_ID'];
													$i_refund['TRANSACTION_ID'] 			= $arrData['TRANSACTION_ID'];													
													$i_refund['RECON_ID']					= $rowRecon->RECON_ID;
													$i_refund['REVERSAL_MODE'] 				= $arrData['REVERSAL_MODE']; 
													$i_refund['PROD_ID'] 					= $arrData['PROD_ID']; 
													$i_refund['REDEEM_STATUS'] 				= 'REVERSED';  // *** REDEEM STAGE *** //
													$i_refund['MERCHANT_ID'] 				= $rowRecon->MERCHANT_ID;
													$i_refund['BRANCH_ID'] 					= $rowRecon->BRANCH_ID;	
													$i_refund['UPLOAD_ID'] 					= $get_UploadID;
													$i_refund['REVERSAL_DATE_TIME'] 		=  $this->my_lib->checkSyncDate($arrData['REVERSAL_DATE_TIME']);
													$i_refund['DATE_CREATED'] 				= $this->my_lib->setDate();		
													$i_refund['RECON_TBL_ID'] 				= $rowRecon->ID;							
													
													$u_refund['REFUND_ID'] = $this->Sys_model->i_refund($i_refund);
													if(!empty($u_refund['REFUND_ID'])){
														$this->Sys_model->u_recon_arr(array('ID' => $rowRecon->ID), $u_refund); //insert refund record
														$this->Sys_model->u_tmprefund(array('TEMP_REFUNDID'=>$get_TempID), $u_refund); //update temp record		
													}	
												}else{
													//update temp_reverse error message
													$this->u_tempReverse($get_TempID, 'Fields RECON_ID, MERCHANT_ID, BRANCH_ID did not Match to the uploaded RECON Transactions'); 
												}												
											}						
										}
									}else{										
										$REDEEM_TBL_ID = $check_RedeemNum =  '';											
										/*
										-- !! condition  !!
											* REDEEM : NULL
											* REFUND : STAGE -> INVALID
											* RECON  : NULL
										*/	
										$i_refund['REDEEM_ID'] = $arrData['REDEMPTION_API_TRANSACTION_ID'];
										$i_refund['PROD_ID'] = $arrData['PROD_ID']; 
										$i_refund['REVERSAL_TRANSACTION_ID'] 	= $arrData['REVERSAL_TRANSACTION_ID'];
										$i_refund['TRANSACTION_ID'] 			= $arrData['TRANSACTION_ID'];													
										$i_refund['RECON_ID'] 					=  $arrData['RECON_API_TRANSACTION_ID'];
										$i_refund['REVERSAL_MODE'] 				= $arrData['REVERSAL_MODE']; 
										$i_refund['MERCHANT_ID']				= $arrData['MERCHANT_ID'];  
										$i_refund['BRANCH_ID']					= $arrData['BRANCH_ID'];	
										$i_refund['UPLOAD_ID'] 					= $get_UploadID;
										$i_refund['REVERSAL_DATE_TIME'] 		=  $this->my_lib->checkSyncDate($arrData['REVERSAL_DATE_TIME']);
										$i_refund['DATE_CREATED'] 				= $this->my_lib->setDate();										
										

										if($DigitalSettlementType == ''){
											$i_refund['REDEEM_STATUS']  		= 'VOID'; 
											$u_refund['ERROR_MESSAGE']			= 'Reversed Item - No Record Found in Recon Tbl';
										}else{
											/**
											 * check REDEEM Tbl if REFUND details exist
											 */
												$check_RedeemWhere['REDEEM_ID'] = $arrData['REDEMPTION_API_TRANSACTION_ID'];
												$check_RedeemWhere['PROD_ID'] = $arrData['PROD_ID'];
												$check_RedeemWhere['MERCHANT_ID'] = $arrData['MERCHANT_ID'];
												$check_RedeemWhere['BRANCH_ID'] = $arrData['BRANCH_ID'];
												$check_RedeemWhere['MERCHANT_ID'] = $arrData['MERCHANT_ID'];
												$check_RedeemWhere['TRANSACTION_ID'] = $arrData['TRANSACTION_ID'];
												$check_RedeemWhere['REFUND_ID'] = '';
											$check_Redeem = $this->Sys_model->v_checkRedeem($check_RedeemWhere, false, 'ID');
											$check_RedeemNum = $check_Redeem->num_rows();
											if($check_RedeemNum <> 0){
												$rowRedeem = $check_Recon->row();
												$REDEEM_TBL_ID	= $rowRedeem->ID;
												$i_refund['REDEEM_TBL_ID'] = $REDEEM_TBL_ID;
											}	
										}
										
										$u_refund['REFUND_ID'] = $this->Sys_model->i_refund($i_refund);										
										if(!empty($u_refund['REFUND_ID'])){
											if($DigitalSettlementType <> ''){												
												if($check_RedeemNum == 0) {													
													$this->Sys_model->u_redeem(array('ID' => $REDEEM_TBL_ID), $u_refund); //insert refund record
												}else{
													$u_refund['ERROR_MESSAGE'] = 'Reversed Item - No Record Found in Redemption Tbl';
												}
											}
											$this->Sys_model->u_tmprefund(array('TEMP_REFUNDID'=>$get_TempID), $u_refund); //update temp record
										}										
									}	
								}else{
									//update temp_reverse error message
									$this->u_tempReverse($get_TempID, 'RECORD ALREADY EXISTS'); 
								}	
								$cronMessage .= 'File Uploaded: '.$fileArr['filname'].'\n';								
							}
						}									
					}else{
						echo 'REVERSAL:: Fail to Upload File - '.$fileArr['filname'];
						log_message('error', 'REVERSAL:: Fail to Upload File - '.$fileArr['filname']); //LOG fail upload file
					}
				}else{				
					$message =  "INSERT STATUS :: FAILED ".( (COUNT($result_data) == 0 || empty($result_data)) ? "NO CONTENT FILE" : "INVALID FORMAT")." - ".$fileArr['filname']." <br /> ";
					echo $message;
					log_message('error', 'REVERSAL :: '.$message);
				}
			}else{
				echo 'REVERSAL:: Filename already exist -'.$fileArr['filname'];
				log_message('info', 'REVERSAL:: Filename already exist -'.$fileArr['filname']);
			}
			//delete file
			unlink(dirname($_SERVER["SCRIPT_FILENAME"])."/to_upload/".$fileArr['module'] ."/".$fileArr['filname']);
		}	
		
		echo 'DONE UPLOAD - PROCEED TO FORCE TAGGING';			
		$this->my_lib->cronLog('reversal', $cronMessage); // generate cron log
		/** reversal_adjustment	**/					
		//$this->Sys_model->bulk_REVERSED();	
		//$this->Sys_model->bulk_INVALID();	
		//$this->Sys_model->bulk_VOID();			
	}

	/**
	 * Update RECON table redeem_tbl_id
	 * 	
	 */
	public function refund_ReconTBL(){
		$this->load->model('Process_model');
			$refund_ReconTBL =  $this->Process_model->refund_ReconTBL(); 
		echo 'DONE: Reversal Assign IDs - refund_ReconTBL ';
	}
	
	public function refund_RedeemTBL(){
		$this->load->model('Process_model');
			$refund_RedeemTBL =  $this->Process_model->refund_RedeemTBL(); 
		echo 'DONE: Reversal Assign IDs - refund_RedeemTBL';
	}

	/*--------- TEMPORARY HIDE THIS FUNCTION --------- */

	/**
	 * MANUAL_FORM has different csv file
	 */
	public function manual_form(){
		echo 'Deactivated Function'; exit;

		$data['success'] = $error_arr = $data['tbl_return'] = $data['error'] = '';
		$error = $success = 0;
		if(isset($_POST['file_upload'])){
			$file = $_FILES['refund_file']['tmp_name'];
			if (empty($file)){
				$data['error'] = 'Empty file!'; 
			}else{		
				$file_count = count(file($file));
				if($file_count == 0){					
					$data['error'] = 'Empty content!';
				}else if($_FILES['refund_file']['type'] <> 'text/plain'){					
					$data['error'] = 'Invalid file type!';
				}else{
					$file = fopen($file ,"r");
					$error_arr = array();	
					$audit_id = $this->Sys_model->i_auditUpload('refund', $_FILES['refund_file']['name']); //save audit
					while(! feof($file)){
						$errorRow = '';
						$redeem_id = trim(fgets($file)); //item by line
						$whereRecon = 'REDEEM_ID = "'.$redeem_id.'"';
						$recordResult = $this->Sys_model->v_checkRecon($whereRecon, false, 'ID, RECON_ID, REDEEM_ID, REFUND_ID, MERCHANT_ID, BRANCH_ID, PA_ID');
							$recordResultNum = $recordResult->num_rows();
						$checkRefund = $this->Sys_model->v_refund($whereRecon, false, 'REDEEM_ID, PA_ID');
							$checkRefundNum = $checkRefund->num_rows();
						//check if status if already
						if($recordResultNum <> 0){
							if($recordResultNum > 1){
								//check if num_rows has more than 1 record
								$error++;
								$errorRow = new stdClass(); 
								$errorRow->redeem_id = $redeem_id;
								$errorRow->status  = 'Has more than 1 record of recon';
							}else{
								//check if refund has value
								$rowName = $recordResult->row();
								if($rowName->REFUND_ID == 0 && $checkRefundNum == 0){
									$i_refund['redeem_id'] = $rowName->REDEEM_ID;
									$i_refund['recon_id'] = $rowName->RECON_ID;
									$i_refund['merchant_id'] = $rowName->MERCHANT_ID;
									$i_refund['branch_id'] = $rowName->BRANCH_ID;								
									$i_refund['upload_id'] = $audit_id;
									$i_refund['user_id'] = $this->auth->get_userid(); 
									$i_refund['date_created'] = $this->my_lib->setDate();								
									$u_recon['refund_id'] = $this->Sys_model->i_refund($i_refund);
									$this->Sys_model->u_recon_arr(array('ID' => $rowName->ID), $u_recon); //insert refund record
									$success++;
								}else{
									$refName = $checkRefund->row();
									//already refunded
									$error++;
									$errorRow = new stdClass();
									$errorRow->redeem_id = $redeem_id;
									if($refName->PA_ID <> 0) $errorRow->status = 'Already processed for PA ID: '.$refName->PA_ID;
									else $errorRow->status = 'Already Refunded';
								}							
							}
						}else{
							$error++;
							$errorRow = new stdClass(); 
							$errorRow->redeem_id = $redeem_id;
							$errorRow->status  = 'No recon transaction found! STATUS: REDEEM';
						}
						if(!empty($errorRow)) $error_arr[] = $errorRow;
					}
					fclose($file);	
					//check if success count is not 0
					if($success == 0) $this->Sys_model->d_auditUpload($audit_id);
					else $data['success'] =  $this->my_layout->alertMsg(6, 'Successfully Upload! ('.$success.')', true);
					$data['invalid_num'] = $error;
				}
			}
			$_POST = array();
		}	
		$data['tbl_return'] = $error_arr;
		$data['css'][] = 'queue.css';
		$data['js'][] = 'main.js';
		$data['js'][] = 'home.js';			
		$data['cssDT'][] = '1.10.16/css/jquery.dataTables.min.css';
		$data['jsDT'][] = '1.10.16/js/jquery.dataTables.min.js';	
		$this->my_layout->layout_nav('refund/index', $data);
	}
	/*--------- --- --------- */
}




