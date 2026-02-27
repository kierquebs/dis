<?php
/**
 * RECONCILLATION CONTROLLER
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class __Recon extends MX_Controller {
	private $DATE_NOW;

	public function	__construct(){
		parent::__construct();	
			$this->DATE_NOW = $this->sr_lib->current_date();
			$this->load->model('Sys_model');
	}

	/**
	 * ---------------------------------------------------------------------------
	 * PUBLIC FUNCTIONS
	 * ---------------------------------------------------------------------------
	 */
	public function index(){
		echo 'RECONCILLATION MMODULE';
	}
	

	/**
	 * cporder_submit function
	 *
	 * get all sr_middleware report
	 * create CSV file for generating order 
	 */
	public function cporder_submit(){		
		$this->load->library('download_file');
		 /**
		 * check sr_oheader table for "submitted_order" != NULL
		 * @param {$whereH = null, $count = false}
		 * @return array $re_h
		 */
		$whereH = 'DATE_FORMAT(sroh.creation_date, "%Y-%m-%d") <= DATE_FORMAT("'.$this->sr_lib->current_date().'", "%Y-%m-%d")';		
		$whereH .= ' AND sroh.supplier_status = '.$this->sr_object->SUPPLIER_STATUS_SENT_TO_SUPPLIER;	
		$whereH .= ' AND sroh.submit_order is null';
		$re_h = $this->Sys_model->getSRorders_H($whereH);
		
		if($re_h->num_rows() != 0){
			/**
			 * consolidate all order data
			 */
			$compileArr = array();
			foreach($re_h->result() as $hval){
				$arrOBJ = array();
				$arrOBJ['H'] = $hval;
				/**
				 * get order details
				 * @param {$whereD = null, $count = false}
				 * @return array $re_d
				 */
				$whereD['sroh.sr_oh'] = $hval->sr_oh;
				$arrOBJ['D'] = $this->Sys_model->getSRorders_D($whereD)->result();				
				/**
				 * get order details
				 * @param {$whereD = null, $count = false}
				 * @return array $re_d
				 */
				$whereB['sroh.sr_oh'] = $hval->sr_oh;
				$arrOBJ['B'] = $this->Sys_model->getSRorders_B($whereB)->result();	
				
				$compileArr[] = $arrOBJ;		
			}	
			$module = $this->sr_lib->_filename('cp_order');
				$module['filname'] = $module['filname'].strtotime($this->DATE_NOW).'.csv';
			$returnDownload = $this->download_file->cp_order($module, $compileArr);
			
			if($returnDownload != false){
				/**
				* update all orders in OBJECT -> submit order to COREPASS
				*/
				$where['submit_order'] = ''; $update['submit_order'] = $this->sr_lib->current_date();
				if($this->Sys_model->u_osubmitCP($sr_ohArr, $returnDownload, $where) == false) log_message('error', 'FAILED TO UPDATE RECORD :: SUBMIT TO COREPASS' );
			}else log_message('error', 'FAILED TO SUBMIT COREPASS ORDERS :: FILENAME :: '.$module['filname'] );
		}
	}
	
}
