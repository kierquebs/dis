<?php
/**
 * MAIN CONTROLLER
 * modules that can update corepass records directly to its database records
 * as data being updated it is not log to the audit trail of corepass actions
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Syspa extends MX_Controller {
	public function	__construct(){

	parent::__construct();	
		if($this->auth->check_session()) redirect('login');	
		
		$this->load->model('Sys_model');
		$this->load->model('Corepass_model');
		$this->load->helper('my_helper');
	}
		
	 /** 
		*** CHECK PA DETAIL ***
	 **/ 
	  public function index(){
		if(!isset($_GET['pa']) || empty($_GET['pa'])) {return 'NO PA ID'; exit;}
		
		$PA_ID = $_GET['pa'];		
		$getPCBranchesRow =  $this->getPCBranchesRow($PA_ID);		
			$brRowNum = $getPCBranchesRow->num_rows();
		
		if($brRowNum != 0){	
			$AFFCODE_li = array_group_by($getPCBranchesRow->result(), "brAFFCODE");						
			$AFFCODE_row = json_decode(json_encode($AFFCODE_li), true);
			$tblArr = array();
			foreach($AFFCODE_row as $k => $v){
				foreach ($v as $row) {	
					$whereAFFCODE = $view_detail = $where_paD = '';
					
					$PA_MerchantFee = $row['MerchantFee'];
					$PA_PayeeDayType = $row['PayeeDayType'];
					$PA_PayeeQtyOfDays = $row['PayeeQtyOfDays'];
					$PA_VAT = $row['vatcond'];
					
					if($row['merAFFCODE'] <> $row['brAFFCODE']){
						$whereAFFCODE['CP_ID'] =  $row['CPID'];
						$whereAFFCODE['AffiliateGroupCode'] = trim($row['brAFFCODE']);
						$getAFFCODE =  $this->Sys_model->v_agreement($whereAFFCODE, false);	
						if($getAFFCODE->num_rows() <> 0){
							$rowAFFCODE = $getAFFCODE->row();													
							$PA_MerchantFee = $rowAFFCODE->MerchantFee;
							$PA_PayeeDayType = $rowAFFCODE->PayeeDayType;
							$PA_PayeeQtyOfDays = $rowAFFCODE->PayeeQtyOfDays;
							$PA_VAT = $rowAFFCODE->VATCond; 
						}
					}	
					
					$VAT = $this->my_lib->checkVAT($PA_VAT);								
					$totalFV = $row['totalAmount'];
					$percentMF = $this->my_lib->convertMFRATE($PA_MerchantFee, true);
					$MF = $this->my_lib->computeMF($totalFV, $percentMF);
					
					/*BUILD PA DETAIL INFO*/		 			
					$where_paD['PA_ID'] = $PA_ID;
					$where_paD['RECON_ID'] = $row['RECON_ID'];		
					$where_paD['BRANCH_ID'] = $row['BRANCH_ID'];
					
					$checkPAD = $this->Sys_model->v_paD($where_paD, false);
						$checkPADNUM = $checkPAD->num_rows();
					if($checkPADNUM == 1){
						$rowPAD = $checkPAD->row();
						
						if(($rowPAD->NUM_PASSES <> $row['totalPasses'] && $rowPAD->TOTAL_FV <> $totalFV) ||  $rowPAD->NET_DUE == 99999.99999){							
							$whereUpdate['PA_DID'] = $rowPAD->PA_DID;					
								$view_detail['BRANCH_ID'] = $rowPAD->BRANCH_ID. ' ** '. $row['BRANCH_ID'];					
								$view_detail['RATE'] = $percentMF;
								$view_detail['NUM_PASSES'] = $row['totalPasses'];
								$view_detail['TOTAL_FV'] = $totalFV;
								$view_detail['MARKETING_FEE'] = $MF; 
								$view_detail['VAT'] = $this->my_lib->computeVAT($totalFV, $percentMF, $VAT);
								$view_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);
								$view_detail['DATE_CREATED'] = $rowPAD->DATE_CREATED;
							
							echo '<pre>';	
							
								if($rowPAD->NET_DUE == 99999.99999)  echo 'NET_DUE UPDATE : PA_DID '.$rowPAD->PA_DID.'<br />'; 
								else echo 'NEED UPDATE RECORD : PA_DID '.$rowPAD->PA_DID.'<br />'; 
							
							print_r($view_detail); 
							echo '</pre>';
						}else{
							//echo '<pre> NO ACTION - PA_DID '.$rowPAD->PA_DID ; echo '</pre>';
						}						
					}else{
						//echo '<pre> NO ACTION - PA_ID '.$PA_ID. ' TOTAL_RESULT - '.$checkPADNUM; echo '</pre>';
					}										
				}
			}
		}
			
		$not_recon = $this->notRecon($PA_ID);
			$not_reconNum = $not_recon->num_rows();
		if($not_reconNum != 0){			
			echo '<h3>NOT IN RECON - '.$not_reconNum .'</h3><pre>'; print_r($not_recon->result()); echo '</pre>';
		}		
		
		$not_padetail = $this->notPADetail($PA_ID);
			$not_padetailNum = $not_padetail->num_rows();
		if($not_padetailNum != 0){			
			echo '<h3>NOT IN PA DETAIL - '.$not_padetailNum .'</h3><pre>'; print_r($not_padetail->result()); echo '</pre>';
		}
		
		$not_refundPAdetail = $this->not_refundPAdetail($PA_ID);
			$not_padetailRefNum = $not_refundPAdetail->num_rows();
		if($not_padetailRefNum != 0){			
			echo '<h3>NOT IN PA DETAIL REFUND - '.$not_padetailRefNum .'</h3><pre>'; print_r($not_padetailRefNum->result()); echo '</pre>';
		}
	 }
		
	 
	  private function getNetDue999(){
		$result = $this->db->query("
				select ph.MERCHANT_ID, pd.PA_DID, pd.PA_ID
				from 
				pa_detail pd,
				pa_header ph
				where 
				pd.PA_ID = ph.PA_ID
				and NET_DUE = 99999.99999
				order by pd.PA_ID ASC
				limit 0, 10
			");		
		return $result;
	 }
	 
	 public function fix_netdue(){
		//if(!isset($_GET['PA_DID']) || empty($_GET['PA_DID']) || empty($_GET['MERCHANT_ID'])) {return 'NO PA_DID'; exit;}
		//$PA_DID = $_GET['PA_DID'];
		//$MERCHANT_ID = $_GET['MERCHANT_ID'];		
		
		$getNetDue999 =  $this->getNetDue999();
		
		
		$rowNetDue999 = $getNetDue999->num_rows();	
		
		if($rowNetDue999 != 0){	
			foreach($getNetDue999->result() as $temp_row){ 
				$where_paD = $whereUpdate = $view_detail = $whereAFFCODE = $update_detail = $MERCHANT_ID = $PA_DID = '';
				
				$PA_DID = $temp_row->PA_DID;
				$MERCHANT_ID = $temp_row->MERCHANT_ID;
			
				$where_paD['PA_DID'] = $PA_DID;
				$where_paD['NET_DUE'] = 99999.99999;
				
				$checkPAD = $this->Sys_model->v_paD($where_paD, false);
				$checkPADNUM = $checkPAD->num_rows();
				if($checkPADNUM == 1){ 
					$rowPAD = $checkPAD->row();					
					$percentMF = $rowPAD->RATE;
					$totalFV = $rowPAD->TOTAL_FV;
					
					$merWhere = "br.BRANCH_ID = '".$rowPAD->BRANCH_ID."' and br.MERCHANT_ID = ".$MERCHANT_ID;
					
					$getMerchantDetails = $this->getMerchantDetails($merWhere);
						$rowMerchantDetails = $getMerchantDetails->row();			
						$PA_VAT = $rowMerchantDetails->vatcond;
							
					if($rowMerchantDetails->merAFFCODE <> $rowMerchantDetails->brAFFCODE){
						$whereAFFCODE['CP_ID'] =  $rowMerchantDetails->CPID;
						$whereAFFCODE['AffiliateGroupCode'] = trim($rowMerchantDetails->brAFFCODE);
						$getAFFCODE =  $this->Sys_model->v_agreement($whereAFFCODE, false);	
						if($getAFFCODE->num_rows() <> 0){
							$rowAFFCODE = $getAFFCODE->row();													
							$PA_MerchantFee = $rowAFFCODE->MerchantFee;
							$PA_PayeeDayType = $rowAFFCODE->PayeeDayType;
							$PA_PayeeQtyOfDays = $rowAFFCODE->PayeeQtyOfDays;
							$PA_VAT = $rowAFFCODE->VATCond;
						}
					}				
					$VAT = $this->my_lib->checkVAT($PA_VAT);
					
					$view_detail['MARKETING_FEE'] = $this->my_lib->computeMF($totalFV, $percentMF); 
					$view_detail['VAT'] = $this->my_lib->computeVAT($totalFV, $percentMF, $VAT);	
									
					
					echo '<pre>PA_DID '.$rowPAD->PA_DID.'<br />';
					//print_r($view_detail); 
					
					if($view_detail['MARKETING_FEE'] == $rowPAD->MARKETING_FEE && $view_detail['VAT'] == $rowPAD->VAT){				
						$whereUpdate['PA_DID'] = $rowPAD->PA_DID;
						$update_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);
						$update_detail['DATE_CREATED'] = $rowPAD->DATE_CREATED;	
						
						$this->Sys_model->u_paD($whereUpdate, $update_detail);
						echo 'UPDATE RECORD <br />';
						print_r($update_detail); 
						
					}else echo 'Action Not VALID';
					echo '************** </pre>';
				}	
			}			
		}else echo $rowNetDue999;		
	 }
	 
	/** 
		**** FIX INCORRECT PA DETAIL ****
	**/ 
	public function fixint_pa(){
		if(!isset($_GET['pa']) || empty($_GET['pa'])) {return 'NO PA ID'; exit;}
		
		$PA_ID = $_GET['pa'];		
		$getPCBranchesRow =  $this->getPCBranchesRow($PA_ID);
		
		$brRowNum = $getPCBranchesRow->num_rows();	
		if($brRowNum != 0){	
			$AFFCODE_li = array_group_by($getPCBranchesRow->result(), "brAFFCODE");						
			$AFFCODE_row = json_decode(json_encode($AFFCODE_li), true);
			$tblArr = array();
			foreach($AFFCODE_row as $k => $v){
				foreach ($v as $row) {	
					$whereAFFCODE = $update_detail = $where_paD = '';
					
					$PA_MerchantFee = $row['MerchantFee'];
					$PA_PayeeDayType = $row['PayeeDayType'];
					$PA_PayeeQtyOfDays = $row['PayeeQtyOfDays'];
					$PA_VAT = $row['vatcond'];
					
					if($row['merAFFCODE'] <> $row['brAFFCODE']){
						$whereAFFCODE['CP_ID'] =  $row['CPID'];
						$whereAFFCODE['AffiliateGroupCode'] = trim($row['brAFFCODE']);
						$getAFFCODE =  $this->Sys_model->v_agreement($whereAFFCODE, false);	
						if($getAFFCODE->num_rows() <> 0){
							$rowAFFCODE = $getAFFCODE->row();													
							$PA_MerchantFee = $rowAFFCODE->MerchantFee;
							$PA_PayeeDayType = $rowAFFCODE->PayeeDayType;
							$PA_PayeeQtyOfDays = $rowAFFCODE->PayeeQtyOfDays;
							$PA_VAT = $rowAFFCODE->VATCond;
						}
					}	
					
					echo '<pre>';print_r($row);echo '</pre>';
					die();
					
					$VAT = $this->my_lib->checkVAT($PA_VAT);								
					$totalFV = $row['totalAmount'];
					$percentMF = $this->my_lib->convertMFRATE($PA_MerchantFee, true);
					$MF = $this->my_lib->computeMF($totalFV, $percentMF);
					
					/*BUILD PA DETAIL INFO*/		 			
					$where_paD['PA_ID'] = $PA_ID;
					$where_paD['RECON_ID'] = $row['RECON_ID'];		
					$where_paD['BRANCH_ID'] = $row['BRANCH_ID'];
					
					$checkPAD = $this->Sys_model->v_paD($where_paD, false);
						$checkPADNUM = $checkPAD->num_rows();
					
					if($checkPADNUM == 1){ 
						$rowPAD = $checkPAD->row();
						
						if($rowPAD->NUM_PASSES <> $row['totalPasses'] && $rowPAD->TOTAL_FV <> $totalFV){							
							$whereUpdate['PA_DID'] = $rowPAD->PA_DID;						
								$update_detail['RATE'] = $percentMF;
								$update_detail['NUM_PASSES'] = $row['totalPasses'];
								$update_detail['TOTAL_FV'] = $totalFV;
								$update_detail['MARKETING_FEE'] = $MF; 
								$update_detail['VAT'] = $this->my_lib->computeVAT($totalFV, $percentMF, $VAT);
								$update_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);
								$update_detail['DATE_CREATED'] = $rowPAD->DATE_CREATED;
							
							$this->Sys_model->u_paD($whereUpdate, $update_detail);						
							echo '<pre> UPDATE RECORD : PA_DID '.$rowPAD->PA_DID.'<br />'; print_r($update_detail); echo '</pre>';
							
						}else if($rowPAD->NET_DUE == 99999.99999 && $rowPAD->TOTAL_FV == $totalFV){							
							$update_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);							
							
							$this->Sys_model->u_paD($whereUpdate, $update_detail);						
							echo '<pre> UPDATE RECORD NET_DUE : PA_DID '.$rowPAD->PA_DID.'<br />'; print_r($update_detail); echo '</pre>';
						}else{
							//echo '<pre> NO ACTION - PA_DID '.$rowPAD->PA_DID.'<br />'; echo '</pre>';
						}						
					}else{
						echo '<pre> NO ACTION - PA_ID '.$PA_ID. ' TOTAL_RESULT - '.$checkPADNUM; echo '</pre>';
					}					
					
				}
			}
		}		
	 }
	
	
	public function fixint_padetail(){
		if(!isset($_GET['pa']) || empty($_GET['pa'])) {return 'NO PA ID'; exit;}
		
		$PA_ID = $_GET['pa'];		
		$getPCBranchesRow =  $this->getPCBranchesRow($PA_ID);
		
		$brRowNum = $getPCBranchesRow->num_rows();	
		if($brRowNum != 0){	
			$AFFCODE_li = array_group_by($getPCBranchesRow->result(), "brAFFCODE");						
			$AFFCODE_row = json_decode(json_encode($AFFCODE_li), true);
			$tblArr = array();
			foreach($AFFCODE_row as $k => $v){
				foreach ($v as $row) {	
					$insert_detail = $whereAFFCODE = $update_detail = $where_paD = '';
					
					$PA_MerchantFee = $row['MerchantFee'];
					$PA_PayeeDayType = $row['PayeeDayType'];
					$PA_PayeeQtyOfDays = $row['PayeeQtyOfDays'];
					$PA_VAT = $row['vatcond'];
					
					if($row['merAFFCODE'] <> $row['brAFFCODE']){
						$whereAFFCODE['CP_ID'] =  $row['CPID'];
						$whereAFFCODE['AffiliateGroupCode'] = trim($row['brAFFCODE']);
						$getAFFCODE =  $this->Sys_model->v_agreement($whereAFFCODE, false);	
						if($getAFFCODE->num_rows() <> 0){
							$rowAFFCODE = $getAFFCODE->row();													
							$PA_MerchantFee = $rowAFFCODE->MerchantFee;
							$PA_PayeeDayType = $rowAFFCODE->PayeeDayType;
							$PA_PayeeQtyOfDays = $rowAFFCODE->PayeeQtyOfDays;
							$PA_VAT = $rowAFFCODE->VATCond;
						}
					}	
					
					$VAT = $this->my_lib->checkVAT($PA_VAT);								
					$totalFV = $row['totalAmount'];
					$percentMF = $this->my_lib->convertMFRATE($PA_MerchantFee, true);
					$MF = $this->my_lib->computeMF($totalFV, $percentMF);
					
					/*BUILD PA DETAIL INFO*/		 			
					$where_paD['PA_ID'] = $PA_ID;
					$where_paD['RECON_ID'] = $row['RECON_ID'];		
					$where_paD['BRANCH_ID'] = $row['BRANCH_ID'];
					
					$checkPAD = $this->Sys_model->v_paD($where_paD, false);
						$checkPADNUM = $checkPAD->num_rows();
					if($checkPADNUM == 0){ 
						$insert_detail['PA_ID'] = $PA_ID;
						$insert_detail['RECON_ID'] = $row['RECON_ID'];		
						$insert_detail['BRANCH_ID'] = $row['BRANCH_ID'];
						$insert_detail['RATE'] = $percentMF;
						$insert_detail['NUM_PASSES'] = $row['totalPasses'];
						$insert_detail['TOTAL_FV'] = $totalFV;						
						$insert_detail['MARKETING_FEE'] = $MF; 
						$insert_detail['VAT'] = $this->my_lib->computeVAT($totalFV, $percentMF, $VAT);
						$insert_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);
						$insert_detail['DATE_CREATED'] = $this->my_lib->current_date();
									
						$paD_ID = $this->Sys_model->i_paD($insert_detail);	
						echo '<pre> INSERT RECORD : PA_DID '.$paD_ID.'<br />'; print_r($insert_detail); echo '</pre>';								
					}else{						
						$rowPAD = $checkPAD->row();
						echo '<pre> PA DETAILS ALREADY EXIST : PA_DID '.$rowPAD->PA_DID.'<br />'; print_r($where_paD); echo '</pre>';	
					}					
				}
			}
		}		
	 }
	 

	public function fixint_padetail_compute(){
		if(!isset($_GET['pa']) || empty($_GET['pa'])) {return 'NO PA ID'; exit;}
		
		$PA_ID = $_GET['pa'];		
		$getPCBranchesRow =  $this->getPCBranchesRow($PA_ID);
		
		$brRowNum = $getPCBranchesRow->num_rows();	
		if($brRowNum != 0){	
			$AFFCODE_li = array_group_by($getPCBranchesRow->result(), "brAFFCODE");						
			$AFFCODE_row = json_decode(json_encode($AFFCODE_li), true);
			$tblArr = array();
			foreach($AFFCODE_row as $k => $v){
				foreach ($v as $row) {	
					$insert_detail = $whereAFFCODE = $update_detail = $where_paD = '';
					
					$PA_MerchantFee = $row['MerchantFee'];
					$PA_PayeeDayType = $row['PayeeDayType'];
					$PA_PayeeQtyOfDays = $row['PayeeQtyOfDays'];
					$PA_VAT = $row['vatcond'];
					
					if($row['merAFFCODE'] <> $row['brAFFCODE']){
						$whereAFFCODE['CP_ID'] =  $row['CPID'];
						$whereAFFCODE['AffiliateGroupCode'] = trim($row['brAFFCODE']);
						$getAFFCODE =  $this->Sys_model->v_agreement($whereAFFCODE, false);	
						if($getAFFCODE->num_rows() <> 0){
							$rowAFFCODE = $getAFFCODE->row();													
							$PA_MerchantFee = $rowAFFCODE->MerchantFee;
							$PA_PayeeDayType = $rowAFFCODE->PayeeDayType;
							$PA_PayeeQtyOfDays = $rowAFFCODE->PayeeQtyOfDays;
							$PA_VAT = $rowAFFCODE->VATCond;
						}
					}	
					
					$VAT = $this->my_lib->checkVAT($PA_VAT);								
					$totalFV = $row['totalAmount'];
					$percentMF = $this->my_lib->convertMFRATE($PA_MerchantFee, true);
					$MF = $this->my_lib->computeMF($totalFV, $percentMF);
					
					/*BUILD PA DETAIL INFO*/		 			
					$where_paD['PA_ID'] = $PA_ID;
					$where_paD['RECON_ID'] = $row['RECON_ID'];		
					$where_paD['BRANCH_ID'] = $row['BRANCH_ID'];
					
					$checkPAD = $this->Sys_model->v_paD($where_paD, false);
						$checkPADNUM = $checkPAD->num_rows();
					if($checkPADNUM == 0){ 
						$insert_detail['PA_ID'] = $PA_ID;
						$insert_detail['RECON_ID'] = $row['RECON_ID'];		
						$insert_detail['BRANCH_ID'] = $row['BRANCH_ID'];
						$insert_detail['RATE'] = $percentMF;
						$insert_detail['NUM_PASSES'] = $row['totalPasses'];
						$insert_detail['TOTAL_FV'] = $totalFV;						
						$insert_detail['MARKETING_FEE'] = $MF; 
						$insert_detail['VAT'] = $this->my_lib->computeVAT($totalFV, $percentMF, $VAT);
						$insert_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);
						$insert_detail['DATE_CREATED'] = $this->my_lib->current_date();
									
						$paD_ID = $this->Sys_model->i_paD($insert_detail);	
						echo '<pre> INSERT RECORD : PA_DID '.$paD_ID.'<br />'; print_r($insert_detail); echo '</pre>';								
					}else{						
						$rowPAD = $checkPAD->row();
						echo '<pre> PA DETAILS ALREADY EXIST : PA_DID '.$rowPAD->PA_DID.'<br />'; print_r($where_paD); echo '</pre>';	
						
						if($rowPAD->TOTAL_REFUND <> 0){
							echo '<pre> NO ACTION - PA_DID '.$rowPAD->PA_DID.'with REFUND AMOUNT <br />'; echo '</pre>';
						}else{
							if($rowPAD->NUM_PASSES <> $row['totalPasses'] && $rowPAD->TOTAL_FV <> $totalFV){							
								$whereUpdate['PA_DID'] = $rowPAD->PA_DID;						
									$update_detail['RATE'] = $percentMF;
									$update_detail['NUM_PASSES'] = $row['totalPasses'];
									$update_detail['TOTAL_FV'] = $totalFV;
									$update_detail['MARKETING_FEE'] = $MF; 
									$update_detail['VAT'] = $this->my_lib->computeVAT($totalFV, $percentMF, $VAT);
									$update_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);
									$update_detail['DATE_CREATED'] = $rowPAD->DATE_CREATED;
								
								$this->Sys_model->u_paD($whereUpdate, $update_detail);						
								echo '<pre> UPDATE RECORD : PA_DID '.$rowPAD->PA_DID.'<br />'; print_r($update_detail); echo '</pre>';
								
							}else if($rowPAD->NET_DUE == 99999.99999 && $rowPAD->TOTAL_FV == $totalFV){							
								$update_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);							
								
								$this->Sys_model->u_paD($whereUpdate, $update_detail);						
								echo '<pre> UPDATE RECORD NET_DUE : PA_DID '.$rowPAD->PA_DID.'<br />'; print_r($update_detail); echo '</pre>';
							}else{
								echo 'PA_DETAIL: NUM_PASSES-'.$rowPAD->NUM_PASSES.' totalPasses-'.$row['totalPasses'].' PD_TOTAL_FV-'.$rowPAD->TOTAL_FV.' totalFV-'.$totalFV;
							}
						}
					
					}					
				}
			}
		}		
	 }
	
	/*
		*** private function ***
	 */
	 private function getPCBranchesRow($PA_ID){
		$result = $this->db->query("
			select 
				recon.PA_ID,
				recon.RECON_ID,
				recon.MERCHANT_ID MID,
				branch.CPID ,
				branch.LegalName,
				branch.MerchantFee,
				branch.vatcond,
				branch.PayeeDayType,
				branch.PayeeQtyOfDays,
				branch.AffiliateGroupCode merAFFCODE,
				SUM(recon.TRANSACTION_VALUE) totalAmount,		
				COUNT(recon.ID) totalPasses,		
				branch.BRANCH_ID,	
				branch.BRANCH_NAME,
				branch.brAFFCODE
			from			
				reconcilation recon,
				(
					select 
						br.BRANCH_ID BRANCH_ID,	
						br.MERCHANT_ID MERCHANT_ID,			
						br.BRANCH_NAME BRANCH_NAME,
						br.AFFILIATEGROUPCODE,
						mer.CP_ID CPID,
						mer.LegalName LegalName,
						mer.MerchantFee MerchantFee,
						mer.vatcond,
						mer.PayeeDayType,
						mer.PayeeQtyOfDays,
						mer.AffiliateGroupCode merAFFCODE,
						CASE 
						  WHEN br.AFFILIATEGROUPCODE IS NULL or br.AFFILIATEGROUPCODE = ''  THEN mer.AFFILIATEGROUPCODE
						  ELSE br.AFFILIATEGROUPCODE
						END AS brAFFCODE
					from
						branches br,
						cp_merchant mer
					where mer.CP_ID = br.CP_ID
					group by br.BRANCH_ID, br.MERCHANT_ID, br.CP_ID
				) branch
				where
				branch.MERCHANT_ID = recon.MERCHANT_ID
				and branch.BRANCH_ID = recon.BRANCH_ID  
				and recon.RECON_ID <> ''
				and recon.PA_ID in (".$PA_ID.")
				group by recon.RECON_ID, recon.BRANCH_ID, recon.MERCHANT_ID
				ORDER BY recon.TRANSACTION_DATE_TIME 
			");		
		return $result;
	 }
	 
	  private function notRecon($PA_ID){
		$result = $this->db->query("select *
			from pa_detail
			where PA_ID = ".$PA_ID."
			and RECON_ID not in (select RECON_ID from reconcilation where PA_ID = ".$PA_ID.")
			");		
		return $result;
	 }
	 
	 private function notPADetail($PA_ID){
		$result = $this->db->query("select 
				recon.PA_ID,
				recon.RECON_ID,
				recon.MERCHANT_ID MID,
				recon.BRANCH_ID,
				SUM(recon.TRANSACTION_VALUE) totalAmount,		
				COUNT(recon.ID) totalPasses	
			from reconcilation recon
			where recon.PA_ID = ".$PA_ID."
				and recon.RECON_ID not in (select RECON_ID from pa_detail where PA_ID = ".$PA_ID." group by RECON_ID)
			group by recon.RECON_ID, recon.BRANCH_ID, recon.MERCHANT_ID
			");		
		return $result;
	 }
	 
	 private function not_refundPAdetail($PA_ID){
		$result = $this->db->query("select 
				ref.PA_ID,
				ref.RECON_ID,
				ref.MERCHANT_ID MID,
				ref.BRANCH_ID,	
				COUNT(ref.REFUND_ID) totalPasses	
			from refund ref
			where ref.PA_ID = ".$PA_ID."
				and ref.RECON_ID not in (select RECON_ID from pa_detail where PA_ID = ".$PA_ID." group by RECON_ID)
			group by ref.RECON_ID, ref.BRANCH_ID, ref.MERCHANT_ID
			");		
		return $result;
	 }
	 
	 
	 
	  private function getMerchantDetails($arr){
		  if(!empty($arr)) $arr = ' AND '.$arr;
		$result = $this->db->query("
				select 
					br.BRANCH_ID BRANCH_ID,	
					br.MERCHANT_ID MERCHANT_ID,			
					br.BRANCH_NAME BRANCH_NAME,
					br.AFFILIATEGROUPCODE,
					mer.CP_ID CPID,
					mer.LegalName LegalName,
					mer.MerchantFee MerchantFee,
					mer.vatcond,
					mer.PayeeDayType,
					mer.PayeeQtyOfDays,
					mer.AffiliateGroupCode merAFFCODE,
					CASE 
					  WHEN br.AFFILIATEGROUPCODE IS NULL or br.AFFILIATEGROUPCODE = ''  THEN mer.AFFILIATEGROUPCODE
					  ELSE br.AFFILIATEGROUPCODE
					END AS brAFFCODE
				from
					branches br,
					cp_merchant mer
				where mer.CP_ID = br.CP_ID 
				".$arr."
				group by br.BRANCH_ID, br.MERCHANT_ID, br.CP_ID
			");		
		return $result;
	 }
	 
}
