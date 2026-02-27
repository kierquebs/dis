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
			$formatter = new NumberFormatter('en_US', NumberFormatter::PERCENT);
			return $formatter->format($MF);
		}else return ($MF / 100); 
	}
	
	/*
	* FORMULA FOR MERCHANT FEE
	199 * (MFRATE 0.02) =  MF 3.98
	*/	
	public function computeMF($totalFV, $MF){
		$total = round(($totalFV * $this->convertMFRATE($MF)), 2); 
		return $total;
	}
	
	/*
	* FORMULA FOR VAT
	MF 3.98 * 0.12 = VAT 0.4776‬	
	MF 0.02 * 0.12 = VAT 0.4776‬
	*/	
	public function computeVAT($totalFV, $MF, $vatCond = 0.12){
		$mfRATE = $this->computeMF($totalFV, $MF);
		$total =  round(($mfRATE * $vatCond), 2); 
		return $total;
	}
	public function checkVAT($vatCond){
		if ($vatCond == 'Taxable') return 0.12;
		else return 0;
	}
	
	/*
	* FORMULA FOR NET DUE
	199 , 2
	*/	
	public function computeNETDUE($totalFV, $MF, $vatCond = 0.12){
		$mfRATE = $this->computeMF($totalFV, $MF); //MF 3.98
		$VAT = $this->computeVAT($totalFV, $MF, $vatCond); // VAT 0.4776‬ (round of)
		$NetTotal = round(($totalFV - $mfRATE - $VAT), 2); //194.5424
		return $NetTotal; //cross checking = ($NetTotal + $mfRATE + $VAT) = $totalFV
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
	* FORMULA FOR VAT
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
	public function multiPANUM($panum){
		/**
		 * check if voucher IS multiple
		 */
		$str = explode(',', trim($panum));
		if(count($str) !=0){
			$arr = array(); 
			for($x=0; $x < count($str); $x++){
				$arr[] = $this->paNumber(str_replace('+','',urlencode($str[$x])), true);
			}	
			$str = implode(',', $arr);
		}else $str = $panum;
		
		return $str;
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
}
