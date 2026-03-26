<?php
/**
 * MAIN CONTROLLER
 * modules that can update corepass records directly to its database records
 * as data being updated it is not log to the audit trail of corepass actions
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Offlineprocess extends MX_Controller {
	private $get_userid;

	public function	__construct(){
	parent::__construct();
		if($this->auth->check_session()) redirect('login');
		$this->load->model('Sys_model');
		$this->load->model('Process_model');
		$this->load->model('Corepass_model');
		$this->load->helper('my_helper');
		$this->get_userid = 2; //Sir Jun
	}


	public function index(){
		echo '<h2>Offline Process</h2>';
		echo '<br /><br /> * per_merchantID - <a href="/mp_dis/syscore/offlineprocess/per_merchantID?merchantID=">GO TO</a>
		<br /><i> GET VALUES: merchantID </i>';
		echo '<br /><br /> * per_merchantDateRange - <a href="/mp_dis/syscore/offlineprocess/per_merchantDateRange?merchantID=&dfrom=&dto=">GO TO</a>
		<br /><i> GET VALUES: merchantID, dfrom, dto, cfdate (not requried) </i>';
	}

	/**
	 * Process PA per Merchant
	 */
	public function per_merchantID(){
		if(isset($_GET['merchantID']) && !empty($_GET['merchantID'])){
		 $this->_processper_MID($_GET['merchantID']);
		}
	}

	public function group_merchantID(){
		$group_merchantID = getenv('group_merchantID') !== false ? getenv('group_merchantID') : '';
		$group = explode(',', $group_merchantID);
		if(!empty($group) && !empty($group_merchantID)){
			for($i=0; $i < count($group); $i++){
				if(!empty($group[$i])){
					$this->_processper_MID($group[$i], true);
					//echo $group[$i].'<br />';
				}
			}
		}
	}

		private function _processper_MID($merchantID, $auto=false){
			if(!empty($merchantID)){
				$merchantData = $this->_check_merchant($merchantID);
				$setGetArr = []; $setGet = '';
				if($merchantData <> ''){
					$setGet = "process=".$merchantID;
					$setGet .= (!empty($setGet) ? "&":"")."terms=".$this->paymentTermsNum($merchantData['POST_terms']);

					$setGetArr['process'] = $merchantID;
					$setGetArr['terms'] = $this->paymentTermsNum($merchantData['POST_terms']);

					if(!empty($merchantData['POST_date']) && is_array($merchantData['POST_date'])){
						$merchantDataPOST_date = "";
						$countDate = count($merchantData['POST_date']);
						for($i = 0; $i<$countDate;$i++){
							//echo $merchantData['POST_date'][$i].' '.date("d");
							if($merchantData['POST_date'][$i] <= date("d")){
								$merchantDataPOST_date = $merchantData['POST_date'][$i] ;
							}else{
								$merchantDataPOST_date = $merchantData['POST_date'][0] ;
							}
						}
						$merchantData['POST_date'] = $merchantDataPOST_date;
						$setGet .= (!empty($setGet) ? "&":"")."date=".$merchantData['POST_date'];
						$setGetArr['date'] = $merchantData['POST_date'];
						//echo 'here'.date("d");
					}

					if(!empty($merchantData['POST_day'])){
						$setGet .= (!empty($setGet) ? "&":"")."day=".$merchantData['POST_day'];
						$setGetArr['day'] = $merchantData['POST_day'];
					}

					if($merchantData['POST_SETTLE'] <> ''){
						$result = $this->nrecon_vfilter($merchantData);
						if($auto <> false) $this->_nrecon_genpa($setGetArr);
						else $result['LINK'] = '<a href="/mp_dis/syscore/offlineprocess/nrecon_genpa?'.$setGet.'">PROCESS NO RECON MERCHANT</a>';
					}else{
						$result = $this->wrecon_vfilter($merchantData);
						if($auto <> false) $this->_wrecon_genpa($setGetArr);
						else $result['LINK'] = '<a href="/mp_dis/syscore/offlineprocess/wrecon_genpa?'.$setGet.'">PROCESS WITH RECON MERCHANT</a>';
					}
					$result['merchantID'] = $merchantID;

					echo '<h3>Offline Process - per_merchantID</h3><pre>';
					print_r($result);
					echo '</pre>';
				}

			}else{
				echo 'INVALID REQUEST! - Missing Merchant ID';
			}
		}



	/**
	 * Process PA per Cutoff
	 */

	 public function per_cutoff(){
		if(isset($_GET['type']) && !empty($_GET['type'])) {
			$paymentTerms = $this->paymentTermsName($_GET['type']);

			$where = $output = [];
			$where = 'TYPE = "'.htmlentities($paymentTerms).'"';
			if($paymentTerms == 'Weekly'){
				if(isset($_GET['date'])  && !empty($_GET['date'])){
					$where .= ' AND SPECIFIC_DAY = "'.htmlentities($_GET['date']).'"';
				}
			}else{
				if(isset($_GET['date'])  && !empty($_GET['date'])){
					$where .=  ' AND SPECIFIC_DATE like "%'.htmlentities($_GET['date']).'%"';
				}
			}
			$v_cutoffResult = $this->Sys_model->v_cutoff($where, false);

			/** ADD TRIGGER TO DATABASE TO CHECK IF THERE'S PENDING ITEM */
			if($v_cutoffResult->num_rows() <> 0){
				foreach ($v_cutoffResult->result() as $row) {
					echo $row->MERCHANT_ID.'<br />';
					$this->_processper_Cutoff($row->MERCHANT_ID, true);
				}
			}

			$this->my_lib->cronLog('processing_'.$_GET['type'].'_'.$_GET['date'], ''); // generate cron log
		}
	}

		private function _processper_Cutoff($merchantID, $auto=true){
			if(!empty($merchantID)){
				$merchantData = $this->_check_merchant($merchantID);
				$setGetArr = []; $setGet = '';
				if($merchantData <> ''){
					$setGet = "process=".$merchantID;
					$setGet .= (!empty($setGet) ? "&":"")."terms=".$this->paymentTermsNum($merchantData['POST_terms']);

					$setGetArr['process'] = $merchantID;
					$setGetArr['terms'] = $this->paymentTermsNum($merchantData['POST_terms']);

					if(!empty($merchantData['POST_date']) && is_array($merchantData['POST_date'])){
						$merchantDataPOST_date = "";
						$countDate = count($merchantData['POST_date']);
						for($i = 0; $i<$countDate;$i++){
							//echo $merchantData['POST_date'][$i].' '.date("d");
							if($merchantData['POST_date'][$i] <= date("d")){
								$merchantDataPOST_date = $merchantData['POST_date'][$i] ;
							}else{
								$merchantDataPOST_date = $merchantData['POST_date'][0] ;
							}
						}
						$merchantData['POST_date'] = $merchantDataPOST_date;
						$setGet .= (!empty($setGet) ? "&":"")."date=".$merchantData['POST_date'];
						$setGetArr['date'] = $merchantData['POST_date'];
						//echo 'here'.date("d");
					}

					if(!empty($merchantData['POST_day'])){
						$setGet .= (!empty($setGet) ? "&":"")."day=".$merchantData['POST_day'];
						$setGetArr['day'] = $merchantData['POST_day'];
					}

					if($merchantData['POST_SETTLE'] <> ''){
						$result = $this->nrecon_vfilter($merchantData);
						if($auto <> false) $this->_nrecon_genpa($setGetArr);
						else $result['LINK'] = '<a href="/mp_dis/syscore/offlineprocess/nrecon_genpa?'.$setGet.'">PROCESS NO RECON MERCHANT</a>';
					}else{
						$result = $this->wrecon_vfilter($merchantData);
						if($auto <> false) $this->_wrecon_genpa($setGetArr);
						else $result['LINK'] = '<a href="/mp_dis/syscore/offlineprocess/wrecon_genpa?'.$setGet.'">PROCESS WITH RECON MERCHANT</a>';
					}
					//$result['merchantID'] = $merchantID;
					//echo '<h3>Offline Process - per_merchantID</h3><pre>';
					//print_r($result);
					//echo '</pre>';
				}

			}else{
				echo 'INVALID REQUEST! - Missing Merchant ID';
			}
		}


	/**
	 * Process PA per Merchant filter by DATE RANGE
	 */
	public function per_merchantDateRange(){
		if(isset($_GET['merchantID']) && !empty($_GET['merchantID']) && isset($_GET['dfrom']) && !empty($_GET['dfrom']) && isset($_GET['dto']) && !empty($_GET['dto'])){
			$merchantData = $this->_check_merchant($_GET['merchantID']);

			$setGet= '';
			if($merchantData <> ''){
				$setGet = "process=".$_GET['merchantID'];
				$setGet .= (!empty($setGet) ? "&":"")."terms=".$this->paymentTermsNum($merchantData['POST_terms']);

				if(!empty($merchantData['POST_date']) && is_array($merchantData['POST_date'])){
					if(isset($_GET['cfdate']) && !empty($_GET['cfdate'])){
						$merchantData['POST_date'] = $_GET['cfdate'];
					}else{
						$countDate = count($merchantData['POST_date']);
						for($i = 0; $i<$countDate;$i++){
							if($merchantData['POST_date'][$i] >= date("d")) $merchantData['POST_date'] = $merchantData['POST_date'][$i] ;
						}
					}
					$setGet .= (!empty($setGet) ? "&":"")."date=".$merchantData['POST_date'];
				}

				if(!empty($merchantData['POST_day'])){
					$setGet .= (!empty($setGet) ? "&":"")."day=".$merchantData['POST_day'];
				}

				if(!empty($_GET['dfrom']) && !empty($_GET['dto'])){
					$merchantData['dfrom'] = $_GET['dfrom'];
					$merchantData['dto'] = $_GET['dto'];
					$setGet .= (!empty($setGet) ? "&":"")."dfrom=".$merchantData['dfrom'];
					$setGet .= (!empty($setGet) ? "&":"")."dto=".$merchantData['dto'];
				}

				if($merchantData['POST_SETTLE'] <> ''){
					$result = $this->nrecon_vfilter($merchantData);
					$result['LINK'] = '<a href="/mp_dis/syscore/offlineprocess/nrecon_genpa?'.$setGet.'">PROCESS NO RECON MERCHANT</a>';
				}else{
					$result = $this->wrecon_vfilter($merchantData);
					$result['LINK'] = '<a href="/mp_dis/syscore/offlineprocess/wrecon_genpa?'.$setGet.'">PROCESS WITH RECON MERCHANT</a>';
				}
				$result['merchantID'] = $_GET['merchantID'];

				echo '<h3>Offline Process - per_merchantID</h3><pre>';
				print_r($result);
				echo '</pre>';
			}

		}else{
			echo 'INVALID REQUEST! - Missing Merchant ID';
		}
	}


/**
 * ////////////////////////////////
 * ////////////////////////////////
*/
	private function _check_merchant($merchantID){
		if(empty($merchantID)) return false;
		$output = [];

		$where['MERCHANT_ID'] = $toProcess = $merchantID;
		$v_cutoffResult = $this->Sys_model->v_cutoff($where, false);

		/** ADD TRIGGER TO DATABASE TO CHECK IF THERE'S PENDING ITEM */
		if($v_cutoffResult->num_rows() <> 0){
			$rowField = $v_cutoffResult->row();
			$output['POST_terms'] = $rowField->TYPE;
			$output['POST_day'] = $rowField->SPECIFIC_DAY;
			$output['POST_date'] =  (!empty($rowField->SPECIFIC_DATE) ? explode(",",substr($rowField->SPECIFIC_DATE, 1, -1)) : '');
				if(is_array($output['POST_date']) && count($output['POST_date']) == 1) $output['POST_date'] = $output['POST_date'][0];
			$output['POST_SETTLE'] = $rowField->DigitalSettlementType;


			if(!empty($rowField->SPECIFIC_DATE) && is_array($output['POST_date']) && count($output['POST_date']) <> 1) rsort($output['POST_date']);
			return $output;
		}
	}

	private function nrecon_vfilter($getData){
		$dateWhere = '';
		$DateToday = $this->my_layout->setDate('', true);
		$paymentTerms =  $getData['POST_terms']; //(isset($getData['POST_terms']) && $getData['POST_terms'] != '' ? $this->my_lib->paymentTerms($getData['POST_terms']) : '');
		$pcfWHere = $where = 'pcf.TYPE = "'.htmlentities($paymentTerms).'"';

		if($paymentTerms  != ''){
			$data['terms'] = $getData['POST_terms'];
			if($data['terms'] == 'Weekly'){
				if(isset($getData['POST_day'])  && $getData['POST_day'] != '' ){
					$data['day'] = $day = $getData['POST_day'];
					$dateWhere = $this->my_lib->setCFDay($day);
						$SPECIFIC_DAY = ' AND pcf.SPECIFIC_DAY = "'.$day.'"';
					$where .= $SPECIFIC_DAY;
					$pcfWHere .= $SPECIFIC_DAY;

					if((isset($getData['dfrom'])  && $getData['dfrom'] != '') && (isset($getData['dto'])  && $getData['dto'] != '')){
						$where .= $where_date = ' AND DATE_FORMAT(redeem.TRANSACTION_DATE_TIME, "%Y-%m-%d") <= "'.$getData['dto'].'" AND AND DATE_FORMAT(redeem.TRANSACTION_DATE_TIME, "%Y-%m-%d") <= "'.$getData['dfrom'].'"';
					}else $where .= $where_date = ' AND DATE_FORMAT(redeem.TRANSACTION_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';

				}
			}else{
				if(isset($getData['POST_date'])  && $getData['POST_date'] != '' ){
					$data['date'] =  $date = $getData['POST_date'];
					$dateWhere = $this->my_lib->setCFDate($date);
					if(strlen ($date) <= 1){
						$SPECIFIC_DATE = " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") ";
					}else {
						$SPECIFIC_DATE =  ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"';
					}
					$where .= $SPECIFIC_DATE;
					$pcfWHere .= $SPECIFIC_DATE;
					if((isset($getData['dfrom'])  && $getData['dfrom'] != '') && (isset($getData['dto'])  && $getData['dto'] != '')){
						$where .= $where_date = ' AND DATE_FORMAT(redeem.TRANSACTION_DATE_TIME, "%Y-%m-%d") <= "'.$getData['dto'].'" AND AND DATE_FORMAT(redeem.TRANSACTION_DATE_TIME, "%Y-%m-%d") <= "'.$getData['dfrom'].'"';
					}else $where .= $where_date = ' AND DATE_FORMAT(redeem.TRANSACTION_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
				}
			}
		}
		$data['where']  = $where;
		$data['date_coverage'] = date('M d Y' , strtotime($dateWhere));
		$data['date_today'] = strtotime($DateToday);
		return $data;
	}

	private function wrecon_vfilter($getData){
		$dateWhere = '';
		$DateToday = $this->my_layout->setDate('', true);
		$paymentTerms =  $getData['POST_terms']; //(isset($getData['POST_terms']) && $getData['POST_terms'] != '' ? $this->my_lib->paymentTerms($getData['POST_terms']) : '');
		$pcfWHere = $where = 'pcf.TYPE = "'.htmlentities($paymentTerms).'"';
		if($paymentTerms  != ''){
			$data['terms'] = $getData['POST_terms'];
			if($data['terms'] == 3 || $data['terms'] == "Weekly"){
				if(isset($getData['POST_day'])  && $getData['POST_day'] != '' ){
					$data['day'] = $day = $getData['POST_day'];
					$dateWhere = $this->my_lib->setCFDay($day);
						$SPECIFIC_DAY = ' AND pcf.SPECIFIC_DAY = "'.$day.'"';
					$where .= $SPECIFIC_DAY;
					$pcfWHere .= $SPECIFIC_DAY;
					if((isset($getData['dfrom'])  && $getData['dfrom'] != '') && (isset($getData['dto'])  && $getData['dto'] != '')){
						$where .= $where_date = ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$getData['dto'].'" AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") >= "'.$getData['dfrom'].'"';
					}else $where .= $where_date = ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
				}
			}else{
				if(isset($getData['POST_date'])  && $getData['POST_date'] != '' ){
					$data['date'] =  $date = $getData['POST_date'];
					$dateWhere = $this->my_lib->setCFDate($date);
					if(strlen ($date) <= 1){
						$SPECIFIC_DATE = " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") ";
					}else {
						$SPECIFIC_DATE =  ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"';
					}
					$where .= $SPECIFIC_DATE;
					$pcfWHere .= $SPECIFIC_DATE;
					if((isset($getData['dfrom'])  && $getData['dfrom'] != '') && (isset($getData['dto'])  && $getData['dto'] != '')){
						$where .= $where_date = ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$getData['dto'].'" AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") >= "'.$getData['dfrom'].'"';
					}else $where .= $where_date = ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
				}
			}
		}
		$data['where']  = $where;

		//*END FILTER SEARCH
		$data['date_coverage'] = date('M d Y' , strtotime($dateWhere));
		$data['date_today'] = strtotime($DateToday);
		return $data;
	}


	private function paymentTermsNum($terms){
		switch ($terms) {
			case 'Semi-Monthly':
			case 'Semi-monthly':
				return 2;
				break;
			case 'Weekly':
				return 3;
				break;
			case 'Every 10 days':
				return 4;
				break;
			default:
				return 1;
		}
	}

	private function paymentTermsName($terms){
		switch ($terms) {
			case 2:
				return 'Semi-Monthly';
				break;
			case 3:
				return 'Weekly';
				break;
			case 4:
				return 'Every 10 days';
				break;
			case 1:
				return 'Monthly';
				break;
			default:
				return '';
		}
	}

	/**
	 * NO RECON OFFLINE PROCESSING
	 */
	public function nrecon_genpa(){
		$GET_DATA = $_GET; //push all get values to $GET_DATA val
		$this->_nrecon_genpa($GET_DATA);
	}

	private function _nrecon_genpa($GET_DATA){
		/*
		* GET ALL CHECK ITEMS
		*/
		$toProcess = $GET_DATA['process'];

		/** ADD TRIGGER TO DATABASE TO CHECK IF THERE'S PENDING ITEM */

		if($toProcess <> '' && $toProcess <> 0){
			$whereRefund =  $where = 'pcf.TYPE = "'.$this->my_lib->paymentTerms($GET_DATA['terms']).'" AND br.BRANCH_NAME <> ""';
			if($GET_DATA['terms'] == 3){
				if(isset($GET_DATA['day'])  && $GET_DATA['day'] != '' ){
					$day = htmlentities($GET_DATA['day']);
					$dateWhere = $this->my_lib->setCFDay($day);
					$where .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"';
					$whereRefund .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"';

					if((isset($GET_DATA['dfrom'])  && $GET_DATA['dfrom'] != '') && (isset($GET_DATA['dto'])  && $GET_DATA['dto'] != '')){
						$where .= ' AND DATE_FORMAT(redeem.TRANSACTION_DATE_TIME, "%Y-%m-%d") <= "'.$GET_DATA['dto'].'" AND DATE_FORMAT(redeem.TRANSACTION_DATE_TIME, "%Y-%m-%d") >= "'.$GET_DATA['dfrom'].'"';
						$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$GET_DATA['dto'].'"  AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") >= "'.$GET_DATA['dfrom'].'"';
					}else {
						$where .= ' AND DATE_FORMAT(redeem.TRANSACTION_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
						$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
					}
				}
			}else{
				if(isset($GET_DATA['date'])  && $GET_DATA['date'] != '' ){
					$date = htmlentities($GET_DATA['date']);
					if(strlen ($date) <= 1){
						$where .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") ";
						$whereRefund .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") ";
					}else{
						$where .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"';
						$whereRefund .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"';
					}
					$dateWhere = $this->my_lib->setCFDate($date);

					if((isset($GET_DATA['dfrom'])  && $GET_DATA['dfrom'] != '') && (isset($GET_DATA['dto'])  && $GET_DATA['dto'] != '')){
						$where .= ' AND DATE_FORMAT(redeem.TRANSACTION_DATE_TIME, "%Y-%m-%d") <= "'.$GET_DATA['dto'].'" AND DATE_FORMAT(redeem.TRANSACTION_DATE_TIME, "%Y-%m-%d") >= "'.$GET_DATA['dfrom'].'"';
						$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$GET_DATA['dto'].'"  AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") >= "'.$GET_DATA['dfrom'].'"';
					}else {
						$where .= ' AND DATE_FORMAT(redeem.TRANSACTION_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
						$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
					}
				}
			}

			$this->load->helper('my_helper');

			$PA_ARR = array(); $whereMerchant = $PA_ID = '';
			/*CREATE PA HEADER*/
			$whereMerchant = ' AND br.MERCHANT_ID = "'.$toProcess.'"';
			$whereBranch = 	$where;
			$whereBranch .= $whereMerchant;

			$whereBranchRef = $whereRefund;
			$whereBranchRef .= $whereMerchant;

			$insert_header['MERCHANT_ID'] = $toProcess;
			$insert_header['DATE_CREATED'] = $insert_header['REIMBURSEMENT_DATE'] = $this->my_lib->current_date();
			$insert_header['USER_ID'] = $this->get_userid;
			$getPCBranchesRow =  $this->Process_model->getPCFNRecon_ProcessPA($whereBranch, false, '', $dateWhere, $whereMerchant);
			$brRowNum = $getPCBranchesRow->num_rows();

			/*CREATE PA DETAIL*/
			if($brRowNum != 0){
				$AFFCODE_li = array_group_by($getPCBranchesRow->result(), "brAFFCODE");
				$AFFCODE_row = json_decode(json_encode($AFFCODE_li), true);

				//echo '<pre>';
				foreach($AFFCODE_row as $k => $v){
					$PA_ID = $this->Sys_model->i_paH($insert_header);
					if(!empty($PA_ID)){
						$PA_ARR[] = $PA_ID;
						foreach ($v as $row) {
							$show = '';
							$whereAFFCODE = $u_paH = $insert_detail = $where_paD = [];

							/**
							* IF branch - AFFILIATIONCODE is not null then get data from cp_agreement
							* ELSE proceed with the old process
							*/
							$PA_MerchantFee = $row['MerchantFee'];
							$PA_PayeeDayType = $row['PayeeDayType'];
							$PA_PayeeQtyOfDays = $row['PayeeQtyOfDays'];
							$PA_VAT = $row['vatcond'];

							if($row['merAFFCODE'] <> $row['brAFFCODE']){
								$whereAFFCODE['CP_ID'] =  $row['CPID'];
								$whereAFFCODE['AffiliateGroupCode'] = trim($row['brAFFCODE']);
								$getAFFCODE =  $this->Sys_model->v_agreement($whereAFFCODE, false);
								if($getAFFCODE->num_rows() <> 0){
									$rowAFFCODE = $getAFFCODE->row();
									$PA_MerchantFee = $rowAFFCODE->MerchantFee;
									$PA_PayeeDayType = $rowAFFCODE->PayeeDayType;
									$PA_PayeeQtyOfDays = $rowAFFCODE->PayeeQtyOfDays;
									$PA_VAT = $rowAFFCODE->VATCond;
								}
							}

							// Start: reinforce merchant fee from cp_merchant table
							if($row['MID'] == 7518){
								$PA_MerchantFee = $this->Sys_model->getMerchantFee($row['CPID'])->row()->MerchantFee;
							}
							// End:

							$VAT = $this->my_lib->checkVAT($PA_VAT);
							$totalFV = $row['totalAmount'];
							$percentMF = $this->my_lib->convertMFRATE($PA_MerchantFee, true);
							$totalRefund = ($row['refundPostAmount'] == NULL ? 0 : $row['refundPostAmount']); //total amount of refund

							$totalMFV = $totalFV - $totalRefund;
							if($totalMFV < 0) $totalMFV = 0;
							$MF = $this->my_lib->computeMF($totalMFV, ($PA_MerchantFee * 100), '', false);

							/*BUILD PA DETAIL INFO*/
							$where_paD['PA_ID'] =  $insert_detail['PA_ID'] = $PA_ID;
							$where_paD['BRANCH_ID'] = $insert_detail['BRANCH_ID'] = $row['BRANCH_ID'];
							$insert_detail['RATE'] = $percentMF;
							$insert_detail['NUM_PASSES'] = $row['totalPasses'];
							$insert_detail['TOTAL_FV'] = $totalFV;
							$insert_detail['TOTAL_REFUND'] = $totalRefund;
							$insert_detail['DATE_CREATED'] = $this->my_lib->current_date();

							if($totalMFV == 0){
								$insert_detail['MARKETING_FEE'] = 0;
								$insert_detail['VAT'] = 0;
								$insert_detail['NET_DUE'] = 0;
							}else{
								$insert_detail['MARKETING_FEE'] = $MF;
								$insert_detail['VAT'] = $this->my_lib->computeVAT($totalMFV, ($PA_MerchantFee * 100), $VAT, FALSE);
								$insert_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalMFV, ($PA_MerchantFee * 100), $VAT, FALSE);
							}

							$checkPAD = $this->Sys_model->v_paD($where_paD, true);
							if($checkPAD == 0){
								$paD_ID = $this->Sys_model->i_paD($insert_detail);

								if(!empty($paD_ID)){
									$whereUBranch = $whereBranch.' AND br.BRANCH_ID = "'.$row['BRANCH_ID'].'"';
									$this->Process_model->uNRecon_ReconPA($whereUBranch, $PA_ID);

									$whereUBranchRef = $whereBranchRef.' AND br.BRANCH_ID = "'.$row['BRANCH_ID'].'"';
									$this->Process_model->uNRecon_RefundPA($whereUBranchRef, $PA_ID);
								}
							}
						}
						$u_paH['MERCHANT_FEE']= $PA_MerchantFee;
						$u_paH['vatcond']= $PA_VAT;
						$u_paH['ExpectedDueDate']= $this->my_lib->computeExpectedDueDate($PA_PayeeDayType, $PA_PayeeQtyOfDays, $dateWhere); //$insert_header['REIMBURSEMENT_DATE']
						$this->Sys_model->u_paH(array('PA_ID'=>$PA_ID), $u_paH);
					}
				}
				//echo '</pre>';
			echo 'DONE - nrecon_genpa';
			} else echo 'No Result Found!';
		}else{
			echo 'Invalid Request!';
		}
	}

	/**
	 * WITH RECON OFFLINE PROCESSING
	 */

	public function wrecon_genpa(){
		$GET_DATA = $_GET; //push all get values to $GET_DATA val
		$this->_wrecon_genpa($GET_DATA);
	}

	private function _wrecon_genpa($GET_DATA){
		/*
		* GET ALL CHECK ITEMS
		*/
		$toProcess = $GET_DATA['process'];

		/** ADD TRIGGER TO DATABASE TO CHECK IF THERE'S PENDING ITEM */
		if($toProcess <> '' && $toProcess <> 0){
			$whereRefund =  $where = 'pcf.TYPE = "'.$this->my_lib->paymentTerms($GET_DATA['terms']).'" AND br.BRANCH_NAME <> ""';
			if($GET_DATA['terms'] == 3){
				if(isset($GET_DATA['day'])  && $GET_DATA['day'] != '' ){
					$day = htmlentities($GET_DATA['day']);
					$dateWhere = $this->my_lib->setCFDay($day);
					$where .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"';
					$whereRefund .= ' AND pcf.SPECIFIC_DAY = "'.$day.'"';

					if((isset($GET_DATA['dfrom'])  && $GET_DATA['dfrom'] != '') && (isset($GET_DATA['dto'])  && $GET_DATA['dto'] != '')){
						$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$GET_DATA['dto'].'" AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") >= "'.$getData['dfrom'].'"';
						$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$GET_DATA['dto'].'" AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") >= "'.$getData['dfrom'].'"';
					}else{
						$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
						$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
					}
				}
			}else{
				if(isset($GET_DATA['date'])  && $GET_DATA['date'] != '' ){
					$date = htmlentities($GET_DATA['date']);
					if(strlen ($date) <= 1){
						$where .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") ";
						$whereRefund .= " AND TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE))) in (".$date.") ";
					}else{
						$where .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"';
						$whereRefund .= ' AND pcf.SPECIFIC_DATE like "%'.$date.'%"';
					}
					$dateWhere = $this->my_lib->setCFDate($date);

					if((isset($GET_DATA['dfrom'])  && $GET_DATA['dfrom'] != '') && (isset($GET_DATA['dto'])  && $GET_DATA['dto'] != '')){
						$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$GET_DATA['dto'].'" AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") >= "'.$GET_DATA['dfrom'].'"';
						$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$GET_DATA['dto'].'" AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") >= "'.$GET_DATA['dfrom'].'"';
					}else{
						$where .= ' AND DATE_FORMAT(recon.RECON_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
						$whereRefund .= ' AND DATE_FORMAT(ref.REVERSAL_DATE_TIME, "%Y-%m-%d") <= "'.$dateWhere.'"';
					}
				}
			}

			$this->load->helper('my_helper');

			$PA_ARR = array(); $whereMerchant = $PA_ID = '';
			/*CREATE PA HEADER*/
			$whereMerchant = ' AND br.MERCHANT_ID = "'.$toProcess.'"';
			$whereBranch = 	$where;
			$whereBranch .= $whereMerchant;

			$whereBranchRef = $whereRefund;
			$whereBranchRef .= $whereMerchant;

			$insert_header['MERCHANT_ID'] = $toProcess;
			$insert_header['DATE_CREATED'] = $insert_header['REIMBURSEMENT_DATE'] = $this->my_lib->current_date();
			$insert_header['USER_ID'] =$this->get_userid;
			$getPCBranchesRow =  $this->Process_model->getPCFWRecon_ProcessPA($whereBranch, false, '', $dateWhere, $whereMerchant);
			$brRowNum = $getPCBranchesRow->num_rows();

			/*CREATE PA DETAIL*/
			if($brRowNum != 0){
				$AFFCODE_li = array_group_by($getPCBranchesRow->result(), "brAFFCODE");
				$AFFCODE_row = json_decode(json_encode($AFFCODE_li), true);

				//echo '<pre>';
				foreach($AFFCODE_row as $k => $v){
					$PA_ID = $this->Sys_model->i_paH($insert_header);
					if(!empty($PA_ID)){
						$PA_ARR[] = $PA_ID;
						foreach ($v as $row) {
							$show = '';
							$whereAFFCODE = $u_paH = $insert_detail = $where_paD = [];
							/*
							** fields available for process table **
								recon.RECON_ID,
								recon.MERCHANT_ID MID,
								mer.CP_ID CPID,
								mer.LegalName LegalName,
								mer.MerchantFee MerchantFee,
								mer.vatcond,
								mer.PayeeDayType,
								mer.PayeeQtyOfDays,
								SUM(recon.TRANSACTION_VALUE) totalAmount,
								COUNT(redeem.REDEEM_ID) totalPasses,
								br.BRANCH_ID BRANCH_ID,
								br.BRANCH_NAME BRANCH_NAME,
								pcf.SPECIFIC_DATE,
								pcf.SPECIFIC_DAY,
								pcf.type
							*/

							/**
							* IF branch - AFFILIATIONCODE is not null then get data from cp_agreement
							* ELSE proceed with the old process
							*/
							$PA_MerchantFee = $row['MerchantFee'];
							$PA_PayeeDayType = $row['PayeeDayType'];
							$PA_PayeeQtyOfDays = $row['PayeeQtyOfDays'];
							$PA_VAT = $row['vatcond'];

							if($row['merAFFCODE'] <> $row['brAFFCODE']){
								$whereAFFCODE['CP_ID'] =  $row['CPID'];
								$whereAFFCODE['AffiliateGroupCode'] = trim($row['brAFFCODE']);
								$getAFFCODE =  $this->Sys_model->v_agreement($whereAFFCODE, false);
								if($getAFFCODE->num_rows() <> 0){
									$rowAFFCODE = $getAFFCODE->row();
									$PA_MerchantFee = $rowAFFCODE->MerchantFee;
									$PA_PayeeDayType = $rowAFFCODE->PayeeDayType;
									$PA_PayeeQtyOfDays = $rowAFFCODE->PayeeQtyOfDays;
									$PA_VAT = $rowAFFCODE->VATCond;
								}
							}

							$VAT = $this->my_lib->checkVAT($PA_VAT);
							$totalFV = $row['totalAmount'];
							$percentMF = $this->my_lib->convertMFRATE($PA_MerchantFee, true);
							$totalRefund = ($row['refundPostAmount'] == NULL ? 0 : $row['refundPostAmount']); //total amount of refund

							$totalMFV = $totalFV - $totalRefund;
							if($totalMFV < 0) $totalMFV = 0;
							$MF = $this->my_lib->computeMF($totalMFV, ($PA_MerchantFee * 100), '', false);

							/*BUILD PA DETAIL INFO*/
							$where_paD['PA_ID'] = $insert_detail['PA_ID'] = $PA_ID;
							$where_paD['RECON_ID'] = $insert_detail['RECON_ID'] = $row['RECON_ID'];
							$where_paD['BRANCH_ID'] = $insert_detail['BRANCH_ID'] = $row['BRANCH_ID'];
							$insert_detail['RATE'] = $percentMF;
							$insert_detail['NUM_PASSES'] = $row['totalPasses'];
							$insert_detail['TOTAL_FV'] = $totalFV;
							$insert_detail['TOTAL_REFUND'] = $totalRefund;
							$insert_detail['DATE_CREATED'] = $this->my_lib->current_date();

							if($totalMFV == 0){
								$insert_detail['MARKETING_FEE'] = 0;
								$insert_detail['VAT'] = 0;
								$insert_detail['NET_DUE'] = 0;
							}else{
								$insert_detail['MARKETING_FEE'] = $MF;
								$insert_detail['VAT'] = $this->my_lib->computeVAT($totalMFV, ($PA_MerchantFee * 100), $VAT, FALSE);
								$insert_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalMFV, ($PA_MerchantFee * 100), $VAT, FALSE);
							}

							$checkPAD = $this->Sys_model->v_paD($where_paD, true);
							if($checkPAD == 0){
								$paD_ID = $this->Sys_model->i_paD($insert_detail);

								if(!empty($paD_ID)){
									$whereUBranch = $whereBranch.' AND br.BRANCH_ID = "'.$row['BRANCH_ID'].'"';
									$this->Process_model->uWRecon_ReconPA($whereUBranch, $PA_ID);

									$whereUBranchRef = $whereBranchRef.' AND br.BRANCH_ID = "'.$row['BRANCH_ID'].'"';
									$this->Process_model->uWRecon_RefundPA($whereUBranchRef, $PA_ID);
								}
							}
						}
						$u_paH['MERCHANT_FEE']= $PA_MerchantFee;
						$u_paH['vatcond']= $PA_VAT;
						$u_paH['ExpectedDueDate']= $this->my_lib->computeExpectedDueDate($PA_PayeeDayType, $PA_PayeeQtyOfDays, $dateWhere); //$insert_header['REIMBURSEMENT_DATE']
						$this->Sys_model->u_paH(array('PA_ID'=>$PA_ID), $u_paH);
					}
				}
				//echo '</pre>';
				echo 'DONE - wrecon_genpa';
			}else echo 'No Result Found!';
		}else{
			echo 'Invalid Request!';
		}
	}



	/**
	 * check PA_DETAIL
	 */
	public function nrecon_fixpadetail(){
		if(!isset($_GET['pa']) || empty($_GET['pa'])) {return 'NO PA ID'; exit;}

		$PA_ID = $_GET['pa'];
		$getPCBranchesRow =  $this->Process_model->getPCFNRecon_PADetail($PA_ID);
		$brRowNum = $getPCBranchesRow->num_rows();

		/*CREATE PA DETAIL*/
		if($brRowNum != 0){
			$AFFCODE_li = array_group_by($getPCBranchesRow->result(), "brAFFCODE");
			$AFFCODE_row = json_decode(json_encode($AFFCODE_li), true);

			foreach($AFFCODE_row as $k => $v){
				if(!empty($PA_ID)){
					foreach ($v as $row) {
						$show = '';
						$whereAFFCODE = $u_paH = $insert_detail = $where_paD = [];

						/**
						* IF branch - AFFILIATIONCODE is not null then get data from cp_agreement
						* ELSE proceed with the old process
						*/
						$PA_MerchantFee = $row['MerchantFee'];
						$PA_PayeeDayType = $row['PayeeDayType'];
						$PA_PayeeQtyOfDays = $row['PayeeQtyOfDays'];
						$PA_VAT = $row['vatcond'];

						if($row['merAFFCODE'] <> $row['brAFFCODE']){
							$whereAFFCODE['CP_ID'] =  $row['CPID'];
							$whereAFFCODE['AffiliateGroupCode'] = trim($row['brAFFCODE']);
							$getAFFCODE =  $this->Sys_model->v_agreement($whereAFFCODE, false);
							if($getAFFCODE->num_rows() <> 0){
								$rowAFFCODE = $getAFFCODE->row();
								$PA_MerchantFee = $rowAFFCODE->MerchantFee;
								$PA_PayeeDayType = $rowAFFCODE->PayeeDayType;
								$PA_PayeeQtyOfDays = $rowAFFCODE->PayeeQtyOfDays;
								$PA_VAT = $rowAFFCODE->VATCond;
							}
						}

						$VAT = $this->my_lib->checkVAT($PA_VAT);
						$totalFV = $row['totalAmount'];
						$percentMF = $this->my_lib->convertMFRATE($PA_MerchantFee, true);
						$totalRefund = ($row['refundPostAmount'] == NULL ? 0 : $row['refundPostAmount']); //total amount of refund

						$totalMFV = $totalFV - $totalRefund;
						if($totalMFV < 0) $totalMFV = 0;
						$MF = $this->my_lib->computeMF($totalMFV, ($PA_MerchantFee * 100), '', false);

						/*BUILD PA DETAIL INFO*/
						$where_paD['PA_ID'] =  $insert_detail['PA_ID'] = $PA_ID;
						$where_paD['BRANCH_ID'] = $insert_detail['BRANCH_ID'] = $row['BRANCH_ID'];
						$insert_detail['RATE'] = $percentMF;
						$insert_detail['NUM_PASSES'] = $row['totalPasses'];
						$insert_detail['TOTAL_FV'] = $totalFV;
						$insert_detail['TOTAL_REFUND'] = $totalRefund;
						$insert_detail['DATE_CREATED'] = $this->my_lib->current_date();

						if($totalMFV == 0){
							$insert_detail['MARKETING_FEE'] = 0;
							$insert_detail['VAT'] = 0;
							$insert_detail['NET_DUE'] = 0;
						}else{
							$insert_detail['MARKETING_FEE'] = $MF;
							$insert_detail['VAT'] = $this->my_lib->computeVAT($totalMFV, ($PA_MerchantFee * 100), $VAT, FALSE);
							$insert_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalMFV, ($PA_MerchantFee * 100), $VAT, FALSE);
						}

						$checkPAD = $this->Sys_model->v_paD($where_paD, false);
						if($checkPAD->num_rows() == 0){
							$paD_ID = $this->Sys_model->i_paD($insert_detail);
							echo '<pre> INSERT RECORD : PA_DID '.$paD_ID.'<br />'; print_r($insert_detail); echo '</pre>';
						}else{
							$rowPAD = $checkPAD->row();
							echo '<pre> PA DETAILS ALREADY EXIST : PA_DID '.$rowPAD->PA_DID.'<br />'; print_r($where_paD); echo '</pre>';
						}
					}
					/*
					*** do not include this it will update PA Header Date Created **
					$u_paH['MERCHANT_FEE']= $PA_MerchantFee;
					$u_paH['vatcond']= $PA_VAT;
					$u_paH['ExpectedDueDate']= $this->my_lib->computeExpectedDueDate($PA_PayeeDayType, $PA_PayeeQtyOfDays, $dateWhere); //$insert_header['REIMBURSEMENT_DATE']
					$this->Sys_model->u_paH(array('PA_ID'=>$PA_ID), $u_paH);
					***
					*/
				}
			}
			echo 'DONE - nrecon_fixpadetail';
		} else echo 'No Result Found!';
	}

	public function nrecon_fixpaInt(){
		if(!isset($_GET['pa']) || empty($_GET['pa'])) {return 'NO PA ID'; exit;}

		$PA_ID = $_GET['pa'];
		$getPCBranchesRow =  $this->Process_model->getPCFNRecon_PADetail($PA_ID);
		$brRowNum = $getPCBranchesRow->num_rows();
		if($brRowNum != 0){
			$AFFCODE_li = array_group_by($getPCBranchesRow->result(), "brAFFCODE");
			$AFFCODE_row = json_decode(json_encode($AFFCODE_li), true);

			$tblArr = array();
			foreach($AFFCODE_row as $k => $v){
				foreach ($v as $row) {
					$whereAFFCODE = $update_detail = $where_paD = [];

					$PA_MerchantFee = $row['MerchantFee'];
					$PA_PayeeDayType = $row['PayeeDayType'];
					$PA_PayeeQtyOfDays = $row['PayeeQtyOfDays'];
					$PA_VAT = $row['vatcond'];

					if($row['merAFFCODE'] <> $row['brAFFCODE']){
						$whereAFFCODE['CP_ID'] =  $row['CPID'];
						$whereAFFCODE['AffiliateGroupCode'] = trim($row['brAFFCODE']);
						$getAFFCODE =  $this->Sys_model->v_agreement($whereAFFCODE, false);
						if($getAFFCODE->num_rows() <> 0){
							$rowAFFCODE = $getAFFCODE->row();
							$PA_MerchantFee = $rowAFFCODE->MerchantFee;
							$PA_PayeeDayType = $rowAFFCODE->PayeeDayType;
							$PA_PayeeQtyOfDays = $rowAFFCODE->PayeeQtyOfDays;
							$PA_VAT = $rowAFFCODE->VATCond;
						}
					}

					$VAT = $this->my_lib->checkVAT($PA_VAT);
					$totalFV = $row['totalAmount'];
					$percentMF = $this->my_lib->convertMFRATE($PA_MerchantFee, true);
					$MF = $this->my_lib->computeMF($totalFV, ($PA_MerchantFee * 100));

					/*BUILD PA DETAIL INFO*/
					$where_paD['PA_ID'] = $PA_ID;
					$where_paD['BRANCH_ID'] = $row['BRANCH_ID'];

					$checkPAD = $this->Sys_model->v_paD($where_paD, false);
						$checkPADNUM = $checkPAD->num_rows();
					if($checkPADNUM == 1){
						$rowPAD = $checkPAD->row();

						if($rowPAD->NUM_PASSES <> $row['totalPasses'] && $rowPAD->TOTAL_FV <> $totalFV){
							$whereUpdate['PA_DID'] = $rowPAD->PA_DID;
								$update_detail['RATE'] = $percentMF;
								$update_detail['NUM_PASSES'] = $row['totalPasses'];
								$update_detail['TOTAL_FV'] = $totalFV;
								$update_detail['MARKETING_FEE'] = $MF;
								$update_detail['VAT'] = $this->my_lib->computeVAT($totalFV, ($PA_MerchantFee * 100), $VAT);
								$update_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, ($PA_MerchantFee * 100), $VAT);
								$update_detail['DATE_CREATED'] = $rowPAD->DATE_CREATED;

							$this->Sys_model->u_paD($whereUpdate, $update_detail);
							echo '<pre> 1 UPDATE RECORD : PA_DID '.$rowPAD->PA_DID.' Branch:'.$rowPAD->BRANCH_ID.'<br />'; print_r($update_detail); echo '</pre>';

						}else if($rowPAD->NET_DUE == 99999.99999 && $rowPAD->TOTAL_FV == $totalFV){
							$update_detail['NET_DUE'] = $this->my_lib->computeNETDUE($totalFV, ($PA_MerchantFee * 100), $VAT);

							 $this->Sys_model->u_paD($whereUpdate, $update_detail);
							echo '<pre> 2 UPDATE RECORD NET_DUE : PA_DID '.$rowPAD->PA_DID.' Branch:'.$rowPAD->BRANCH_ID.'<br />'; print_r($update_detail); echo '</pre>';
						}
					}else{
						echo '<pre> NO ACTION - PA_ID '.$PA_ID. ' TOTAL_RESULT - '.$checkPADNUM; echo '</pre>';
					}

				}
			}
		}
	 }



}//END OF CONTROLLER
