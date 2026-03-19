<?php
class Process_model extends CI_Model{	

	public function __construct(){
		parent::__construct();
	}

	/**
	 * MODEL FOR MERCHANT WITH RECON
	 */
	public function getPCFWRecon($where = '', $count = false, $page = '', $dateWhere = '', $pcfWHere = ''){
		$limit = '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		$pcfWHere = (empty($pcfWHere) ? '' : ' AND ').$pcfWHere;	//paymentcutoff 
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		$result = $this->db->query("
			select 
				combine_tbl.MID,
				combine_tbl.CPID,
				combine_tbl.LegalName,
				combine_tbl.SPECIFIC_DATE,
				combine_tbl.SPECIFIC_DAY,
				combine_tbl.TYPE,
				combine_tbl.PayeeDayType,
				combine_tbl.PayeeQtyOfDays,
				SUM(combine_tbl.totalAmount) AS totalAmount,	
				SUM(combine_tbl.totalPasses) AS totalPasses,	
				SUM(combine_tbl.refundAmount) refundAmount,			
				SUM(combine_tbl.totalPassesRef) totalPassesRef,	
				SUM(combine_tbl.totalBranch) AS totalBranch
			from
			(
				(
					select 
						TBL2.MID AS MID,
						TBL2.CPID AS CPID,
						TBL2.LegalName AS LegalName,
						TBL2.SPECIFIC_DATE AS SPECIFIC_DATE,
						TBL2.SPECIFIC_DAY AS SPECIFIC_DAY,
						TBL2.TYPE AS TYPE,
						TBL2.PayeeDayType AS PayeeDayType,
						TBL2.PayeeQtyOfDays AS PayeeQtyOfDays,
						SUM(TBL2.totalAmount) AS totalAmount,	
						SUM(TBL2.totalPasses) AS totalPasses,	
						SUM(TBL2.refundAmount) refundAmount,			
						SUM(TBL2.totalPassesRef) totalPassesRef,	
						COUNT(TBL2.totalBranch) AS totalBranch
					from 
					(
					select 
						TBL1.REDEEM_ID,
						TBL1.RECON_ID,
						TBL1.MID,
						TBL1.CPID,
						TBL1.LegalName,
						TBL1.SPECIFIC_DATE,
						TBL1.SPECIFIC_DAY,
						TBL1.TYPE,
						SUM(TBL1.totalAmount) totalAmount,		
						COUNT(TBL1.totalPasses) totalPasses,	
						SUM(TBL1.refundAmount) refundAmount,
						SUM(TBL1.totalPassesRef) totalPassesRef,		
						TBL1.totalBranch totalBranch,		
						TBL1.PayeeDayType,		
						TBL1.PayeeQtyOfDays
					from
					(
					select 
						recon.REDEEM_ID,
						recon.RECON_ID,
						recon.MERCHANT_ID MID,
						mer.CP_ID CPID,
						mer.LegalName LegalName,
						SUM(recon.TRANSACTION_VALUE) totalAmount,	
						recon.REDEEM_ID totalPasses,		
						SUM(CASE WHEN recon.REFUND_ID = ref.REFUND_ID && ref.PA_ID = 0 && ref.REDEEM_TBL_ID = 0 && DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."' THEN  recon.TRANSACTION_VALUE ELSE 0 END) refundAmount,
						CASE WHEN recon.REFUND_ID = ref.REFUND_ID && ref.PA_ID = 0 && ref.REDEEM_TBL_ID = 0 && DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <=  '".$dateWhere."' THEN 1 ELSE 0 END totalPassesRef,	
						recon.BRANCH_ID totalBranch,
						pcf.SPECIFIC_DATE,
						pcf.SPECIFIC_DAY,
						pcf.type TYPE,
						mer.PayeeDayType,
						mer.PayeeQtyOfDays
					from		
						reconcilation recon	
						INNER JOIN branch_merchant brm ON brm.MERCHANT_ID = recon.MERCHANT_ID	
						INNER JOIN branches br ON br.MERCHANT_ID = brm.MERCHANT_ID
						INNER JOIN payment_cutoff pcf ON pcf.MERCHANT_ID = recon.MERCHANT_ID
						INNER JOIN cp_merchant mer ON mer.CP_ID = br.CP_ID
						LEFT JOIN refund ref on recon.REFUND_ID = ref.REFUND_ID
					where
						recon.REDEEM_TBL_ID <> 0
						and recon.branch_id =  brm.branch_id
						and br.branch_id = brm.branch_id
						and br.BRANCH_NAME <> ''
						and br.CP_ID <> 0
						and recon.RECON_ID <> ''
						and recon.PROD_ID <> 0
						and recon.PA_ID = 0
						and recon.STAGE = 'RECONCILED'
						and pcf.DigitalSettlementType = ''
						".$where."			
						group by recon.REDEEM_ID, recon.BRANCH_ID, recon.MERCHANT_ID 
						ORDER BY recon.TRANSACTION_DATE_TIME ".$orderName."
					) TBL1
					group by TBL1.totalBranch, TBL1.MID 
					) TBL2
					group by TBL2.MID
					".$limit."
				)
				UNION
				( SELECT
					DISTINCT tbl_refund.MERCHANT_ID AS MID,
					tbl_refund.CP_ID AS CPID,
					tbl_refund.LegalName AS LegalName,
					tbl_refund.SPECIFIC_DATE AS SPECIFIC_DATE,
					tbl_refund.SPECIFIC_DAY AS SPECIFIC_DAY,
					tbl_refund.TYPE AS TYPE,
					tbl_refund.PayeeDayType AS PayeeDayType,
					tbl_refund.PayeeQtyOfDays AS PayeeQtyOfDays,
					SUM(tbl_refund.totalAmount) AS totalAmount,	
					SUM(tbl_refund.totalPasses) AS totalPasses,	
					SUM(tbl_refund.refundAmount) refundAmount,			
					SUM(tbl_refund.totalPassesRef) totalPassesRef,		
					count(tbl_refund.BRANCH_ID) AS totalBranch
				FROM
					(select 
							recon.REDEEM_ID,
							recon.RECON_ID,
							recon.MERCHANT_ID,
							mer.CP_ID,
							mer.LegalName,
							pcf.SPECIFIC_DATE,
							pcf.SPECIFIC_DAY,
							pcf.TYPE,
							mer.PayeeDayType,
							mer.PayeeQtyOfDays,
							0 AS totalAmount,	
							0 AS totalPasses,	
							SUM(recon.TRANSACTION_VALUE) refundAmount,			
							COUNT(recon.REDEEM_ID) totalPassesRef,		
							br.BRANCH_ID
					from		
						refund ref 
						inner join reconcilation recon on recon.REFUND_ID = ref.REFUND_ID	
						INNER JOIN branch_merchant brm ON brm.MERCHANT_ID = ref.MERCHANT_ID	
						inner join branches br on br.MERCHANT_ID = brm.MERCHANT_ID
						inner join payment_cutoff pcf on pcf.MERCHANT_ID = ref.MERCHANT_ID	
						inner join cp_merchant mer on mer.CP_ID = br.CP_ID
					where
						recon.ID = ref.RECON_TBL_ID
						and ref.branch_id =  brm.branch_id
						and br.branch_id = brm.branch_id
						and br.BRANCH_NAME <> ''
						and br.CP_ID <> 0
						and ref.PA_ID = 0
						and recon.PROD_ID = ref.PROD_ID
						and recon.PROD_ID <> 0
						and recon.STAGE = 'REVERSED'
						and recon.REDEEM_TBL_ID <> 0
						and ref.REDEEM_TBL_ID = 0
						and pcf.DigitalSettlementType = ''
						and DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."'
						".$pcfWHere."	
					group by recon.RECON_ID, recon.BRANCH_ID, recon.MERCHANT_ID
					ORDER BY recon.TRANSACTION_DATE_TIME  asc) tbl_refund
				group by tbl_refund.MERCHANT_ID 
				)
			) combine_tbl
			group by combine_tbl.MID");
		if( $count == true) return $result->num_rows();
		else return $result->result();
	}


	public function getPCFWRecon_Branch($where = '', $count = false, $page = ''){
		$limit = '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		$result = $this->db->query("
			select 
					TBL1.REDEEM_ID,
					TBL1.RECON_ID,
					TBL1.MID,
					TBL1.CPID,
					TBL1.LegalName,
					TBL1.SPECIFIC_DATE,
					TBL1.SPECIFIC_DAY,
					TBL1.type,
					SUM(TBL1.totalAmount) totalAmount,			
					COUNT(TBL1.totalPasses) totalPasses,		
					TBL1.BRANCH_ID,		
					TBL1.BRANCH_NAME,
					TBL1.MerchantFee,
					TBL1.vatcond,
					TBL1.PayeeDayType,
					TBL1.PayeeQtyOfDays
				from
				(
				select 
					recon.REDEEM_ID,
					recon.RECON_ID,
					recon.MERCHANT_ID MID,
					mer.CP_ID CPID,
					mer.LegalName LegalName,
					mer.MerchantFee MerchantFee,
					mer.vatcond,
					mer.PayeeDayType,
					mer.PayeeQtyOfDays,
					SUM(recon.TRANSACTION_VALUE) totalAmount,	
					COUNT(recon.ID) totalPasses,		
					br.BRANCH_ID BRANCH_ID,	
					br.BRANCH_NAME BRANCH_NAME,
					pcf.SPECIFIC_DATE,
					pcf.SPECIFIC_DAY,
					pcf.type
				from		
					reconcilation recon	
					INNER JOIN branch_merchant brm ON brm.MERCHANT_ID = recon.MERCHANT_ID	
					INNER JOIN branches br ON br.MERCHANT_ID = brm.MERCHANT_ID
					INNER JOIN payment_cutoff pcf ON pcf.MERCHANT_ID = recon.MERCHANT_ID
					INNER JOIN cp_merchant mer ON mer.CP_ID = br.CP_ID
				where
					recon.REDEEM_TBL_ID <> 0
					and recon.branch_id =  brm.branch_id
					and br.branch_id = brm.branch_id
					and br.BRANCH_NAME <> ''
					and br.CP_ID <> 0
					and recon.RECON_ID <> ''
					and recon.PROD_ID <> 0
					and recon.PA_ID = 0
					and recon.STAGE = 'RECONCILED'
					and pcf.DigitalSettlementType = ''
					".$where."	
					group by recon.REDEEM_ID, recon.BRANCH_ID, recon.MERCHANT_ID
					ORDER BY recon.TRANSACTION_DATE_TIME  ".$orderName."
				) TBL1
			group by TBL1.BRANCH_ID, TBL1.MID
			".$limit." 
			");
		if( $count == true) return $result->num_rows();
		else return $result;
	}	


	public function getPCFWRecon_ProcessPA($where = '', $count = false, $page = '', $dateWhere = '', $whereMerchant = ''){
		$limit = '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		
		/*		
		SUM(recon.TRANSACTION_VALUE) AS totalAmount,	
		COUNT(recon.REDEEM_ID) AS totalPasses,	
		*/
		$result = $this->db->query("(select 
				recon.RECON_ID AS RECON_ID,
				recon.MERCHANT_ID AS MID,
				mer.CP_ID AS CPID,
				mer.LegalName AS LegalName,
				mer.MerchantFee AS MerchantFee,
				mer.vatcond AS vatcond,
				mer.PayeeDayType AS PayeeDayType,
				mer.PayeeQtyOfDays as PayeeQtyOfDays,
				mer.AffiliateGroupCode merAFFCODE,
				SUM(recon.TRANSACTION_VALUE) totalAmount,		
				COUNT(recon.ID) totalPasses,		
				br.BRANCH_ID BRANCH_ID,	
				br.BRANCH_NAME BRANCH_NAME,
				CASE 
				  WHEN br.AFFILIATEGROUPCODE IS NULL or br.AFFILIATEGROUPCODE = ''  THEN mer.AffiliateGroupCode
				  ELSE br.AFFILIATEGROUPCODE
				END AS brAFFCODE,
				pcf.SPECIFIC_DATE as SPECIFIC_DATE,
				pcf.SPECIFIC_DAY as SPECIFIC_DAY,
				pcf.type,
				SUM(CASE WHEN recon.REFUND_ID = ref.REFUND_ID && ref.PA_ID = 0 && ref.REDEEM_TBL_ID = 0 && DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."' THEN  recon.TRANSACTION_VALUE ELSE 0 END) refundPostAmount,
				recon.REFUND_ID
			from		
				reconcilation recon		
				INNER JOIN branch_merchant brm ON brm.MERCHANT_ID = recon.MERCHANT_ID	
				INNER JOIN branches br ON br.MERCHANT_ID = brm.MERCHANT_ID
				INNER JOIN payment_cutoff pcf ON pcf.MERCHANT_ID = recon.MERCHANT_ID
				INNER JOIN cp_merchant mer ON mer.CP_ID = br.CP_ID
				LEFT JOIN refund ref on recon.REFUND_ID = ref.REFUND_ID
			where
				recon.REDEEM_TBL_ID <> 0
				and recon.branch_id =  brm.branch_id
				and br.branch_id = brm.branch_id
				and br.BRANCH_NAME <> ''
				and br.CP_ID <> 0
				and recon.RECON_ID <> ''
				and recon.PROD_ID <> 0
				and recon.PA_ID = 0
				and recon.STAGE = 'RECONCILED'
				and pcf.DigitalSettlementType = ''
				".$where."	
			group by recon.RECON_ID, recon.BRANCH_ID, recon.MERCHANT_ID
			ORDER BY recon.TRANSACTION_DATE_TIME  ".$orderName."
			".$limit.")
			union
			(select 
				recon.RECON_ID AS RECON_ID,
				recon.MERCHANT_ID AS MID,
				mer.CP_ID AS CPID,
				mer.LegalName AS LegalName,
				mer.MerchantFee AS MerchantFee,
				mer.vatcond AS vatcond,
				mer.PayeeDayType AS PayeeDayType,
				mer.PayeeQtyOfDays as PayeeQtyOfDays,
				mer.AffiliateGroupCode merAFFCODE,
				0 totalAmount,		
				0 totalPasses,
				br.BRANCH_ID BRANCH_ID,	
				br.BRANCH_NAME BRANCH_NAME,
				CASE 
				  WHEN br.AFFILIATEGROUPCODE IS NULL or br.AFFILIATEGROUPCODE = ''  THEN mer.AffiliateGroupCode
				  ELSE br.AFFILIATEGROUPCODE
				END AS brAFFCODE,
				pcf.SPECIFIC_DATE as SPECIFIC_DATE,
				pcf.SPECIFIC_DAY as SPECIFIC_DAY,
				pcf.type,
				SUM(recon.TRANSACTION_VALUE) as refundPostAmount,
				recon.REFUND_ID
			from		
				refund ref 
				inner join reconcilation recon on recon.REFUND_ID = ref.REFUND_ID	
				INNER JOIN branch_merchant brm ON brm.MERCHANT_ID = ref.MERCHANT_ID	
				INNER JOIN branches br ON br.MERCHANT_ID = brm.MERCHANT_ID
				inner join payment_cutoff pcf on pcf.MERCHANT_ID = ref.MERCHANT_ID	
				inner join cp_merchant mer on mer.CP_ID = br.CP_ID
			where
				recon.ID = ref.RECON_TBL_ID
				and ref.branch_id =  brm.branch_id
				and br.branch_id = brm.branch_id
				and br.BRANCH_NAME <> ''
				and br.CP_ID <> 0
				and ref.PA_ID = 0
				and recon.PROD_ID = ref.PROD_ID
				and recon.PROD_ID <> 0
				and recon.STAGE = 'REVERSED'
				and recon.REDEEM_TBL_ID <> 0
				and ref.REDEEM_TBL_ID = 0
				and pcf.DigitalSettlementType = ''
				and DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."'				 
				".$whereMerchant."	
			group by recon.RECON_ID, recon.BRANCH_ID, recon.MERCHANT_ID
			ORDER BY recon.TRANSACTION_DATE_TIME  asc)
			");
		if( $count == true) return $result->num_rows();
		else return $result;
	}	

	public function uWRecon_ReconPA($where, $PA_ID){
		if(empty($where) || empty($PA_ID)) return ''; 
		$where = (empty($where) ? '' : ' AND ').$where;	
		
		$result = $this->db->query("
			UPDATE reconcilation recon		
			INNER JOIN redemption redeem ON redeem.ID = recon.REDEEM_TBL_ID
			INNER JOIN branches br ON br.MERCHANT_ID = recon.MERCHANT_ID
			INNER JOIN payment_cutoff pcf ON pcf.MERCHANT_ID = br.MERCHANT_ID
			INNER JOIN cp_merchant mer ON mer.CP_ID = br.CP_ID
			SET 
				recon.PA_ID = ".$PA_ID.", 
				redeem.PA_ID = ".$PA_ID."
			where
				recon.REDEEM_TBL_ID <> 0
				and br.BRANCH_ID = recon.BRANCH_ID 
				and pcf.MERCHANT_ID = recon.MERCHANT_ID
				and pcf.DigitalSettlementType = ''	
				and br.CP_ID <> 0
				and recon.STAGE = 'RECONCILED'	
				and recon.RECON_ID <> ''
				and recon.PROD_ID <> 0
				and recon.PA_ID = 0
				and redeem.PA_ID = 0
			".$where."");
		return $result;
	}

	public function uWRecon_RefundPA($where, $PA_ID){
		if(empty($where) || empty($PA_ID)) return ''; 
		$where = (empty($where) ? '' : ' AND ').$where;	
		
		$result = $this->db->query("
			UPDATE refund ref 
				INNER JOIN reconcilation recon on recon.REFUND_ID = ref.REFUND_ID	
				INNER JOIN branches br on br.MERCHANT_ID = recon.MERCHANT_ID
				INNER JOIN payment_cutoff pcf on pcf.MERCHANT_ID = br.MERCHANT_ID	
				INNER JOIN cp_merchant mer on mer.CP_ID = br.CP_ID
			SET ref.PA_ID = ".$PA_ID."				
			where
				recon.ID = ref.RECON_TBL_ID
				and br.BRANCH_ID = recon.BRANCH_ID 
				and br.CP_ID <> 0
				and recon.PROD_ID = ref.PROD_ID
				and recon.STAGE = 'REVERSED'
				and recon.REDEEM_TBL_ID <> 0
				and recon.PROD_ID <> 0
				and ref.REDEEM_TBL_ID = 0
				and ref.PA_ID = 0
				and pcf.DigitalSettlementType = ''
			".$where."");
		return $result;
	}
	

	/**
	 * MODEL FOR MERCHANT NO RECON
	 */
	
	public function getPCFNRecon($where = '', $count = false, $page = '', $dateWhere = '', $pcfWHere = ''){
		$limit = '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		$pcfWHere = (empty($pcfWHere) ? '' : ' AND ').$pcfWHere;	//paymentcutoff 
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		$result = $this->db->query("
			select 
				combine_tbl.MID,
				combine_tbl.CPID,
				combine_tbl.LegalName,
				combine_tbl.SPECIFIC_DATE,
				combine_tbl.SPECIFIC_DAY,
				combine_tbl.TYPE,
				combine_tbl.PayeeDayType,
				combine_tbl.PayeeQtyOfDays,
				SUM(combine_tbl.totalAmount) AS totalAmount,	
				SUM(combine_tbl.totalPasses) AS totalPasses,	
				SUM(combine_tbl.refundAmount) refundAmount,			
				SUM(combine_tbl.totalPassesRef) totalPassesRef,	
				SUM(combine_tbl.totalBranch) AS totalBranch
			from
			(
				(
					select 
						TBL2.MID AS MID,
						TBL2.CPID AS CPID,
						TBL2.LegalName AS LegalName,
						TBL2.SPECIFIC_DATE AS SPECIFIC_DATE,
						TBL2.SPECIFIC_DAY AS SPECIFIC_DAY,
						TBL2.TYPE AS TYPE,
						TBL2.PayeeDayType AS PayeeDayType,
						TBL2.PayeeQtyOfDays AS PayeeQtyOfDays,
						SUM(TBL2.totalAmount) AS totalAmount,	
						SUM(TBL2.totalPasses) AS totalPasses,	
						SUM(TBL2.refundAmount) refundAmount,			
						SUM(TBL2.totalPassesRef) totalPassesRef,	
						COUNT(TBL2.totalBranch) AS totalBranch
					from 
					(
					select 
						redeem.BRANCH_ID totalBranch,
						redeem.MERCHANT_ID MID,
						mer.CP_ID CPID,
						mer.LegalName LegalName,
						SUM(redeem.TRANSACTION_VALUE) totalAmount,	
						count(redeem.REDEEM_ID) totalPasses,		
						SUM(CASE WHEN redeem.REFUND_ID = ref.REFUND_ID && ref.PA_ID = 0 && ref.RECON_TBL_ID = 0 && DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."' THEN  redeem.TRANSACTION_VALUE ELSE 0 END) refundAmount,
						CASE WHEN redeem.REFUND_ID = ref.REFUND_ID && ref.PA_ID = 0 && ref.RECON_TBL_ID = 0 && DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <=  '".$dateWhere."' THEN 1 ELSE 0 END totalPassesRef,	
						pcf.SPECIFIC_DATE,
						pcf.SPECIFIC_DAY,
						pcf.type TYPE,
						mer.PayeeDayType,
						mer.PayeeQtyOfDays
					from		
						redemption redeem		
						INNER JOIN branch_merchant brm ON brm.MERCHANT_ID = redeem.MERCHANT_ID	
						INNER JOIN branches br ON br.MERCHANT_ID = brm.MERCHANT_ID
						INNER JOIN payment_cutoff pcf ON pcf.MERCHANT_ID = redeem.MERCHANT_ID
						INNER JOIN cp_merchant mer ON mer.CP_ID = br.CP_ID
						LEFT JOIN refund ref on redeem.REFUND_ID = ref.REFUND_ID
					where
						redeem.branch_id =  brm.branch_id
						and br.branch_id = brm.branch_id
						and br.BRANCH_NAME <> ''
						and br.CP_ID <> 0
						and redeem.PA_ID = 0
						and redeem.PROD_ID <> 0
						and redeem.STAGE in ('RECONCILED', 'REDEEMED')
						and pcf.DigitalSettlementType <> ''
						".$where."			
						group by redeem.BRANCH_ID, redeem.MERCHANT_ID 
						ORDER BY redeem.TRANSACTION_DATE_TIME ".$orderName."
					) TBL2
					group by TBL2.MID
					".$limit."
				)
				UNION
				( SELECT
					DISTINCT tbl_refund.MERCHANT_ID AS MID,
					tbl_refund.CP_ID AS CPID,
					tbl_refund.LegalName AS LegalName,
					tbl_refund.SPECIFIC_DATE AS SPECIFIC_DATE,
					tbl_refund.SPECIFIC_DAY AS SPECIFIC_DAY,
					tbl_refund.TYPE AS TYPE,
					tbl_refund.PayeeDayType AS PayeeDayType,
					tbl_refund.PayeeQtyOfDays AS PayeeQtyOfDays,
					SUM(tbl_refund.totalAmount) AS totalAmount,	
					SUM(tbl_refund.totalPasses) AS totalPasses,	
					SUM(tbl_refund.refundAmount) refundAmount,			
					SUM(tbl_refund.totalPassesRef) totalPassesRef,		
					count(tbl_refund.BRANCH_ID) AS totalBranch
				FROM
					(select 
							redeem.BRANCH_ID,
							redeem.MERCHANT_ID,
							mer.CP_ID,
							mer.LegalName,
							pcf.SPECIFIC_DATE,
							pcf.SPECIFIC_DAY,
							pcf.TYPE,
							mer.PayeeDayType,
							mer.PayeeQtyOfDays,
							0 AS totalAmount,	
							0 AS totalPasses,	
							SUM(redeem.TRANSACTION_VALUE) refundAmount,			
							COUNT(redeem.REDEEM_ID) totalPassesRef
					from		
						refund ref 
						inner join redemption redeem on redeem.REFUND_ID = ref.REFUND_ID	
						INNER JOIN branch_merchant brm ON brm.MERCHANT_ID = ref.MERCHANT_ID	
						INNER JOIN branches br ON br.MERCHANT_ID = brm.MERCHANT_ID
						inner join payment_cutoff pcf on pcf.MERCHANT_ID = br.MERCHANT_ID	
						inner join cp_merchant mer on mer.CP_ID = br.CP_ID
					where
						redeem.ID = ref.REDEEM_TBL_ID
						and ref.branch_id =  brm.branch_id
						and br.branch_id = brm.branch_id
						and br.BRANCH_NAME <> ''
						and br.CP_ID <> 0
						and ref.PA_ID = 0
						and redeem.PROD_ID = ref.PROD_ID
						and redeem.PROD_ID <> 0
						and redeem.STAGE in ('REVERSED', 'VOID') 
						and ref.RECON_TBL_ID = 0
						and pcf.DigitalSettlementType <> ''
						and DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."'
						".$pcfWHere."	
					group by redeem.BRANCH_ID, redeem.MERCHANT_ID
					ORDER BY redeem.TRANSACTION_DATE_TIME  asc) tbl_refund
				group by tbl_refund.MERCHANT_ID 
				)
			) combine_tbl
			group by combine_tbl.MID");
		if( $count == true) return $result->num_rows();
		else return $result->result();
	}

	public function getPCFNRecon_Branch($where = '', $count = false, $page = ''){
		$limit = '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		$result = $this->db->query("
			select 
					TBL1.REDEEM_ID,
					TBL1.MID,
					TBL1.CPID,
					TBL1.LegalName,
					TBL1.SPECIFIC_DATE,
					TBL1.SPECIFIC_DAY,
					TBL1.type,
					SUM(TBL1.totalAmount) totalAmount,			
					COUNT(TBL1.totalPasses) totalPasses,		
					TBL1.BRANCH_ID,		
					TBL1.BRANCH_NAME,
					TBL1.MerchantFee,
					TBL1.vatcond,
					TBL1.PayeeDayType,
					TBL1.PayeeQtyOfDays
				from
				(
				select 
					redeem.REDEEM_ID,
					redeem.MERCHANT_ID MID,
					mer.CP_ID CPID,
					mer.LegalName LegalName,
					mer.MerchantFee MerchantFee,
					mer.vatcond,
					mer.PayeeDayType,
					mer.PayeeQtyOfDays,
					SUM(redeem.TRANSACTION_VALUE) totalAmount,	
					COUNT(redeem.ID) totalPasses,		
					br.BRANCH_ID BRANCH_ID,	
					br.BRANCH_NAME BRANCH_NAME,
					pcf.SPECIFIC_DATE,
					pcf.SPECIFIC_DAY,
					pcf.type
				from		
					redemption redeem	
					INNER JOIN branch_merchant brm ON brm.MERCHANT_ID = redeem.MERCHANT_ID	
					INNER JOIN branches br ON br.MERCHANT_ID = redeem.MERCHANT_ID
					INNER JOIN payment_cutoff pcf ON pcf.MERCHANT_ID = redeem.MERCHANT_ID
					INNER JOIN cp_merchant mer ON mer.CP_ID = br.CP_ID
				where					
					redeem.branch_id =  brm.branch_id
					and br.branch_id = brm.branch_id
					and br.BRANCH_NAME <> ''
					and br.CP_ID <> 0
					and redeem.PA_ID = 0
					and redeem.PROD_ID <> 0
					and redeem.STAGE in ('RECONCILED', 'REDEEMED')
					and pcf.DigitalSettlementType <> ''
					".$where."	
					group by redeem.REDEEM_ID, redeem.BRANCH_ID, redeem.MERCHANT_ID
					ORDER BY redeem.TRANSACTION_DATE_TIME  ".$orderName."
				) TBL1
			group by TBL1.BRANCH_ID, TBL1.MID
			".$limit." 
			");
		if( $count == true) return $result->num_rows();
		else return $result;
	}	

	public function getPCFNRecon_ProcessPA($where = '', $count = false, $page = '', $dateWhere = '', $whereMerchant = ''){
		$limit = '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		
		/*		
		SUM(recon.TRANSACTION_VALUE) AS totalAmount,	
		COUNT(recon.REDEEM_ID) AS totalPasses,	
		*/
		$result = $this->db->query("(select 
				redeem.BRANCH_ID BRANCH_ID,
				redeem.MERCHANT_ID AS MID,
				mer.CP_ID AS CPID,
				mer.LegalName AS LegalName,
				mer.MerchantFee AS MerchantFee,
				mer.vatcond AS vatcond,
				mer.PayeeDayType AS PayeeDayType,
				mer.PayeeQtyOfDays as PayeeQtyOfDays,
				mer.AffiliateGroupCode merAFFCODE,
				SUM(redeem.TRANSACTION_VALUE) totalAmount,		
				COUNT(redeem.ID) totalPasses,		
				br.BRANCH_NAME BRANCH_NAME,
				CASE 
				  WHEN br.AFFILIATEGROUPCODE IS NULL or br.AFFILIATEGROUPCODE = ''  THEN mer.AffiliateGroupCode
				  ELSE br.AFFILIATEGROUPCODE
				END AS brAFFCODE,
				pcf.SPECIFIC_DATE as SPECIFIC_DATE,
				pcf.SPECIFIC_DAY as SPECIFIC_DAY,
				pcf.type,
				SUM(CASE WHEN redeem.REFUND_ID = ref.REFUND_ID && ref.PA_ID = 0 && ref.RECON_TBL_ID = 0 && DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."' THEN  redeem.TRANSACTION_VALUE ELSE 0 END) refundPostAmount,
				redeem.REFUND_ID
			from		
				redemption redeem		
				INNER JOIN branch_merchant brm ON brm.MERCHANT_ID = redeem.MERCHANT_ID	
				INNER JOIN branches br ON br.MERCHANT_ID = brm.MERCHANT_ID
				INNER JOIN payment_cutoff pcf ON pcf.MERCHANT_ID = redeem.MERCHANT_ID
				INNER JOIN cp_merchant mer ON mer.CP_ID = br.CP_ID
				LEFT JOIN refund ref on redeem.REFUND_ID = ref.REFUND_ID
			where					
				redeem.branch_id =  brm.branch_id
				and br.branch_id = brm.branch_id
				and br.BRANCH_NAME <> ''
				and br.CP_ID <> 0
				and redeem.PA_ID = 0
				and redeem.PROD_ID <> 0
				and redeem.STAGE in ('RECONCILED', 'REDEEMED')
				and pcf.DigitalSettlementType <> ''
				".$where."	
			group by redeem.BRANCH_ID, redeem.MERCHANT_ID
			ORDER BY redeem.TRANSACTION_DATE_TIME  ".$orderName."
			".$limit.")
			union
			(select 
				redeem.BRANCH_ID BRANCH_ID,	
				redeem.MERCHANT_ID AS MID,
				mer.CP_ID AS CPID,
				mer.LegalName AS LegalName,
				mer.MerchantFee AS MerchantFee,
				mer.vatcond AS vatcond,
				mer.PayeeDayType AS PayeeDayType,
				mer.PayeeQtyOfDays as PayeeQtyOfDays,
				mer.AffiliateGroupCode merAFFCODE,
				0 totalAmount,		
				0 totalPasses,
				br.BRANCH_NAME BRANCH_NAME,
				CASE 
				  WHEN br.AFFILIATEGROUPCODE IS NULL or br.AFFILIATEGROUPCODE = ''  THEN mer.AffiliateGroupCode
				  ELSE br.AFFILIATEGROUPCODE
				END AS brAFFCODE,
				pcf.SPECIFIC_DATE as SPECIFIC_DATE,
				pcf.SPECIFIC_DAY as SPECIFIC_DAY,
				pcf.type,
				SUM(redeem.TRANSACTION_VALUE) as refundPostAmount,
				redeem.REFUND_ID
			from		
				refund ref 
				inner join redemption redeem on redeem.REFUND_ID = ref.REFUND_ID	
				INNER JOIN branch_merchant brm ON brm.MERCHANT_ID = ref.MERCHANT_ID	
				INNER JOIN branches br ON br.MERCHANT_ID = brm.MERCHANT_ID
				inner join payment_cutoff pcf on pcf.MERCHANT_ID = ref.MERCHANT_ID	
				inner join cp_merchant mer on mer.CP_ID = br.CP_ID
			where
				redeem.ID = ref.REDEEM_TBL_ID
				and ref.branch_id =  brm.branch_id
				and br.branch_id = brm.branch_id
				and ref.PA_ID = 0
				and redeem.PROD_ID = ref.PROD_ID
				and redeem.PROD_ID <> 0
				and redeem.STAGE in ('REVERSED', 'VOID') 
				and ref.RECON_TBL_ID = 0
				and pcf.DigitalSettlementType <> ''
				and DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."'				 
				".$whereMerchant."	
			group by redeem.BRANCH_ID, redeem.MERCHANT_ID
			ORDER BY redeem.TRANSACTION_DATE_TIME  asc)
			");
		if( $count == true) return $result->num_rows();
		else return $result;
	}	

	public function getPCFNRecon_PADetail($PA_ID, $count = false){
		if(empty($PA_ID)) return false;

		$result = $this->db->query("(select 
				redeem.BRANCH_ID BRANCH_ID,
				redeem.MERCHANT_ID AS MID,
				mer.CP_ID AS CPID,
				mer.LegalName AS LegalName,
				mer.MerchantFee AS MerchantFee,
				mer.vatcond AS vatcond,
				mer.PayeeDayType AS PayeeDayType,
				mer.PayeeQtyOfDays as PayeeQtyOfDays,
				mer.AffiliateGroupCode merAFFCODE,
				SUM(redeem.TRANSACTION_VALUE) totalAmount,		
				COUNT(redeem.ID) totalPasses,		
				br.BRANCH_NAME BRANCH_NAME,
				CASE 
				  WHEN br.AFFILIATEGROUPCODE IS NULL or br.AFFILIATEGROUPCODE = ''  THEN mer.AffiliateGroupCode
				  ELSE br.AFFILIATEGROUPCODE
				END AS brAFFCODE,
				pcf.SPECIFIC_DATE as SPECIFIC_DATE,
				pcf.SPECIFIC_DAY as SPECIFIC_DAY,
				pcf.type,
				SUM(CASE WHEN redeem.REFUND_ID = ref.REFUND_ID && ref.PA_ID = ".$PA_ID." && ref.RECON_TBL_ID = 0 THEN  redeem.TRANSACTION_VALUE ELSE 0 END) refundPostAmount,
				redeem.REFUND_ID
			from		
				redemption redeem		
				INNER JOIN branch_merchant brm ON brm.MERCHANT_ID = redeem.MERCHANT_ID	
				INNER JOIN branches br ON br.MERCHANT_ID = brm.MERCHANT_ID
				INNER JOIN payment_cutoff pcf ON pcf.MERCHANT_ID = redeem.MERCHANT_ID
				INNER JOIN cp_merchant mer ON mer.CP_ID = br.CP_ID
				LEFT JOIN refund ref on redeem.REFUND_ID = ref.REFUND_ID
			where					
				redeem.branch_id =  brm.branch_id
				and br.branch_id = brm.branch_id
				and br.BRANCH_NAME <> ''
				and br.CP_ID <> 0
				and redeem.PA_ID = ".$PA_ID."
				and redeem.PROD_ID <> 0
				and redeem.STAGE in ('RECONCILED', 'REDEEMED')
				and pcf.DigitalSettlementType <> ''
			group by redeem.BRANCH_ID, redeem.MERCHANT_ID
			ORDER BY redeem.TRANSACTION_DATE_TIME  ASC)
			union
			(select 
				redeem.BRANCH_ID BRANCH_ID,	
				redeem.MERCHANT_ID AS MID,
				mer.CP_ID AS CPID,
				mer.LegalName AS LegalName,
				mer.MerchantFee AS MerchantFee,
				mer.vatcond AS vatcond,
				mer.PayeeDayType AS PayeeDayType,
				mer.PayeeQtyOfDays as PayeeQtyOfDays,
				mer.AffiliateGroupCode merAFFCODE,
				0 totalAmount,		
				0 totalPasses,
				br.BRANCH_NAME BRANCH_NAME,
				CASE 
				  WHEN br.AFFILIATEGROUPCODE IS NULL or br.AFFILIATEGROUPCODE = ''  THEN mer.AffiliateGroupCode
				  ELSE br.AFFILIATEGROUPCODE
				END AS brAFFCODE,
				pcf.SPECIFIC_DATE as SPECIFIC_DATE,
				pcf.SPECIFIC_DAY as SPECIFIC_DAY,
				pcf.type,
				SUM(redeem.TRANSACTION_VALUE) as refundPostAmount,
				redeem.REFUND_ID
			from		
				refund ref 
				inner join redemption redeem on redeem.REFUND_ID = ref.REFUND_ID	
				INNER JOIN branch_merchant brm ON brm.MERCHANT_ID = ref.MERCHANT_ID	
				INNER JOIN branches br ON br.MERCHANT_ID = brm.MERCHANT_ID
				inner join payment_cutoff pcf on pcf.MERCHANT_ID = ref.MERCHANT_ID	
				inner join cp_merchant mer on mer.CP_ID = br.CP_ID
			where
				redeem.ID = ref.REDEEM_TBL_ID
				and ref.branch_id =  brm.branch_id
				and br.branch_id = brm.branch_id
				and redeem.PROD_ID = ref.PROD_ID
				and redeem.PROD_ID <> 0
				and redeem.STAGE in ('REVERSED', 'VOID') 
				and ref.RECON_TBL_ID = 0
				and pcf.DigitalSettlementType <> ''	
				and ref.PA_ID = ".$PA_ID."		 
			group by redeem.BRANCH_ID, redeem.MERCHANT_ID
			ORDER BY redeem.TRANSACTION_DATE_TIME  asc)
			");
		if( $count == true) return $result->num_rows();
		else return $result;
	}

	public function uNRecon_ReconPA($where, $PA_ID){
		if(empty($where) || empty($PA_ID)) return ''; 
		$where = (empty($where) ? '' : ' AND ').$where;	
		
		$result = $this->db->query("
			UPDATE redemption redeem		
				INNER JOIN branches br ON br.MERCHANT_ID = redeem.MERCHANT_ID
				INNER JOIN payment_cutoff pcf ON pcf.MERCHANT_ID = br.MERCHANT_ID
				INNER JOIN cp_merchant mer ON mer.CP_ID = br.CP_ID
			SET redeem.PA_ID = ".$PA_ID."
			WHERE
				br.BRANCH_ID = redeem.BRANCH_ID 
				and br.CP_ID <> 0
				and pcf.MERCHANT_ID = redeem.MERCHANT_ID
				and redeem.PA_ID = 0
				and redeem.PROD_ID <> 0
				and redeem.STAGE in ('RECONCILED', 'REDEEMED')
				and pcf.DigitalSettlementType <> ''	
			".$where."");
		return $result;
	}

	public function uNRecon_RefundPA($where, $PA_ID){
		if(empty($where) || empty($PA_ID)) return ''; 
		$where = (empty($where) ? '' : ' AND ').$where;	
		//EARP JULY 23 2023 ADDED CONDITION on WHERE PARAMETERS
		// and redeem.PA_ID != 0
		$result = $this->db->query("
			UPDATE refund ref 
				INNER JOIN redemption redeem on redeem.REFUND_ID = ref.REFUND_ID	
				INNER JOIN branches br on br.MERCHANT_ID = redeem.MERCHANT_ID
				INNER JOIN payment_cutoff pcf on pcf.MERCHANT_ID = br.MERCHANT_ID	
				INNER JOIN cp_merchant mer on mer.CP_ID = br.CP_ID
			SET ref.PA_ID = ".$PA_ID."				
			WHERE
				redeem.ID = ref.REDEEM_TBL_ID
				and br.BRANCH_ID = redeem.BRANCH_ID 
				and br.CP_ID <> 0
				and ref.PA_ID = 0
				and redeem.PA_ID != 0
				and redeem.PROD_ID = ref.PROD_ID
				and redeem.PROD_ID <> 0
				and redeem.STAGE in ('REVERSED', 'VOID') 
				and ref.RECON_TBL_ID = 0
				and pcf.DigitalSettlementType <> ''
			".$where."");
		return $result;
	}

	/**
	 * REVERSAL TABLE - Reassigning IDs
	 */
	public function refund_ReconTBL(){
		/**
		 * Update Refund table that is already existing in RECON Table
		 */		
		$result = $this->db->query("
			UPDATE refund ref 
			INNER JOIN reconcilation recon on recon.REFUND_ID = ref.REFUND_ID	
			INNER JOIN payment_cutoff pcf on pcf.MERCHANT_ID = ref.MERCHANT_ID	
		SET ref.RECON_TBL_ID  = recon.ID		
		WHERE
			ref.RECON_TBL_ID = 0
			and ref.REDEEM_TBL_ID = 0			
			and recon.ID not in (select RECON_TBL_ID from (SELECT RECON_TBL_ID 
									FROM   refund 
									WHERE  RECON_TBL_ID <> 0
									group by RECON_TBL_ID) t )
			and (pcf.DigitalSettlementType = '' OR 
				(pcf.DigitalSettlementType <> '' and ref.PA_ID <> 0)
				)
			");
		return $result; 
		//and pcf.MERCHANT_ID in (47,49,90,73,70,31,93,54,35,30)
	}

	public function refund_RedeemTBL(){	
		/**
		 * Update Refund and Redeem Tbl 
		 ** VALIDATE **
		 * merchant_settlement = "NO RECON"
		 * REF.PA_ID  = 0
		 */
		/**
		OLD query
		
		UPDATE refund ref 
			INNER JOIN redemption redeem on redeem.REDEEM_ID = ref.REDEEM_ID	
			INNER JOIN payment_cutoff pcf on pcf.MERCHANT_ID = ref.MERCHANT_ID	
		SET 
			ref.REDEEM_TBL_ID  = redeem.ID,			
			redeem.REFUND_ID  = ref.REFUND_ID	
		WHERE			
			redeem.PROD_ID = ref.PROD_ID
			and redeem.MERCHANT_ID = ref.MERCHANT_ID
			and redeem.BRANCH_ID = ref.BRANCH_ID
			and redeem.TRANSACTION_ID = ref.TRANSACTION_ID
			and redeem.REFUND_ID = 0
			and redeem.PA_ID  = 0
			and ref.PA_ID  = 0
			and ref.RECON_TBL_ID = 0
			and ref.REDEEM_TBL_ID = 0					
			and pcf.DigitalSettlementType <> ''
		*/
		$result = $this->db->query("update refund
    inner join redemption on refund.REDEEM_ID = redemption.REDEEM_ID
    INNER JOIN payment_cutoff pcf on pcf.MERCHANT_ID = refund.MERCHANT_ID
set
    refund.REDEEM_TBL_ID = redemption.ID,
    redemption.REFUND_ID = refund.REFUND_ID
where
    REVERSAL_MODE = 'Support Center'
    and redemption.PROD_ID = refund.PROD_ID
    and redemption.PA_ID != 0
	and refund.PA_ID = 0 
    and (REDEEM_TBL_ID is null or REDEEM_TBL_ID = 0)
    and pcf.DigitalSettlementType <> ''
		");
		return $result;
		//and pcf.MERCHANT_ID in (47,49,90,73,70,31,93,54,35,30)
	}

} // END MODEL

