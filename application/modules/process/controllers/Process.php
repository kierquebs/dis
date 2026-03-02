<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Process extends MX_Controller {
	private $MODULE_ID;
	private $checkUpload;
	public function	__construct(){
	parent::__construct();
		$this->MODULE_ID = 2;
		if(!$this->auth->check_session()) redirect('login');		
		$this->load->model('Sys_model');	
		$this->load->model('Action_model');
		$this->form_validation->run($this);
		if($this->auth->role_all($this->MODULE_ID) == false) redirect('404_override');
		
		$this->checkUpload = getenv('checkUpload') !== false ? getenv('checkUpload') : 1; //UPLOADS
	}
	
	public function getReimbursementUserIds(){
		$where['utype_id'] = 2; 

		$this->db->from('user');	
		$this->db->where($where);	
		$this->db->select('user_id');
		$result = $this->db->get();	

		$json = json_encode($result->result(), JSON_PRETTY_PRINT);

		// Decode JSON to PHP array
		$array = json_decode($json, true);

		// Extract user_id values and join them into a comma-separated string
		$user_ids = array_column($array, 'user_id');
		$comma_separated = implode(',', $user_ids);

		return $comma_separated;
	}

	public function index(){ 
		$data['css'][] = 'queue.css';
		$data['js'][] = 'main.js';
		$data['js'][] = 'home.js';		
				
		$data['cssDT'][] = '1.10.16/css/jquery.dataTables.min.css';
		$data['jsDT'][] = '1.10.16/js/jquery.dataTables.min.js';	
	
		//$whereU['user_id'] = $this->auth->get_userid();
		
		$userIds = $this->getReimbursementUserIds();
		$whereU['generated'] = 0; 
		$whereU['merchant_fee !='] = ''; 
		$data['generate'] = $this->Sys_model->v_paH_new($whereU, true, null, $userIds);
		
		//$data['generate'] = $this->Sys_model->v_paH($whereU, true);

		$data['checkUpload'] = $this->checkUpload;
		//if($this->checkUpload == 1) $data['checkUpload'] = $this->Sys_model->v_auditUpload($whereUpload, true);	
				
		$this->my_layout->layout_nav('process/index', $data);				
	}

	public function _index(){ 
		//redirect('process/Wrecon');
		
		$data['css'][] = 'queue.css';
		$data['js'][] = 'main.js';
		$data['js'][] = 'home.js';
		$data['js'][] = 'process/main.js';			
				
		$data['cssDT'][] = '1.10.16/css/jquery.dataTables.min.css';
		$data['jsDT'][] = '1.10.16/js/jquery.dataTables.min.js';	
	
		$whereU['user_id'] = $this->auth->get_userid();
		$whereU['generated'] = 0; 
		$whereU['merchant_fee !='] = ''; 
		$data['generate'] = $this->Sys_model->v_paH($whereU, true);
		
		$data['daysOfWeek'] = $this->my_lib->daysOfWeek();	
		//$where, $count, $select	
		$whereCut = "SPECIFIC_DATE <> ''";	
		$groupByCut = "SPECIFIC_DATE";	
		$data['specificDate'] = $this->Sys_model->getSpecificDate($whereCut, $groupByCut);
		
		$whereUpload = "module_name in ('reconciliation_temp', 'redemption', 'reconciliation') and date_format(date_created, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d')";
		
		$data['checkUpload'] = $this->checkUpload;
		//if($this->checkUpload == 1) $data['checkUpload'] = $this->Sys_model->v_auditUpload($whereUpload, true);		
		
		$this->my_layout->layout_nav('process/index', $data);				
	}


	/*
	* PROCESS PA HEADER AND DETAIL
	*/
	public function _gen_pa(){
		/*
		* GET ALL CHECK ITEMS
		*/
		$toProcess = $_POST['process'];
		$countProcess = count($toProcess);

		/** ADD TRIGGER TO DATABASE TO CHECK IF THERE'S PENDING ITEM */

		if($countProcess <> 0){			
			$whereRefund =  $where = 'pcf.TYPE = "'.$this->my_lib->paymentTerms($_POST['terms']).'" AND br.BRANCH_NAME <> ""';					
			if($_POST['terms'] == 3){		
				if(isset($_POST['day'])  && $_POST['day'] != '' ){
					$day = htmlentities($this->input->post('day', true));
					$dateWhere = $this->my_lib->setCFDay($day);
					$where .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
					$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
					
					$whereRefund .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
					$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
				}
			}else{
				if(isset($_POST['date'])  && $_POST['date'] != '' ){
					$date = htmlentities($this->input->post('date', true));
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
					$insert_header['USER_ID'] = $this->auth->get_userid();			
					$getPCBranchesRow =  $this->Sys_model->getPCBranch_PA_REVERSAL($whereBranch, false, '', $dateWhere, $whereMerchant);	 //getPCBranch_PA
					$brRowNum = $getPCBranchesRow->num_rows();	
					/*CREATE PA DETAIL*/	
					if($brRowNum != 0){						
						$AFFCODE_li = array_group_by($getPCBranchesRow->result(), "brAFFCODE");						
						$AFFCODE_row = json_decode(json_encode($AFFCODE_li), true);
						
						//echo '<pre>';
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
								$this->Action_model->audit_save(2, array('PA_ID'=>$PA_ID));
								$this->Action_model->ajax_update(); //update session for AJAX REQUEST	
							}
						}
						//echo '</pre>';
					}
				}
			} 
			//die();
			redirect('process');
		}else{
			redirect('transaction');
		}
	}
/*
* ajax form request
*/	
	public function _get_item(){
		if(!$this->input->is_ajax_request()) exit('No direct script access allowed');
		
		$dateWhere = $data['result'] = ''; 
		$DateToday = $this->my_layout->setDate('', true);
		//*FILTER SEARCH		
		$pcfWHere = $data['day'] = $data['date'] =  $data['terms'] = $where = ''; 

		$data['date_coverage'] = $data['date_today'] = $data['where'] = $data['per_page'] = $data['offset'] = $data['total'] =  $data['result'] = '';				
				
		if($this->checkUpload == 1){		
			$paymentTerms =  (isset($_GET['terms']) && $_GET['terms'] != '' ? $this->my_lib->paymentTerms($_GET['terms']) : '');
			$pcfWHere = $where = 'pcf.TYPE = "'.htmlentities($paymentTerms).'"';		
			if($paymentTerms  != ''){		
				$data['terms'] = $_GET['terms'];	
				if(isset($_GET['search'])  && $_GET['search'] != '' ){
					$SEARCH = $this->input->get('search', true);
					$where .= ' AND (mer.LegalName like "%'.$SEARCH.'%")';
				}						
				if($data['terms'] == 3){		
					if(isset($_GET['day'])  && $_GET['day'] != '' ){
						$data['day'] = $day = htmlentities($this->input->get('day', true));
						$dateWhere = $this->my_lib->setCFDay($day);
							$SPECIFIC_DAY = ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
						$where .= $SPECIFIC_DAY;
						$pcfWHere .= $SPECIFIC_DAY;
						$where .= $where_date = ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
					}
				}else{
					if(isset($_GET['date'])  && $_GET['date'] != '' ){
						$data['date'] =  $date = htmlentities($this->input->get('date', true));
						$dateWhere = $this->my_lib->setCFDate($date);
						if(strlen ($date) <= 1){
							$SPECIFIC_DATE = " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") "; 
						}else {
							$SPECIFIC_DATE =  ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
						}
						$where .= $SPECIFIC_DATE;
						$pcfWHere .= $SPECIFIC_DATE;
						$where .= $where_date = ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
					}	
				}
			}
			//*END FILTER SEARCH		 
			$data['date_coverage'] = date('M d Y' , strtotime($dateWhere));	
			$data['date_today'] = strtotime($DateToday);	
			$data['where'] = $where;	
			$page = $this->my_layout->pagination();	
			$data['per_page'] = $page['per_page'];	
			$data['offset'] = $page['offset'];	
			$data['total'] =  $this->Sys_model->getPaymentCutoff_REVERSAL($where, true, '', $dateWhere, $pcfWHere); //getPaymentCutoff
			if($data['total'] != 0){	
				$temp_transac =  $this->Sys_model->getPaymentCutoff_REVERSAL($where, false, $page,$dateWhere, $pcfWHere);	//getPaymentCutoff	
				$data['result'] = $this->arr_result($temp_transac, false, $where_date, $dateWhere);
			}
		}
		echo json_encode($data); 		
		exit();
	}
	
		private function arr_result($temp_transac, $export, $where_date, $dateWhere){
			$arr = array();
			foreach($temp_transac as $temp_row){ 
				$newRow = new stdClass(); 						
					$newRow->MID = $temp_row->MID;			
					$newRow->CPID = $this->my_lib->digitalID($temp_row->CPID);
					$newRow->LegalName = $temp_row->LegalName;
					$newRow->totalAmount = $temp_row->totalAmount;
					$newRow->totalTransaction = $temp_row->totalPasses;	
					$newRow->totalBranch = $temp_row->totalBranch;				
					$newRow->get_userid = $this->auth->get_userid(); 				
					$newRow->uTransac = $this->auth->get_userid(); 
					
					//** check UNKNOWN TRANSACTIONS
						$where3 = $where2 = $where = 'pcf.TYPE ="'.$temp_row->TYPE.'" AND br.MERCHANT_ID = "'.$temp_row->MID.'" ';
						$where .= ' AND br.BRANCH_NAME = ""';
						$where .= $where_date;
						$unkownTransac = $this->Sys_model->getPaymentCutoffBranch($where, false);
						$newRow->unkownTransac = $unkownTransac->num_rows();
						$newRow->uAmount = ($newRow->unkownTransac != 0 ? $unkownTransac->row()->totalAmount : 0 );	
					//** check VALID TRANSACTIONS
						$where2 .= ' AND br.BRANCH_NAME <> ""';
						$where2 .= $where_date;
						$newRow->validTransac = $this->Sys_model->getPaymentCutoffBranch($where2, true);
						
					
					//** get refund records	
						//$whereRefund = 'DATE_FORMAT(ref.DATE_CREATED, "%Y-%m-%d") = "'.$dateWhere.'" AND recon.MERCHANT_ID = "'.$temp_row->MID.'"';
					//$refundTransac = $this->Sys_model->getPaymentCutoff_Refund($whereRefund, false);
					$newRow->refundAmount = $temp_row->refundAmount; //$refundTransac->row()->refundAmount;	
					$newRow->totalRefTransac = $temp_row->totalPassesRef; //$refundTransac->row()->totalPassesRef;
					
					//** calculate expected due date for this merchant
					$ExpectedDueDate = $this->my_lib->computeExpectedDueDate($temp_row->PayeeDayType, $temp_row->PayeeQtyOfDays, $dateWhere);
					$newRow->ExpectedDueDate = date('M d Y' , strtotime($ExpectedDueDate));		
					$newRow->ExpectedDueDateCon = strtotime($ExpectedDueDate);				
				$arr[] = $newRow;
			}
			return $arr;
		}
	
	public function _get_branches(){
		if(!$this->input->is_ajax_request() || !isset($_POST['id'])) exit('No direct script access allowed');
		$data['result'] = '';
		$DateToday = $this->my_layout->setDate('', true);	
		$where = 'br.MERCHANT_ID = "'.$_POST['id'].'" AND pcf.TYPE = "'.$this->my_lib->paymentTerms($_POST['terms']).'"';		
		//--check if branch has details
		if($_POST['validate'] == 1) $where .= ' AND br.BRANCH_NAME <> ""';
		else $where .= ' AND br.BRANCH_NAME = ""'; 
		
		if($_POST['terms'] == 3){		
			if(isset($_POST['day'])  && $_POST['day'] != '' ){ 
				$day = htmlentities($this->input->post('day', true));
				$where .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
				$where .= $where_date = ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$this->my_lib->setCFDay($day).'"'; //$this->my_lib->setCFDate($_GET['date'])
			}
		}else{
			if(isset($_POST['date'])  && $_POST['date'] != '' ){
				$date = htmlentities($this->input->post('date', true));
				if(strlen ($date) <= 1){						
					$where .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") "; 
				}else{
					$where .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
				}
				$where .= $where_date = ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$this->my_lib->setCFDate($date).'"'; //$this->my_lib->current_date()
			}	
		}
		$data['where'] = $where;	
		$page = $this->my_layout->pagination();	
		$data['per_page'] = $page['per_page'];	
		$data['offset'] = $page['offset'];	
		$data['total'] =  $this->Sys_model->getPaymentCutoffBranch($where, true);
		if($data['total'] != 0){	
			$temp_transac =  $this->Sys_model->getPaymentCutoffBranch($where, false, $page);			
			$data['result'] = $this->arr_result_br($temp_transac);
		}
		echo json_encode($data); 
		exit();
	}	
		
		private function arr_result_br($temp_transac, $export = false){
			$arr = array();
			foreach($temp_transac->result() as $temp_row){ 
				$newRow = new stdClass(); 						
					$newRow->MID = $temp_row->MID;
					$newRow->LegalName = $temp_row->LegalName;
					$newRow->BRANCH_ID = $temp_row->BRANCH_ID;
					$newRow->BRANCH_NAME = $temp_row->BRANCH_NAME;
					$newRow->totalAmount = $temp_row->totalAmount;	
					$newRow->totalTransaction = $temp_row->totalPasses;					
					$newRow->get_userid = $this->auth->get_userid(); 					
				$arr[] = $newRow;
			}
			return $arr;
		}
}




