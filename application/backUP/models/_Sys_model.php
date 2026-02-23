<?php
class Sys_model extends CI_Model{	
	public function __construct(){
		parent::__construct();
	}

	/*
	** QUERY ACTION FOR TBL audit_upload
	*/
	public function i_auditUpload($module, $filename){
		$arr['module_name'] = $module;
		$arr['file_name'] = $filename;		
			$this->db->insert('audit_upload',$arr);
		return $this->db->insert_id();
	}
	
	
	/*
	** QUERY ACTION FOR TBL cp_merchant
	*/
	public function i_merchant($arr){
		if(empty($arr)) return false;
		if(!empty($arr['CP_ID'])){
			$this->db->query('INSERT INTO cp_merchant (
			CP_ID,
			TIN,
			LegalName,
			TradingName,
			GroupTIN,
			GroupName,
			Address,
			MeanofPayment,
			PayeeCode,
			BankName,
			BankAccountNumber,
			PayeeName,
			MerchantFee,
			Industry,
			VATCond,
			InsertType,
			BankBranchCode,
			PayeeQtyOfDays,
			PayeeDayType,
			PayeeComments,
			AffiliateGroupCode
			) VALUES (
			"'.$arr['CP_ID'].'"
			, "'.$arr['TIN'].'"
			, "'.$arr['LEGALNAME'].'"
			, "'.$arr['TRADINGNAME'].'"
			, "'.$arr['GROUPTIN'].'"
			, "'.$arr['GROUPNAME'].'"
			, "'.$arr['ADDRESS'].'"
			, "'.$arr['MEANOFPAYMENT'].'"
			, "'.$arr['PAYEECODE'].'"
			, "'.$arr['BANKNAME'].'"
			, "'.$arr['BANKACCOUNTNUMBER'].'"
			, "'.$arr['PAYEENAME'].'"
			, "'.$arr['MERCHANTFEE'].'"
			, "'.$arr['INDUSTRY'].'"
			, "'.$arr['VATCOND'].'"
			, "'.$arr['InsertType'].'"
			, "'.$arr['BankBranchCode'].'"
			, "'.$arr['PayeeQtyOfDays'].'"
			, "'.$arr['PayeeDayType'].'"
			, "'.$arr['PayeeComments'].'"
			, "'.$arr['AffiliateGroupCode'].'"
			)
			ON DUPLICATE KEY UPDATE CP_ID = VALUES(CP_ID)');
			return true;
		}
		return false;
	}
	public function u_merchant($where, $update){
		if(empty($where)) return '';
		$this->db->where($where);
		$this->db->update('cp_merchant',$update);
	}
	public function v_merchant($where = null, $count = false, $select = null){
		$this->db->from('cp_merchant');	
		if(!empty($where)) $this->db->where($where);	
		
		if(!empty($select)) $this->db->select($select);
		if($count == true){
			$this->db->select('id');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	
	/*
	** QUERY ACTION FOR TBL reconcilation
	*/
	public function v_checkRecon($where = null, $count = false, $select = null){
		$this->db->from('reconcilation');	
		if(!empty($where)) $this->db->where($where);	
		
		if($select <> null) $this->db->select($select);
		if($count == true){
			$this->db->select('id');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}	
	
	public function i_recon($arr){
		if(empty($arr)) return '';		
			$this->db->insert('reconcilation',$arr);
		return $this->db->insert_id();
	}
	public function u_recon($where, $update){
		if(empty($where)) return '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		
		$ids = $this->db->query("select GROUP_CONCAT(DISTINCT recon.ID SEPARATOR ', ') ids
				from reconcilation recon, redemption redeem, payment_cutoff pcf, branches br, cp_merchant mer 
				where recon.REDEEM_ID = redeem.REDEEM_ID 
				and recon.PROD_ID = redeem.PROD_ID and br.MERCHANT_ID = recon.MERCHANT_ID and br.BRANCH_ID = recon.BRANCH_ID and mer.CP_ID = br.CP_ID 
				and pcf.MERCHANT_ID = br.MERCHANT_ID and recon.RECON_ID <> '' and recon.PA_ID = 0 and redeem.STAGE = 'RECONCILED' 
				".$where."");
		if($ids->num_rows() <> 0){
			$IDs = $ids->row()->ids;
			if(!empty($IDs)){
				$this->db->where('ID in ('.$IDs.')');
				$this->db->update('reconcilation',$update);
			}
		}
	}
	
	
	/*
	** QUERY ACTION FOR TBL redemption
	*/
	public function v_checkRedeem($where = null, $count = false, $select = null){
		$this->db->from('redemption');	
		if(!empty($where)) $this->db->where($where);	
		
		if($select <> null) $this->db->select($select);
		if($count == true){
			$this->db->select('id');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	public function v_TransacRedeem($where = null, $count = false, $page = '', $returnResult = true){
		$this->db->from('redemption redeem')
			->join('cp_product prod', 'prod.SERVICE_ID = redeem.PROD_ID')
			->join('reconcilation recon', 'redeem.REDEEM_ID = recon.REDEEM_ID', 'LEFT')		
			->join('pa_header paH', 'recon.PA_ID = paH.PA_ID', 'LEFT')
			->join('branches br', 'br.BRANCH_ID = redeem.BRANCH_ID', 'LEFT')
			->join('cp_merchant mer', 'mer.CP_ID = br.CP_ID', 'LEFT'); 
		$this->db->group_by('redeem.id');		
		$this->db->where('br.MERCHANT_ID = recon.MERCHANT_ID');
		if(!empty($where)) $this->db->where($where);				
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));	
		$this->db->order_by('redeem.TRANSACTION_DATE_TIME',  $orderName);	
			
		if($count == true){
			$this->db->select('redeem.id');
			$data =  $this->db->count_all_results();
		}else{			
			if(!empty($page)) $this->db->limit($page['limit'], $page['offset']);
			$this->db->select('
			redeem.REDEEM_ID redeem_id,
			redeem.MERCHANT_ID m_id,
			redeem.BRANCH_ID br_id,
			redeem.POS_ID pos_id,
			redeem.VOUCHER_CODE voucher_code,
			redeem.TRANSACTION_DATE_TIME redeem_date,
			redeem.TRANSACTION_VALUE redeem_fv,
			redeem.STAGE redeem_status,
			redeem.PROD_ID prod_id,
			recon.RECON_ID recon_id,
			recon.RECON_DATE_TIME recon_date,
			paH.PA_ID pa_id,
			paH.REIMBURSEMENT_DATE pa_date,	
			paH.ExpectedDueDate pa_duedate,			
			prod.SERVICE_NAME prod_name,
			mer.TIN');
			$data =  $this->db->get();		
			if($returnResult == true) $data  = $data->result();
		}	
		return $data;
	}
	public function i_redeem($arr){
		if(empty($arr)) return '';		
			$this->db->insert('redemption',$arr);
		return $this->db->insert_id();
	}	
	public function getTransaction($where = '', $count = false, $page = '', $GROUP_BY = 'redeem.REDEEM_ID, redeem.PROD_ID'){
		/*
		** RAW SQL
		//amount, recon_status, settlement_status, 		
		*/	
		$limit = '';
		$where = 'WHERE redeem.BRANCH_ID = br.BRANCH_ID '.(empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		if($count == false){
			$select = 'redeem.REDEEM_ID redeem_id,
			redeem.MERCHANT_ID m_id,
			redeem.BRANCH_ID br_id,
			redeem.POS_ID pos_id,
			redeem.VOUCHER_CODE voucher_code,
			redeem.TRANSACTION_DATE_TIME redeem_date,
			redeem.TRANSACTION_VALUE redeem_fv,
			redeem.STAGE redeem_status,
			redeem.PROD_ID prod_id,
			recon.RECON_ID recon_id,
			recon.RECON_DATE_TIME recon_date,
			paH.PA_ID pa_id,
			paD.RATE,
			paD.NUM_PASSES,
			paD.TOTAL_FV,
			paD.MARKETING_FEE,
			paD.VAT,
			paD.NET_DUE,
			paH.REIMBURSEMENT_DATE pa_date,		
			paH.ExpectedDueDate pa_duedate,		
			mer.LegalName legalname,	
			mer.MeanofPayment meanofpayment,
			br.BRANCH_NAME br_name,
			prod.SERVICE_NAME prod_name';
		}else{
			$select = 'redeem.REDEEM_ID';
		}
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));

		$result = $this->db->query("
		select 
		".$select."
		from
		redemption redeem
		left join reconcilation recon on recon.REDEEM_ID = redeem.REDEEM_ID
		left join pa_detail paD on paD.RECON_ID = recon.RECON_ID
		left join pa_header paH on paH.PA_ID = paD.PA_ID
		left join cp_product prod on prod.SERVICE_ID = redeem.PROD_ID
		left join branches br on br.MERCHANT_ID = redeem.MERCHANT_ID
		inner join cp_merchant mer on mer.CP_ID = br.CP_ID
		".$where."
		GROUP BY  ".$GROUP_BY."
		ORDER BY redeem.TRANSACTION_DATE_TIME  ".$orderName."
		".$limit."
		");
		return $result;
	}
	
	
	/*
	** QUERY ACTION FOR TBL cp_product
	*/
	public function v_product($where = null, $count = false, $select = null){
		$this->db->from('cp_product');	
		if(!empty($where)) $this->db->where($where);	
		
		if($select <> null) $this->db->select($select);
		if($count == true){
			$this->db->select('SERVICE_ID');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	public function i_product($arr){
		if(empty($arr)) return '';		
			$this->db->insert('cp_product',$arr);
		return $this->db->insert_id();
	}
	
	/*
	** QUERY ACTION FOR TBL branches
	*/
	public function branchInfo($where = null, $count = false, $select = null){
		$this->db->from('branches br') 
				->join('cp_merchant mer', 'mer.CP_ID = br.CP_ID');	
		if(!empty($where)) $this->db->where($where);	
		
		if($select <> null) $this->db->select($select);
		if($count == true){
			$this->db->select('br.BRANCH_ID');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	public function v_branches($where = null, $count = false, $select = null){
		$this->db->from('branches');	
		if(!empty($where)) $this->db->where($where);	
		
		if($select <> null) $this->db->select($select);
		if($count == true){
			$this->db->select('BRANCH_ID');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	public function i_branches($arr){
		if(empty($arr)) return '';		
			$this->db->insert('branches',$arr);
		return $this->db->insert_id();
	}
	public function u_branches($whereArr, $update){
		$this->db->where($whereArr);
		$this->db->update('branches',$update);
	}
	
	/*
	** QUERY ACTION FOR TBL payment_cutoff
	*/
	public function getSpecificDate($whereCut, $groupByCut){
		$merge_arr = array();
		
		$result = $this->v_cutoff($whereCut, '', $groupByCut, $groupByCut)->result();		
		foreach($result as $row){ 
			$date_arr = explode(",",substr($row->SPECIFIC_DATE, 1, -1));
			$merge_arr = array_unique(array_merge($date_arr,$merge_arr), SORT_REGULAR);
		}	
		asort($merge_arr);
		$merge_arr = array_values($merge_arr);	
		return $merge_arr;
	}
	public function v_cutoff($where = null, $count = false, $select = null, $groupBy = ''){
		$this->db->from('payment_cutoff');	
		if(!empty($where)) $this->db->where($where);	
		if(!empty($groupBy)) $this->db->group_by($groupBy);
		
		if($select <> null) $this->db->select($select);
		if($count == true){
			$this->db->select('MERCHANT_ID');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	public function i_cutoff($arr){
		if(empty($arr)) return '';		
			$this->db->insert('payment_cutoff',$arr);
		return $this->db->insert_id();
	}
	public function u_cutoff($MERCHANT_ID, $update){
		$this->db->where('MERCHANT_ID',$MERCHANT_ID);
		$this->db->update('payment_cutoff',$update);
	}
	
	/*
	** QUERY ACTION FOR TBL pa_header
	*/
	public function v_paH($where = null, $count = false, $select = null){
		$this->db->from('pa_header');	
		if(!empty($where)) $this->db->where($where);	
		
		if($select <> null) $this->db->select($select);
		if($count == true){
			$this->db->select('pa_id');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	function getPA($where = null, $count = false){
		/*
		** RAW SQL
		*/
		$this->db->from('pa_header paH')		
					->join('pa_detail paD', 'paD.PA_ID = paH.PA_ID')
					->join('reconcilation recon', 'recon.RECON_ID = paD.RECON_ID')
					->join('branches br', 'br.MERCHANT_ID = paH.MERCHANT_ID')
					->join('cp_merchant mer', 'mer.CP_ID = br.CP_ID');
		if(!empty($where)) $this->db->where($where);			
		if($count == true){
			$this->db->select('paH.PA_ID');
			$result =  $this->db->count_all_results();
		}else{
			$this->db->select("paH.*, paD.*, recon.PROD_ID, mer.CP_ID")
					->select("(select SUM(TOTAL_FV) from pa_detail paD2 where paH.PA_ID = paD2.PA_ID) as TotalAmount"); 
			$result =  $this->db->get();	
		}	
		return $result;
	}	
	public function i_paH($arr){
		if(empty($arr)) return '';		
			$this->db->insert('pa_header',$arr);
		return $this->db->insert_id();
	}	
	public function u_paH($where, $update){
		if(empty($where)) return '';	
		$this->db->where($where);
		$this->db->update('pa_header',$update);
	}
	/*
	** QUERY ACTION FOR TBL pa_detail
	*/
	public function i_paD($arr){
		if(empty($arr)) return '';		
			$this->db->insert('pa_detail',$arr);
		return $this->db->insert_id();
	}
	
	/*
	** QUERY ACTION FOR TBL nav_header
	*/
	public function v_navH($where = null, $count = false, $select = null){
		/*
		** RAW SQL
		*/	
		if($count == false){
			$select = 'TBL.RECON_ID, TBL.RECONDATE, SUM(TBL.TRANSACTION_VALUE) TOTAL_FV , TBL.PROD_ID,
			TBL.paymentAdvice, TBL.paGenDate, TBL.MERCHANT_ID,
			branch.TIN, branch.LegalName, branch.PayeeCode, branch.PayeeName, branch.BankAccountNumber, branch.CP_ID, branch.PayeeQtyOfDays, branch.PayeeDayType, TBL.ExpectedDueDate, TBL.MERCHANT_FEE, TBL.vatCond';
		}else{
			$select = 'TBL.RECON_ID';
		}

		$result = $this->db->query("
		select 
		".$select."
		from
		(
			select 
			recon.ID, recon.RECON_ID, date_format(recon.TRANSACTION_DATE_TIME, '%m/%d/%Y') RECONDATE, recon.TRANSACTION_VALUE, recon.PROD_ID,
			paH.PA_ID paymentAdvice, date_format(paH.REIMBURSEMENT_DATE, '%m/%d/%Y') paGenDate, paH.MERCHANT_ID, paH.ExpectedDueDate, paH.MERCHANT_FEE, paH.vatCond
			from
			reconcilation recon,
			pa_header paH
			where
			recon.PA_ID = paH.PA_ID
			".$where."
			order by paH.PA_ID asc
		) TBL,
		(
		  select br.MERCHANT_ID, mer.TIN, mer.LegalName, mer.PayeeCode, mer.PayeeName, mer.BankAccountNumber, mer.CP_ID, mer.PayeeQtyOfDays, mer.PayeeDayType
		  from branches br inner join cp_merchant mer on mer.CP_ID = br.cp_id group by br.MERCHANT_ID
		) branch
		where
		branch.MERCHANT_ID = TBL.MERCHANT_ID
		group by TBL.paymentAdvice, TBL.RECON_ID, TBL.PROD_ID");
		return $result;
	}
	
	public function i_navH($arr){
		if(empty($arr)) return '';		
			$this->db->insert('nav_header',$arr);
		return $this->db->insert_id();
	}
	
	/*
	** QUERY ACTION FOR TBL nav_header
	*/
	public function v_navD($where = null, $count = false, $select = null){
		/*
		** RAW SQL
		*/	
		if($count == false){
			$select = 'recon.RECON_ID, paH.PA_ID, recon.PROD_ID, recon.TRANSACTION_VALUE FV, paH.vatCond, paH.MERCHANT_FEE';
		}else{
			$select = 'recon.RECON_ID';
		}
		$result = $this->db->query("
			select 
			".$select."
			from
			pa_detail pad,
			pa_header paH,
			reconcilation recon
			where
			pad.PA_ID = paH.PA_ID
			and pad.RECON_ID = recon.RECON_ID
			".$where."
			ORDER BY recon.RECON_ID");
		return $result;
	}
	public function i_navD($arr){
		if(empty($arr)) return '';		
			$this->db->insert('nav_detail',$arr);
		return $this->db->insert_id();
	}
	
	
	/*
	** PROCESS PA
	*/	
	public function getPaymentCutoff($where = '', $count = false, $page = ''){
		$limit = '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		$result = $this->db->query("
			select 
				TBL2.REDEEM_ID,
				TBL2.RECON_ID,
				TBL2.MID,
				TBL2.CPID,
				TBL2.LegalName,
				TBL2.SPECIFIC_DATE,
				TBL2.SPECIFIC_DAY,
				TBL2.TYPE,
				TBL2.PayeeDayType,
				TBL2.PayeeQtyOfDays,
				SUM(TBL2.totalAmount) totalAmount,		
				SUM(TBL2.totalPasses) totalPasses,		
				COUNT(TBL2.totalBranch) totalBranch
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
				TBL1.totalBranch totalBranch,		
				TBL1.PayeeDayType,		
				TBL1.PayeeQtyOfDays
			from
			(
			select 
				redeem.REDEEM_ID,
				recon.RECON_ID,
				recon.MERCHANT_ID MID,
				mer.CP_ID CPID,
				mer.LegalName LegalName,
				SUM(recon.TRANSACTION_VALUE) totalAmount,		
				redeem.REDEEM_ID totalPasses,		
				recon.BRANCH_ID totalBranch,
				pcf.SPECIFIC_DATE,
				pcf.SPECIFIC_DAY,
				pcf.type TYPE,
				mer.PayeeDayType,
				mer.PayeeQtyOfDays
			from		
				reconcilation recon,
				redemption redeem,	
				payment_cutoff pcf,	
				branches br,
				cp_merchant mer
			where
				recon.REDEEM_ID = redeem.REDEEM_ID
				and recon.PROD_ID = redeem.PROD_ID
				and br.MERCHANT_ID = recon.MERCHANT_ID
				and br.BRANCH_ID = recon.BRANCH_ID 
				and mer.CP_ID = br.CP_ID
				and pcf.MERCHANT_ID = br.MERCHANT_ID
				and recon.RECON_ID <> ''
				and recon.PA_ID = 0
				and redeem.STAGE = 'RECONCILED'
				".$where."				
				group by recon.REDEEM_ID, recon.BRANCH_ID, recon.MERCHANT_ID 
				ORDER BY recon.TRANSACTION_DATE_TIME  ".$orderName."
				".$limit."
			) TBL1
			group by TBL1.totalBranch, TBL1.MID 
			) TBL2
			group by TBL2.MID
			");
		if( $count == true) return $result->num_rows();
		else return $result->result();
	}
	public function getPaymentCutoffBranch($where = '', $count = false, $page = ''){
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
					redeem.REDEEM_ID,
					recon.RECON_ID,
					recon.MERCHANT_ID MID,
					mer.CP_ID CPID,
					mer.LegalName LegalName,
					mer.MerchantFee MerchantFee,
					mer.vatcond,
					mer.PayeeDayType,
					mer.PayeeQtyOfDays,
					SUM(recon.TRANSACTION_VALUE) totalAmount,		
					redeem.REDEEM_ID totalPasses,		
					br.BRANCH_ID BRANCH_ID,	
					br.BRANCH_NAME BRANCH_NAME,
					pcf.SPECIFIC_DATE,
					pcf.SPECIFIC_DAY,
					pcf.type
				from		
					reconcilation recon,
					redemption redeem,	
					payment_cutoff pcf,	
					branches br,
					cp_merchant mer
				where
					recon.REDEEM_ID = redeem.REDEEM_ID
					and recon.PROD_ID = redeem.PROD_ID
					and br.MERCHANT_ID = recon.MERCHANT_ID
					and br.BRANCH_ID = recon.BRANCH_ID 
					and mer.CP_ID = br.CP_ID
					and pcf.MERCHANT_ID = br.MERCHANT_ID
					and recon.RECON_ID <> ''
					and recon.PA_ID = 0
					and redeem.STAGE = 'RECONCILED'
					".$where."	
					group by recon.REDEEM_ID, recon.BRANCH_ID, recon.MERCHANT_ID
					ORDER BY recon.TRANSACTION_DATE_TIME  ".$orderName."
					".$limit."
				) TBL1
			group by TBL1.BRANCH_ID, TBL1.MID
			");
		if( $count == true) return $result->num_rows();
		else return $result;
	}
	public function getPCBranches($where = '', $count = false, $page = '', $detailed = false, $select = null, $returnResult = true){
		// MID, Branch ID, Branch Name, Total Amount, Transaction #
		$this->db->from('redemption redeem')		
				->join('reconcilation recon', 'recon.REDEEM_ID = redeem.REDEEM_ID')
				->join('payment_cutoff pcf', 'pcf.MERCHANT_ID = redeem.MERCHANT_ID')	
				->join('branches br', 'br.MERCHANT_ID = redeem.MERCHANT_ID')	 
				->join('cp_merchant mer', 'mer.CP_ID = br.CP_ID');
		if(!empty($where))$this->db->where($where);

		$this->db->where('redeem.BRANCH_ID = br.BRANCH_ID'); 	
		$this->db->where('recon.RECON_ID <>',''); //VALIDATE THAT ALL TRANSACTION MUST HAVE RECON_ID
		$this->db->where('recon.PA_ID',0); //VALIDATE THAT ALL TRANSACTION MUST HAVE RECON_ID
		$this->db->where('redeem.STAGE',"RECONCILED"); //VALIDATE THAT ALL TRANSACTION MUST BE RECONCILED	
		
		$this->db->group_by('recon.VOUCHER_CODE, recon.MERCHANT_ID, recon.BRANCH_ID');
		if($count == true){ 
			$this->db->select('recon.ID');
			$data =  $this->db->count_all_results();
		}else{
			if(!empty($page)) $this->db->limit($page['limit'], $page['offset']);
			if($select <> null){
				$this->db->select($select);
				$data =  $this->db->get();
			}else {
				if($detailed == false){
					$this->db->select('recon.ID primary_id,
						recon.MERCHANT_ID MID,
						recon.BRANCH_ID BRANCH_ID,
						br.BRANCH_NAME BRANCH_NAME,		
						mer.LegalName,
						SUM(recon.TRANSACTION_VALUE) totalAmount,		
						COUNT(recon.VOUCHER_CODE) totalTransaction');
				}else{
					$this->db->select('recon.ID primary_id,
					recon.RECON_ID,
					recon.REDEEM_ID,	
					recon.MERCHANT_ID,
					recon.BRANCH_ID,
					mer.LegalName,
					mer.MerchantFee,
					SUM(recon.TRANSACTION_VALUE) totalAmount,		
					COUNT(recon.VOUCHER_CODE) totalTransaction');
				}
				$data =  $this->db->get();
				if($returnResult == true) $data  = $data->result();
			}
			
		}			
		return $data;
	}
	
	/*
	** FOR PDF PA
	*/	
	public function merchantPA($where = null, $count = false, $select = null){
		$this->db->from('branches br')			
				->join('cp_merchant mer', 'mer.CP_ID = br.CP_ID') 
				->join('pa_header paH', 'paH.MERCHANT_ID = br.MERCHANT_ID')
				->join('user u', 'u.USER_ID = paH.USER_ID');
		if(!empty($where)) $this->db->where($where);	
		
		$this->db->group_by('br.MERCHANT_ID');	
		if(!empty($select)) $this->db->select($select);
		if($count == true){
			$this->db->select('mer.id');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	public function branchesPA($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;			
		if($count == false){
			$select = '
				recon.ID,
				br.BRANCH_NAME,
				recon.RECON_ID,
				paH.PA_ID,
				paH.MERCHANT_ID,
				paD.BRANCH_ID,
				paD.RATE,
				paD.MARKETING_FEE,
				paD.NUM_PASSES,
				paD.TOTAL_FV,
				paD.VAT,
				paD.NET_DUE';
		}else $select = 'recon.ID';

		$result = $this->db->query("
			select 
				".$select."
			from
				pa_header paH,
				reconcilation recon,
				pa_detail pad,
				branches br
			where 
				paH.PA_ID = recon.PA_ID 
				and paH.PA_ID = paD.PA_ID
				and br.BRANCH_ID = paD.BRANCH_ID
			".$where."
			GROUP BY paD.BRANCH_ID
			ORDER BY recon.TRANSACTION_DATE_TIME asc
			");
		return $result;
	}
	public function servicesPA($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;			
		if($count == false){
			$select = '				
				prod.SERVICE_NAME,	
				SUM(recon.TRANSACTION_VALUE) TOTAL_FV,
				paH.MERCHANT_FEE MerchantFee, paH.vatcond';
		}else $select = 'recon.PROD_ID';

		$result = $this->db->query("
			select 
				".$select."
			from
				reconcilation recon,
				cp_product prod,
				pa_header paH
			where 
				paH.PA_ID = recon.PA_ID
				and recon.PROD_ID = prod.SERVICE_ID				
			".$where."
			GROUP BY recon.PROD_ID
			ORDER BY recon.PROD_ID asc
			");
		return $result;
	}
	
	
}

