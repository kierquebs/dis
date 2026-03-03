<?php
/**
 * RECONCILLATION CONTROLLER
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Recon extends MX_Controller {
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
		//echo 'RECONCILLATION MODULE <br />';
		$this->upload_recon();
	}
	
	/**
	 * GET ALL DATA FROM CSV FILE
	 * // upload_recon/evoucher_recon -> for Evoucher Upload
	 */

	public function upload_recon($otherFolder = 'reconciliation'){
		$map = directory_map('./to_upload/'.$otherFolder.'/', FALSE, TRUE);
		
		if(!is_array($map) || count($map) == 0){
			log_message('info', 'RECON:: EMPTY FOLDER');
			echo 'EMPTY FOLDER'; exit();
		}
			
		for($x=0; $x<count($map); $x++){
			$fileArr = array();	$return = false;
			$fileArr['filname']  = $map[$x];
			//$fileArr['module']  = $this->my_lib->check_filename($fileArr['filname'], 'reconciliation'); //interim solution			
			$fileArr['module']  = $this->my_lib->check_foldername($otherFolder, 'reconciliation'); //interim solution		 		
			$this->load->library('upload_file');
			

			/**
			 * @todo  get all data insert to DB
			*/
			$result_data = $this->upload_file->read_file($fileArr['filname'], $fileArr['module'], $otherFolder);	
			if(is_array($result_data) && COUNT($result_data) != 0){		
			
				echo "Do not close this window - ONGOING INSERT RECORD : <br /> <pre>";
				for($i=0;$i<count($result_data);$i++){
					$DigitalSettlementType = $checkRefund = $recordResult = $whereRefund = $where = '';
				
					/** *** UPDATE Reversal Table *** **/						
						$whereRefund2['REDEEM_ID'] = $result_data[$i]['REDEEM_ID'];
						$whereRefund2['PROD_ID'] =  $result_data[$i]['PROD_ID'];
						$whereRefund2['RECON_ID'] = '';
							$updateRefund['RECON_ID'] = $result_data[$i]['RECON_ID'];
						$this->Sys_model->u_refundWhere($whereRefund2, $updateRefund); //** UPDATE REFUND **//						
					/**************************************************/
					
						$where['RECON_ID'] = $result_data[$i]['RECON_ID'];
						$where['REDEEM_ID'] = $result_data[$i]['REDEEM_ID'];
						$where['PROD_ID'] = $result_data[$i]['PROD_ID'];
					$recordResult = $this->Sys_model->v_checkRecon($where, false, 'ID, PA_ID, REFUND_ID');

						$whereRefund = $where;
						$whereRefund['RECON_ID'] = $result_data[$i]['RECON_ID']; 
						$whereRefund['PA_ID'] = 0;
						$whereRefund['REDEEM_TBL_ID'] = 0;
						$whereRefund['RECON_TBL_ID'] = 0;
					$checkRefund = $this->Sys_model->v_refund($whereRefund, false, 'REFUND_ID');
						$checkRefundNum = $checkRefund->num_rows();
					
					//CHECK PAYMENT_CUTOFF DigitalSettlementType
					$check_pcf = $this->Sys_model->v_cutoff(array('MERCHANT_ID'=>$result_data[$i]['MERCHANT_ID']), false, 'MERCHANT_ID, DigitalSettlementType', 'MERCHANT_ID');
					if($check_pcf->num_rows() <> 0){
						$rowPCF = $check_pcf->row();
						$DigitalSettlementType = $rowPCF->DigitalSettlementType;
					}

					if($recordResult->num_rows() == 0 && !empty($where['RECON_ID'])  && !empty($where['REDEEM_ID']) && !empty($where['PROD_ID'])){
						/*** for REVERSAL TBL Insert Record ***/							
						if($checkRefundNum <> 0 && $DigitalSettlementType <> ''){
							$rowRefund = $checkRefund->row();
							$result_data[$i]['REFUND_ID'] = $rowRefund->REFUND_ID;							
						}						
						$result_data[$i]['SOURCE_FILE'] = $fileArr['filname'];//add source file						
							$this->Sys_model->branch_merchant($result_data[$i]);//add branch_merchant to the list
						$recordID = $this->Sys_model->i_recon($result_data[$i]);
						print_r($result_data[$i]);	
						
						if($checkRefundNum <> 0 && $DigitalSettlementType <> ''){					
							$this->Sys_model->u_refundWhere(array('REFUND_ID' => $result_data[$i]['REFUND_ID']), array('RECON_TBL_ID' => $recordID)); //update refund
						}
					
					}else{
						if($checkRefundNum <> 0 && $DigitalSettlementType <> ''){
							/*** for REVERSAL TBL Update Record ***/	
							$rowRecon = $recordResult->row();
							if($recordResult->num_rows() == 1 && $rowRecon->PA_ID == 0 && $rowRecon->REFUND_ID == 0){
								$rowRefund = $checkRefund->row();
								$this->Sys_model->u_recon_arr(array('ID' => $rowRecon->ID), array('REFUND_ID'=>$rowRefund->REFUND_ID)); //update recon 
								$this->Sys_model->u_refundWhere(array('REFUND_ID' => $rowRefund->REFUND_ID), array('RECON_TBL_ID' => $rowRecon->ID)); //update refund
							}
						}
					}
				}	
				echo '</pre>';	
				$this->Sys_model->i_auditUpload($fileArr['module'], $fileArr['filname']); //save audit
				//$this->Sys_model->assign_redeemTblID();  //assign redeem_id to recon table
				
			}else{
				$message =  "INSERT STATUS :: FAILED ".( (COUNT($result_data) == 0 || empty($result_data)) ? "NO CONTENT FILE" : "INVALID FORMAT")." - ".$fileArr['filname']." <br /> ";
				log_message('error', 'RECON :: '.$message);
				echo "<br /> RECON :: ".$message;
			}			
			//delete file 
			unlink(dirname($_SERVER["SCRIPT_FILENAME"])."/to_upload/".$otherFolder."/".$fileArr['filname']);
		}
		
		echo 'DONE UPLOAD - PROCEED TO FORCE TAGGING';	
		/** reversal_adjustment	**/	
		//$this->Sys_model->bulk_RECONCILED();	
		//$this->Sys_model->bulk_REVERSED();	
		//$this->Sys_model->_bulk_INVALID();		
	}

	
	
}

