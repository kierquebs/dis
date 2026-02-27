<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Process extends MX_Controller {
	private $MODULE_ID;
	public function	__construct(){
	parent::__construct();
		$this->MODULE_ID = 2;
		if(!$this->auth->check_session()) redirect('login');		
		$this->load->model('Sys_model');
		$this->form_validation->run($this);
		if($this->auth->role_all($this->MODULE_ID) == false) redirect('404_override');
	}
	
	public function index(){ 		
		$data['css'][] = 'queue.css';
		$data['js'][] = 'main.js';
		$data['js'][] = 'home.js';
		$data['js'][] = 'process/main.js';			
				
		$data['cssDT'][] = '1.10.16/css/jquery.dataTables.min.css';
		$data['jsDT'][] = '1.10.16/js/jquery.dataTables.min.js';	
		
		$data['daysOfWeek'] = $this->my_lib->daysOfWeek();	
		//$where, $count, $select	
		$whereCut = "SPECIFIC_DATE <> ''";	
		$groupByCut = "SPECIFIC_DATE";	
		$data['specificDate'] = $this->Sys_model->getSpecificDate($whereCut, $groupByCut);
		$this->my_layout->layout_nav('process/index', $data);
	}
	
	
/*
* PROCESS PA HEADER AND DETAIL
*/
/*
** Payment Advice Format
--HEADER--
legal name
ton
trading name
address
reimbursement date
payee name
mean of payment
bank name
acct number		

--DETAIL--
branch
reimb no
rate %
no. of passes
total 
*/
	public function gen_pa(){
		if(!$this->input->is_ajax_request()) exit('No direct script access allowed');
		/*
		* GET ALL CHECK ITEMS
		*/
		$toProcess = $_POST['process'];
		$countProcess = count($toProcess);
		
		if($countProcess <> 0){
			$where['pcf.TYPE'] = $this->my_lib->paymentTerms($_POST['terms']);			
			$where['br.BRANCH_NAME <>'] = '';			
			if(isset($_POST['date'])  && $_POST['date'] != '' ) $where['DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <='] = $this->my_lib->setCFDate(htmlentities($_POST['date']));			
			if(isset($_POST['day'])  && $_POST['day'] != '' ) $where['pcf.SPECIFIC_DAY'] = htmlentities($_POST['day']);
			
			$PA_ARR = array();
			for($i=0; $i<=($countProcess-1); $i++){
				/*CREATE PA HEADER*/	
				$where['mer.MERCHANT_ID'] = $insert_header['MERCHANT_ID'] = $toProcess[$i];		
				$insert_header['DATE_CREATED'] = $insert_header['REIMBURSEMENT_DATE'] = $this->my_lib->current_date(); 
				$PA_ID = $this->Sys_model->i_paH($insert_header);							
				if(!empty($PA_ID)){
					$PA_ARR[] = $PA_ID;
					/*CREATE PA DETAIL*/	
					$getPCBranchesRow =  $this->Sys_model->getPCBranches($where, false, '', true);	
					foreach($getPCBranchesRow as $row){
						$totalFV = $row->totalAmount;
						$MFRATE = $this->my_lib->convertMFRATE($row->MerchantFee, true);
						$MF = $this->my_lib->computeMF($totalFV, $MFRATE);
						
						/*BUILD PA DETAIL INFO*/		
						$wRECON['ID'] = $row->ID;						
						$uRECON['PA_ID'] = $insert_detail['PA_ID'] = $PA_ID;
						$insert_detail['RECON_ID'] = $row->RECON_ID;		
						$insert_detail['BRANCH_ID'] = $row->BRANCH_ID;
						$insert_detail['RATE'] = $MFRATE;
						$insert_detail['NUM_PASSES'] = $row->totalTransaction;
						$insert_detail['TOTAL_FV'] = $totalFV;
						$insert_detail['MARKETING_FEE'] = $MF;
						$insert_detail['VAT'] = $this->my_lib->computeMF($totalFV, $MF);
						$insert_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, $MF);
						$insert_detail['DATE_CREATED'] = $this->my_lib->current_date(); 
						$this->Sys_model->i_paD($insert_detail);						
						$this->Sys_model->u_recon($wRECON, $uRECON);
					}
				}
			}
			$data['pa_arr'] = $PA_ARR;
			$data['success'] = true;
		}else $data['success'] = false;
		echo json_encode($data);
		//modules::run('Pdf_pa/group_gen', $PA_ARR);
		exit();
	}
/*
* ajax form request
*/	
	public function get_item(){
		if(!$this->input->is_ajax_request()) exit('No direct script access allowed');
		$data['result'] = ''; 
		$DateToday = $this->my_layout->setDate('', true);
		//*FILTER SEARCH		
		$data['day'] = $data['date'] =  $data['terms'] = $where = ''; //$_GET['terms'] = 2;
		
		$paymentTerms =  (isset($_GET['terms']) && $_GET['terms'] != '' ? $this->my_lib->paymentTerms($_GET['terms']) : '');
		$where = 'pcf.TYPE = "'.htmlentities($paymentTerms).'"';				
 				
		if($paymentTerms  != ''){
			if(isset($_GET['date'])  && $_GET['date'] != '' ){
				$data['date'] =  $date = htmlentities($this->input->get('date', true));
				$where .= ' AND pcf.SPECIFIC_DATE like "%'.$_GET['date'].'%"'; 
				$where .= $where_date = ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$this->my_lib->setCFDate($_GET['date']).'"';
			}
			
			if(isset($_GET['search'])  && $_GET['search'] != '' ){
				$SEARCH = htmlentities($this->input->get('search', true));
				$where .= ' AND (mer.LegalName = "'.$SEARCH.'")';
			}		
			
			if(isset($_GET['day'])  && $_GET['day'] != '' ){
				$data['day'] = $day = htmlentities($this->input->get('day', true));
				$where .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"'; 
				//$where .= $where_date = ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$this->my_lib->setCFDate($_GET['date']).'"';
			}
			$data['terms'] = $_GET['terms'];
		}
		//*END FILTER SEARCH		
		$data['where'] = $where;	
		$page = $this->my_layout->pagination();	
		$data['per_page'] = $page['per_page'];	
		$data['offset'] = $page['offset'];	
		$data['total'] =  $this->Sys_model->getPaymentCutoff($where, true);
		if($data['total'] != 0){	
			$temp_transac =  $this->Sys_model->getPaymentCutoff($where, false, $page);			
			$data['result'] = $this->arr_result($temp_transac, false, $where_date);
		}
		echo json_encode($data); 
		exit();
	}
	
		private function arr_result($temp_transac, $export = false, $where_date){
			$arr = array();
			foreach($temp_transac as $temp_row){ 
				$newRow = new stdClass(); 						
					$newRow->MID = $temp_row->MID;			
					$newRow->CPID = $temp_row->CPID;
					$newRow->LegalName = $temp_row->LegalName;
					$newRow->totalAmount = $temp_row->totalAmount;
					$newRow->totalBranch = $temp_row->totalBranch;					
					$newRow->get_userid = $this->auth->get_userid(); 				
					$newRow->uTransac = $this->auth->get_userid(); 
					//** check UNKNOWN TRANSACTIONS
						$where = 'br.BRANCH_NAME = "" AND pcf.TYPE ="'.$temp_row->TYPE.'" AND br.MERCHANT_ID = "'.$temp_row->MID.'" ';
						$where .= $where_date;
						$unkownTransac = $this->Sys_model->getPCBranches($where, false, '', false, "SUM(recon.TRANSACTION_VALUE) totalAmount"); 	
					$newRow->unkownTransac = $unkownTransac->num_rows();
					$newRow->uAmount = ($newRow->unkownTransac != 0 ? $unkownTransac->result()->row('totalAmount') : 0 );
					
					/*$newRow->access_edit = $this->User_model->check_access($this->MODULE_ID, array_merge($this->my_layout->_authorizeAction('store'), $this->my_layout->_authorizeAction('delete')));*/
					
				$arr[] = $newRow;
			}
			return $arr;
		}
	
	public function get_branches(){
		if(!$this->input->is_ajax_request() || !isset($_POST['id'])) exit('No direct script access allowed');
		$data['result'] = ''; 
		$DateToday = $this->my_layout->setDate('', true);
		$where['br.MERCHANT_ID'] = $_POST['id'];	
		$where['pcf.TYPE'] = $this->my_lib->paymentTerms($_POST['terms']);			
		//--check if branch has details
		if($_POST['validate'] == 1) $where['br.BRANCH_NAME <>'] = '';
		else $where['br.BRANCH_NAME'] = '';
		
		if(isset($_GET['date'])  && $_GET['date'] != '' ){
			$date = htmlentities($this->input->get('date', true));
			$where['DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <='] = $this->my_lib->setCFDate($_GET['date']);
		}
		if(isset($_GET['day'])  && $_GET['day'] != '' ){
			$p_day = htmlentities($this->input->get('day', true));
			$where .= ' AND pcf.SPECIFIC_DAY = "'.$_GET['day'].'"'; 
			//$where['DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <='] = $p_day;
		}
		
		//---
		$data['where'] = $where;	
		$page = $this->my_layout->pagination();	
		$data['per_page'] = $page['per_page'];	
		$data['offset'] = $page['offset'];	
		$data['total'] =  $this->Sys_model->getPCBranches($where, true);
		if($data['total'] != 0){	
			$temp_transac =  $this->Sys_model->getPCBranches($where, false, $page);			
			$data['result'] = $this->arr_result_br($temp_transac);
		}
		echo json_encode($data); 
		exit();
	}	
		private function arr_result_br($temp_transac, $export = false){
			$arr = array();
			foreach($temp_transac as $temp_row){ 
				$newRow = new stdClass(); 						
					$newRow->MID = $temp_row->MID;
					$newRow->LegalName = $temp_row->LegalName;
					$newRow->BRANCH_ID = $temp_row->BRANCH_ID;
					$newRow->BRANCH_NAME = $temp_row->BRANCH_NAME;
					$newRow->totalAmount = $temp_row->totalAmount;	
					$newRow->totalTransaction = $temp_row->totalTransaction;					
					$newRow->get_userid = $this->auth->get_userid(); 
					
				$arr[] = $newRow;
			}
			return $arr;
		}
}




