<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class My_lib extends MX_Controller{
	private $DATE_NOW;

	public function __construct() {
		parent::__construct();
		$this->DATE_NOW = $this->current_date();
	}
		
	/**
	 * asset_location function
	 *
	 * @return string
	 */
	public function asset_location(){
		switch (ENVIRONMENT) {
			case 'development':
				return 'C:/xampp/htdocs/mp_dis/';
				break;
			default:
				return '/var/www/html/mp_dis/';
		}
	}
	
	/**
	 * check_filename function
	 *
	 * @return string
	 */
	 
	public function check_filename($filename, $prevModule){	
		/* check if there is an filename contains string*/
		if(strpos($filename,'QRReconciliationReport') !== false || strpos($filename,'MerchantPortalReconciliationReport') !== false){
			return $prevModule.'_temp';
		} else{
			return $prevModule;
		}
	}
	
	public function check_foldername($foldername, $prevModule){	
		/* check if there is an filename contains string*/
		if(in_array($foldername, array('evoucher_recon')) !== false){
			return $prevModule.'_temp';
		} else{
			return $prevModule;
		}
	}
	
	/**
	 * _filename function
	 * HANDLE CSV FILE NAME & FOLDER LOCATION FROM JASPER
	 * @param string $module
	 * @return array
	 
	public function _filename($module){
		if(empty($module)) return false;
		$arr = '';
		switch ($module) {
			case 'service': 
				return array('module' => 'service','filname' => 'CP_Service.xls'); break;
			case 'client': 
				return array('module' => 'client','filname' => 'CP_Client.xls'); break;
			case 'agreement': 
				return array('module' => 'agreement','filname' => 'CP_Agreement.xls'); break;
			case 'cp_order': 
				return array('module' => 'cp_order','filname' => 'CP_ORDER_'); break;
		}
	
	}	*/	

	/**
	 * u_download function
	 * 
	 * @param array $module {module: module name, filname: old name} 
	 * @return string
	 */
	public function u_download($module){	
		if(empty($module)) return false;			
		
		$dirLocation = dirname($_SERVER["SCRIPT_FILENAME"])."/to_upload";	
		$newPathName = $dirLocation."/".$module['module']."/".$module['filname'];
	
		return $newPathName;
	}
	
	/**
	 * u_rename function
	 *
	 * @param array $module {module: module name, filname: old name} 
	 * @param boolean $db_r {from db query}
	 * @return void
	 */
	public function u_rename($module, $db_r){	
		if(empty($module) && empty($db_r)) return false;			
		
		$dirLocation = dirname($_SERVER["SCRIPT_FILENAME"]);	
		$newPathName = $dirLocation."/to_archive/".$module['module']."/".($db_r == false ? 'ERR' : '').strtotime($this->DATE_NOW).'_'.$module['filname'];
			rename($dirLocation."/to_upload/".$module['module']."/".$module['filname'], $newPathName);
			
		return $newPathName;
	}

	/**
	 * current_date function
	 *
	 * @method call setDate()
	 */
	public function current_date(){
		return $this->setDate();
	}
	
	public function checkSyncDate($date){
		$date = trim(str_replace("/","-",$date));
		if(empty($date)){
			return '0000-00-00 00:00:00';
		}  
		return date("Y-m-d H:i:s", strtotime($date));
	}

	/**
	 * setDate function
	 *
	 * @param string $date
	 * @param boolean $dateOnly
	 * @return datetime
	 */
	public function setDate($date = null, $dateOnly = false, $completeDate = false) {
		$time = now();	
		if($completeDate == true) $format = ('%Y-%m-%d %H:%i:%s %A');	
		else  $format = ('%Y-%m-%d %H:%i:%s');	
		if($date != null) $time = strtotime($date); 		
		if($dateOnly == true) $format = ('%Y-%m-%d');
		
		return mdate($format , $time);
	}
	
	public function convertDate($orgDate, $format = 'mm/dd/YYYY') {
		if(empty($date)) return '';
		
		return date($format, strtotime($orgDate));
	}

	/**
	 * setMyDate function
	 *
	 * @param timeformat $dateSET
	 * @param boolean $dateFORMAT
	 * @return datetime
	 */
	public function setMyDate($dateSET, $dateFORMAT = false) {
			if(!$dateSET) $dateSET = date("Y-m-d");
		$format = ('Y-m-d H:i:s');
		if($dateFORMAT  != false) $format = ($dateFORMAT);			
		return date($format, strtotime($dateSET));
	}
	
	/**
	 * convertPointsValue function
	 *
	 * @param int $points
	 * @return int 
	 */
	public function convertPointsValue($points){
		$check =  is_numeric( $points ) && floor( $points ) != $points;
	
		if($check == true) return floor($points * 100);
		else return $points;
	}
	

	/**
	 * getContextID function
	 * identify ORDER STATUS
	 * @param string $subsReference
	 * @return void
	 */
	public function getContextID($subsReference){
		if(empty($subsReference)) return false;
		$string = strstr($subsReference, 'PH', true);
		return $string;
	}
	
	/**
	 * strstr_after function
	 *
	 * @param string $haystack
	 * @param int $needle
	 * @param boolean $case_insensitive
	 * @return string
	 */
	public function strstr_after($haystack, $needle, $case_insensitive = false) {
		$strpos = ($case_insensitive) ? 'stripos' : 'strpos';
		$pos = $strpos($haystack, $needle);
		if (is_int($pos)) {
			return substr($haystack, $pos + strlen($needle));
		}
		return $pos;
	}	
	
	public function daysOfWeek(){
		$timestamp = strtotime('next Sunday');
		$days = array();
		for ($i = 0; $i < 7; $i++) {
			$days[] = strftime('%A', $timestamp);
			$timestamp = strtotime('+1 day', $timestamp);
		}
		return $days;
	}
	
	/*
	* PAYMENT CUTOFF TERMS
	*/
	public function paymentTerms($terms){
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
			default:
				return 'Monthly';
		}
	}
	public function setCFDate($selectedDate){
		$YM = date("Y-m");
		if($selectedDate > date("d")) $YM = date("Y-m", strtotime('-1 month', now()));
		
		if($selectedDate == 31){
			return $YM."-".cal_days_in_month(CAL_GREGORIAN, date('m', strtotime($YM)), date('Y', strtotime($YM))); 
		}else{			
			return $YM."-".$selectedDate;
		}
	}
	public function setCFDay($selectedDay){
		return date('Y-m-d',strtotime('last '.$selectedDay));
	}
	
	/*
	* FORMULA FOR CONVERT MERCHANT FEE
	2 * 100 = MFRATE 0.02
	*/
	public function convertMFRATE($MF, $percentage = false){
		if($percentage == true){
			//$formatter = new NumberFormatter('en_US', NumberFormatter::PERCENT);		 
			//return $formatter->format($MF);
			return ($MF * 100).'%'; 
		}else return ($MF / 100); 
	}
	
	/*
	* FORMULA FOR MERCHANT FEE
	199 * (MFRATE 0.02) =  MF 3.98
	*/	
	public function computeMF($totalFV, $MF, $roundUp = 2, $ROUND = TRUE){
		if($ROUND == false)  $total = ($totalFV * $this->convertMFRATE($MF));
		else $total = round(($totalFV * $this->convertMFRATE($MF)), $roundUp); 
		return $total;
	}
	
	/*
	* FORMULA FOR MERCHANT FEE INTERFACE FILE
	199 * (MFRATE 0.02) =  MF 3.98
	*/	
	public function computeMFVATINCL($totalFV, $MF, $VAT_OUTPUT){
		$total = $this->computeMF($totalFV, $MF, '', FALSE) + $VAT_OUTPUT; 
		return $total;
	}
	
	/*
	* FORMULA FOR VAT
	MF 3.98 * 0.12 = VAT 0.4776‬	
	MF 0.02 * 0.12 = VAT 0.4776‬
	mfrate (18.50 * 0.05)  = 0.925 * 0.12 = 
	*/	
	public function computeVAT($totalFV, $MF, $vatCond = 0.12, $ROUND = TRUE){
		$mfRATE = $this->computeMF($totalFV, $MF, '', FALSE);
		$total = ($mfRATE * $vatCond); 
		if($ROUND == false) return $total; 
		else return round($total, 2); 
		return $total;
	}
	public function checkVAT($vatCond){
		if ($vatCond == 'Taxable') return 0.12;
		else return 0;
	}
	
	/*
	* FORMULA FOR NET DUE *** FOR AFF
	199 , 2
	*/	
	public function computeNETDUE($totalFV, $MF, $vatCond = 0.12, $ROUND = TRUE){
		$mfRATE = $this->computeMF($totalFV, $MF, '', FALSE); //MF 3.98
		$VAT = $this->computeVAT($totalFV, $MF, $vatCond, FALSE); // VAT 0.4776‬ (round of)
		$NetTotal = ($totalFV - $mfRATE - $VAT); //194.5424
		if($ROUND == false) return $NetTotal; 
		else return round($NetTotal, 2); 
		//cross checking = ($NetTotal + $mfRATE + $VAT) = $totalFV
	}
	
	public function numFormat($number){
		return floor(($number*100))/100; // return number format without rounding
	}
	
	/*
	* FORMULA FOR VAT
	*/	
	public function digitalID($CPID, $decode = FALSE){
		$prefix = 'Z';		 
		if($decode == true) return ltrim($CPID, $prefix);
		else return $prefix.$CPID; 
	}
	
	/*
	* FORMULA FOR PA NUMBER
	*/	
	public function paNumber($PAID, $decode = false){
		$prefix = 'Z';		 
		$minimum = 6;
		$str = str_repeat(0, $minimum);
		$PAID_Num = strlen($PAID);
		
		if($PAID_Num < $minimum){
			if($decode == true) $sub_str = ltrim($PAID, 0);
			else $sub_str = substr_replace($str, $PAID,($minimum - $PAID_Num), $PAID_Num);
		}else  $sub_str = $PAID;

		if($decode == true) return ltrim(strtoupper($sub_str), $prefix);
		else return $prefix.$sub_str; 
	}

	/**
	 * multiVoucher
	 *
	 * @return array voucher_id
	 */
	public function multiVoucher($voucher){
		/**
		 * check if voucher IS multiple
		 */
		$str = explode(',', $voucher);
		if(count($str) !=0) $str = implode(',', $str);
		else $str = $voucher;
		
		return $str;
	}
	public function multiPANUM($panum, $not_pa = false){
		/**
		 * check if voucher IS multiple
		 */
		$str = explode(',', trim($panum));
		if(count($str) !=0){
			$arr = array(); 
			for($x=0; $x < count($str); $x++){ 
				if($not_pa <> false) $arr[] = str_replace('+','',urlencode($str[$x])); 
				else $arr[] = $this->paNumber(str_replace('+','',urlencode($str[$x])), true);
			}	
			$str = implode(',', $arr);
		}else $str = $panum;
		
		return $str;
	}	
	public function explodeVoucher($voucher){
		/**
		 * check if voucher IS multiple
		 */
		$str = explode("\n", $voucher);
		$return['count'] = count($str);
		$return['result'] = $str;
		return $return;
	}

	/**
	 * expected_due_date
	 *
	 * @return array expected_due_date
	 */
	public function computeExpectedDueDate($dayType, $qtyOfDays, $paGenDate){
		//check dayType
		/*
		1	Calendar Days (coverage all)
		2	Working Days (coverage all except sat & sun)
		3	Calendar days (coverage all)
		*/			
		$Date = date("Y-m-d", strtotime($paGenDate)); 
		
		if($dayType == 2) return date('Y-m-d', strtotime($Date. ' +'.$qtyOfDays.' weekdays'));
		else return date('Y-m-d', strtotime($Date. ' +'.$qtyOfDays.' days'));
	}
	
	/**
	 * create folder for payment advise
	 */
	 public function makeDIR($location, $folderName){
		if (!is_dir($location.$folderName)) {
			mkdir($location.$folderName, 0777, TRUE);
		}		
		return $location.$folderName.'/'; 
	}
	
	/**
	 * convert TIN number format
	 */
	 public function setTin($TIN){
		$count = STRLEN($TIN);
		if($count == 12 || $count == 14){
			$TIN = trim(str_replace('-', '', $TIN));
			//add "-" every 3 numbers
			$TIN = str_split($TIN, 3);
			return implode('-', $TIN);
		}
		return false;
	 }

/**
 * --------------- CIC FUNCTIONS --------------------------
 */

	 /**
	  * formula for NET DUE of Client Digital Orders
	  * 
	  * NET DUE = (TFV + OTHER BILLABLE ITEMS(with VAT)) - BILLABLE DISCOUNT
	  * NET sumOfBillablItem  = without tax
	  * GROSS sumOfBillablItem = with tax
	  */
	 public function computeNETGROSS_CLIENT($sumOfBillablItem, $discountBILLABLE){
		$NetTotal = round(($sumOfBillablItem  - $discountBILLABLE), 2);
		return $NetTotal; 
	}

	public function computeBillVAT($creditAmount, $vatPercent){
		$total = $creditAmount * $vatPercent; //get the vat amount from the billable credit
		//return number_format($total,2);
		return round($total, 2);
	}

	public function computeBillIncVAT($creditAmount, $vatAmount){
		$total = $creditAmount + $vatAmount; 
		//return number_format($total,2);
		return round($total, 2);
	}

	/**
	 * validation for Face Value Name
	 */
	public function validateAM_Billable($AM = '', $BillableName = ''){
		$houseArr = array();
		$houseArr[] ='SPI House Account';
		$houseArr[] ='SM House Account';
		
		if(in_array($AM, $houseArr)) return 'Face value (credits in stock)';
		else return $BillableName;
	}

	/**
	 * READ VOUCHER CODE
	 *	0000 - 00 - 00 - 0000 - 00000000
		VOUCHER ID EXT - VERIFY DGT - SERVICE ID - FACE VALUE - VOUCHER ID
	 */
	public function read_barcode($BARCODE, $lookFor = ''){
		//CHECK LENGTH OF BARCODE = must be 20 CHAR
		if(empty($BARCODE) || strlen($BARCODE) <> 20) return false;

		$BARCODE = trim($BARCODE);
		$return['VID_EXT'] = substr($BARCODE,0,4);
		$return['VERIFY_DIGIT'] = substr($BARCODE,4,2);
		$return['SERVICE_ID'] = substr($BARCODE,6,2);
		$return['FACE_VALUE'] = substr($BARCODE,8,4);
		$return['VOUCHER_ID'] = substr($BARCODE,12,8);

		if(empty($lookFor)) return $return;
		else return $return[$lookFor];
	}
	
	/*
	* FORMULA FOR RS NUMBER ::
		DIS + "_" + BRANCH_ID + "_" + PAYMENTDUEDATE + "_" + RS_ID
	*/	
	public function rsNumber($BRANCHID, $PAYMENTDUEDATE, $RSID){
		if(empty($BRANCHID) || empty($PAYMENTDUEDATE) || empty($RSID)) return false;

		$prefix = "DIS";
		$delimiter = "_";
		return $prefix.$delimiter.$BRANCHID.$delimiter.date("mdY", strtotime($PAYMENTDUEDATE)).$delimiter.$RSID;  
	}

	/**
	 * 
	 */
	public function rsStages($CURRENT_STAGE){
		if(empty($CURRENT_STAGE)) return $CURRENT_STAGE;

		if($CURRENT_STAGE == 'CONVERTED') return 'PROCESSED';
		else return $CURRENT_STAGE;
	}

	/**
	 * 
	 */
	public function product_mapping($SERVICE_ID){
		if(empty($SERVICE_ID)) return $SERVICE_ID;
		if(in_array($SERVICE_ID, array(26,27,36,16,37))) return 42;
		else if(in_array($SERVICE_ID, array(9,1))) return 43;
		else if(in_array($SERVICE_ID, array(6,3,17))) return 44;
		else if(in_array($SERVICE_ID, array(20,23,15))) return 45;
		else if(in_array($SERVICE_ID, array(18,21,13))) return 46;
		else if(in_array($SERVICE_ID, array(19,22,14))) return 47;
		else if(in_array($SERVICE_ID, array(35))) return 48;
		else return $SERVICE_ID;
	}
	
	
	/**
	 * Function that groups an array of associative arrays by some key.
	 * 
	 * @param {String} $key Property to sort by.
	 * @param {Array} $data Array that stores multiple associative arrays.
	 */
	function group_by($key, $data) {
		$result = array();

		foreach($data as $val) {
			if(array_key_exists($key, $val)){
				$result[$val[$key]][] = $val;
			}else{
				$result[""][] = $val;
			}
		}

		return $result;
	}
	
	/**
	 * Function to remove special characters
	 */
	public function xss_filter($data){
		if(empty($data)) return false;
		$data = html_entity_decode($data, ENT_NOQUOTES, "UTF-8");
			$data = str_replace(array('�'), '?', $data);
		return $data;
	}
	
	
	/**
	 * Function that will generate log file for CRON checking
	 * 
	 * @param {String} $funcName Name of cron log for checking
	 * @param {Array} $msg Message
	* 	• Processing checker :  Check_MMDDYYYY.zzz
	* 	• Redemption data : redeem_MMDDYYYY.zzz
	* 	• Reversal data : reversal_MMDDYYYY.zzz
	* 	• Transaction Tagging : tagging_MMDDYYYY.zzz
	* $this->my_lib->cronLog('redeem', 'testing');
	 */
	public function cronLog($level, $msg) { //here overriding
		$_log_path = "C:/xampp/htdocs/cron_logs/";

        /* HERE YOUR LOG FILENAME YOU CAN CHANGE ITS NAME */
        $filepath = $_log_path.$level.'_'.date('mdY').'.zzz';
        $message  = '';
		
        if ( ! file_exists($filepath))
        {
        $message .= "<"."?php  if ( ! defined('BASEPATH'))
        exit('No direct script access allowed'); ?".">\n\n";
        }

        if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
        {
        return FALSE;
        }

        $message = date('Y-m-d H:i:s'). ' --> '.$msg."\n";

        flock($fp, LOCK_EX);
        fwrite($fp, $message);
        flock($fp, LOCK_UN);
        fclose($fp);

        @chmod($filepath, FILE_WRITE_MODE);
        return TRUE;
    }
}
