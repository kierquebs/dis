<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rs extends MX_Controller {
	private $MODULE_ID;
	public function	__construct(){
	parent::__construct();
		$this->MODULE_ID = 6;
		if(!$this->auth->check_session()) redirect('login');		
		$this->load->model('Sys_model');	
		$this->load->model('Action_model');
		$this->form_validation->run($this);
		if($this->auth->role_all($this->MODULE_ID) == false) redirect('404_override');
	}
	
	public function index(){ 
		$data['css'][] = 'queue.css';
		$data['js'][] = 'main.js';
		$data['js'][] = 'home.js';
		$data['js'][] = 'process/rs_main.js';			
				
		$data['cssDT'][] = '1.10.16/css/jquery.dataTables.min.css';
		$data['jsDT'][] = '1.10.16/js/jquery.dataTables.min.js';	
	
		$whereU['user_id'] = $this->auth->get_userid();
		$whereU['generated'] = 0; 
		$data['generate'] = $this->Sys_model->v_rsH($whereU, true);
		
		$data['daysOfWeek'] = $this->my_lib->daysOfWeek();	
		//$where, $count, $select	
		$whereCut = "SPECIFIC_DATE <> ''";	
		$groupByCut = "SPECIFIC_DATE";	
		$data['specificDate'] = $this->Sys_model->getSpecificDate($whereCut, $groupByCut);
		
		$this->my_layout->layout_nav('process/rs', $data);				
	}
	
	/*
	* PROCESS RS HEADER AND DETAIL
	*/
	public function gen_rs(){
		/*
		* GET ALL CHECK ITEMS
		*/
		$toProcess = $_POST['process'];
		$countProcess = count($toProcess);

		/** ADD TRIGGER TO DATABASE TO CHECK IF THERE'S PENDING ITEM */

		if($countProcess <> 0){			
			$where = 'pcf.TYPE = "'.$this->my_lib->paymentTerms($_POST['terms']).'" AND br.BRANCH_NAME <> ""';					
			if($_POST['terms'] == 3){		
				if(isset($_POST['day'])  && $_POST['day'] != '' ){
					$day = htmlentities($this->input->post('day', true));
					$dateWhere = $this->my_lib->setCFDay($day);
					$where .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
					$where .= ' AND DATE_FORMAT(conv.CREATED_AT, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
				}
			}else{
				if(isset($_POST['date'])  && $_POST['date'] != '' ){
					$date = htmlentities($this->input->post('date', true));
					$dateWhere = $this->my_lib->setCFDate($date);
					$where .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
					$where .= ' AND DATE_FORMAT(conv.CREATED_AT, "%Y-%m-%d") <= "'.$dateWhere.'"';
				}	
			}
			
			for($i=0; $i<=$countProcess; $i++){
				if(!empty($toProcess[$i])){
					/*CREATE PA HEADER*/
					$whereBranch = 	$where;
					$whereBranch .= ' AND br.MERCHANT_ID = "'.$toProcess[$i].'"';
					$checkBranch = $this->Sys_model->getRSPayCutoffMerBranch($whereBranch); 

					if($checkBranch->num_rows() != 0){
						$whereBRLI = $PA_ID = '';
						foreach($checkBranch->result() as $brRow){
							$BRANCH_ID = $brRow->BRANCH_ID;
							$whereBRLI = $whereBranch .' AND br.BRANCH_ID = "'.$BRANCH_ID.'"';
							/* GET LIST OF BRANCHES UNDER THIS MERCHANT */					
							$insert_header['BRANCH_ID'] = $BRANCH_ID; 		
							$insert_header['MERCHANT_ID'] = $brRow->MERCHANT_ID; 
							$insert_header['DATE_CREATED'] = $insert_header['REIMBURSEMENT_DATE'] = $REIMBURSEMENT_DATE = $this->my_lib->current_date(); 
							$insert_header['USER_ID'] = $this->auth->get_userid();		
							$getPCBranchesRow =  $this->Sys_model->getPCBranch_RS($whereBRLI, false);	 //getPaymentCutoffBranch
							$brRowNum = $getPCBranchesRow->num_rows();	
							
							//echo '<br />'.$BRANCH_ID.' '.$brRow->MERCHANT_ID.' '.$brRowNum .'<br />';
							//echo '<pre>'; print_r($getPCBranchesRow->result());echo '</pre>';
							/*CREATE RS DETAIL*/ 	  
							if($brRowNum != 0){	
								/*$paResult = $getPCBranchesRow->result();
								$arrData['PA_PayeeDayType'] = $PA_PayeeDayType = $paResult[0]->PayeeDayType;
								$arrData['PA_PayeeQtyOfDays'] = $PA_PayeeQtyOfDays =$paResult[0]->PayeeQtyOfDays;		
								$arrData['dateWhere'] = $dateWhere;							
								$arrData['ExpectedDueDate'] = $ExpectedDueDate = $this->my_lib->computeExpectedDueDate($PA_PayeeDayType, $PA_PayeeQtyOfDays, $dateWhere);
								echo '<pre>'; print_r($whereBRLI);  print_r($arrData); echo '</pre>';*/

								$RS_ID = $this->Sys_model->i_rsH($insert_header);	
								if(!empty($RS_ID)){							
									foreach($getPCBranchesRow->result() as $row){
										$PA_MerchantFee = $row->MerchantFee;
										$PA_PayeeDayType = $row->PayeeDayType;
										$PA_PayeeQtyOfDays = $row->PayeeQtyOfDays;
										$PA_VAT = $row->vatcond;
										$VAT = $this->my_lib->checkVAT($PA_VAT);										
										$totalFV = $row->totalAmount;
										$percentMF = $this->my_lib->convertMFRATE($row->MerchantFee, true);
										$MF = $this->my_lib->computeMF($totalFV, $percentMF);
										
										/*BUILD PA DETAIL INFO*/					
										$uRECON['RS_ID'] = $insert_detail['RS_ID'] = $RS_ID;
										$uRECON['STAGE'] = $this->my_lib->rsStages($row->STAGE); //update new status 
										$insert_detail['COV_ID'] = $row->COV_ID;		
										$insert_detail['RATE'] = $percentMF;
										$insert_detail['TOTAL_FV'] = $totalFV;
										$insert_detail['MARKETING_FEE'] = $MF; 
										$insert_detail['VAT'] = $this->my_lib->computeVAT($totalFV, $percentMF, $VAT);
										$insert_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, $percentMF, $VAT);
										$insert_detail['DATE_CREATED'] = $this->my_lib->current_date(); 								
										$this->Sys_model->i_rsD($insert_detail);										
										$this->Sys_model->u_conv($whereBRLI, $uRECON);
									}
									$ExpectedDueDate =  $this->my_lib->computeExpectedDueDate($PA_PayeeDayType, $PA_PayeeQtyOfDays, $dateWhere);
									$u_paH['MERCHANT_FEE']= $PA_MerchantFee;
									$u_paH['VATCOND']= $PA_VAT; 
									$u_paH['ExpectedDueDate'] =	$ExpectedDueDate;  
									$u_paH['RS_NUMBER']= $this->my_lib->rsNumber($BRANCH_ID, $ExpectedDueDate, $RS_ID);
									$this->Sys_model->u_rsH(array('RS_ID'=>$RS_ID), $u_paH); //UPDATE RS HEADER
									//AUDIT TRAIL HERE							
									$this->Action_model->audit_save(2, array('RS_ID'=>$RS_ID));
									$this->Action_model->ajax_update(); //update session for AJAX REQUEST	
								}
							}
						}
					}
				}
			}	
			redirect('process/rs');
		}else{
			redirect('conversion');
		} 
	}
	
/*
* ajax form request
*/	
	public function get_item(){
		if(!$this->input->is_ajax_request()) exit('No direct script access allowed');
		$dateWhere = $data['result'] = ''; 
		$DateToday = $this->my_layout->setDate('', true);
		//*FILTER SEARCH		
		$data['day'] = $data['date'] =  $data['terms'] = $where = ''; 
		$paymentTerms =  (isset($_GET['terms']) && $_GET['terms'] != '' ? $this->my_lib->paymentTerms($_GET['terms']) : '');
		$where = 'pcf.TYPE = "'.htmlentities($paymentTerms).'"';
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
					$where .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
					$where .= $where_date = ' AND DATE_FORMAT(conv.CREATED_AT, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
				}
			}else{
				if(isset($_GET['date'])  && $_GET['date'] != '' ){
					$data['date'] =  $date = htmlentities($this->input->get('date', true));
					$dateWhere = $this->my_lib->setCFDate($date);
					$where .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
					$where .= $where_date = ' AND DATE_FORMAT(conv.CREATED_AT, "%Y-%m-%d") <= "'.$dateWhere.'"'; 
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
		$data['total'] =  $this->Sys_model->getRSPaymentCutoff($where, true);
		if($data['total'] != 0){	
			$temp_transac =  $this->Sys_model->getRSPaymentCutoff($where, false, $page);			
			$data['result'] = $this->arr_result($temp_transac, false, $where_date, $dateWhere);
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
						$unkownTransac = $this->Sys_model->getRSPaymentCutoffBranch($where, false);
						$newRow->unkownTransac = $unkownTransac->num_rows();
						$newRow->uAmount = ($newRow->unkownTransac != 0 ? $unkownTransac->row()->totalAmount : 0 );	
					//** check VALID TRANSACTIONS
						$where2 .= ' AND br.BRANCH_NAME <> ""';
						$where2 .= $where_date;
						$newRow->validTransac = $this->Sys_model->getRSPaymentCutoffBranch($where2, true);
					//** calculate expected due date for this merchant
					$ExpectedDueDate = $this->my_lib->computeExpectedDueDate($temp_row->PayeeDayType, $temp_row->PayeeQtyOfDays, $dateWhere);
					$newRow->ExpectedDueDate = date('M d Y' , strtotime($ExpectedDueDate));		
					$newRow->ExpectedDueDateCon = strtotime($ExpectedDueDate);				
				$arr[] = $newRow;
			}
			return $arr;
		}
	
	public function get_branches(){
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
				$where .= $where_date = ' AND DATE_FORMAT(conv.CREATED_AT, "%Y-%m-%d") <= "'.$this->my_lib->setCFDay($day).'"'; //$this->my_lib->setCFDate($_GET['date'])
			}
		}else{
			if(isset($_POST['date'])  && $_POST['date'] != '' ){
				$date = htmlentities($this->input->post('date', true));
				$where .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"'; 
				$where .= $where_date = ' AND DATE_FORMAT(conv.CREATED_AT, "%Y-%m-%d") <= "'.$this->my_lib->setCFDate($date).'"'; //$this->my_lib->current_date()
			}	
		}
		$data['where'] = $where;	
		$page = $this->my_layout->pagination();	
		$data['per_page'] = $page['per_page'];	
		$data['offset'] = $page['offset'];	
		$data['total'] =  $this->Sys_model->getRSPaymentCutoffBranch($where, true);
		if($data['total'] != 0){	
			$temp_transac =  $this->Sys_model->getRSPaymentCutoffBranch($where, false, $page);			
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




