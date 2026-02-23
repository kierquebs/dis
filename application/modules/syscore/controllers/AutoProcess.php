<?php
/**
 * MAIN CONTROLLER
 * modules that can update corepass records directly to its database records
 * as data being updated it is not log to the audit trail of corepass actions
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class AutoProcess extends MX_Controller {
	private $POST_toProcess;
	private $POST_terms;
	private $POST_day;
	private $POST_date;
	private $get_userid;
	
	public function	__construct(){
	parent::__construct();	
		if($this->auth->check_session()) redirect('login');			
		$this->load->model('Sys_model');
		$this->load->model('Corepass_model');		
		//$MID = array(1610,2845,1900,5504,1905,6002,46,1904,76,7063,3913,72,1902,5902,49,1611,69,1901,44,92,1899,5505,5133,364,47,53,6377,6399,3887,3922,2004,86,40,43,57,38,77,65,1612,1609,5799,4772,5512,3932,3911,70,3920,31,3254,3919,3921,3918);
		//$MID = array(1609,1899,1900,1907);
		//$MID = array(1899);
		
		$MID = array(4398,4399);		
		$this->POST_toProcess = $MID;
		$this->POST_terms = 3; //2-Semi-Monthly, 3-Weekly, 4-Every 10 days, default-Monthly
		$this->POST_day = 'Sunday';
		$this->POST_date = '31'; //15, 31, 30
		$this->get_userid = 3; //Sir Jun
	}
	public function check_expecteddue(){
		$toProcess = array(7311);//1899);	
		$countProcess = count($toProcess);
		$PA_PayeeDayType = 2;//1;
		$PA_PayeeQtyOfDays = 15;//8;
		
		/** ADD TRIGGER TO DATABASE TO CHECK IF THERE'S PENDING ITEM */
		if($countProcess <> 0){			
			$whereRefund =  $where = 'pcf.TYPE = "'.$this->my_lib->paymentTerms($this->POST_terms).'" AND br.BRANCH_NAME <> ""';					
			if($this->POST_terms == 3){		
				if($this->POST_day != '' ){
					$day = $this->POST_day;
					$dateWhere = $this->my_lib->setCFDay($day);
					$where .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
					$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
					
					$whereRefund .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
					$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
				}
			}else{
				if($this->POST_date != '' ){
					$date = $this->POST_date;
					if(strlen ($date) <= 1){
						$where .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") "; 
						$whereRefund .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") "; 
					}else{
						$where .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
						$whereRefund .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
					}
					$dateWhere = $this->my_lib->setCFDate($date);
					$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';				
					$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
				}	
			}
		}		
		
		echo 'WARNING!!! <br /> This Page will process Payment Cutoff';
		echo '<br /><br /><b>Expected Due Date: '. $this->my_lib->computeExpectedDueDate($PA_PayeeDayType, $PA_PayeeQtyOfDays, $dateWhere);
		echo '<br /><br /><b>Payment Cutoff:</b> '. $where;
		echo '<br /><br /><b>Reversal Payment Cutoff:</b> '. $whereRefund;
		echo '<br /><br /><b>Merchant List:</b> <pre>';
		print_r($toProcess);
	}
	
	
	public function index(){
		$toProcess = $this->POST_toProcess;
		$countProcess = count($toProcess);

		/** ADD TRIGGER TO DATABASE TO CHECK IF THERE'S PENDING ITEM */
		if($countProcess <> 0){			
			$whereRefund =  $where = 'pcf.TYPE = "'.$this->my_lib->paymentTerms($this->POST_terms).'" AND br.BRANCH_NAME <> ""';					
			if($this->POST_terms == 3){		
				if($this->POST_day != '' ){
					$day = $this->POST_day;
					$dateWhere = $this->my_lib->setCFDay($day);
					$where .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
					$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
					
					$whereRefund .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
					$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
				}
			}else{
				if($this->POST_date != '' ){
					$date = $this->POST_date;
					if(strlen ($date) <= 1){
						$where .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") "; 
						$whereRefund .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") "; 
					}else{
						$where .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
						$whereRefund .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
					}
					$dateWhere = $this->my_lib->setCFDate($date);
					$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';				
					$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
				}	
			}
		}		
		
		echo 'WARNING!!! <br /> This Page will process Payment Cutoff';
		//echo '<br /><br /><b>Expected Due Date: '. $this->my_lib->computeExpectedDueDate($PA_PayeeDayType, $PA_PayeeQtyOfDays, $dateWhere);
		echo '<br /><br /><b>Payment Cutoff:</b> '. $where;
		echo '<br /><br /><b>Reversal Payment Cutoff:</b> '. $whereRefund;
		echo '<br /><br /><b>Merchant List:</b> <pre>';
		print_r($this->POST_toProcess);
		echo '</pre> <br /> <a href="/mp_dis/syscore/autoprocess/processing">CLICK ME TO PROCEED</a>';
	}
	
	public function forecast(){
		//semi-monthly
		//$dateWhere = '2022-02-15'; 
		//$date = '15';
		
		$merchant_id = ' and recon.MERCHANT_ID in (4398,4399)';
		$toProcess = $this->POST_toProcess;
		$countProcess = count($toProcess);

		/** ADD TRIGGER TO DATABASE TO CHECK IF THERE'S PENDING ITEM */
		if($countProcess <> 0){			
			$whereRefund =  $where = 'and pcf.TYPE = "'.$this->my_lib->paymentTerms($this->POST_terms).'" AND br.BRANCH_NAME <> ""';					
			if($this->POST_terms == 3){		
				if($this->POST_day != '' ){
					$day = $this->POST_day;
					$dateWhere = $this->my_lib->setCFDay($day);
					$where .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
					$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
					
					$whereRefund .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
					$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
				}
			}else{
				if($this->POST_date != '' ){
					$date = $this->POST_date;
					if(strlen ($date) <= 1){
						$where .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") "; 
						$whereRefund .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") "; 
					}else{
						$where .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
						$whereRefund .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
					}
					$dateWhere = $this->my_lib->setCFDate($date);
					$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';				
					$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
				}	
			}		
		$result = $this->db->query('select 
						combine_tbl.MID,
						combine_tbl.CPID,
						combine_tbl.LegalName,
						combine_tbl.SPECIFIC_DATE,
						combine_tbl.SPECIFIC_DAY,
						combine_tbl.TYPE,
						combine_tbl.PayeeDayType,
						combine_tbl.PayeeQtyOfDays,
						SUM(combine_tbl.totalAmount) AS totalAmount,	
						SUM(combine_tbl.totalPasses) AS totalPasses,	
						SUM(combine_tbl.refundAmount) refundAmount,			
						SUM(combine_tbl.totalPassesRef) totalPassesRef,	
						SUM(combine_tbl.totalBranch) AS totalBranch
					from
					(
						(
							select 
								TBL2.MID AS MID,
								TBL2.CPID AS CPID,
								TBL2.LegalName AS LegalName,
								TBL2.SPECIFIC_DATE AS SPECIFIC_DATE,
								TBL2.SPECIFIC_DAY AS SPECIFIC_DAY,
								TBL2.TYPE AS TYPE,
								TBL2.PayeeDayType AS PayeeDayType,
								TBL2.PayeeQtyOfDays AS PayeeQtyOfDays,
								SUM(TBL2.totalAmount) AS totalAmount,	
								SUM(TBL2.totalPasses) AS totalPasses,	
								SUM(TBL2.refundAmount) refundAmount,			
								SUM(TBL2.totalPassesRef) totalPassesRef,	
								COUNT(TBL2.totalBranch) AS totalBranch
							from 
							(
							select 
								TBL1.REDEEM_ID,
								TBL1.RECON_ID,
								TBL1.MID,
								TBL1.CPID,
								TBL1.LegalName,
								TBL1.SPECIFIC_DATE,
								TBL1.SPECIFIC_DAY,
								TBL1.TYPE,
								SUM(TBL1.totalAmount) totalAmount,		
								COUNT(TBL1.totalPasses) totalPasses,	
								SUM(TBL1.refundAmount) refundAmount,
								SUM(TBL1.totalPassesRef) totalPassesRef,		
								TBL1.totalBranch totalBranch,		
								TBL1.PayeeDayType,		
								TBL1.PayeeQtyOfDays
							from
							(
							select 
								redeem.REDEEM_ID,
								recon.RECON_ID,
								recon.MERCHANT_ID MID,
								mer.CP_ID CPID,
								mer.LegalName LegalName,
								SUM(recon.TRANSACTION_VALUE) totalAmount,	
								redeem.REDEEM_ID totalPasses,		
								SUM(CASE WHEN recon.REFUND_ID = ref.REFUND_ID && DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'" THEN  recon.TRANSACTION_VALUE ELSE 0 END) refundAmount,
								CASE WHEN recon.REFUND_ID = ref.REFUND_ID && DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <=  "'.$dateWhere.'" THEN 1 ELSE 0 END totalPassesRef,	
								recon.BRANCH_ID totalBranch,
								pcf.SPECIFIC_DATE,
								pcf.SPECIFIC_DAY,
								pcf.type TYPE,
								mer.PayeeDayType,
								mer.PayeeQtyOfDays
							from		
								redemption redeem		
								INNER JOIN reconcilation recon ON recon.REDEEM_ID = redeem.REDEEM_ID
								INNER JOIN branches br ON br.MERCHANT_ID = recon.MERCHANT_ID
								INNER JOIN payment_cutoff pcf ON pcf.MERCHANT_ID = br.MERCHANT_ID
								INNER JOIN cp_merchant mer ON mer.CP_ID = br.CP_ID
								LEFT JOIN refund ref on recon.REFUND_ID = ref.REFUND_ID
							where
								recon.PROD_ID = redeem.PROD_ID
								and br.BRANCH_ID = recon.BRANCH_ID 
								and recon.RECON_ID <> ""
								and recon.PA_ID = 0
								and redeem.STAGE = "RECONCILED"
								'.$merchant_id.'
								'.$where.'	
								group by recon.REDEEM_ID, recon.BRANCH_ID, recon.MERCHANT_ID 
								ORDER BY recon.TRANSACTION_DATE_TIME asc
							) TBL1
							group by TBL1.totalBranch, TBL1.MID 
							) TBL2
							group by TBL2.MID
						)
						UNION
						( SELECT
							DISTINCT tbl_refund.MERCHANT_ID AS MID,
							tbl_refund.CP_ID AS CPID,
							tbl_refund.LegalName AS LegalName,
							tbl_refund.SPECIFIC_DATE AS SPECIFIC_DATE,
							tbl_refund.SPECIFIC_DAY AS SPECIFIC_DAY,
							tbl_refund.TYPE AS TYPE,
							tbl_refund.PayeeDayType AS PayeeDayType,
							tbl_refund.PayeeQtyOfDays AS PayeeQtyOfDays,
							SUM(tbl_refund.totalAmount) AS totalAmount,	
							SUM(tbl_refund.totalPasses) AS totalPasses,	
							SUM(tbl_refund.refundAmount) refundAmount,			
							SUM(tbl_refund.totalPassesRef) totalPassesRef,		
							count(tbl_refund.BRANCH_ID) AS totalBranch
						FROM
							(select 
									recon.REDEEM_ID,
									recon.RECON_ID,
									recon.MERCHANT_ID,
									mer.CP_ID,
									mer.LegalName,
									pcf.SPECIFIC_DATE,
									pcf.SPECIFIC_DAY,
									pcf.TYPE,
									mer.PayeeDayType,
									mer.PayeeQtyOfDays,
									0 AS totalAmount,	
									0 AS totalPasses,	
									SUM(recon.TRANSACTION_VALUE) refundAmount,			
									COUNT(recon.REDEEM_ID) totalPassesRef,		
									br.BRANCH_ID
							from		
								refund ref 
								inner join reconcilation recon on recon.REFUND_ID = ref.REFUND_ID	
								inner join redemption redeem on recon.REDEEM_ID = redeem.REDEEM_ID
								inner join branches br on br.MERCHANT_ID = recon.MERCHANT_ID
								inner join payment_cutoff pcf on pcf.MERCHANT_ID = br.MERCHANT_ID	
								inner join cp_merchant mer on mer.CP_ID = br.CP_ID
							where
								br.BRANCH_ID = recon.BRANCH_ID 
								and ref.PA_ID = 0
								and recon.PROD_ID = redeem.PROD_ID
								and redeem.STAGE = "REVERSED"
								and br.BRANCH_NAME <> "" 
								'.$merchant_id.'
								and DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"
							group by recon.RECON_ID, recon.BRANCH_ID, recon.MERCHANT_ID
							ORDER BY recon.TRANSACTION_DATE_TIME  asc) tbl_refund
						group by tbl_refund.MERCHANT_ID 
						)
					) combine_tbl
		group by combine_tbl.MID')->result();
		
			echo '<table>
			 <tr>
				<td>MID 		</td>
				<td>CPID          </td>
				<td>LegalName     </td>
				<td>SPECIFIC_DATE </td>
				<td>SPECIFIC_DAY  </td>
				<td>TYPE          </td>
				<td>PayeeDayType  </td>
				<td>PayeeQtyOfDays</td>
				<td>totalAmount	  </td>
				<td>totalPasses	  </td>
				<td>refundAmount	</td>	
				<td>totalPassesRef </td>
				<td>totalBranch		</td>
			  </tr>';
			foreach($result as $temp_row){ 
			echo '<tr>	
				<td>'.$temp_row->MID			.'</td>
				<td>'.$temp_row->CPID          	.'</td>
				<td>'.$temp_row->LegalName     	.'</td>
				<td>'.$temp_row->SPECIFIC_DATE 	.'</td>
				<td>'.$temp_row->SPECIFIC_DAY  	.'</td>
				<td>'.$temp_row->TYPE          	.'</td>
				<td>'.$temp_row->PayeeDayType  	.'</td>
				<td>'.$temp_row->PayeeQtyOfDays	.'</td>
				<td>'.$temp_row->totalAmount	.'</td>
				<td>'.$temp_row->totalPasses	.'</td>
				<td>'.$temp_row->refundAmount	.'</td>
				<td>'.$temp_row->totalPassesRef	.'</td>
				<td>'.$temp_row->totalBranch	.'</td>
			</tr>';
			}
			echo '</table>';
		
		}
	}


	public function processing(){
		exit;
		echo 'START - PA generation DO NOT CLOSE THIS WINDOW... ';
		/*
		* GET ALL CHECK ITEMS
		*/
		$toProcess = $this->POST_toProcess;
		$countProcess = count($toProcess);

		/** ADD TRIGGER TO DATABASE TO CHECK IF THERE'S PENDING ITEM */

		if($countProcess <> 0){			
			$whereRefund =  $where = 'pcf.TYPE = "'.$this->my_lib->paymentTerms($this->POST_terms).'" AND br.BRANCH_NAME <> ""';					
			if($this->POST_terms == 3){		
				if($this->POST_day != '' ){
					$day = $this->POST_day;
					$dateWhere = $this->my_lib->setCFDay($day);
					$where .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
					$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
					
					$whereRefund .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
					$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
				}
			}else{
				if($this->POST_date != '' ){
					$date = $this->POST_date;
					if(strlen ($date) <= 1){
						$where .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") "; 
						$whereRefund .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") "; 
					}else{
						$where .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
						$whereRefund .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
					}
					$dateWhere = $this->my_lib->setCFDate($date);
					$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';				
					$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
				}	
			}
			
			$this->load->helper('my_helper');
			
			$PA_ARR = array(); $whereMerchant = $PA_ID = '';
			for($i=0; $i<=$countProcess; $i++){
				if(!empty($toProcess[$i])){
					/*CREATE PA HEADER*/
					$whereMerchant = ' AND br.MERCHANT_ID = "'.$toProcess[$i].'"'; 					
					$whereBranch = 	$where;
					$whereBranch .= $whereMerchant;  
					
					$whereBranchRef = $whereRefund;	
					$whereBranchRef .= $whereMerchant; 					
					
					$insert_header['MERCHANT_ID'] = $toProcess[$i];	
					$insert_header['DATE_CREATED'] = $insert_header['REIMBURSEMENT_DATE'] = $this->my_lib->current_date(); 
					$insert_header['USER_ID'] = $this->get_userid;			
					$getPCBranchesRow =  $this->Sys_model->getPCBranch_PA_REVERSAL($whereBranch, false, '', $dateWhere, $whereMerchant);	 //getPCBranch_PA
					$brRowNum = $getPCBranchesRow->num_rows();	
					/*CREATE PA DETAIL*/	
					if($brRowNum != 0){						
						$AFFCODE_li = array_group_by($getPCBranchesRow->result(), "brAFFCODE");						
						$AFFCODE_row = json_decode(json_encode($AFFCODE_li), true);
						
						foreach($AFFCODE_row as $k => $v){
							$PA_ID = $this->Sys_model->i_paH($insert_header);
							if(!empty($PA_ID)){		
								$PA_ARR[] = $PA_ID;
								foreach ($v as $row) {		
									$show = $whereAFFCODE = $u_paH = $insert_detail =  $where_paD = '';						
									/*
									** fields available for process table **
										recon.RECON_ID,
										recon.MERCHANT_ID MID,
										mer.CP_ID CPID,
										mer.LegalName LegalName,
										mer.MerchantFee MerchantFee,
										mer.vatcond,
										mer.PayeeDayType,
										mer.PayeeQtyOfDays,
										SUM(recon.TRANSACTION_VALUE) totalAmount,		
										COUNT(redeem.REDEEM_ID) totalPasses,		
										br.BRANCH_ID BRANCH_ID,	
										br.BRANCH_NAME BRANCH_NAME,
										pcf.SPECIFIC_DATE,
										pcf.SPECIFIC_DAY,
										pcf.type
									*/							
						
									/**
									* IF branch - AFFILIATIONCODE is not null then get data from cp_agreement
									* ELSE proceed with the old process
									*/																		
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
									$totalRefund = ($row['refundPostAmount'] == NULL ? 0 : $row['refundPostAmount']); //total amount of refund 																		
									
									$totalMFV = $totalFV - $totalRefund;
									if($totalMFV < 0) $totalMFV = 0;
									$MF = $this->my_lib->computeMF($totalMFV, $percentMF, '', false);
									
									/*BUILD PA DETAIL INFO*/		 	 		
									$where_paD['PA_ID'] = $uRECON['PA_ID'] = $insert_detail['PA_ID'] = $PA_ID;
									$where_paD['RECON_ID'] = $insert_detail['RECON_ID'] = $row['RECON_ID'];		
									$where_paD['BRANCH_ID'] = $insert_detail['BRANCH_ID'] = $row['BRANCH_ID'];
									$insert_detail['RATE'] = $percentMF;
									$insert_detail['NUM_PASSES'] = $row['totalPasses'];
									$insert_detail['TOTAL_FV'] = $totalFV;
									$insert_detail['TOTAL_REFUND'] = $totalRefund;
									$insert_detail['DATE_CREATED'] = $this->my_lib->current_date();
									
									if($totalMFV == 0){										
										$insert_detail['MARKETING_FEE'] = 0; 
										$insert_detail['VAT'] = 0; 
										$insert_detail['NET_DUE'] = 0;
									}else{
										$insert_detail['MARKETING_FEE'] = $MF; 
										$insert_detail['VAT'] = $this->my_lib->computeVAT($totalMFV, $percentMF, $VAT, FALSE); 
										$insert_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalMFV, $percentMF, $VAT, FALSE);
									}
																	
									$checkPAD = $this->Sys_model->v_paD($where_paD, true);									
									if($checkPAD == 0){
										$paD_ID = $this->Sys_model->i_paD($insert_detail);
										
										if(!empty($paD_ID)){
											$whereUBranch = $whereBranch.' AND br.BRANCH_ID = "'.$row['BRANCH_ID'].'"';
											$this->Sys_model->u_recon($whereUBranch, $uRECON);	
											
											$whereUBranchRef = $whereBranchRef.' AND br.BRANCH_ID = "'.$row['BRANCH_ID'].'"';
											$this->Sys_model->u_refund($whereUBranchRef, $uRECON);	
										}
									}
								}
								$u_paH['MERCHANT_FEE']= $PA_MerchantFee;
								$u_paH['vatcond']= $PA_VAT;
								$u_paH['ExpectedDueDate']= $this->my_lib->computeExpectedDueDate($PA_PayeeDayType, $PA_PayeeQtyOfDays, $dateWhere); //$insert_header['REIMBURSEMENT_DATE']
								$this->Sys_model->u_paH(array('PA_ID'=>$PA_ID), $u_paH);
								//AUDIT TRAIL HERE							
								//$this->Action_model->audit_save(2, array('PA_ID'=>$PA_ID));
								//$this->Action_model->ajax_update(); //update session for AJAX REQUEST	
								
								echo '<br /> PA_ID : '.$PA_ID;
							}							
						}
					}
				}
			} 
			echo 'Done Processing - '.$this->my_lib->paymentTerms($this->POST_terms);
		}else{
			echo 'No selected Merchant';
		}
	}
}
