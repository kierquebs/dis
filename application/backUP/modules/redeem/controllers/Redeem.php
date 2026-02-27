<?php
/**
 * REDEEMPTION CONTROLLER
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Redeem extends MX_Controller {
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
		//echo 'REDEEMPTION MODULE <br />';
		$this->upload_redeem();
	}
	
	/**
	 * GET ALL DATA FROM CSV FILE
	 */

	public function upload_redeem(){
		$map = directory_map('./to_upload/redemption/', FALSE, TRUE);
		
		if(count($map) == 0){
			log_message('info', 'REDEEM:: EMPTY FOLDER');
			echo 'EMPTY FOLDER'; exit();
		}
		
		for($x=0; $x<count($map); $x++){
			
			$fileArr = array();	$return = false;
			$fileArr['filname']  = $map[$x];
			$fileArr['module']  = 'redemption'; 		
			$this->load->library('upload_file');

			/**
			 * @todo  get all data insert to DB
			 */
			$result_data = $this->upload_file->read_file($fileArr['filname'], $fileArr['module']);	
			if(is_array($result_data) && COUNT($result_data) != 0){	
		
				echo "SUCCESS INSERT REDEEMPTION : <br /> <pre>";
				for($i=0;$i<count($result_data);$i++){
					$where['REDEEM_ID'] = $result_data[$i]['REDEEM_ID'];
					$where['PROD_ID'] = $result_data[$i]['PROD_ID'];
					
					$recordResult = $this->Sys_model->v_checkRedeem($where, true);
					if($recordResult == 0){
						$recordID = $this->Sys_model->i_redeem($result_data[$i]);
						print_r($result_data[$i]);
					}
				}	
				echo '</pre>';
				$this->Sys_model->i_auditUpload($fileArr['module'], $fileArr['filname']); //save audit
			}else{				
				$message =  "INSERT STATUS :: FAILED ".( (COUNT($result_data) == 0 || empty($result_data)) ? "NO CONTENT FILE" : "INVALID FORMAT")." - ".$fileArr['filname']." <br /> ";
				log_message('error', 'REDEEM :: '.$message);
				echo "<br /> ".$message."<pre>";
				print_r($result_data[$i]);
				echo '</pre>';
			}
			//delete file
			unlink(dirname($_SERVER["SCRIPT_FILENAME"])."/to_upload/".$fileArr['module'] ."/".$fileArr['filname']);
		}	
	}
	
}
