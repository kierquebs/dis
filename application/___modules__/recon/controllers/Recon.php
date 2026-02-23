<?php
/**
 * RECONCILLATION CONTROLLER
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Recon extends MX_Controller {
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
		echo 'RECONCILLATION MODULE';
		$this->upload_recon();
	}
	
	/**
	 * GET ALL DATA FROM CSV FILE
	 */

	public function upload_recon(){
		$map = directory_map('./to_upload/reconciliation/', FALSE, TRUE);
		for($x=0; $x<=count($map); $x++){
			$fileArr = array();	$return = false;
			$fileArr['filname']  = $map[$x++];
			$fileArr['module']  = 'reconciliation'; 		
			$this->load->library('upload_file');
			

			/**
			 * @todo  get all data insert to DB
			*/
			$result_data = $this->upload_file->read_file($fileArr['filname'], $fileArr['module']);	
			if(is_array($result_data)){	
		
				echo "SUCCESS INSERT RECON : <br /> <pre>";
				for($i=0;$i<count($result_data);$i++){
					$where['RECON_ID'] = $result_data[$i]['RECON_ID'];
					$where['REDEEM_ID'] = $result_data[$i]['REDEEM_ID'];
					
					$recordResult = $this->Sys_model->v_checkRecon($where, true);
					if($recordResult == 0 && !empty($where['RECON_ID'])  && !empty($where['REDEEM_ID'])){
					//if($recordResult == 0){
						$recordID = $this->Sys_model->i_recon($result_data[$i]);
						echo '<pre>';print_r($result_data[$i]);
					}
				}	
				echo '</pre>';	
				//delete file
				unlink(dirname($_SERVER["SCRIPT_FILENAME"])."/to_upload/".$fileArr['module'] ."/".$fileArr['filname']);
				$this->Sys_model->i_auditUpload($fileArr['module'], $fileArr['filname']); //save audit
			}else echo "INSERT STATUS : FAILED";
		}	
	}
	
}
