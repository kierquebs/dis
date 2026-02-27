<?php
/**
 * MAIN CONTROLLER
 * modules that can update corepass records directly to its database records
 * as data being updated it is not log to the audit trail of corepass actions
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class SysCore extends MX_Controller {
	public function	__construct(){

	parent::__construct();	
		$this->load->model('Sys_model');
		$this->load->model('Corepass_model');
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
			foreach($existingAcct->result() as $row){$arr[]=$row->cp_id;}
			//$arr = implode(" , ",$arr);
		}

		/*if($totlExisting != 0){
			$where = 'AND EC.COMPANY_ID NOT IN ('.$arr.')';	
		}*/
		
		//get corepass data merchant
		$getMerchant = $this->Corepass_model->getQueryClient($where);			
		if( $getMerchant->num_rows() != 0){
			$arrMerchant = array();
			$fields = $getMerchant->list_fields(); 
			foreach($getMerchant->result() as $data){
				$arrMerchantData = array();
				foreach ($fields as $field){
					if($field == 'ADDRESS'){
						$address = $this->Corepass_model->getQueryAddress($data->$field)->result();
						$arrMerchantData[$field] = $address[0]->ADDRESS;
					}else if($field == 'VATCOND'){
						$arrMerchantData[$field] = ($data->$field == 3 ? 'Exempt' : ($data->$field == 2 ? 'Zero rated' : 'Standard rated'));
					}else $arrMerchantData[$field] = $data->$field;			
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
				$arrMerchant[] = $arrMerchantData;
			}	
		}
		echo 'DONE';
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
}
