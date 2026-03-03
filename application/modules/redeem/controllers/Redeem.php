<?php
/**
 * REDEEMPTION CONTROLLER
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Redeem extends MX_Controller {
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
		//echo 'REDEEMPTION MODULE <br />';
		$this->upload_redeem();
	}
	
	/**
	 * GET ALL DATA FROM CSV FILE
	 */

	public function upload_redeem(){
		$map = directory_map('./to_upload/redemption/', FALSE, TRUE);
		
		if(!is_array($map) || count($map) == 0){
			log_message('info', 'REDEEM:: EMPTY FOLDER');
			echo 'EMPTY FOLDER'; exit();
		}
		
		$cronMessage = '';
		for($x=0; $x<count($map); $x++){
			
			$fileArr = array();	$return = false;
			$fileArr['filname']  = $map[$x];
			$fileArr['module']  = 'redemption'; 		
			$this->load->library('upload_file');

			/**
			 * @todo  get all data insert to DB
			 */
			$result_data = $this->upload_file->read_file($fileArr['filname'], $fileArr['module']);	
			if(is_array($result_data) && COUNT($result_data) != 0){	
		
				echo "Do not close this window - ONGOING PROCESSING <br /> <pre>";
				for($i=0;$i<count($result_data);$i++){
					$recordID = $DigitalSettlementType = $u_refundWhere = $whereRefund = $where = '';

					//CHECK PAYMENT_CUTOFF DigitalSettlementType
					$check_pcf = $this->Sys_model->v_cutoff(array('MERCHANT_ID'=>$result_data[$i]['MERCHANT_ID']), false, 'MERCHANT_ID, DigitalSettlementType', 'MERCHANT_ID');
					if($check_pcf->num_rows() <> 0){
						$rowPCF = $check_pcf->row();
						$DigitalSettlementType = $rowPCF->DigitalSettlementType;
					}

					$where['REDEEM_ID'] = $result_data[$i]['REDEEM_ID'];
					$where['PROD_ID'] = $result_data[$i]['PROD_ID'];
					$where['TRANSACTION_VALUE'] = $result_data[$i]['TRANSACTION_VALUE']; // EARP 2024 MARCH 14
					$where['MERCHANT_ID'] = $result_data[$i]['MERCHANT_ID'];	
					$where['BRANCH_ID'] = $result_data[$i]['BRANCH_ID'];		
					
					// Check redemption table if exists
					$recordResult = $this->Sys_model->v_checkRedeem($where, false, 'ID, PA_ID, REFUND_ID');
					
					// additional checker for redemption backup table
					$recordResultBckup = $this->Sys_model->v_checkRedeemBackup($where, false, 'ID, PA_ID, REFUND_ID');
					
					// additional checker for old redemption table
					$recordResultOldRedemption = $this->Sys_model->v_checkRedeemOld($where, false, 'ID');
					
					$wheref['REDEEM_ID'] = $result_data[$i]['REDEEM_ID'];
					$wheref['PROD_ID'] = $result_data[$i]['PROD_ID'];
					$wheref['MERCHANT_ID'] = $result_data[$i]['MERCHANT_ID'];	
					$wheref['BRANCH_ID'] = $result_data[$i]['BRANCH_ID'];	

					$whereRefund = $wheref;
					$whereRefund['TRANSACTION_ID'] = $result_data[$i]['TRANSACTION_ID']; 
					$whereRefund['PA_ID'] = 0;
					$whereRefund['REDEEM_TBL_ID'] = 0;
					$whereRefund['RECON_TBL_ID'] = 0;
					$checkRefund = $this->Sys_model->v_refund($whereRefund, false, 'REFUND_ID');
						$checkRefundNum = $checkRefund->num_rows();
					
					if(
						$recordResult->num_rows() == 0 
						&& $recordResultBckup->num_rows() == 0  // Check redemption backup before ingestion
						&& $recordResultOldRedemption->num_rows() == 0  // Check old redemption before ingestion
						&& !empty($where['REDEEM_ID']) 
						&& !empty($where['PROD_ID']))
						{					
						/** INSERT DB **/
						/*** for REVERSAL TBL Insert Record ***/							
						if($checkRefundNum <> 0 && $DigitalSettlementType <> ''){
							$rowRefund = $checkRefund->row();
							$result_data[$i]['REFUND_ID'] = $rowRefund->REFUND_ID;							
						}	
						$result_data[$i]['SOURCE_FILE'] = $fileArr['filname'];//add source file	
							$this->Sys_model->branch_merchant($result_data[$i]);//add branch_merchant to the list
						
						$recordID = $this->Sys_model->i_redeem($result_data[$i]);//insert redeem data
						//print_r($result_data[$i]);
			
						if($checkRefundNum <> 0 && $DigitalSettlementType <> ''){		
							$u_refundWhere['REFUND_ID'] = $result_data[$i]['REFUND_ID'];
							$u_refundWhere['REDEEM_ID'] = $result_data[$i]['REDEEM_ID'];							
							$u_refundWhere['REDEEM_TBL_ID'] = 0;	
							$this->Sys_model->u_refundWhere($u_refundWhere, array('REDEEM_TBL_ID' => $recordID)); //update refund
						}
					}else{
						if($checkRefundNum <> 0 && $DigitalSettlementType <> ''){
							/*** for REVERSAL TBL Update Record ***/	
							$rowRedeem = $recordResult->row();
							if($recordResult->num_rows() == 1 && $rowRedeem->PA_ID == 0 && $rowRedeem->REFUND_ID == 0){
								$rowRefund = $checkRefund->row();
								$this->Sys_model->u_redeem(array('ID' => $rowRedeem->ID), array('REFUND_ID'=>$rowRefund->REFUND_ID)); //update redemption 
								$this->Sys_model->u_refundWhere(array('REFUND_ID' => $rowRefund->REFUND_ID), array('REDEEM_TBL_ID' => $rowRedeem->ID)); //update refund
							}
						}
					}
				}		
				//echo '</pre>';
				$this->Sys_model->i_auditUpload($fileArr['module'], $fileArr['filname']); //save audit
				$cronMessage .= 'File Uploaded: '.$fileArr['filname'].'\n';
	
			}else{				
				$message =  "INSERT STATUS :: FAILED ".( (COUNT($result_data) == 0 || empty($result_data)) ? "NO CONTENT FILE" : "INVALID FORMAT")." - ".$fileArr['filname']." \n ";
				log_message('error', 'REDEEM :: '.$message);
				echo "<br /> REDEEM :: ".$message;
			}
			//delete file
			unlink(dirname($_SERVER["SCRIPT_FILENAME"])."/to_upload/".$fileArr['module'] ."/".$fileArr['filname']);
		}

		echo 'DONE UPLOAD - PROCEED TO FORCE TAGGING';
		
		$this->my_lib->cronLog('redemption', $cronMessage); // generate cron log
							
		/** reversal_adjustment	**/						 
		//$this->Sys_model->bulk_RECONCILED();
		//$this->Sys_model->bulk_REVERSED();	
		//$this->Sys_model->bulk_INVALID();	
		//$this->Sys_model->bulk_VOID();
	}
	
	
}
