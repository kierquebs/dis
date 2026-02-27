<?php
/**
 * CONVERSION CONTROLLER
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Conversion extends MX_Controller {
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
		//echo 'CONVERSION MODULE <br />';
		$this->upload_conv();
	}
	
	/**
	 * GET ALL DATA FROM CSV FILE
	 */

	public function upload_conv(){
		$map = directory_map('./to_upload/conversion/', FALSE, TRUE);
		
		if(count($map) == 0){
			log_message('info', 'CONVERSION:: EMPTY FOLDER');
			echo 'EMPTY FOLDER'; exit();
		}
			
		for($x=0; $x<count($map); $x++){
			$fileArr = array();	$return = false;
			$fileArr['filname']  = $map[$x];
			$fileArr['module']  = 'conversion'; 		
			$this->load->library('upload_file');
			
			/**
			 * @todo  get all data insert to DB
			 * 
			*/
			$result_data = $this->upload_file->read_file($fileArr['filname'], $fileArr['module']);	
			if(is_array($result_data) && COUNT($result_data) != 0){		
				echo "SUCCESS INSERT CONVERSION : <br /> <pre>";
				for($i=0;$i<count($result_data);$i++){
					$where['VOUCHER_CODES'] = $result_data[$i]['VOUCHER_CODES'];
					$where['BRANCH_NAME'] = $result_data[$i]['BRANCH_NAME'];
					
					$recordResult = $this->Sys_model->v_checkConv($where, true);
					if($recordResult == 0 && !empty($where['BRANCH_NAME'])  && !empty($where['VOUCHER_CODES'])){						
						$insert = $result_data[$i];
							$whereMerchantInfo['BRANCH_NAME'] = $where['BRANCH_NAME'];
						$checkMerchantInfo = $this->Sys_model->branchInfo($whereMerchantInfo, false); //($where = null, $count = false, $select = null)
						if($checkMerchantInfo->num_rows() <> 0){
							$checkMerchantInfo = $checkMerchantInfo->result();
							$insert['MERCHANT_ID'] = $checkMerchantInfo[0]->MERCHANT_ID;
							$insert['BRANCH_ID'] = $checkMerchantInfo[0]->BRANCH_ID;							
						}
						$insert['PROD_ID'] = $this->my_lib->read_barcode($result_data[$i]['VOUCHER_CODES'], 'SERVICE_ID');
						$insert['DENO'] = $this->my_lib->read_barcode($result_data[$i]['VOUCHER_CODES'], 'FACE_VALUE');
						$insert['DATE_CREATED'] = $this->DATE_NOW;
						$recordID = $this->Sys_model->i_conv($insert);
						//print_r($insert); 
					}
				}	
				echo '</pre>'; 
				$this->Sys_model->i_auditUpload($fileArr['module'], $fileArr['filname']); //save audit
			}else{
				$message =  "INSERT STATUS :: FAILED ".( (COUNT($result_data) == 0 || empty($result_data)) ? "NO CONTENT FILE" : "INVALID FORMAT")." - ".$fileArr['filname']." <br /> ";
				log_message('error', 'CONVERSION :: '.$message);
				echo "<br /> ".$message."<pre>";
					print_r($result_data[$i]);
				echo '</pre>';
			}			
			//delete file 
			unlink(dirname($_SERVER["SCRIPT_FILENAME"])."/to_upload/".$fileArr['module'] ."/".$fileArr['filname']);
		}	
	}
	
}

