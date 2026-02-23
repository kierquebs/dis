<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Lib_object extends MX_Controller{ 
	
	/**
	 * CONTEXT OBJ variable
	 *
	 * @var integer
	 */
	public $CONTEXT_STATUS_INACTIVE		= 0;
    public $CONTEXT_STATUS_ACTIVE		= 1;
    public $CONTEXT_STATUS_CLOSED		= 2;
    public $CONTEXT_STATUS_INACTIVATED	= 3;
	
	/**
	 * ORDER OBJ variable
	 *
	 * @var integer
	 */
	public $ORDER_TYPE_GIFT						= 1;
	public $ORDER_TYPE_ANONYM_VOUCHER			= 2;
	public $ORDER_TYPE_ASSIGNED_VOUCHER			= 4;
	public $ORDER_TYPE_ONESHOT_VOUCHER			= 8;
	public $ORDER_TYPE_PERSONAL					= 16;
	public $ORDER_TYPE_TOPUP					= 8192;
	public $ORDER_TYPE_SERVICE_AWARD_ONESHOT	= 1024;
	public $ORDER_TYPE_SERVICE_AWARD_GIFT		= 4096;
	public $ORDER_TYPE_GIFT_PURCHASE			= 16384;
	public $ORDER_TYPE_GIFT_PARTICIPANT			= 32768;	
	//---- ---//
	public $ORDER_STATUS_NEW				= 1;
	public $ORDER_STATUS_APPROVED			= 2;
	public $ORDER_STATUS_PROCESSING			= 3;
	public $ORDER_STATUS_DELIVERED			= 4;
	public $ORDER_STATUS_FINISHED			= 5;
	public $ORDER_STATUS_CANCELLED			= 6;
	public $ORDER_STATUS_INVALID			= 7;
	public $ORDER_STATUS_ERROR				= 8;
	public $ORDER_STATUS_MISSING_DATA		= 9; //Client id missing
	public $ORDER_STATUS_IN_DELIVERY		= 10;
	public $ORDER_STATUS_RETURN				= 11;
	public $ORDER_STATUS_REFUND				= 12;	
	
	/**
	 * ORDER_LINE_DELIVERY OBJ variable
	 *
	 * @var integer
	 */
	public $ORDERDEL_STATUS_NEW				= 0;
	public $ORDERDEL_STATUS_PURCHASED		= 1;
	public $ORDERDEL_STATUS_EXPIRED			= 2;
	public $ORDERDEL_STATUS_ATTRIBUTED		= 3;
	public $ORDERDEL_STATUS_DELIVERED		= 4;
	public $ORDERDEL_STATUS_IN_DELIVERY		= 5;
	public $ORDERDEL_STATUS_PROCESSING		= 6;
	public $ORDERDEL_STATUS_CANCELLED		= 7;
	public $ORDERDEL_STATUS_REFUND			= 8;
	public $ORDERDEL_STATUS_RETURN			= 9;

	/**
	 * PAYMENTS OBJ variable
	 *
	 * @var integer
	 */
	public $PAYMENT_STATUS_OPEN				= 1;
	public $PAYMENT_STATUS_CLOSED			= 2;
	public $PAYMENT_STATUS_ERROR			= 3;
	public $PAYMENT_STATUS_CANCELLED		= 4;
	public $PAYMENT_STATUS_TO_CATALOGUE		= 5;
	
	/**
	 * SUPPLIER OBJ variable
	 *
	 * @var integer
	 */
	public $SUPPLIER_STATUS_NEW						= 1;
	public $SUPPLIER_STATUS_SENDING_TO_SUPPLIER 	= 2;
	public $SUPPLIER_STATUS_SENT_TO_SUPPLIER 		= 3;
	public $SUPPLIER_STATUS_IN_TREATMENT			= 4;
	public $SUPPLIER_STATUS_DELIVERED				= 5;
	public $SUPPLIER_STATUS_CANCELLED				= 6;
	public $SUPPLIER_STATUS_IN_DELIVERY				= 7;
	public $SUPPLIER_STATUS_RETURN					= 8;
	public $SUPPLIER_STATUS_REFUND					= 9;
	
	public function __construct() {
		parent::__construct();
    }
	
	/**
	 * getStatText function
	 *
	 * @param string $name
	 * @param int $code
	 * @return string
	 */
	public function getStatText($name, $code){
		if ($name == 'context'){		
			switch($code){	
				case $this->CONTEXT_STATUS_INACTIVE: 	return 'Not Activated';
				case $this->CONTEXT_STATUS_ACTIVE: 		return 'Active';
				case $this->CONTEXT_STATUS_CLOSED: 		return 'Closed';
				case $this->CONTEXT_STATUS_INACTIVATED:	return 'Inactive';
				default: 								return 'InvalidStatus';			
			}
		}
		elseif ($name == 'order'){
			switch($code){
				case $this->ORDER_STATUS_NEW: 			 return 'new';
				case $this->ORDER_STATUS_APPROVED:		 return 'approved';
				case $this->ORDER_STATUS_PROCESSING: 	 return 'processing';
				case $this->ORDER_STATUS_DELIVERED: 	 return 'delivered';
				case $this->ORDER_STATUS_FINISHED: 	     return 'finished';
				case $this->ORDER_STATUS_CANCELLED: 	 return 'cancelled';
				case $this->ORDER_STATUS_INVALID: 		 return 'invalidOrder';
				case $this->ORDER_STATUS_ERROR: 		 return 'erroneousOrder';
				case $this->ORDER_STATUS_MISSING_DATA:	 return 'missingData';
				case $this->ORDER_STATUS_IN_DELIVERY:	 return 'inDelivery';
				case $this->ORDER_STATUS_RETURN:		 return 'return';
				case $this->ORDER_STATUS_REFUND:		 return 'refund';
				default:								 return 'invalidStatus';		
			}
		
		}
		elseif ($name == 'orderdel'){
			switch($code){
				case $this->ORDERDEL_STATUS_NEW:			return 'new';
				case $this->ORDERDEL_STATUS_PURCHASED:		return 'used';
				case $this->ORDERDEL_STATUS_EXPIRED:		return 'expired';
				case $this->ORDERDEL_STATUS_ATTRIBUTED:		return 'attributed';
				case $this->ORDERDEL_STATUS_DELIVERED:		return 'delivered';
				case $this->ORDERDEL_STATUS_IN_DELIVERY:	return 'inDelivery';
				case $this->ORDERDEL_STATUS_PROCESSING:		return 'processing';
				case $this->ORDERDEL_STATUS_CANCELLED:		return 'cancelled';
				case $this->ORDERDEL_STATUS_REFUND:			return 'refund';
				case $this->ORDERDEL_STATUS_RETURN:			return 'return';
				default:									return 'invalidStatus';
			}
		}
		elseif ($name == 'payment'){
			switch($code){
				case $this->PAYMENT_STATUS_OPEN: 			return 'open';
				case $this->PAYMENT_STATUS_CLOSED: 			return 'closed';
				case $this->PAYMENT_STATUS_ERROR: 			return 'error';
				case $this->PAYMENT_STATUS_CANCELLED: 		return 'cancelled';
				case $this->PAYMENT_STATUS_TO_CATALOGUE: 	return 'toCatalogue';
				default: 									return 'invalidStatus';
			}
		}
		elseif ($name == 'supplier'){
			switch($code){
				case $this->SUPPLIER_STATUS_NEW:					return 'new';
				case $this->SUPPLIER_STATUS_SENDING_TO_SUPPLIER:	return 'sendingToSupplier';
				case $this->SUPPLIER_STATUS_SENT_TO_SUPPLIER:		return 'sentToSupplier';
				case $this->SUPPLIER_STATUS_IN_TREATMENT:			return 'inTreatment';
				case $this->SUPPLIER_STATUS_DELIVERED:				return 'delivered';
				case $this->SUPPLIER_STATUS_CANCELLED:				return 'cancelled';
				case $this->SUPPLIER_STATUS_IN_DELIVERY:			return 'inDelivery';
				case $this->SUPPLIER_STATUS_RETURN:					return 'return';
				case $this->SUPPLIER_STATUS_REFUND:					return 'refund';
				default:											return 'invalidStatus';
			}
		}
		return '';
	}
	
	/**
	 * getTypeText function
	 *
	 * @param string $name
	 * @param int $code
	 * @return string
	 */
	public function getTypeText($name, $code){
		if ($name == 'order'){
			if ($code == ($code | $this->ORDER_TYPE_GIFT))											return 'Gift';
			if ($code == ($code | $this->ORDER_TYPE_GIFT_PURCHASE))									return 'GiftPass';
			if ($code == ($code | $this->ORDER_TYPE_PERSONAL | $this->ORDER_TYPE_ONESHOT_VOUCHER))	return 'BeneficiaryOrder';
			if ($code == ($code | $this->ORDER_TYPE_PERSONAL))										return 'ParticipantOrder';
			if ($code == ($code | $this->ORDER_TYPE_ONESHOT_VOUCHER))								return 'OneShotVoucher';
			if ($code == ($code | $this->ORDER_TYPE_ASSIGNED_VOUCHER))								return 'ParticipantVoucher';
			if ($code == ($code | $this->ORDER_TYPE_ANONYM_VOUCHER))								return 'AnonymousVoucher';
			if ($code == ($code | $this->ORDER_TYPE_TOPUP))											return 'AccountTopup';
			if ($code == ($code | $this->ORDER_TYPE_GIFT_PARTICIPANT))								return 'GiftParticipant';
			if ($code == ($code | $this->ORDER_TYPE_SERVICE_AWARD_ONESHOT))							return 'CelebrateEventOneShot';
			if ($code == ($code | $this->ORDER_TYPE_SERVICE_AWARD_GIFT))							return 'CelebrateEventGift';
																									return 'InvalidType';
		}
		return '';
	}
	
	
	/**
	 * setOrderStatus function
	 *
	 * @param int $supplierCode
	 * @param int $suppUpdateCode
	 * @return int
	 */
	public function setOrderStatus($supplierCode, $suppUpdateCode = null){
		if(empty($supplierCode)) return false;
		$returnArr = '';
		/* -- SUPPLIER ORDER STATUS --*/	
		
		if($suppUpdateCode == null){
			switch($supplierCode){
				case 	$this->SUPPLIER_STATUS_NEW : 
							$changeCode = $this->SUPPLIER_STATUS_SENDING_TO_SUPPLIER; break;
				case 	$this->SUPPLIER_STATUS_SENDING_TO_SUPPLIER : 
							$changeCode = $this->SUPPLIER_STATUS_SENT_TO_SUPPLIER; break;
				case 	$this->SUPPLIER_STATUS_SENT_TO_SUPPLIER : 
							$changeCode = $this->SUPPLIER_STATUS_IN_TREATMENT; break;
				case 	$this->SUPPLIER_STATUS_IN_TREATMENT : 
							$changeCode = $this->SUPPLIER_STATUS_IN_DELIVERY; break;
				case 	$this->SUPPLIER_STATUS_IN_DELIVERY : 
							$changeCode = $this->SUPPLIER_STATUS_DELIVERED; break;
				case 	$this->SUPPLIER_STATUS_DELIVERED : 
							$changeCode = $this->SUPPLIER_STATUS_RETURN; break;
				case 	$this->SUPPLIER_STATUS_RETURN : 
							$changeCode = $this->SUPPLIER_STATUS_REFUND; break;

			}
		}else $changeCode = $suppUpdateCode;
		
		$returnArr['supplier_status'] = $changeCode;
		
		/**
		 * -- ORDER STATUS -- 
		 */	
		switch($changeCode){
			case 	$this->SUPPLIER_STATUS_NEW : 
						$returnArr['order_status'] = $this->ORDER_STATUS_APPROVED; 
						$returnArr['orderdel_status'] = $this->ORDERDEL_STATUS_NEW;
						break;
			case 	$this->SUPPLIER_STATUS_SENDING_TO_SUPPLIER || 
					$this->SUPPLIER_STATUS_SENT_TO_SUPPLIER || 
					$this->SUPPLIER_STATUS_IN_TREATMENT || 
					$this->SUPPLIER_STATUS_IN_DELIVERY : 
						$returnArr['order_status'] = $this->ORDER_STATUS_PROCESSING;
						$returnArr['orderdel_status'] = $this->ORDERDEL_STATUS_PROCESSING;
						break;
			case 	$this->SUPPLIER_STATUS_DELIVERED : 
						$returnArr['order_status'] = $this->ORDER_STATUS_DELIVERED; 
						$returnArr['orderdel_status'] = $this->ORDERDEL_STATUS_DELIVERED;
						break;
			case 	$this->SUPPLIER_STATUS_RETURN : 
						$returnArr['order_status'] = $this->ORDER_STATUS_IN_DELIVERY;
						$returnArr['orderdel_status'] = $this->ORDERDEL_STATUS_IN_DELIVERY;
						break;
			case 	$this->SUPPLIER_STATUS_REFUND : 
						$returnArr['order_status'] = $this->ORDER_STATUS_REFUND; 
						$returnArr['orderdel_status'] = $this->ORDERDEL_STATUS_REFUND;
						break;
			case 	$this->SUPPLIER_STATUS_CANCELLED : 
						$returnArr['order_status'] = $this->ORDER_STATUS_CANCELLED; 
						$returnArr['orderdel_status'] = $this->ORDERDEL_STATUS_CANCELLED;
						break;
		}
		
		return $returnArr;
	}
	
}
