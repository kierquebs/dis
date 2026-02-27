<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Conversion extends MX_Controller {
	private $MODULE_ID;
	public function	__construct(){
	parent::__construct();
		$this->MODULE_ID = 7;
		if(!$this->auth->check_session()) redirect('login');		
		$this->load->model('Sys_model');	
		$this->form_validation->run($this);
		if($this->auth->role_all($this->MODULE_ID) == false) redirect('404_override');
	}
	
	public function index(){
		$data['css'][] = 'queue.css';
		$data['js'][] = 'main.js';
		$data['js'][] = 'home.js';
		$data['js'][] = 'conversion/main.js';
		$data['RS_NUMBER'] = (isset($_GET['rsnum']) ? $_GET['rsnum'] : '');				
		$data['cssDT'][] = '1.10.16/css/jquery.dataTables.min.css';
		$data['jsDT'][] = '1.10.16/js/jquery.dataTables.min.js';	
		$this->my_layout->layout_nav('conversion/index', $data);
	}
/*
* ajax form request
*/	

	public function get_item(){
		//if(!$this->input->is_ajax_request()) exit('No direct script access allowed');
		$stat = $data['result'] = ''; 
		$DateToday = $this->my_layout->setDate('', true);
		//*FILTER SEARCH		
		$SEARCH = $where = ''; 		
		if(isset($_GET['search']) && $_GET['search'] != '' ){
			$SEARCH = htmlentities($this->input->get('search', true));
			$where .= (empty($where) ? '' : ' AND ').' (mer.LegalName like "%'.$SEARCH.'%" OR br.MERCHANT_ID = "'.$SEARCH.'") ';
		}		
		if(isset($_GET['branch']) && $_GET['branch'] != '' ){
			$branch = htmlentities($this->input->get('branch', true));
			$where .= (empty($where) ? '' : ' AND ').' (conv.BRANCH_NAME like "%'.$branch.'%" OR br.BRANCH_ID = "'.$branch.'") ';
		}		
		if(isset($_GET['panumber']) && $_GET['panumber'] != '' ){
			$pa_number = htmlentities($this->input->get('panumber', true));
			$where .= (empty($where) ? '' : ' AND ').' conv.RS_NUMBER in ('.$this->my_lib->multiPANUM($pa_number,TRUE).')';
		}			
		if(isset($_GET['voucher']) && $_GET['voucher'] != '' ){
			$voucher = $this->my_lib->multiVoucher(htmlentities($this->input->get('voucher', true)));
			$where .= (empty($where) ? '' : ' AND ').' conv.VOUCHER_CODES in ('.$voucher.') ';
		}
		
		$stat = 'conv.CREATED_AT';
		if(isset($_GET['stat'])  && $_GET['stat'] != '' ){
			$statNum = htmlentities($this->input->get('stat', true));
			if($statNum == 1){
				$stat = 'rsH.REIMBURSEMENT_DATE';
				$where .= (empty($where) ? '' : ' AND ').' conv.RS_ID <> 0 ';
			}else if($statNum == 3){
				$stat = 'conv.CREATED_AT';
				$where .= (empty($where) ? '' : ' AND ').' conv.STAGE = "CONVERTED" ';
			}else $where .= (empty($where) ? '' : ' AND ').' conv.STAGE = "PROCESSED" ';	
		}

		if(isset($_GET['datef']) || isset($_GET['datet'])){		
			$datef = (isset($_GET['datef']) ? htmlentities($this->my_lib->setDate($_GET['datef'], true)) : '');
			$datet = (isset($_GET['datet']) ? htmlentities($this->my_lib->setDate($_GET['datet'], true)): '');
					
			if(!empty($datef) && !empty($datet)) $where .= (empty($where) ? '' : ' AND ').' DATE_FORMAT('.$stat.', "%Y-%m-%d") >= "'.$datef.'" AND DATE_FORMAT('.$stat.', "%Y-%m-%d") <= "'.$datet.'" ';
			else if(!empty($datef) && empty($datet)) $where .= (empty($where) ? '' : ' AND ').' DATE_FORMAT('.$stat.', "%Y-%m-%d") = "'.$datef.'" ';
			else if(empty($datef) && !empty($datet)) $where .= (empty($where) ? '' : ' AND ').' DATE_FORMAT('.$stat.', "%Y-%m-%d") = "'.$datet.'" ';
		}		
		//*END FILTER SEARCH
		
		$data['where'] = $where;	
		$page = $this->my_layout->pagination();	
		$data['per_page'] = $page['per_page'];	
		$data['offset'] = $page['offset'];	
		$data['total'] =  $this->Sys_model->v_TransacConv($where, true);  
		if($data['total'] != 0){	
			$temp_transac =  $this->Sys_model->v_TransacConv($where, false, $page, true);	
			$data['result'] = $this->arr_result($temp_transac);
		}
		echo json_encode($data); 
		exit();
	}

	public function export(){	
		//if(!$this->input->is_ajax_request()) exit('No direct script access allowed');
		//*FILTER SEARCH		
		$SEARCH = $where = ''; 
		
		if(isset($_GET['search']) && $_GET['search'] != '' ){
			$SEARCH = htmlentities($this->input->get('search', true));
			$where .= (empty($where) ? '' : ' AND ').' (mer.LegalName like "%'.$SEARCH.'%" OR br.MERCHANT_ID = "'.$SEARCH.'") ';
		}		
		if(isset($_GET['branch']) && $_GET['branch'] != '' ){
			$branch = htmlentities($this->input->get('branch', true));
			$where .= (empty($where) ? '' : ' AND ').' (br.BRANCH_NAME like "%'.$branch.'%" OR br.BRANCH_ID = "'.$branch.'") ';
		}		
		if(isset($_GET['panumber']) && $_GET['panumber'] != '' ){
			$pa_number = htmlentities($this->input->get('panumber', true));
			$where .= (empty($where) ? '' : ' AND ').' conv.RS_ID in ('.$this->my_lib->multiPANUM($pa_number).')';
		}				
		if(isset($_GET['voucher']) && $_GET['voucher'] != '' ){
			$voucher = $this->my_lib->multiVoucher(htmlentities($this->input->get('voucher', true)));
			$where .= (empty($where) ? '' : ' AND ').' conv.VOUCHER_CODES in ('.$voucher.') ';
		}
		
		$stat = 'conv.CREATED_AT';
		if(isset($_GET['stat'])  && $_GET['stat'] != '' ){
			$statNum = htmlentities($this->input->get('stat', true));
			if($statNum == 1 && $statNum == 3) $stat = 'conv.CREATED_AT';
			
			if($statNum == 1) $where .= (empty($where) ? '' : ' AND ').' conv.RS_ID <> 0 ';
			else if($statNum == 2) $where .= (empty($where) ? '' : ' AND ').' conv.STAGE = "CONVERTED" ';
			else if($statNum == 3) $where .= (empty($where) ? '' : ' AND ').' conv.STAGE = "PROCESSED" ';			
		}

		if(isset($_GET['datef']) || isset($_GET['datet'])){		
			$datef = (isset($_GET['datef']) ? htmlentities($this->my_lib->setDate($_GET['datef'], true)) : '');
			$datet = (isset($_GET['datet']) ? htmlentities($this->my_lib->setDate($_GET['datet'], true)): '');
					
			if(!empty($datef) && !empty($datet)) $where .= (empty($where) ? '' : ' AND ').' DATE_FORMAT('.$stat.', "%Y-%m-%d") >= "'.$datef.'" AND DATE_FORMAT('.$stat.', "%Y-%m-%d") <= "'.$datet.'" ';
			else if(!empty($datef) && empty($datet)) $where .= (empty($where) ? '' : ' AND ').' DATE_FORMAT('.$stat.', "%Y-%m-%d") = "'.$datef.'" ';
			else if(empty($datef) && !empty($datet)) $where .= (empty($where) ? '' : ' AND ').' DATE_FORMAT('.$stat.', "%Y-%m-%d") = "'.$datet.'" ';
		}		
		//*END FILTER SEARCH
		
		$result = $this->Sys_model->v_TransacConv($where, false, '', false); 
		if($result->num_rows() != 0 ){
			$arr = $this->arr_result($result->result());  
			$this->load->model('Action_model');								
			$this->Action_model->audit_save(7, '');	
			$this->load->library('download_file');
			return $this->download_file->transaction_report(array('filename'=>'CONVERSION TRANSACTION'), $arr);
		}
	}
		private function arr_result($temp_transac, $export = false){
			$arr = array();
			foreach($temp_transac as $temp_row){ 
				$newRow = new stdClass(); 						
					$newRow->get_userid = $this->auth->get_userid(); 				
					$newRow->uTransac = $this->auth->get_userid(); 

					$newRow->cov_id = $temp_row->cov_id;			
					$newRow->m_id = $whereBranch['br.MERCHANT_ID'] = $temp_row->m_id;
					$newRow->br_id = $whereBranch['br.BRANCH_ID'] = $temp_row->br_id; 
					$newRow->br_name = $temp_row->br_name;
					$newRow->tin = (!empty($temp_row->TIN) ? $this->my_lib->setTin($temp_row->TIN) : ''); 
					$newRow->conv_date = $temp_row->conv_date;
					$newRow->conv_agent = $temp_row->conv_agent;
					$newRow->voucher_code = $temp_row->voucher_code;	
					$newRow->total_amount = $temp_row->total_amount;
					$newRow->conv_deno = $temp_row->conv_deno;	
					$newRow->conv_uid = $temp_row->conv_uid;	
					$newRow->conv_uname = $temp_row->conv_uname;	 
					$newRow->conv_status = $temp_row->conv_status;	
					$newRow->prod_id = $temp_row->prod_id;	
					$newRow->rs_num = $temp_row->rs_num;	
					$newRow->rs_id = $temp_row->rs_id;
					$newRow->rs_date = $temp_row->rs_date;
					$newRow->rs_duedate = $temp_row->rs_duedate;
					$newRow->prod_name = $temp_row->prod_name;
					$newRow->cp_id = $temp_row->CP_ID;
					$newRow->legalname = $temp_row->LegalName;
					
					/*
					GET BRANCH DATA
					*/
					$selectBranch = 'mer.LegalName legalname, mer.MeanofPayment meanofpayment, br.BRANCH_NAME br_name, br.BRANCH_ID br_id, mer.cp_id cp_id';											
					$get_branch = $this->Sys_model->branchInfo($whereBranch, false, $selectBranch);
					if($get_branch->num_rows() <> 0){
						foreach($get_branch->result() as $branchROW){
							$newRow->br_name =  $branchROW->br_name;
							$newRow->legalname =  $branchROW->legalname;
							$newRow->cp_id =  $this->my_lib->digitalID($branchROW->cp_id);
						}						
					}			
				$arr[] = $newRow;
			}
			return $arr;
		}
		
	/**
	 * companies function
	 *
	 * @return json_array
	 */
	function companies(){
		if(!$this->input->is_ajax_request()) redirect(base_url());	
		$where = ''; $select = 'LegalName as value, CP_ID as data';
		if(isset($_GET['query']) && !empty($_GET['query'])){
			$SEARCH = htmlentities($this->input->get('query', true));
			$where = '(LegalName like "%'.$SEARCH.'%")';
		}
		$result = $this->Sys_model->v_merchant($where, '', $select)->result();
		$suggestions['suggestions'] = $result;
		echo json_encode($suggestions);
		exit();
	}

}




