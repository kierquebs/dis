<?php
/**
 * Inpreparation for PA Temp Table
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Temporary extends MX_Controller {
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
		//echo 'Inpreparation for PA Temp Table';
		$this->w_recon();
	}

	/**
	 * Handles MERCHANT with RECON Transactions
	 */
	private function w_recon(){

		$where['DigitalSettlementType'] = '';
		$select = 'TYPE, SPECIFIC_DAY, SPECIFIC_DATE';
		$groupBy = 'TYPE, SPECIFIC_DAY, SPECIFIC_DATE';
		
		$getPaymentCutoff = $this->Sys_model->v_cutoff($where, false, $select, $groupBy); 
		//$where = null, $count = false, $select = null, $groupBy = ''

		if($getPaymentCutoff->num_rows() <> 0){

			//LOOP PAYMENT_CUTOFF DATES
			foreach($getPaymentCutoff->result() as $row){ 				
				if($row->SPECIFIC_DAY <> '' && $row->SPECIFIC_DATE == ''){ //SPECIFIC DATE field is null					
				//	echo $row->SPECIFIC_DAY ;

				}else if($row->SPECIFIC_DAY == '' && $row->SPECIFIC_DATE <> ''){ //SPECIFIC DATE field is not null
					$date_arr = explode(",",substr($row->SPECIFIC_DATE, 1, -1));
					$countProcess = count($date_arr);
					if($countProcess <> 0){						
						for($i=0; $i<$countProcess; $i++){							
							if($date_arr[$i] <= date("d")){
								echo $this->my_lib->setCFDate($date_arr[$i]).'<br />'; 
							}
						}
					}
				}else{
					echo 'INVALID REQUEST!';
				}

			}
		}
	}
	
	
}
