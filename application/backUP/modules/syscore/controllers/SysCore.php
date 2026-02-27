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
		if($this->auth->check_session()) redirect('login');	
		
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
	
	/**
	 * force_tagging
	 * @return void
	 */
	 public function force_tagging(){
		$query = $this->db->query("select 
			redeem.id ID,
			redeem.REDEEM_ID redeem_id,
			redeem.STAGE redeem_status,
			redeem.PROD_ID prod_id,
			recon.RECON_ID recon_id
			from
			redemption redeem
			inner join reconcilation recon on redeem.REDEEM_ID = recon.REDEEM_ID
			where
			redeem.STAGE = 'REDEEMED'
			group by redeem.id
			order by redeem.id asc");
		if($query->num_rows() != 0){
			foreach($query->result() as $row){
				$whereArr['id'] = $row->ID;
				$update['STAGE'] = 'RECONCILED';
					$this->Sys_model->u_redeem($whereArr, $update);	
				echo $row->redeem_id.'<br />';
			}
		}
	 }
}
