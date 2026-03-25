<?php
/**
 * MAIN CONTROLLER
 * modules that can update corepass records directly to its database records
 * as data being updated it is not log to the audit trail of corepass actions
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Syscore extends MX_Controller {
	public function	__construct(){

	parent::__construct();	
		if($this->auth->check_session()) redirect('login');	
		
		$this->load->model('Sys_model');
		$this->load->model('Corepass_model');
	}
	
	public function logger(){
		$this->load->library('my_log');
		$this->my_log->write_log('test', 'testing');
	}
	
	public function check_arr(){
		$entry = '';
		for($i=2;$i<=5;$i++){
			$entry[$i] = array(
								'MERCHANT_NAME' => "A".$i,
								'MERCHANT_ID' => "B".$i,
								'BRANCH_ID' => "C".$i,
								'POS_ID' => "D",
								'POS_TXN_ID' => "E",
								'PROD_ID' => "F",
								'TRANSACTION_DATE_TIME' => "G",
								'TRANSACTION_ID' => "H",
								'VOUCHER_CODE' => "I",
								'TRANSACTION_VALUE' => "J",
								'STAGE' => "K",
								'REDEEM_ID' => "L",
								'PAYMENT_MODE' =>"M"
							);
			$entry[$i]['SOURCE_FILE'] = 'SOURCE'.$i;
		}		
		echo '<pre>';print_r($entry);echo '</pre>';
	}
	
	public function index(){
		$this->load->view('index');	
	}


	/**
	 * GET COREPASS COMPANY DETAILS and INSERT IN DIS
	 * @return void
	 */
	public function get_corepass_account(){
		$where = '';
		$arr = array();

		//check if there's an existing merchant 
		$existingAcct = $this->Sys_model->v_merchant('','','cp_id');
		$totlExisting = $existingAcct->num_rows();
		
		if($totlExisting != 0){
			foreach($existingAcct->result() as $row){ $arr[]=$row->cp_id; }
			//$arr = implode(" , ",$arr);
		}

		/*
			ContactPerson
			ContactNumber
			AffiliateGroupCode
			PayeeQtyOfDays
			PayeeDayType
			PayeeComments
		*/
		
		//get corepass data merchant
		$getMerchant = $this->Corepass_model->getQueryClient($where);			
		if( $getMerchant->num_rows() != 0){
			$arrMerchant = array();
			$fields = $getMerchant->list_fields(); 
			foreach($getMerchant->result() as $data){
				$arrMerchantData = array();
				foreach ($fields as $field){
					if($field <> 'AGREEMENT_ID'){
						if($field == 'ADDRESS'){
							$address = $this->Corepass_model->getQueryAddress($data->$field)->result();
							$arrMerchantData[$field] = $address[0]->ADDRESS;
						}else if($field == 'VATCOND'){
							$arrMerchantData[$field] = ($data->$field == 2 ? 'Exempt' : 'Taxable');
						}else $arrMerchantData[$field] = $data->$field;	
					}else $AGREEMENT_ID = $data->$field;				
				}
				$contact = $this->Corepass_model->getQueryAgreementRole($AGREEMENT_ID);
				if($contact->num_rows() <> 0){
					$contact = $contact->result();
					$arrMerchantData['ContactPerson'] = $contact[0]->FULLNAME;
					$arrMerchantData['ContactNumber'] = $contact[0]->CONTACT;
				}
				//check if merchant is already existing
				if(in_array($arrMerchantData['CP_ID'], $arr)){					
					$arrMerchantData['InsertType'] = 'U';
					//update data to DIS
					$this->Sys_model->u_merchant(array('CP_ID' => $arrMerchantData['CP_ID']), $arrMerchantData);
				}else{
					$arrMerchantData['InsertType'] = 'I';
					//insert data to DIS
					$statInsert = $this->Sys_model->i_merchant($arrMerchantData);
					if($statInsert == false) log_message('error', 'PROVISION MERCHANT:: INSERT RECORD FAILED :: [CPID : '.$arrMerchantData['CP_ID'].']');	
				}			
				$this->get_corepass_agreements($arrMerchantData['CP_ID'], $AGREEMENT_ID);	
				$arrMerchant[] = $arrMerchantData;	
			}	
			
			$this->Sys_model->pcf_msettlement(); //UPDATE PAYMENT CUTOFF SETTLEMENT
		}
		echo 'DONE';
		exit();
	}
	
	/**
	 * GET COREPASS MOBILEPASS AGREEMENTS DETAILS and INSERT IN DIS
	 * @return void
	 */
	 private function get_corepass_agreements($arr_CPID, $arr_AGRID){
		if(empty($arr_CPID)) return false;
		
		$arr = array();

		//check if there's an existing merchant 
		$existingAgr = $this->Sys_model->v_agreement('','','AGREEMENT_ID');
		$totlExisting = $existingAgr->num_rows();
		
		if($totlExisting != 0){
			foreach($existingAgr->result() as $row){ $arr[]=$row->AGREEMENT_ID; }
		}
		
		$where = " AND EA.COMPANY_ID = ".$arr_CPID;
		if(!empty($arr_AGRID)) $where .= " AND EA.AGREEMENT_ID NOT IN (".$arr_AGRID.")";
		
		//get corepass mobilepass agreement
		$getAgreement = $this->Corepass_model->getQueryAgreements($where);
		if( $getAgreement->num_rows() != 0){			
			$fields = $getAgreement->list_fields(); 
			foreach($getAgreement->result() as $data){
				$arrMerchantData = array();
				foreach ($fields as $field){
					if($field == 'ADDRESS'){
						$address = $this->Corepass_model->getQueryAddress($data->$field)->result();
						$arrMerchantData[$field] = $address[0]->ADDRESS;
					}else if($field == 'VATCOND'){
						$arrMerchantData[$field] = ($data->$field == 2 ? 'Exempt' : 'Taxable');
					}else $arrMerchantData[$field] = $data->$field;				
				}
				$contact = $this->Corepass_model->getQueryAgreementRole($arrMerchantData['AGREEMENT_ID']); 
				if($contact->num_rows() <> 0){
					$contact = $contact->result();
					$arrMerchantData['ContactPerson'] = $contact[0]->FULLNAME;
					$arrMerchantData['ContactNumber'] = $contact[0]->CONTACT;
				}
				//check if merchant is already existing
				if(in_array($arrMerchantData['AGREEMENT_ID'], $arr)){					
					$arrMerchantData['InsertType'] = 'U';
					//update data to DIS
					$this->Sys_model->u_agreement(array('AGREEMENT_ID' => $arrMerchantData['AGREEMENT_ID']), $arrMerchantData);
				}else{
					$arrMerchantData['InsertType'] = 'I';
					//insert data to DIS
					$statInsert = $this->Sys_model->i_agreement($arrMerchantData);
					if($statInsert == false) log_message('error', 'PROVISION AGREEMENT:: INSERT RECORD FAILED :: [CPID : '.$arrMerchantData['AGREEMENT_ID'].']');	
				}
			}	
		}
	}
	
	/**
	 * GET COREPASS COMPANY DETAILS and INSERT IN DIS with tagged as "Merchant Conversion"
	 * @return void
	 */
	 
	 public function test(){		 
		$whereAFFCODE['CP_ID'] =  8304;
		$whereAFFCODE['AffiliateGroupCode'] = 'ROBINSONS-SUP';
		$getAFFCODE =  $this->Sys_model->v_agreement($whereAFFCODE, false);	
		$data = $getAFFCODE->row();
		echo $data->AGREEMENT_ID;
	}
	public function get_merchant_conversion(){
		$where = ''; $arr = array();

		//check if there's an existing merchant 
		$existingAcct = $this->Sys_model->v_merchant('','','cp_id');
		$totlExisting = $existingAcct->num_rows();
		
		if($totlExisting != 0){
			foreach($existingAcct->result() as $row){ $arr[]=$row->cp_id; }
			//$arr = implode(" , ",$arr);
		}	
		$DATA_CP = 'UAT';
		//$where = "AND ECG.COMPANYGROUPTYPE_ID = 307 AND ECG.COMPANYGROUP_ID = 1228"; //UAT 
		$where = "AND ECG.COMPANYGROUPTYPE_ID = 308 AND ECG.COMPANYGROUP_ID = 1262";  //PROD 

		//get corepass data merchant
		$getMerchant = $this->Corepass_model->getQueryMerchantConv($where);			
		if( $getMerchant->num_rows() != 0){
			$arrMerchant = array();
			$fields = $getMerchant->list_fields(); 
			foreach($getMerchant->result() as $data){
				$arrMerchantData = array();
				foreach ($fields as $field){
					$arrMerchantData[$field] = $data->$field;

					if($field == 'CP_ID'){
						$CP_ID = $data->$field;	
						/** GET AGREEMENT **/
						$whereArg = 'AND EA.COMPANY_ID = '.$CP_ID;
						$getAgreement = $this->Corepass_model->getQueryMerConvAgr($whereArg);
						if($getAgreement->num_rows() != 0){
							$fields2 = $getAgreement->list_fields(); 							
							foreach($getAgreement->result() as $data2){
								foreach ($fields2 as $field2){
									if($field2 <> 'AGREEMENT_ID'){
										if($field2 == 'ADDRESS'){
											$address = $this->Corepass_model->getQueryAddress($data2->$field2)->result();
											$arrMerchantData[$field2] = $address[0]->ADDRESS;
										}else if($field2 == 'VATCOND'){
											$arrMerchantData[$field2] = ($data2->$field2 == 2 ? 'Exempt' : 'Taxable');
										}else{
											if($field2 <> 'CREDITLIMIT') $arrMerchantData[$field2] = $data2->$field2;
										}	
									}else $AGREEMENT_ID = $data2->$field2;				
								}						
							}	
							$contact = $this->Corepass_model->getQueryAgreementRole($AGREEMENT_ID);
							if($contact->num_rows() <> 0){
								$contact = $contact->result();
								$arrMerchantData['ContactPerson'] = $contact[0]->FULLNAME;
								$arrMerchantData['ContactNumber'] = $contact[0]->CONTACT;
							}
							$W_AGREEMENT = TRUE;
						}else $W_AGREEMENT = FALSE;
					}						
				}				
				if($W_AGREEMENT == true){
					//check if merchant is already existing
					if(in_array($arrMerchantData['CP_ID'], $arr)){					
						$arrMerchantData['InsertType'] = 'U';
						//update data to DIS
						$this->Sys_model->u_merchant(array('CP_ID' => $arrMerchantData['CP_ID']), $arrMerchantData);
					}else{
						$arrMerchantData['InsertType'] = 'I';
						//insert data to DIS
						$statInsert = $this->Sys_model->i_merchant($arrMerchantData);
						if($statInsert == false) log_message('error', 'PROVISION MERCHANT:: INSERT RECORD FAILED :: [CPID : '.$arrMerchantData['CP_ID'].']');	
					}
					$arrMerchantData['AGREEMENT'] = $AGREEMENT_ID;
					$arrMerchant[] = $arrMerchantData; //LIST
				}else log_message('error', 'PROVISION MERCHANT:: INSERT RECORD FAILED :: [CPID : '.$arrMerchantData['CP_ID'].'] :: NO AGREEMENT');				
				
			}				
			echo '<pre>'; print_r($arrMerchant); echo '</pre>';
		}
		echo 'DONE - '.$DATA_CP ;
		exit();
	}
	
	/**
	 * GET COREPASS COMPANY DETAILS and INSERT IN DIS
	 * @return void
	 */
	public function get_corepass_product(){
		$where = $arr = '';

		//check if there's an existing merchant 
		$existingAcct = $this->Sys_model->v_product('','','SERVICE_ID');
		$totlExisting = $existingAcct->num_rows();
		
		if($totlExisting != 0){
			$arr = array();
			foreach($existingAcct->result() as $row){$arr[]=$row->SERVICE_ID;}
			$arr = implode(" , ",$arr);
		}

		if($totlExisting != 0){
			$where = 'AND s.SERVICE_ID NOT IN ('.$arr.')';	
		}
		//get corepass data product
		$getProduct = $this->Corepass_model->getQueryService($where);			
		if( $getProduct->num_rows() != 0){
			$arrProduct = array();
			$fields = $getProduct->list_fields();
			foreach($getProduct->result() as $data){
				$arrProductData = array();
				foreach ($fields as $field){
					$arrProductData[$field] = $data->$field; 			
				}
				//insert data to DIS
				$statInsert = $this->Sys_model->i_product($arrProductData);	
				$arrProduct[] = $arrProductData;
					if($statInsert == false) log_message('error', 'PROVISION PRODUCT:: INSERT RECORD FAILED :: [CPID : '.$arrProductData['SERVICE_ID'].']');
				
			}
		}
		echo 'DONE';
		exit();
	}	

	/**
	 * check_upload
	 * @return void
	 */
	public function check_upload(){
		//$select = 'LEFT(file_name , 10) as file_name, id, date_created';
		$select = 'LEFT(file_name , 10) as file_date, id, date_created, file_name';
		$data['redemption'] = $this->Sys_model->v_auditUpload(array('module_name'=>'redemption'), false, $select);
		$data['reconciliation'] = $this->Sys_model->v_auditUpload(array('module_name'=>'reconciliation'), false, $select);
		
		$this->load->view('check_upload', $data);	
	}

	/**
	 *** force_tagging *** 
	 *** @return void  *** 
	 */
	 public function force_tagging(){		 
		/*echo 'START - bulk_RECONCILED ... ';
		$bulk_RECONCILED = $this->Sys_model->bulk_RECONCILED();		
		if($bulk_RECONCILED == true){
			echo '<br /> DONE - bulk_RECONCILED <br /> START - bulk_REVERSED ...';*/
			echo 'START - bulk_REVERSED ...';
			$bulk_REVERSED = $this->Sys_model->bulk_REVERSED();			
			if($bulk_REVERSED == true ){
				echo '<br /> DONE - bulk_RECONCILED <br /> START - bulk_REVERSED ...';
				$bulk_VOID = $this->Sys_model->bulk_VOID();
				if($bulk_VOID == true ) echo '<br /> DONE - bulk_VOID';
				else echo '<br /> FAILED - bulk_VOID';
			}else echo '<br /> FAILED - bulk_REVERSED - Unable to Update VOID Tagging';
		/*}else echo '<br /> FAILED - bulk_RECONCILED - Unable to Update REVERSED and VOID Tagging';*/
			
		echo 'PROCEED TO <a href="/mp_dis/syscore/force_invalid_tag" target="_new">INVALID TAGGING</a>';
		$this->my_lib->cronLog('tagging', 'force_tagging'); // generate cron log
	 }
	 
	 public function force_invalid_tag(){	
		$bulk_INVALID = $this->Sys_model->bulk_INVALID();	
		if($bulk_INVALID == true ) echo 'DONE - bulk_INVALID';
		else echo 'FAILED - bulk_INVALID';		
	 }


	/**
	 * FOR SYSTEM CONFIG
	 */
	 public function assignID_batchUpdate(){				
		$this->force_tagging(); //1		 
		//$this->assign_redeemTblID(); //2
		//$this->assign_refund_ReconTBL(); //3
		$this->assign_refund_RedeemTBL(); //4	
		// UNCOMMENTED #4 for tagging of refund
	}
	 
	 /**
	 * Update RECON table redeem_tbl_id
	 * 	
	 */
	public function assign_redeemTblID(){
		/**
		 * 	Insert REDEEM_TBL_ID in Reconciliation Table
		 * 	VALIDATION
				§ If payment_cutoff.DIGITAL_SETTLEMENT_TYPE  <> "No RECON"
				§ AND REDEEM TABLE: REDEEM_ID, PROD_ID, VOUCHER_ID, TRANSACTION_VALUE
		 * 	GET/UPDATE DATA in REDEEM TABLE
				§ ID -> Update to RECON_TBL.REDEEM_TBL_ID
				§ STAGE -> Update to RECON_TBL.STAGE
		 */
		$result = $this->Sys_model->assign_redeemTblID();
		echo 'DONE - assign_redeemTblID <br />';	
	}
	
	public function assign_refund_RedeemTBL(){
		$this->load->model('Process_model');
			$refund_RedeemTBL =  $this->Process_model->refund_RedeemTBL(); 
		echo 'DONE: Reversal Assign IDs - refund_RedeemTBL <br />';	
	} 

	public function assign_refund_ReconTBL(){
		$this->load->model('Process_model');
			$refund_ReconTBL =  $this->Process_model->refund_ReconTBL(); 
		echo 'DONE: Reversal Assign IDs - refund_ReconTBL <br />';	
	}

	// *******************     ******************** //
	
	public function assign_redeemPAID(){
		/**
		 * 	Insert REDEEM_TBL_ID in Reconciliation Table
		 * 	VALIDATION
				§ If payment_cutoff.DIGITAL_SETTLEMENT_TYPE  <> "No RECON"
				§ AND REDEEM TABLE: REDEEM_ID, PROD_ID, VOUCHER_ID, TRANSACTION_VALUE
		 * 	GET/UPDATE DATA in REDEEM TABLE
				§ ID -> Update to RECON_TBL.REDEEM_TBL_ID
				§ STAGE -> Update to RECON_TBL.STAGE
		 */
		$result = $this->Sys_model->assign_redeemPAID();
		echo 'DONE - assign_redeemPAID '.$result;
	}
		
	// *******************     ******************** //
	
	public function reassign_redeemPAID(){	
		// exit;	
		if(!isset($_GET['pa']) || empty($_GET['pa'])) {return 'NO PA ID'; exit;}

		$PA_ID = $_GET['pa']; //4271
		$result = $this->Sys_model->assign_reconPA_to_redeemPA($PA_ID);		
		echo 'DONE - reassign_redeemPAID '.$PA_ID.' - '.$result;
			$recon_count = $this->Sys_model->v_checkRecon(array('PA_ID'=>$PA_ID), TRUE); 
			$redeem_count = $this->Sys_model->v_checkRedeem(array('PA_ID'=>$PA_ID), TRUE); 
		echo '<br /> Recon Count: '.$recon_count.' - Redeem Count: '.$redeem_count;
	}
	
	// *******************     ******************** //
	
	public function clean_redeemTBLID(){	
		if(!isset($_GET['mid']) || empty($_GET['mid'])) {return 'NO MERCHANT ID'; exit;}

		echo 'CLEAN REDEEMTBLID <br />';
		$MERCHANT_ID = $_GET['mid'];	
			$this->_clean_redeemTBLID($MERCHANT_ID);			
	}
	public function grpclean_redeemTBLID(){	
		exit();
		echo 'CLEAN REDEEMTBLID <br />';
		///$group_mid = array(26,32,33,34,92,1609,1610,1611,1612);//25
		///$group_mid = array(1899,1900,1901,1902,1903,1904,1905,1906);//
		/*$group_mid = array(1907,1910,2205,2309,2368,2369,2754,2845,2849,2850,2925,2926,3061,3147,3225,3254
		,3911,3913,3920,3922,3925,3932,4203,4735,4741,4743,4746,4750,4761,4763,4766,4774,4872,4875,4876
		,4878,4880,4883,4891,4900,4901,4907,4912,4928,4930,4931,4937,4939,4943,4945,4946,4949,4951,4952,4956
		,4957,4960,4967,4973,5008,5012,5133,5134,5135,5136,5505,5509,5512,5518,5519,5640,5799,5902,5974,5982);*/
		$group_mid = array(5983,5987,6002,6233,6252,6272,6273,6274,6377,6422,6469,6470,6471,6472,6476,6497
		,6505,6845,6886,6981,7338,7432,7433,7437,7439,7440,7443,7450,7463,7466,7475,7507,7518,4772);
		for($x = 0; $x <= count($group_mid); $x++){	
			$this->_clean_redeemTBLID($group_mid[$x]);	
		}		
	}
		private function _clean_redeemTBLID($MERCHANT_ID){
			if(empty($MERCHANT_ID)) {return 'NO MERCHANT ID'; exit;}
			
			$mer_result = $this->Sys_model->noredeemID_reconPA($MERCHANT_ID);			
			if($mer_result->num_rows() <> 0){
				foreach($mer_result->result() as $mer_row){ 
					
					$result = $this->Sys_model->get_clean_redeemTBLID($MERCHANT_ID,$mer_row->PA_ID);				
					if($result->num_rows() <> 0){
						echo 'With Result PER PA-'.$mer_row->PA_ID;					
						foreach($result->result() as $row){ 
							$MIN_ID = $row->ID;
							$REDEEM_TBL_ID = $row->re_redeem_id;
							$RE_STAGE = $row->re_stage;
							
							$update_arr['REDEEM_TBL_ID'] = $REDEEM_TBL_ID; 
							$update_arr['STAGE'] = $RE_STAGE;
							$this->Sys_model->u_recon_arr(array('ID'=>$MIN_ID), $update_arr);

								$result_a_w['REDEEM_TBL_ID'] = $REDEEM_TBL_ID;
								$result_a_w['PA_ID'] = 0;						
							$v_checkRecon_result = $this->Sys_model->v_checkRecon($result_a_w);	
							if($v_checkRecon_result->num_rows() <> 0){ 
								$update_r = $this->Sys_model->removeDuplicate_RedeemTBLID($REDEEM_TBL_ID, $MIN_ID);
								echo 'removeDuplicate_RedeemTBLID <br />';
							}else {
								echo 'update recon data only RECON-MIN_ID:'.$MIN_ID.' - REDEEM_TBL_ID'.$REDEEM_TBL_ID.'<br />';
							}
						}
					}else{
						echo 'No Result PER PA-'.$mer_row->PA_ID;
					}	
				}
			}else{
				echo 'No Result PA';
			}
		}


	public function check_duplicate(){	
		echo 'CHECK DUPLICATE REDEEMTBLID <br />';
		$result = $this->Sys_model->get_duplicateRedeemTBLID();
		
		if($result->num_rows() <> 0){
			foreach($result->result() as $row){ 
				if($row->total > 1){
					$result_a = $this->Sys_model->checkRecon_RedeemTBLID($row->REDEEM_TBL_ID);
					$checkRedeem = $this->Sys_model->v_checkRedeem(array('id'=>$row->REDEEM_TBL_ID), false, 'PA_ID');
					echo $row->REDEEM_TBL_ID.' Total Rows ('.$row->total.') - ';					
					if($checkRedeem->row('PA_ID') == 0){ 
						//echo '<a href="http://10.63.16.144:8080/mp_dis/syscore/del_redeemTblID?redeemid='.$row->REDEEM_TBL_ID.'&min='.$row->MIN_ID.'" target="_blank">Remove Duplicate</a><br />';
						$update_r = $this->Sys_model->removeDuplicate_RedeemTBLID($row->REDEEM_TBL_ID, $row->MIN_ID);
						echo 'DONE - removeDuplicate_RedeemTBLID '.$row->REDEEM_TBL_ID.' - '.$update_r.'<br />';
					}else {
						echo 'Unable to update Redeem has PA_ID - '.$checkRedeem->row('PA_ID').'<br />';
					}
					/*echo '<pre>';
					print_r($result_a->result());
					echo '</pre><br />';*/
				}
			}
		}		
	}

		public function del_redeemTblID(){	
			if(!isset($_GET['redeemid']) || empty($_GET['redeemid'])) {return 'NO REDEEM_TBL_ID'; exit;}

			$REDEEM_TBL_ID = $_GET['redeemid'];
			$MIN_ID = $_GET['min'];
			$result = $this->Sys_model->removeDuplicate_RedeemTBLID($REDEEM_TBL_ID, $MIN_ID);
			echo 'DONE - removeDuplicate_RedeemTBLID '.$REDEEM_TBL_ID.' - '.$result;
		}

	// *******************     ******************** //
	
	public function correct_duplicateRedeemTBLID(){	
		if(!isset($_GET['pa']) || empty($_GET['pa'])) {return 'NO PA ID'; exit;}
		
		$PA_ID = $_GET['pa']; //4271
		$result = $this->Sys_model->correct_duplicateRedeemTBLID($PA_ID);
		echo 'Correct Duplicate RedeemTBLID for PA_ID '.$PA_ID.'<br />';
		
		if($result->num_rows() <> 0){
			foreach($result->result() as $row){ 
				$where = '';
				$where['REDEEM_ID'] = $row->REDEEM_ID;
				$where['PROD_ID'] = $row->PROD_ID;
				$where['VOUCHER_CODE'] = $row->VOUCHER_CODE;
				$result_a = $this->Sys_model->v_checkRecon($where, false); //$where = null, $count = false, $select = null
				
				$REDEEM_TBL_ID = $row->REDEEM_TBL_ID;
				$RE_PAID = $row->RE_PAID;
				$RE_STAGE = $row->RE_STAGE;

				if($result_a->num_rows() <> 0){								
					echo  '** '.$REDEEM_TBL_ID.' <br />';	
					foreach($result_a->result() as $row_a){ 
						$update_arr = '';
						if($row_a->PA_ID <> 0){					
							if($RE_PAID <> $row_a->PA_ID && $row_a->REDEEM_TBL_ID == $REDEEM_TBL_ID){
								$update_arr['PA_ID'] = 0;								
								$update_arr['REDEEM_TBL_ID'] = 0; 
								$update_arr['STAGE'] = '';
									$this->Sys_model->u_recon_arr(array('ID'=>$row_a->ID), $update_arr);
								echo $row_a->ID.' - remove redeemtbl_id';
							}else if($RE_PAID == $row_a->PA_ID && $row_a->REDEEM_TBL_ID == 0){							
								$update_arr['REDEEM_TBL_ID'] = $REDEEM_TBL_ID; 
								$update_arr['STAGE'] = $RE_STAGE;
									$this->Sys_model->u_recon_arr(array('ID'=>$row_a->ID), $update_arr);
								echo $row_a->ID.' - add redeemtbl_id';
							}else{
								echo $row_a->ID.' - other';
							}										
							echo  '<br />';				
						}						
					}
				}
				
			}
		}		
	}
	
		public function get_corepass_account_v2(){
		$where = '';
		$arr = array();

		//check if there's an existing merchant 
		$existingAcct = $this->Sys_model->v_merchant('','','cp_id');
		$totlExisting = $existingAcct->num_rows();
		
		if($totlExisting != 0){
			foreach($existingAcct->result() as $row){ $arr[]=$row->cp_id; }
			//$arr = implode(" , ",$arr);
		}

		/*
			ContactPerson
			ContactNumber
			AffiliateGroupCode
			PayeeQtyOfDays
			PayeeDayType
			PayeeComments
		*/
		
		//get corepass data merchant
		$getMerchant = $this->Corepass_model->getQueryClientV2($where);			
		if( $getMerchant->num_rows() != 0){
			$arrMerchant = array();
			$fields = $getMerchant->list_fields(); 
			foreach($getMerchant->result() as $data){
				$arrMerchantData = array();
				foreach ($fields as $field){
					if($field <> 'AGREEMENT_ID'){
						if($field == 'ADDRESS'){
							$address = $this->Corepass_model->getQueryAddress($data->$field)->result();
							$arrMerchantData[$field] = $address[0]->ADDRESS;
						}else if($field == 'VATCOND'){
							$arrMerchantData[$field] = ($data->$field == 2 ? 'Exempt' : 'Taxable');
						}else $arrMerchantData[$field] = $data->$field;	
					}else $AGREEMENT_ID = $data->$field;				
				}
				$contact = $this->Corepass_model->getQueryAgreementRole($AGREEMENT_ID);
				if($contact->num_rows() <> 0){
					$contact = $contact->result();
					$arrMerchantData['ContactPerson'] = $contact[0]->FULLNAME;
					$arrMerchantData['ContactNumber'] = $contact[0]->CONTACT;
				}
				//check if merchant is already existing
				if(in_array($arrMerchantData['CP_ID'], $arr)){					
					$arrMerchantData['InsertType'] = 'U';
					//update data to DIS
					$this->Sys_model->u_merchant(array('CP_ID' => $arrMerchantData['CP_ID']), $arrMerchantData);
				}else{
					$arrMerchantData['InsertType'] = 'I';
					//insert data to DIS
					$statInsert = $this->Sys_model->i_merchant($arrMerchantData);
					if($statInsert == false) log_message('error', 'PROVISION MERCHANT:: INSERT RECORD FAILED :: [CPID : '.$arrMerchantData['CP_ID'].']');	
				}			
				$this->get_corepass_agreements($arrMerchantData['CP_ID'], $AGREEMENT_ID);	
				$arrMerchant[] = $arrMerchantData;	
			}	
			
			$this->Sys_model->pcf_msettlement(); //UPDATE PAYMENT CUTOFF SETTLEMENT
		}
		echo 'DONE';
		exit();
	}

}
