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

	public function v_auditUpload($where = null, $count = false, $select = null){
		$this->db->from('audit_upload');	
		if(!empty($where)) $this->db->where($where);	
		
		if(!empty($select)) $this->db->select($select);
		$this->db->order_by('file_name',  'desc');	
		if($count == true){
			$this->db->select('id');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	
	// Start: fix for shakeys - reinforced merchant fee from cp_merchant table
	public function getMerchantFee($cp_id){
		$this->db->from('cp_merchant');	
		$this->db->where('CP_ID', $cp_id);	
		$this->db->select('MerchantFee');
		$result =  $this->db->get();
	
		return $result;
	}
	// End: fix for shakeys - reinforced merchant fee from cp_merchant table
	
	
	/*
	** QUERY ACTION FOR TBL cp_merchant
	*/
	
	public function branch_merchant($arr){
		if(empty($arr)) return false;
		$this->db->query('INSERT IGNORE INTO branch_merchant (
				MERCHANT_ID,
				BRANCH_ID
			) VALUES (
				"'.$arr['MERCHANT_ID'].'"
				, "'.$arr['BRANCH_ID'].'"		
			)
			ON DUPLICATE KEY UPDATE MERCHANT_ID = VALUES(MERCHANT_ID), BRANCH_ID = VALUES(BRANCH_ID)');
		return true;
		/** -----------------
		  		INSERT IGNORE INTO branch_merchant (MERCHANT_ID, BRANCH_ID)
				SELECT * FROM
				(SELECT MERCHANT_ID, BRANCH_ID FROM branches group by MERCHANT_ID, BRANCH_ID 
				UNION
				SELECT MERCHANT_ID, BRANCH_ID FROM redemption group by MERCHANT_ID, BRANCH_ID 
				union
				SELECT MERCHANT_ID, BRANCH_ID FROM reconcilation group by MERCHANT_ID, BRANCH_ID 
				UNION
				SELECT MERCHANT_ID, BRANCH_ID FROM refund group by MERCHANT_ID, BRANCH_ID) AS dt
				ON DUPLICATE KEY UPDATE MERCHANT_ID = VALUES(MERCHANT_ID), BRANCH_ID = VALUES(BRANCH_ID);
		----- **/
	}

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
				AffiliateGroupCode,
				MerchantType,
				DIGITALSETTLEMENTTYPE
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
				, "'.$arr['BANKBRANCHCODE'].'"
				, "'.$arr['PAYEEQTYOFDAYS'].'"
				, "'.$arr['PAYEEDAYTYPE'].'"
				, "'.$arr['PAYEECOMMENTS'].'"
				, "'.$arr['AFFILIATEGROUPCODE'].'"
				, "'.$arr['MerchantType'].'"	
				, "'.$arr['DigitalSettlementType'].'"			
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
	
	public function v_merchant_new($where = null, $count = false, $select = null){
		
		$this->db->from('cp_agreement');	
		$this->db->join('cp_merchant', 'cp_agreement.CP_ID = cp_merchant.CP_ID', 'left'); // 'left' join is optional, can be 'inner' or 'right' based on your requirement
		$this->db->where($where);	
		$this->db->select('cp_agreement.*, cp_merchant.*');
		$result =  $this->db->get();
	
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
				".$where."");
		if($ids->num_rows() <> 0){
			$IDs = $ids->row()->ids;
			if(!empty($IDs)){
				$IDs = implode(', ', array_diff(explode(',',$IDs), array(" ","",null)));
				$this->u_reconid($IDs, $update);
			}
		}
	}
		private function u_reconid($IDs, $update){
			if(!empty($IDs)){
				$this->db->where('ID in ('.$IDs.') and PA_ID = 0');
				$this->db->update('reconcilation',$update);
			}
		}

	public function u_recon_arr($whereArr, $update){
		$this->db->where($whereArr);
		$this->db->update('reconcilation',$update);
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
	
	/*
	** QUERY TO CHECK REDEMPTION BACKUP 
	*/
	public function v_checkRedeemBackup($where = null, $count = false, $select = null){
		$this->db->from('redemption_20230608');	
		if(!empty($where)) $this->db->where($where);	
		
		if($select <> null) $this->db->select($select);
		if($count == true){
			$this->db->select('id');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	} 
	
	/*
	** QUERY TO CHECK OLD REDEMPTION  
	*/
	public function v_checkRedeemOld($where = null, $count = false, $select = null){
		$this->db->from('old_redemption');	
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
			->join('branches br', 'br.BRANCH_ID = redeem.BRANCH_ID', 'LEFT')
			->join('cp_merchant mer', 'mer.CP_ID = br.CP_ID', 'LEFT')
			->join('reconcilation recon', 'redeem.REDEEM_ID = recon.REDEEM_ID', 'LEFT')	
			->join('refund ref', 'recon.REFUND_ID = ref.REFUND_ID', 'LEFT')	
			->join('pa_header paH', 'recon.PA_ID = paH.PA_ID', 'LEFT'); 
		$this->db->group_by('redeem.id');		
		$this->db->where('br.MERCHANT_ID = redeem.MERCHANT_ID');
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
			ref.PA_ID ref_paid,		
			ref.REVERSAL_TRANSACTION_ID ref_id,	
			ref.REVERSAL_DATE_TIME ref_date,	
			(select revPAH.REIMBURSEMENT_DATE from pa_header revPAH where revPAH.PA_ID = ref.PA_ID)	ref_padate,	
			prod.SERVICE_NAME prod_name,
			mer.TIN');
			$data =  $this->db->get();		
			if($returnResult == true) $data  = $data->result();
		}	
		return $data;
	}

	public function i_redeem($arr){

	if(empty($arr)) return ''; // EARP MAY-2023 MODIFIED FOR REDEEM DUPLICATES

			$insert = $this->db->insert_string('redemption',$arr);
			$insert = str_replace('INSERT INTO','INSERT IGNORE INTO', $insert);
			$this->db->query($insert);
				
		if ($this->db->affected_rows() > 0) {
		// Get the ID of the last inserted row
			return $this->db->insert_id();
		}else{
			
			$this->db->select('id');
			$this->db->where('REDEEM_ID', $arr['REDEEM_ID']);
			$this->db->where('PROD_ID', $arr['PROD_ID']);
			$this->db->where('MERCHANT_ID', $arr['MERCHANT_ID']);
			$this->db->where('BRANCH_ID', $arr['BRANCH_ID']);
			$this->db->where('STAGE', $arr['STAGE']);
			$this->db->where('TRANSACTION_DATE_TIME', $arr['TRANSACTION_DATE_TIME']);
			$this->db->where('VOUCHER_CODE', $arr['VOUCHER_CODE']);
			$this->db->where('TRANSACTION_VALUE', $arr['TRANSACTION_VALUE']);
			$query1 = $this->db->get('redemption');
			
			$result = $query1->result();
		// Access the first result object and retrieve the value of the column
			return $result[0]->id;
		}
		/* original code below
		$this->db->insert('redemption',$arr);		
		return $this->db->insert_id(); */
	}	
	
	public function u_redeem($whereArr, $update){
		$this->db->where($whereArr);
		$this->db->update('redemption',$update);
	}

	public function getTransaction($where = '', $count = false, $page = '', $GROUP_BY = ''){
		if(empty($GROUP_BY)) $GROUP_BY = 'redeem.REDEEM_ID, redeem.PROD_ID';
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
		
		$orderName = 'desc';
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
		ORDER BY paH.PA_ID  ".$orderName."
		".$limit."
		");
		return $result;
	}

	public function getTransactionSummary($where = '', $count = false, $page = '', $GROUP_BY = ''){
		/*
		** RAW SQL
		//PAYMENT ADVICE ID	MERCHANT ID	MERCHANT NAME	TOTAL AMOUNT	PAYMENT DUE DATE
		*/	
		$limit = '';
		$where = 'WHERE paD.PA_ID = paH.PA_ID and recon_tbl.PA_ID = paH.PA_ID '.(empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
	
		if($count == false){
			$select = '	
				paH.PA_ID pa_id,
				recon_tbl.MERCHANT_ID m_id,
				recon_tbl.LegalName legalname,
				paH.ExpectedDueDate pa_duedate,	
				SUM(paD.TOTAL_FV) TOTAL_FV';
		}else{
			$select = 'paH.PA_ID';
		}
		
		$orderName = 'desc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));

		$result = $this->db->query("
		select 
			".$select."
		from
			pa_detail pad,
			pa_header paH, 
			(
				select 
					recon.PA_ID,
					redeem.MERCHANT_ID,
					mer.LegalName
				from
					reconcilation recon
					left join redemption redeem  on redeem.REDEEM_ID = recon.REDEEM_ID
					left join cp_product prod on prod.SERVICE_ID = redeem.PROD_ID
					left join branches br on br.MERCHANT_ID = redeem.MERCHANT_ID
					inner join cp_merchant mer on mer.CP_ID = br.CP_ID
				where
				recon.PA_ID <> '' and redeem.BRANCH_ID = br.BRANCH_ID
				group by recon.PA_ID
			) recon_tbl
		".$where."
		GROUP BY  ".$GROUP_BY."
		ORDER BY paH.PA_ID  ".$orderName."
		".$limit."
		");
		return $result;
	}
	
	public function getTransactionSummary_part2($where = '', $count = false, $page = '', $GROUP_BY = ''){
		/*
		** RAW SQL
		//PAYMENT ADVICE ID	MERCHANT ID	MERCHANT NAME	TOTAL AMOUNT	PAYMENT DUE DATE
		*/	
		$limit = '';
		$where = 'WHERE paD.PA_ID = paH.PA_ID and recon_tbl.PA_ID = paH.PA_ID '.(empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
	
		if($count == false){
			$select = '	
				paH.PA_ID pa_id,
				recon_tbl.MERCHANT_ID m_id,
				recon_tbl.LegalName legalname,
				paH.ExpectedDueDate pa_duedate,	
				SUM(paD.TOTAL_FV) TOTAL_FV';
		}else{
			$select = 'paH.PA_ID';
		}
		
		$orderName = 'desc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));

		$result = $this->db->query("
		select 
			".$select."
		from
			pa_detail pad,
			pa_header paH, 
			(
				select 
					recon.PA_ID,
					recon.MERCHANT_ID,
					mer.LegalName
				from
					reconcilation recon
					left join cp_product prod on prod.SERVICE_ID = recon.PROD_ID
					left join branches br on br.MERCHANT_ID = recon.MERCHANT_ID
					inner join cp_merchant mer on mer.CP_ID = br.CP_ID
				where
				recon.PA_ID <> '' and recon.BRANCH_ID = br.BRANCH_ID
				group by recon.PA_ID
			) recon_tbl
		".$where."
		GROUP BY  ".$GROUP_BY."
		ORDER BY paH.PA_ID  ".$orderName."
		".$limit."
		");
		return $result;
	}
	
	public function getTransactionSummary_part3($where = '', $count = false, $page = '', $GROUP_BY = ''){
		$limit = '';
		$whereFind = 'WHERE paD.PA_ID = paH.PA_ID and paD.BRANCH_ID = br.BRANCH_ID and paH.MERCHANT_ID = br.MERCHANT_ID and mer.CP_ID = br.CP_ID ';	
		if(!empty($where)) $whereFind .= ' AND '.$where;
		
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];	
		if($count == false){
			$select = '	
				paH.PA_ID pa_id,
				paH.MERCHANT_ID m_id,
				mer.LegalName legalname,
				paH.ExpectedDueDate pa_duedate,	
				SUM(paD.TOTAL_FV) TOTAL_FV';
		}else{
			$select = 'paH.PA_ID';
		}
		
		$orderName = 'desc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));

		$result = $this->db->query("		
		select 
			".$select."
		from
			pa_detail pad,
			pa_header paH, 
			branches br,
			cp_merchant mer		
		".$whereFind."
		GROUP BY  ".$GROUP_BY."
		ORDER BY paH.PA_ID  ".$orderName."
		".$limit."");
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
	
	public function pcf_msettlement(){
		$result = $this->db->query("
		UPDATE  payment_cutoff pcf
		INNER JOIN (
			select br.MERCHANT_ID, mer.DIGITALSETTLEMENTTYPE
			from branches br 
			inner join cp_merchant mer on mer.CP_ID = br.cp_id 
			where mer.DIGITALSETTLEMENTTYPE <> ''
			group by br.MERCHANT_ID
		) branch ON pcf.MERCHANT_ID = branch.MERCHANT_ID
		SET pcf.DIGITALSETTLEMENTTYPE = branch.DIGITALSETTLEMENTTYPE");
		return $result;
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

	public function v_paH_new($where = null, $count = false, $select = null, $userIds = null){
		

		$this->db->from('pa_header');	
		if(!empty($where)){
			$this->db->where($where);	
			$this->db->where_in('USER_ID', explode(',', $userIds));
		} 
		
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
	public function v_paD($where = null, $count = false, $select = null){
		$this->db->from('pa_detail');	
		if(!empty($where)) $this->db->where($where);	
		
		if($select <> null) $this->db->select($select);
		if($count == true){
			$this->db->select('PA_DID');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	
	public function pad_wrecon($where = null){
		/*
		** RAW SQL
		*/	
		if(!empty($where)) $where = ' AND '.$where;
			
		$result = $this->db->query("select pd.PA_ID
		from pa_detail pd,
		pa_header paH
		where
		paH.PA_ID = pd.PA_ID
		and pd.RECON_ID <> ''
		".$where."
		group by pd.PA_ID");
		return $result; 
	}
	
	public function i_paD($arr){
		if(empty($arr)) return '';		
			$this->db->insert('pa_detail',$arr);
		return $this->db->insert_id();
	}

	public function u_paD($where, $update){
		if(empty($where)) return '';	
		$this->db->where($where);
		$this->db->update('pa_detail',$update);
	}
	

	/*
	** QUERY ACTION FOR TBL insert header and detail
	*/	
	public function i_navH($arr){
		if(empty($arr)) return '';		
			$this->db->insert('nav_header',$arr);
		return $this->db->insert_id();
	}

	public function i_navD($arr){
		if(empty($arr)) return '';		
			$this->db->insert('nav_detail',$arr);
		return $this->db->insert_id();
	}


	/*
	** with recon transaction
	** QUERY ACTION FOR TBL nav_header
	*/
	public function v_navH($where = null, $count = false, $select = null, $dormancy = false){
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
		
		
		$dormancyWhere = '';
		if($dormancy <> false){
			$dormancyWhere = " AND mer.MerchantType = 'Merchant Dormancy' ";
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
			(
				select paHT.*
				from pa_header paHT
				INNER join pa_detail paD on paD.PA_ID = paHT.PA_ID
				where pad.RECON_ID <> ''
				group by paHT.PA_ID
			) paH
			where
			recon.PA_ID = paH.PA_ID
			".$where."
			order by paH.PA_ID asc
		) TBL,
		(
		  select br.MERCHANT_ID, mer.TIN, mer.LegalName, mer.PayeeCode, mer.PayeeName, mer.BankAccountNumber, mer.CP_ID, mer.PayeeQtyOfDays, mer.PayeeDayType
		  from branches br inner join cp_merchant mer on mer.CP_ID = br.cp_id where br.BRANCH_NAME <> '' ".$dormancyWhere." group by br.MERCHANT_ID
		) branch
		where
		branch.MERCHANT_ID = TBL.MERCHANT_ID
		group by TBL.paymentAdvice, TBL.RECON_ID, TBL.PROD_ID");
		return $result; //mer.DigitalSettlementType is null OR mer.DigitalSettlementType = ''
	}

	public function v_navH_reversal($where = null, $count = false, $select = null, $dormancy = false){
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
		
		$dormancyWhere = '';
		if($dormancy <> false){
			$dormancyWhere = " AND mer.MerchantType = 'Merchant Dormancy' ";
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
			refund ref,
			reconcilation recon,
			(
				select paHT.*
				from pa_header paHT
				INNER join pa_detail paD on paD.PA_ID = paHT.PA_ID
				where pad.RECON_ID <> ''
				group by paHT.PA_ID
			) paH
			where
			ref.PA_ID = paH.PA_ID
			AND ref.REFUND_ID = recon.REFUND_ID
			and ref.PA_ID <> 0
			".$where."
			order by paH.PA_ID asc
		) TBL,
		(
		  select br.MERCHANT_ID, mer.TIN, mer.LegalName, mer.PayeeCode, mer.PayeeName, mer.BankAccountNumber, mer.CP_ID, mer.PayeeQtyOfDays, mer.PayeeDayType
		  from branches br inner join cp_merchant mer on mer.CP_ID = br.cp_id  where br.BRANCH_NAME <> '' ".$dormancyWhere."  group by br.MERCHANT_ID
		) branch
		where
		branch.MERCHANT_ID = TBL.MERCHANT_ID
		group by TBL.paymentAdvice, TBL.RECON_ID, TBL.PROD_ID"); // mer.DigitalSettlementType = '' 
		return $result;
	}
	
	/*
	** QUERY ACTION FOR TBL nav_detail
	*/
	public function v_navD($where = null, $count = false, $select = null){
		/*
		** RAW SQL
		*/	
		if($count == false){
			$select = 'recon.RECON_ID, paH.PA_ID, recon.PROD_ID, recon.TRANSACTION_VALUE FV, paH.vatCond, paH.MERCHANT_FEE, pad.PA_DID';
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
			and pad.PA_ID = recon.PA_ID
			and pad.RECON_ID <> ''
			".$where."
			GROUP BY recon.REDEEM_ID, recon.RECON_ID, recon.PROD_ID
			ORDER BY recon.RECON_ID");
		return $result;
	}

	public function v_navD_reversal($where = null, $count = false, $select = null){
		/*
		** RAW SQL
		*/	
		if($count == false){
			$select = 'recon.RECON_ID, paH.PA_ID, recon.PROD_ID, recon.TRANSACTION_VALUE FV, paH.vatCond, paH.MERCHANT_FEE, pad.PA_DID';
		}else{
			$select = 'recon.RECON_ID';
		}
		$result = $this->db->query("
			select 
			".$select."
			from
			pa_detail pad,
			pa_header paH,
			refund ref,
			reconcilation recon
			where
			pad.PA_ID = paH.PA_ID
			and pad.RECON_ID = recon.RECON_ID
			and pad.PA_ID = ref.PA_ID
			and ref.REFUND_ID = recon.REFUND_ID
			and pad.RECON_ID <> ''
			".$where."
			GROUP BY recon.REDEEM_ID, recon.RECON_ID, recon.PROD_ID
			ORDER BY recon.RECON_ID");
		return $result;
	}

	public function v_navD_dormancy($where = null, $count = false, $select = null){
		/*
		** RAW SQL
		*/	
		if($count == false){
			$select = 'recon.RECON_ID, paH.PA_ID, recon.PROD_ID, recon.TRANSACTION_VALUE FV, paH.vatCond, paH.MERCHANT_FEE, pad.PA_DID';
		}else{
			$select = 'recon.RECON_ID';
		}
		$result = $this->db->query("
			select 
			".$select."
			from
			pa_detail pad,
			pa_header paH,
			refund ref,
			reconcilation recon
			where
			pad.PA_ID = paH.PA_ID
			and pad.RECON_ID = recon.RECON_ID
			and pad.PA_ID = ref.PA_ID
			and ref.REFUND_ID = recon.REFUND_ID
			and pad.RECON_ID <> ''
			".$where."
			GROUP BY recon.REDEEM_ID, recon.RECON_ID, recon.PROD_ID
			ORDER BY recon.RECON_ID");
		return $result;
	}


	/*
	** no recon transaction
	** QUERY ACTION FOR TBL nav_header
	*/
	public function v_navH_NRecon($where = null, $count = false, $select = null, $dormancy = false){
				/*
		** RAW SQL
		*/	
		if($count == false){
			$select = 'TBL.PROD_ID, SUM(TBL.TRANSACTION_VALUE) TOTAL_FV , 
			TBL.paymentAdvice, TBL.paGenDate, TBL.MERCHANT_ID,
			branch.TIN, branch.LegalName, branch.PayeeCode, branch.PayeeName, branch.BankAccountNumber, branch.CP_ID, branch.PayeeQtyOfDays, branch.PayeeDayType, TBL.ExpectedDueDate, TBL.MERCHANT_FEE, TBL.vatCond';
		}else{
			$select = 'TBL.PROD_ID';
		}
		
		
		$dormancyWhere = '';
		if($dormancy <> false){
			$dormancyWhere = " AND mer.MerchantType = 'Merchant Dormancy' ";
		} 

		$result = $this->db->query("
		select 
		".$select."
		from
		(
			select 
			redeem.PROD_ID, redeem.TRANSACTION_VALUE, 
			paH.PA_ID paymentAdvice, date_format(paH.REIMBURSEMENT_DATE, '%m/%d/%Y') paGenDate, paH.MERCHANT_ID, paH.ExpectedDueDate, paH.MERCHANT_FEE, paH.vatCond
			from
			redemption redeem,
			(
				select paHT.*
				from pa_header paHT
				INNER join pa_detail paD on paD.PA_ID = paHT.PA_ID
				where pad.RECON_ID = ''
				group by paHT.PA_ID
			) paH
			where
			redeem.PA_ID = paH.PA_ID
			".$where."
			order by paH.PA_ID asc
		) TBL,
		(
		  select br.MERCHANT_ID, mer.TIN, mer.LegalName, mer.PayeeCode, mer.PayeeName, mer.BankAccountNumber, mer.CP_ID, mer.PayeeQtyOfDays, mer.PayeeDayType, mer.DigitalSettlementType
		  from branches br inner join cp_merchant mer on mer.CP_ID = br.cp_id where br.BRANCH_NAME <> ''  ".$dormancyWhere." group by br.MERCHANT_ID
		) branch
		where
		branch.MERCHANT_ID = TBL.MERCHANT_ID
		group by TBL.paymentAdvice, TBL.PROD_ID");
		return $result;
	}

	public function v_navH_reversal_NRecon($where = null, $count = false, $select = null, $dormancy = false){
	/*
		** RAW SQL
		*/	
		if($count == false){
			$select = 'TBL.PROD_ID, SUM(TBL.TRANSACTION_VALUE) TOTAL_FV , 
			TBL.paymentAdvice, TBL.paGenDate, TBL.MERCHANT_ID,
			branch.TIN, branch.LegalName, branch.PayeeCode, branch.PayeeName, branch.BankAccountNumber, branch.CP_ID, branch.PayeeQtyOfDays, branch.PayeeDayType, TBL.ExpectedDueDate, TBL.MERCHANT_FEE, TBL.vatCond';
		}else{
			$select = 'TBL.PROD_ID'; //TBL.BRANCH_ID
		}
		
		$dormancyWhere = '';
		if($dormancy <> false){
			$dormancyWhere = " AND mer.MerchantType = 'Merchant Dormancy' ";
		} 

		$result = $this->db->query("
		select 
		".$select."
		from
		(
			select 
			redeem.PROD_ID, redeem.TRANSACTION_VALUE, 
			paH.PA_ID paymentAdvice, date_format(paH.REIMBURSEMENT_DATE, '%m/%d/%Y') paGenDate, paH.MERCHANT_ID, paH.ExpectedDueDate, paH.MERCHANT_FEE, paH.vatCond
			from
			refund ref,
			redemption redeem,
			(
				select paHT.*
				from pa_header paHT
				INNER join pa_detail paD on paD.PA_ID = paHT.PA_ID
				where pad.RECON_ID = ''
				group by paHT.PA_ID
			) paH
			where
			ref.PA_ID = paH.PA_ID
			AND ref.REFUND_ID = redeem.REFUND_ID
			and ref.PA_ID <> 0
			".$where."
			order by paH.PA_ID asc
		) TBL,
		(
		  select br.MERCHANT_ID, mer.TIN, mer.LegalName, mer.PayeeCode, mer.PayeeName, mer.BankAccountNumber, mer.CP_ID, mer.PayeeQtyOfDays, mer.PayeeDayType
		  from branches br inner join cp_merchant mer on mer.CP_ID = br.cp_id  where br.BRANCH_NAME <> '' ".$dormancyWhere."  group by br.MERCHANT_ID
		) branch
		where
		branch.MERCHANT_ID = TBL.MERCHANT_ID
		group by TBL.paymentAdvice, TBL.PROD_ID"); //TBL.paymentAdvice, TBL.BRANCH_ID, TBL.PROD_ID
		return $result;
	}

	/*
	** QUERY ACTION FOR TBL nav_detail
	*/
	public function v_navD_NRecon($where = null, $count = false, $select = null){
		/*
		** RAW SQL
		*/	
		if($count == false){
			$select = 'redeem.PROD_ID, paH.PA_ID, sum(redeem.TRANSACTION_VALUE) FV, paH.vatCond, paH.MERCHANT_FEE';
		}else{
			$select = 'redeem.PROD_ID'; //redeem.BRANCH_ID
		}
		$result = $this->db->query("
			select 
			".$select."
			from
			redemption redeem,
			(
				select paHT.*
				from pa_header paHT
				INNER join pa_detail paD on paD.PA_ID = paHT.PA_ID
				where pad.RECON_ID = ''
				group by paHT.PA_ID
			) paH
			where
			paH.PA_ID = redeem.PA_ID
			and paH.MERCHANT_ID = redeem.MERCHANT_ID
			".$where."
			GROUP BY redeem.PROD_ID, redeem.PA_ID 
			ORDER BY redeem.PROD_ID");

		/**
		 * select 
			".$select."
			from
			pa_header paH,
			payment_cutoff pcf,
			redemption redeem
			where
			paH.PA_ID = redeem.PA_ID
			and paH.MERCHANT_ID = redeem.MERCHANT_ID
			and pcf.MERCHANT_ID = redeem.MERCHANT_ID	
			and pcf.DigitalSettlementType <> ''
			".$where."
			GROUP BY redeem.PROD_ID, redeem.PA_ID 
			ORDER BY redeem.PROD_ID
		 */
		return $result;
	}

	
	public function v_navD_reversal_NRecon($where = null, $count = false, $select = null){
		/*
		** RAW SQL
		*/	
		if($count == false){
			$select = 'ref.PROD_ID, paH.PA_ID, sum(redeem.TRANSACTION_VALUE) FV, paH.vatCond, paH.MERCHANT_FEE';
		}else{
			$select = 'ref.PROD_ID';//redeem.BRANCH_ID
		}
		$result = $this->db->query("
			select 
			".$select."
			from
			refund ref,
			redemption redeem,
			(
				select paHT.*
				from pa_header paHT
				INNER join pa_detail paD on paD.PA_ID = paHT.PA_ID
				where pad.RECON_ID = ''
				group by paHT.PA_ID
			) paH
			where
			ref.PA_ID = paH.PA_ID
			and paH.MERCHANT_ID = ref.MERCHANT_ID
			and pcf.MERCHANT_ID = ref.MERCHANT_ID
			and ref.REFUND_ID = redeem.REFUND_ID	
			".$where."
			GROUP BY ref.PROD_ID, ref.PA_ID 
			ORDER BY ref.PROD_ID");
		return $result;
	}



	/*
	** PROCESS FOR PAYMENT CUTOFF
	*/	
	public function getPaymentCutoff($where = '', $count = false, $page = '', $dateWhere = ''){
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
				redemption redeem		
				INNER JOIN reconcilation recon ON recon.REDEEM_ID = redeem.REDEEM_ID
				INNER JOIN branches br ON br.MERCHANT_ID = recon.MERCHANT_ID
				INNER JOIN payment_cutoff pcf ON pcf.MERCHANT_ID = br.MERCHANT_ID
				INNER JOIN cp_merchant mer ON mer.CP_ID = br.CP_ID
			where
				recon.PROD_ID = redeem.PROD_ID
				and br.BRANCH_ID = recon.BRANCH_ID 
				and recon.RECON_ID <> ''
				and recon.PA_ID = 0
				and redeem.STAGE = 'RECONCILED'
				".$where."			
				group by recon.REDEEM_ID, recon.BRANCH_ID, recon.MERCHANT_ID 
				ORDER BY recon.TRANSACTION_DATE_TIME  ".$orderName."
			) TBL1
			group by TBL1.totalBranch, TBL1.MID 
			) TBL2
			group by TBL2.MID
			".$limit."
			");
		if( $count == true) return $result->num_rows();
		else return $result->result();
	}
	
	public function getPaymentCutoff_REVERSAL($where = '', $count = false, $page = '', $dateWhere = '', $pcfWHere = ''){
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
						redeem.REDEEM_ID,
						recon.RECON_ID,
						recon.MERCHANT_ID MID,
						mer.CP_ID CPID,
						mer.LegalName LegalName,
						SUM(recon.TRANSACTION_VALUE) totalAmount,	
						redeem.REDEEM_ID totalPasses,		
						SUM(CASE WHEN recon.REFUND_ID = ref.REFUND_ID && DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."' THEN  recon.TRANSACTION_VALUE ELSE 0 END) refundAmount,
						CASE WHEN recon.REFUND_ID = ref.REFUND_ID && DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <=  '".$dateWhere."' THEN 1 ELSE 0 END totalPassesRef,	
						recon.BRANCH_ID totalBranch,
						pcf.SPECIFIC_DATE,
						pcf.SPECIFIC_DAY,
						pcf.type TYPE,
						mer.PayeeDayType,
						mer.PayeeQtyOfDays
					from		
						redemption redeem		
						INNER JOIN reconcilation recon ON recon.REDEEM_ID = redeem.REDEEM_ID
						INNER JOIN branches br ON br.MERCHANT_ID = recon.MERCHANT_ID
						INNER JOIN payment_cutoff pcf ON pcf.MERCHANT_ID = br.MERCHANT_ID
						INNER JOIN cp_merchant mer ON mer.CP_ID = br.CP_ID
						LEFT JOIN refund ref on recon.REFUND_ID = ref.REFUND_ID
					where
						recon.PROD_ID = redeem.PROD_ID
						and br.BRANCH_ID = recon.BRANCH_ID 
						and recon.RECON_ID <> ''
						and recon.PA_ID = 0
						and redeem.STAGE = 'RECONCILED'
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
						inner join redemption redeem on recon.REDEEM_ID = redeem.REDEEM_ID
						inner join branches br on br.MERCHANT_ID = recon.MERCHANT_ID
						inner join payment_cutoff pcf on pcf.MERCHANT_ID = br.MERCHANT_ID	
						inner join cp_merchant mer on mer.CP_ID = br.CP_ID
					where
						br.BRANCH_ID = recon.BRANCH_ID 
						and ref.PA_ID = 0
						and recon.PROD_ID = redeem.PROD_ID
						and redeem.STAGE = 'REVERSED'
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
					COUNT(redeem.REDEEM_ID) totalPasses,		
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
				) TBL1
			group by TBL1.BRANCH_ID, TBL1.MID
			".$limit." 
			");
		if( $count == true) return $result->num_rows();
		else return $result;
	}	
	
	public function getPCBranch_PA($where = '', $count = false, $page = '', $dateWhere = ''){
		$limit = '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		$result = $this->db->query(" 
			select 
				recon.RECON_ID,
				recon.MERCHANT_ID MID,
				mer.CP_ID CPID,
				mer.LegalName LegalName,
				mer.MerchantFee MerchantFee,
				mer.vatcond,
				mer.PayeeDayType,
				mer.PayeeQtyOfDays,
				mer.AffiliateGroupCode merAFFCODE,
				SUM(recon.TRANSACTION_VALUE) totalAmount,		
				COUNT(redeem.REDEEM_ID) totalPasses,		
				br.BRANCH_ID BRANCH_ID,	
				br.BRANCH_NAME BRANCH_NAME,
				CASE 
				  WHEN br.AFFILIATEGROUPCODE IS NULL or br.AFFILIATEGROUPCODE = ''  THEN mer.AffiliateGroupCode
				  ELSE br.AFFILIATEGROUPCODE
				END AS brAFFCODE,
				pcf.SPECIFIC_DATE,
				pcf.SPECIFIC_DAY,
				pcf.type,
				SUM(CASE WHEN recon.REFUND_ID = ref.REFUND_ID && DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."' THEN  recon.TRANSACTION_VALUE ELSE 0 END) refundPostAmount
				from		
				reconcilation recon
				inner join redemption redeem on recon.REDEEM_ID = redeem.REDEEM_ID
				inner join branches br on br.MERCHANT_ID = recon.MERCHANT_ID
				inner join payment_cutoff pcf on pcf.MERCHANT_ID = br.MERCHANT_ID	
				inner join cp_merchant mer on mer.CP_ID = br.CP_ID
				LEFT JOIN refund ref on recon.REFUND_ID = ref.REFUND_ID
				where
				recon.PROD_ID = redeem.PROD_ID
				and br.BRANCH_ID = recon.BRANCH_ID 
				and recon.RECON_ID <> ''
				and recon.PA_ID = 0
				and redeem.STAGE = 'RECONCILED'
				".$where."	
				group by recon.RECON_ID, recon.BRANCH_ID, recon.MERCHANT_ID
				ORDER BY recon.TRANSACTION_DATE_TIME  ".$orderName."
				".$limit."
			");
		if( $count == true) return $result->num_rows();
		else return $result;
	}	
	
	public function getPCBranch_PA_REVERSAL($where = '', $count = false, $page = '', $dateWhere = '', $whereMerchant = ''){
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
				COUNT(redeem.REDEEM_ID) totalPasses,		
				br.BRANCH_ID BRANCH_ID,	
				br.BRANCH_NAME BRANCH_NAME,
				CASE 
				  WHEN br.AFFILIATEGROUPCODE IS NULL or br.AFFILIATEGROUPCODE = ''  THEN mer.AffiliateGroupCode
				  ELSE br.AFFILIATEGROUPCODE
				END AS brAFFCODE,
				pcf.SPECIFIC_DATE as SPECIFIC_DATE,
				pcf.SPECIFIC_DAY as SPECIFIC_DAY,
				pcf.type,
				SUM(CASE WHEN recon.REFUND_ID = ref.REFUND_ID && DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."' THEN  recon.TRANSACTION_VALUE ELSE 0 END) refundPostAmount,
				recon.REFUND_ID
			from		
				reconcilation recon
				inner join redemption redeem on recon.REDEEM_ID = redeem.REDEEM_ID
				inner join branches br on br.MERCHANT_ID = recon.MERCHANT_ID
				inner join payment_cutoff pcf on pcf.MERCHANT_ID = br.MERCHANT_ID	
				inner join cp_merchant mer on mer.CP_ID = br.CP_ID
				LEFT JOIN refund ref on recon.REFUND_ID = ref.REFUND_ID
			where
				recon.PROD_ID = redeem.PROD_ID
				and br.BRANCH_ID = recon.BRANCH_ID 
				and recon.RECON_ID <> ''
				and recon.PA_ID = 0
				and redeem.STAGE = 'RECONCILED'
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
				inner join redemption redeem on recon.REDEEM_ID = redeem.REDEEM_ID
				inner join branches br on br.MERCHANT_ID = recon.MERCHANT_ID
				inner join payment_cutoff pcf on pcf.MERCHANT_ID = br.MERCHANT_ID	
				inner join cp_merchant mer on mer.CP_ID = br.CP_ID
			where
				br.BRANCH_ID = recon.BRANCH_ID 
				and ref.PA_ID = 0
				and recon.PROD_ID = redeem.PROD_ID
				and redeem.STAGE = 'REVERSED'
				and DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."'				 
				".$whereMerchant."	
			group by recon.RECON_ID, recon.BRANCH_ID, recon.MERCHANT_ID
			ORDER BY recon.TRANSACTION_DATE_TIME  asc)
			");
		if( $count == true) return $result->num_rows();
		else return $result;
	}	
	
	public function getPCBranch_PA_RE($where = '', $count = false, $page = ''){
		$limit = '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		$result = $this->db->query("
			select 
				recon.RECON_ID,
				recon.MERCHANT_ID MID,
				recon.PA_ID,
				mer.CP_ID CPID,
				mer.LegalName LegalName,
				mer.MerchantFee MerchantFee,
				mer.vatcond,
				mer.PayeeDayType,
				mer.PayeeQtyOfDays,
				mer.AFFILIATEGROUPCODE merAFFCODE,
				SUM(recon.TRANSACTION_VALUE) totalAmount,		
				COUNT(redeem.REDEEM_ID) totalPasses,		
				br.BRANCH_ID BRANCH_ID,	
				br.BRANCH_NAME BRANCH_NAME,
				br.AFFILIATEGROUPCODE brAFFCODE,
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
				and redeem.STAGE = 'RECONCILED'
				".$where."	
				group by recon.RECON_ID, recon.BRANCH_ID, recon.MERCHANT_ID
				ORDER BY recon.TRANSACTION_DATE_TIME  ".$orderName."
				".$limit."
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
	
	public function branchesPAView($where = '', $count = false, $limit = ''){
		$where = (empty($where) ? '' : ' where ').$where;			
		if($count == false){
			$select = '*';
		}else $select = 'ID';
		//branchesgen_pa
		//branchespa
		$result = $this->db->query("
			select 
				".$select."
			from
				branchesgen_pa2 
			".$where."
			ORDER BY RECON_ID asc
			".$limit."
			");
		if($count == true) return $result->num_rows();
		else return $result;
	}
	
	public function branchesPA($where = '', $count = false, $limit = ''){
		$where = (empty($where) ? '' : ' AND ').$where;			
		if($count == false){
			/* remove -- recon.ID, paD.RECON_ID,*/
			$select = '
				br.BRANCH_NAME,				
				paH.PA_ID,
				paH.MERCHANT_ID,
				paD.BRANCH_ID,
				paD.RATE,
				SUM(paD.MARKETING_FEE) MARKETING_FEE,
				SUM(paD.NUM_PASSES) NUM_PASSES,
				SUM(paD.TOTAL_FV) TOTAL_FV,
				SUM(paD.VAT) VAT,
				SUM(paD.NET_DUE) NET_DUE';
		}else $select = 'paD.BRANCH_ID';

		/* remove -- 
				reconcilation recon,
				paH.PA_ID = recon.PA_ID 
				and 
				recon.TRANSACTION_DATE_TIME*/
		$result = $this->db->query("
			select 
				".$select."
			from
				pa_header paH,
				pa_detail pad,
				branches br
			where paH.PA_ID = paD.PA_ID
				and br.BRANCH_ID = paD.BRANCH_ID
				and br.MERCHANT_ID = paH.MERCHANT_ID
			".$where."
			GROUP BY paD.BRANCH_ID, paH.MERCHANT_ID, paH.PA_ID
			ORDER BY paH.PA_ID asc
			".$limit."
			");
			/* OLD -- GROUP BY paD.RECON_ID, paD.BRANCH_ID, paH.MERCHANT_ID */
		if($count == true) return $result->num_rows();
		else return $result;
	}

	
	
	public function branchesPA2($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;			
		if($count == false){
			$select = '
				recon.ID,
				br.BRANCH_NAME,
				paD.RECON_ID,
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
			ORDER BY recon.TRANSACTION_DATE_TIME asc
			");
		return $result;
	}

	public function servicesPA($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;	
		if($count == false){	
			/*
				prod.SERVICE_NAME,	
				SUM(recon.TRANSACTION_VALUE) TOTAL_FV,
				SUM(CASE WHEN ref.PA_ID = paH.PA_ID THEN recon.TRANSACTION_VALUE ELSE 0 END) TOTAL_REFUND
				,paH.MERCHANT_FEE MerchantFee, paH.vatcond
			*/		
			$select = '				
				prod.SERVICE_NAME
				,SUM(recon.TRANSACTION_VALUE) TOTAL_FV
				,paH.MERCHANT_FEE MerchantFee
				,paH.vatcond
				,(select 
					SUM(recon2.TRANSACTION_VALUE) 
					from 
						reconcilation recon2
						INNER JOIN refund ref ON ref.REFUND_ID = recon2.REFUND_ID
					where  
						ref.PA_ID = paH.PA_ID 
						and ref.PROD_ID = recon.PROD_ID 
				group by ref.PA_ID, ref.PROD_ID
				) TOTAL_REFUND';
		}else $select = 'recon.PROD_ID';

		$result = $this->db->query("
			select 
				".$select."
			from
				reconcilation recon
				INNER JOIN cp_product prod ON recon.PROD_ID = prod.SERVICE_ID		
				INNER JOIN pa_header paH ON recon.PA_ID = paH.PA_ID	
			where 
				paH.PA_ID = recon.PA_ID
				and recon.PROD_ID = prod.SERVICE_ID				
			".$where."
			GROUP BY recon.PROD_ID
			ORDER BY recon.PROD_ID asc
			");
		return $result;
	}

	public function servicesPANrecon($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;	
		if($count == false){	
			/*
				prod.SERVICE_NAME,	
				SUM(recon.TRANSACTION_VALUE) TOTAL_FV,
				SUM(CASE WHEN ref.PA_ID = paH.PA_ID THEN recon.TRANSACTION_VALUE ELSE 0 END) TOTAL_REFUND
				,paH.MERCHANT_FEE MerchantFee, paH.vatcond
			*/		
			$select = '				
				prod.SERVICE_NAME
				,SUM(redeem.TRANSACTION_VALUE) TOTAL_FV
				,paH.MERCHANT_FEE MerchantFee
				,paH.vatcond
				,(select 
					SUM(redeem2.TRANSACTION_VALUE) 
					from 
						redemption redeem2
						INNER JOIN refund ref ON ref.REFUND_ID = redeem2.REFUND_ID
					where  
						ref.PA_ID = paH.PA_ID 
						and ref.PROD_ID = redeem.PROD_ID 
				group by ref.PA_ID, ref.PROD_ID
				) TOTAL_REFUND';
		}else $select = 'redeem2.PROD_ID';

		$result = $this->db->query("
			select 
				".$select."
			from
				redemption redeem
				INNER JOIN cp_product prod ON redeem.PROD_ID = prod.SERVICE_ID		
				INNER JOIN pa_header paH ON redeem.PA_ID = paH.PA_ID	
			where 
				paH.PA_ID = redeem.PA_ID
				and redeem.PROD_ID = prod.SERVICE_ID				
			".$where."
			GROUP BY redeem.PROD_ID
			ORDER BY redeem.PROD_ID asc
			");
		return $result;
	}
	
	/*
	** QUERY ACTION FOR TBL conversion
	*/
	public function v_checkConv($where = null, $count = false, $select = null){
		$this->db->from('conversion');	
		if(!empty($where)) $this->db->where($where);	
		
		if($select <> null) $this->db->select($select);
		if($count == true){
			$this->db->select('id');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}	
	
	public function i_conv($arr){
		if(empty($arr)) return '';		
			$this->db->insert('conversion',$arr);
		return $this->db->insert_id();
	}
	
	public function u_conv($where, $update){
		if(empty($where)) return '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		
		$ids = $this->db->query("select GROUP_CONCAT(DISTINCT conv.COV_ID SEPARATOR ', ') ids
				from conversion conv, payment_cutoff pcf, branches br, cp_merchant mer 
				where br.MERCHANT_ID = conv.MERCHANT_ID and br.BRANCH_ID = conv.BRANCH_ID and mer.CP_ID = br.CP_ID 
				and pcf.MERCHANT_ID = br.MERCHANT_ID and conv.RS_ID = 0 and conv.STAGE = 'CONVERTED' 
				".$where."");
		if($ids->num_rows() <> 0){
			$IDs = $ids->row()->ids;
			if(!empty($IDs)){
				$this->db->where('COV_ID in ('.$IDs.')');
				$this->db->update('conversion',$update);
			}
		}
	}

	public function v_TransacConv($where = null, $count = false, $page = '', $returnResult = true){
		$this->db->from('conversion conv')
			->join('cp_product prod', 'prod.SERVICE_ID = conv.PROD_ID', 'LEFT')	
			->join('rs_header rsH', 'rsH.RS_ID = conv.RS_ID', 'LEFT')
			->join('branches br', 'br.BRANCH_NAME = conv.BRANCH_NAME', 'LEFT')
			->join('cp_merchant mer', 'mer.CP_ID = br.CP_ID', 'LEFT'); 
		$this->db->group_by('conv.cov_id');		
		//$this->db->where('br.MERCHANT_ID = conv.MERCHANT_ID');
		if(!empty($where)) $this->db->where($where);				
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));	
		$this->db->order_by('conv.CREATED_AT',  $orderName);	
			
		if($count == true){
			$this->db->select('conv.cov_id');
			$data =  $this->db->count_all_results();
		}else{			
			if(!empty($page)) $this->db->limit($page['limit'], $page['offset']);
			$this->db->select('
			conv.COV_ID cov_id,
			conv.MERCHANT_ID m_id,
			conv.BRANCH_ID br_id,
			conv.BRANCH_NAME br_name,
			conv.VOUCHER_CODES voucher_code,
			conv.CREATED_AT conv_date,
			conv.AGENT_ID conv_agent,
			conv.DENO conv_deno,
			conv.TOTAL_AMOUNT total_amount,
			conv.STAGE conv_status,
			conv.PROD_ID prod_id,
			conv.USER_ID conv_uid,
			conv.NAME conv_uname,
			rsH.RS_ID rs_id,
			rsH.RS_NUMBER rs_num,
			rsH.REIMBURSEMENT_DATE rs_date,	
			rsH.ExpectedDueDate rs_duedate,			
			prod.SERVICE_NAME prod_name,  
			mer.TIN,  
			mer.LegalName,
			mer.CP_ID');
			$data =  $this->db->get();		
			if($returnResult == true) $data  = $data->result();
		}	
		return $data;
	}

	/*
	** PROCESS FOR REIMBURSEMENT SUMMARY
	* MULTIPLE VOUCHER TRANSACTION combination::
		-- conv.CREATED_AT, conv.AGENT_ID, conv.TOTAL_AMOUNT, conv.BRANCH_ID, conv.MERCHANT_ID --
	*/	
	public function getRSPaymentCutoff($where = '', $count = false, $page = ''){
		$limit = '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		$result = $this->db->query("select 
				TBL2.COV_ID,
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
				TBL1.COV_ID,
				TBL1.MID,
				TBL1.CPID,
				TBL1.LegalName,
				TBL1.SPECIFIC_DATE,
				TBL1.SPECIFIC_DAY,
				TBL1.TYPE,
				SUM(TBL1.totalAmount) totalAmount,		
				SUM(TBL1.totalPasses) totalPasses,		
				TBL1.totalBranch totalBranch,		
				TBL1.PayeeDayType,		
				TBL1.PayeeQtyOfDays
			from
			(
			select 
				conv.COV_ID,
				conv.MERCHANT_ID MID,
				mer.CP_ID CPID,
				mer.LegalName LegalName,
				SUM(conv.DENO) totalAmount,		
				COUNT(conv.VOUCHER_CODES) totalPasses,		
				conv.BRANCH_ID totalBranch,
				pcf.SPECIFIC_DATE,
				pcf.SPECIFIC_DAY,
				pcf.type TYPE,
				mer.PayeeDayType,
				mer.PayeeQtyOfDays
			from		
				conversion conv,
				cp_merchant mer,
				payment_cutoff pcf,	
				branches br
			where
				conv.MERCHANT_ID = br.MERCHANT_ID
				and br.BRANCH_ID = conv.BRANCH_ID 
				and mer.CP_ID = br.CP_ID
				and pcf.MERCHANT_ID = conv.MERCHANT_ID
				and conv.RS_ID = 0
				and conv.STAGE = 'CONVERTED'
				".$where."				
				group by conv.CREATED_AT, conv.AGENT_ID, conv.TOTAL_AMOUNT, conv.BRANCH_ID, conv.MERCHANT_ID 
				ORDER BY conv.CREATED_AT   ".$orderName."
			) TBL1
			group by TBL1.totalBranch, TBL1.MID 
			".$limit."
			) TBL2
			group by TBL2.MID
			");
		if( $count == true) return $result->num_rows();
		else return $result->result();
	}
	
	public function getRSPaymentCutoffBranch($where = '', $count = false, $page = ''){
		$limit = '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		$result = $this->db->query("select 
					TBL1.COV_ID,
					TBL1.MID,
					TBL1.CPID,
					TBL1.LegalName,
					TBL1.SPECIFIC_DATE,
					TBL1.SPECIFIC_DAY,
					TBL1.type,
					SUM(TBL1.totalAmount) totalAmount,		
					SUM(TBL1.totalPasses) totalPasses,		
					TBL1.BRANCH_ID,		
					TBL1.BRANCH_NAME,
					TBL1.MerchantFee,
					TBL1.vatcond,
					TBL1.PayeeDayType,
					TBL1.PayeeQtyOfDays
				from
				(
				select 
					conv.COV_ID,
					conv.MERCHANT_ID MID,
					mer.CP_ID CPID,
					mer.LegalName LegalName,
					mer.MerchantFee MerchantFee,
					mer.vatcond,
					mer.PayeeDayType,
					mer.PayeeQtyOfDays,
					SUM(conv.DENO) totalAmount,		
					COUNT(conv.VOUCHER_CODES) totalPasses,		
					br.BRANCH_ID BRANCH_ID,	
					br.BRANCH_NAME BRANCH_NAME,
					pcf.SPECIFIC_DATE,
					pcf.SPECIFIC_DAY,
					pcf.type
				from		
					conversion conv,
					payment_cutoff pcf,	
					branches br,
					cp_merchant mer
				where
					br.MERCHANT_ID = conv.MERCHANT_ID
					and br.BRANCH_ID = conv.BRANCH_ID 
					and mer.CP_ID = br.CP_ID
					and pcf.MERCHANT_ID = br.MERCHANT_ID
					and conv.RS_ID = 0
					and conv.STAGE = 'CONVERTED'
					".$where."	
					group by conv.CREATED_AT, conv.AGENT_ID, conv.TOTAL_AMOUNT, conv.BRANCH_ID, conv.MERCHANT_ID 
					ORDER BY conv.CREATED_AT  ".$orderName."
				) TBL1
			group by TBL1.BRANCH_ID, TBL1.MID
			".$limit."");
		if( $count == true) return $result->num_rows();
		else return $result;
	}

	public function getRSPayCutoffMerBranch($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;	
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		$result = $this->db->query("
			select 
				conv.BRANCH_ID,
				conv.MERCHANT_ID
			from		
				conversion conv,
				payment_cutoff pcf,	
				branches br,
				cp_merchant mer
			where
				br.MERCHANT_ID = conv.MERCHANT_ID
				and br.BRANCH_ID = conv.BRANCH_ID 
				and mer.CP_ID = br.CP_ID
				and pcf.MERCHANT_ID = br.MERCHANT_ID
				and conv.RS_ID = 0
				and conv.STAGE = 'CONVERTED'
				".$where."	
		group by conv.BRANCH_ID, conv.MERCHANT_ID");
		if( $count == true) return $result->num_rows();
		else return $result;
	}

	/*
	** QUERY ACTION FOR TBL rs_header
	*/
	public function v_rsH($where = null, $count = false, $select = null){
		$this->db->from('rs_header');	
		if(!empty($where)) $this->db->where($where);	
		
		if($select <> null) $this->db->select($select);
		if($count == true){
			$this->db->select('rs_id');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}

	public function i_rsH($arr){
		if(empty($arr)) return '';		
			$this->db->insert('rs_header',$arr);
		return $this->db->insert_id();
	}	

	public function u_rsH($where, $update){
		if(empty($where)) return '';	
		$this->db->where($where);
		$this->db->update('rs_header',$update);
	}

	public function getReimSummary($where = '', $count = false, $page = '', $GROUP_BY = ''){
		/*
		** RAW SQL
		*/	
		$limit = '';
		$where = 'WHERE rsD.RS_ID = rsH.RS_ID and conv_tbl.RS_ID = rsH.RS_ID '.(empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
	
		if($count == false){
			$select = 'rsH.RS_ID rs_id,
				conv_tbl.BRANCH_ID b_id,
				conv_tbl.MERCHANT_ID m_id,
				conv_tbl.LegalName legalname,
				rsH.ExpectedDueDate rs_duedate,	
				rsH.RS_NUMBER rs_num,	
				SUM(rsD.TOTAL_FV) TOTAL_FV';
		}else{
			$select = 'rsH.RS_ID';
		}
		
		$orderName = 'desc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));

		$result = $this->db->query("
			select 
				".$select."
			from
				rs_detail rsD,
				rs_header rsH, 
				(
					select 
						conv.RS_ID,
						conv.BRANCH_ID,
						conv.MERCHANT_ID,
						mer.LegalName
					from
						conversion conv
						left join cp_product prod on prod.SERVICE_ID = conv.PROD_ID
						left join branches br on br.MERCHANT_ID = conv.MERCHANT_ID
						inner join cp_merchant mer on mer.CP_ID = br.CP_ID
					where
					conv.RS_ID <> '' and conv.BRANCH_ID = br.BRANCH_ID
					group by conv.RS_ID
				) conv_tbl
			".$where."
			GROUP BY  ".$GROUP_BY."
			ORDER BY rsH.RS_ID  ".$orderName."
		".$limit."
		");
		return $result;
	}

	public function getPCBranch_RS($where = '', $count = false, $page = ''){ 
		$limit = '';
		$where = (empty($where) ? '' : ' AND ').$where;	
		if(!empty($page))$limit = 'LIMIT '.$page['offset'].','.$page['limit'];
		
		$orderName = 'asc';
		if(isset($_GET['order'])) $orderName = htmlentities($this->input->get('order', true));
		$result = $this->db->query("
			select 
				conv.COV_ID,
				conv.MERCHANT_ID MID,
				mer.CP_ID CPID,
				mer.LegalName LegalName,
				mer.MerchantFee MerchantFee,
				mer.vatcond,
				mer.PayeeDayType,
				mer.PayeeQtyOfDays,
				conv.DENO totalAmount,		
				conv.STAGE,			
				br.BRANCH_ID BRANCH_ID,	
				br.BRANCH_NAME BRANCH_NAME,
				pcf.SPECIFIC_DATE,
				pcf.SPECIFIC_DAY,
				pcf.type
				from		
				conversion conv,	
				payment_cutoff pcf,	
				branches br,
				cp_merchant mer
			where
					br.MERCHANT_ID = conv.MERCHANT_ID
					and br.BRANCH_ID = conv.BRANCH_ID 
					and mer.CP_ID = br.CP_ID
					and pcf.MERCHANT_ID = br.MERCHANT_ID
					and conv.RS_ID = 0
					and conv.STAGE = 'CONVERTED'
					".$where."	
			ORDER BY conv.CREATED_AT  ".$orderName."
			".$limit."");
		if( $count == true) return $result->num_rows();
		else return $result;
	}
	
	/*
	** QUERY ACTION FOR TBL rs_detail
	*/
	public function v_rsD($where = null, $count = false, $select = null){
		$this->db->from('rs_detail');	
		if(!empty($where)) $this->db->where($where);	
		
		if($select <> null) $this->db->select($select);
		if($count == true){
			$this->db->select('RS_DID');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	
	public function i_rsD($arr){
		if(empty($arr)) return '';		
			$this->db->insert('rs_detail',$arr);
		return $this->db->insert_id();
	}

	public function u_rsD($where, $update){
		if(empty($where)) return '';	
		$this->db->where($where);
		$this->db->update('rs_detail',$update);
	}

	/*
	** FOR PDF RS
	*/	
	public function branchInfoRS($where = null, $count = false, $select = null){
		$this->db->from('branches br')			
				->join('cp_merchant mer', 'mer.CP_ID = br.CP_ID') 
				->join('rs_header rsH', 'rsH.MERCHANT_ID = br.MERCHANT_ID')
				->join('user u', 'u.USER_ID = rsH.USER_ID');
		if(!empty($where)) $this->db->where($where);	
		
		$this->db->group_by('br.MERCHANT_ID');	
		if(!empty($select)) $this->db->select($select);
		if($count == true){
			$this->db->select('mer.id');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}

	public function branchRSList($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;			
		$result = $this->db->query("select
				tbl.RS_ID,
				tbl.SERVICE_NAME,
				tbl.DENO,
				tbl.PROD_ID,
				SUM(tbl.DENO) TOTAL_FV,
				COUNT(tbl.COV_ID) QTY
			from
			(
				select 
				rsD.RS_ID,
				prod.SERVICE_NAME,
				rsD.TOTAL_FV DENO,
				conv.PROD_ID,
				conv.COV_ID
				from
					rs_detail rsD,
					rs_header rsH,
					conversion conv,
					cp_product prod
				where 
					rsD.RS_ID = rsH.RS_ID
					and rsH.RS_ID = conv.RS_ID 
					and conv.PROD_ID = prod.SERVICE_ID	
				".$where."
				group by conv.COV_ID
				ORDER BY conv.CREATED_AT asc	
			)tbl
			group by tbl.RS_ID, tbl.DENO, tbl.PROD_ID");
		return $result;
	}

	public function voucherRSList($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;			
		if($count == false){
			$select = 'conv.VOUCHER_CODES barcode';
		}else $select = 'conv.COV_ID';

		$result = $this->db->query("select 
				".$select."
			from
				rs_detail rsD,
				rs_header rsH,
				conversion conv
			where 
				rsD.RS_ID = rsH.RS_ID
			 	and rsH.RS_ID = conv.RS_ID 
			".$where."
			group by rsD.RS_ID, conv.COV_ID
			ORDER BY conv.CREATED_AT asc");
		return $result;
	}
	
	/*
	** QUERY ACTION FOR TBL cp_agreement
	*/
	public function i_agreement($arr){
		if(empty($arr)) return false;
		if(!empty($arr['CP_ID'])){
			$this->db->query('INSERT INTO cp_agreement (
			AGREEMENT_ID,
			CP_ID,
			Address,
			MeanofPayment,
			PayeeCode,
			BankName,
			BankAccountNumber,
			PayeeName,
			MerchantFee,
			VATCond,
			InsertType,
			BankBranchCode,
			PayeeQtyOfDays,
			PayeeDayType,
			PayeeComments,
			AffiliateGroupCode,
			ContactPerson,
			ContactNumber
			) VALUES (
			"'.$arr['AGREEMENT_ID'].'"
			, "'.$arr['CP_ID'].'"
			, "'.$arr['ADDRESS'].'"
			, "'.$arr['MEANOFPAYMENT'].'"
			, "'.$arr['PAYEECODE'].'"
			, "'.$arr['BANKNAME'].'"
			, "'.$arr['BANKACCOUNTNUMBER'].'"
			, "'.$arr['PAYEENAME'].'"
			, "'.$arr['MERCHANTFEE'].'"
			, "'.$arr['VATCOND'].'"
			, "'.$arr['InsertType'].'"
			, "'.$arr['BANKBRANCHCODE'].'"
			, "'.$arr['PAYEEQTYOFDAYS'].'"
			, "'.$arr['PAYEEDAYTYPE'].'"
			, "'.$arr['PAYEECOMMENTS'].'"
			, "'.$arr['AFFILIATEGROUPCODE'].'"
			, "'.$arr['ContactPerson'].'"
			, "'.$arr['ContactNumber'].'"
			)
			ON DUPLICATE KEY UPDATE AGREEMENT_ID = VALUES(AGREEMENT_ID)');
			return true;
		}
		return false;
	}

	public function u_agreement($where, $update){
		if(empty($where)) return '';
		$this->db->where($where);
		$this->db->update('cp_agreement',$update);
	}	

	public function v_agreement($where = null, $count = false, $select = null){
		$this->db->from('cp_agreement');	
		if(!empty($where)) $this->db->where($where);	
		
		if(!empty($select)) $this->db->select($select);
		if($count == true){
			$this->db->select('AGREEMENT_ID');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	
	/*
	** QUERY ACTION FOR TBL refund
	*/	
	public function i_refund($arr){
		if(empty($arr)) return '';	
			//$this->db->ignore();
			$this->db->insert('refund',$arr);
		return $this->db->insert_id();
	}
	public function v_refund($where = null, $count = false, $select = null){
		$this->db->from('refund');	
		if(!empty($where)) $this->db->where($where);	
		
		if(!empty($select)) $this->db->select($select);
		if($count == true){
			$this->db->select('refund_id');
			$result =  $this->db->count_all_results();
		}else $result =  $this->db->get();	
	
		return $result;
	}
	public function u_refundWhere($where, $update){
		if(empty($where)) return '';
		$this->db->where($where);
		$this->db->update('refund',$update);
	}	
	
	public function u_refund($where, $update){
		if(empty($where)) return '';
		$where = (empty($where) ? '' : ' AND ').$where;		
		
		/*
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
				SUM(recon.TRANSACTION_VALUE) totalAmount,		
				COUNT(recon.REDEEM_ID) totalPasses,
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
				inner join redemption redeem on recon.REDEEM_ID = redeem.REDEEM_ID
				inner join branches br on br.MERCHANT_ID = recon.MERCHANT_ID
				inner join payment_cutoff pcf on pcf.MERCHANT_ID = br.MERCHANT_ID	
				inner join cp_merchant mer on mer.CP_ID = br.CP_ID
			where
				br.BRANCH_ID = recon.BRANCH_ID 
				and ref.PA_ID = 0
				and recon.PROD_ID = redeem.PROD_ID
				and redeem.STAGE = 'REVERSED'
				and DATE_FORMAT(ref.REVERSAL_DATE_TIME, '%Y-%m-%d') <= '".$dateWhere."'
			group by recon.RECON_ID, recon.BRANCH_ID, recon.MERCHANT_ID
			ORDER BY recon.TRANSACTION_DATE_TIME  asc
		*/
		$ids = $this->db->query("select GROUP_CONCAT(DISTINCT ref.refund_id SEPARATOR ', ') ids
				from 
					refund ref, 
					reconcilation recon, 
					redemption redeem, 
					payment_cutoff pcf, 
					branches br, 
					cp_merchant mer 
				where 
				ref.REDEEM_ID = recon.REDEEM_ID 
				and ref.REDEEM_ID = recon.REDEEM_ID 
				and ref.PROD_ID = recon.PROD_ID 
				and ref.REFUND_ID = recon.REFUND_ID 				
				and recon.REDEEM_ID = redeem.REDEEM_ID 
				and recon.PROD_ID = redeem.PROD_ID 				
				and br.MERCHANT_ID = recon.MERCHANT_ID 
				and br.BRANCH_ID = recon.BRANCH_ID 
				and mer.CP_ID = br.CP_ID 
				and pcf.MERCHANT_ID = br.MERCHANT_ID				
				and ref.RECON_ID <> '' 
				and ref.PA_ID = 0 
				and redeem.STAGE = 'REVERSED' 
				".$where."");
			if($ids->num_rows() <> 0){
				$IDs = $ids->row()->ids;
				if(!empty($IDs)){
					$IDs = implode(', ', array_diff(explode(',',$IDs), array(" ","",null)));
					$this->u_refundid($IDs, $update);
				}
			}
	}		
		private function u_refundid($IDs, $update){
			if(!empty($IDs)){
				$this->db->where('refund_id in ('.$IDs.') and pa_id = 0');
				$this->db->update('refund',$update);
			}
		}
	
	/*
	** get REFUND :: PaymentCutoff
	*/
	public function getPaymentCutoff_Refund($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;			
		if($count == false){
			$select = '			
				recon.MERCHANT_ID,
				recon.BRANCH_ID, 
				SUM(recon.TRANSACTION_VALUE) refundAmount,
				count(recon.ID) totalPassesRef';
		}else $select = 'recon.MERCHANT_ID';

		$result = $this->db->query("
			select 
				".$select."
			from
				refund ref 
				inner join reconcilation recon on recon.REFUND_ID = ref.REFUND_ID	
			where 
			ref.PA_ID = 0				
			".$where."
			group by recon.MERCHANT_ID");
		return $result;
	}
	
	
	/*
	** get REFUND P.A 
	*/
	public function refundPA($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;			
		if($count == false){
			$select = '			
				(select BRANCH_NAME FROM branches br where br.MERCHANT_ID = ref.MERCHANT_ID AND br.BRANCH_ID = ref.BRANCH_ID group by br.BRANCH_ID, br.MERCHANT_ID) BRANCH_NAME,		
				SUM(recon.TRANSACTION_VALUE) TOTALREF_FV,
				COUNT(ref.REFUND_ID) NUM_PASSES';
		}else $select = 'recon.PROD_ID';

		$result = $this->db->query("
			select 
				".$select."
			from
				refund ref
				inner join reconcilation recon ON recon.REFUND_ID = ref.REFUND_ID
				inner join pa_header paH ON paH.PA_ID = ref.PA_ID
			where 
			ref.MERCHANT_ID = recon.MERCHANT_ID
			AND ref.BRANCH_ID = recon.BRANCH_ID
			".$where."
			GROUP BY ref.BRANCH_ID, ref.MERCHANT_ID
			ORDER BY ref.RECON_ID asc
			");
		return $result;
	}

	/* original
	public function refundPANrecon($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;			
		if($count == false){
			$select = '
				(select BRANCH_NAME FROM branches br where br.MERCHANT_ID = ref.MERCHANT_ID AND br.BRANCH_ID = ref.BRANCH_ID group by br.BRANCH_ID, br.MERCHANT_ID) BRANCH_NAME,	
				SUM(redeem.TRANSACTION_VALUE) TOTALREF_FV,
				COUNT(ref.REFUND_ID) NUM_PASSES';
		}else $select = 'redeem.PROD_ID';

		$result = $this->db->query("
			select 
				".$select."
			from
				refund ref
				inner join redemption redeem ON redeem.REFUND_ID = ref.REFUND_ID
				inner join pa_header paH ON paH.PA_ID = ref.PA_ID
			where 
			ref.MERCHANT_ID = redeem.MERCHANT_ID
			AND ref.BRANCH_ID = redeem.BRANCH_ID
			".$where."
			GROUP BY ref.BRANCH_ID, ref.MERCHANT_ID
			ORDER BY ref.REFUND_ID asc
			");
		return $result;
	}*/

	/**
	 * version: 20230709
	 * by: dcj
	 */
	public function refundPANrecon($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;
		$where = str_replace("paH","ref",$where);
		$where = str_replace("AND", "", $where); 	
		if($count == false){
			$select = 'br.BRANCH_NAME, sum(r.TRANSACTION_VALUE) TOTALREF_FV, COUNT(ref.REFUND_ID) NUM_PASSES';
		}else $select = 'redeem.PROD_ID';

		$result = $this->db->query("
			select 
				".$select."
			from
				refund ref
				inner join redemption r on ref.REDEEM_ID = r.REDEEM_ID
				inner join branches br on br.MERCHANT_ID = ref.MERCHANT_ID AND br.BRANCH_ID = ref.BRANCH_ID
			where 
			".$where."
			GROUP BY ref.BRANCH_ID, ref.MERCHANT_ID
			ORDER BY ref.REFUND_ID asc
			");
		return $result;
	}

	public function refundDetail($where = '', $count = false){
		$where = (empty($where) ? '' : ' AND ').$where;			
		if($count == false){
			$select = '
				ref.REFUND_ID,
				br.BRANCH_NAME,
				br.BRANCH_ID,
				recon.TRANSACTION_VALUE TOTAL_FV,
				ref.REDEEM_ID';
		}else $select = 'refund.REFUND_ID';

		$result = $this->db->query("
			select 
				".$select."
			from
				refund ref,
				reconcilation recon,
				branches br,
				pa_header paH
			where 
				ref.PA_ID = paH.PA_ID
				and paH.PA_ID = recon.PA_ID
				and ref.REDEEM_ID = recon.REDEEM_ID
				and ref.RECON_ID = recon.RECON_ID
				and br.BRANCH_ID = ref.BRANCH_ID
			".$where."
			GROUP BY ref.REFUND_ID
			ORDER BY ref.REVERSAL_DATE_TIME asc
			");
		return $result;
	}
	
	public function servicesREF($where = '', $count = false){
		$where = (empty($where) ? '' : ' where ').$where;			
		if($count == false){
			$select = '				
				prod.SERVICE_NAME,	
				SUM(recon.TRANSACTION_VALUE) TOTAL_FV';
		}else $select = 'recon.PROD_ID';

		$result = $this->db->query("
			select 
				".$select."
			from
				refund ref 	
				INNER JOIN pa_header paH ON ref.PA_ID = paH.PA_ID		
				INNER JOIN reconcilation recon ON ref.REFUND_ID = recon.REFUND_ID
				INNER JOIN cp_product prod ON recon.PROD_ID = prod.SERVICE_ID	
			".$where."
			GROUP BY recon.PROD_ID
			ORDER BY recon.PROD_ID asc
			");
		return $result;
	}

	public function servicesREFNrecon($where = '', $count = false){
		$where = (empty($where) ? '' : ' where ').$where;			
		if($count == false){
			$select = '				
				prod.SERVICE_NAME,	
				SUM(redeem.TRANSACTION_VALUE) TOTAL_FV';
		}else $select = 'redeem.PROD_ID';

		$result = $this->db->query("
			select 
				".$select."
			from
				refund ref 	
				INNER JOIN pa_header paH ON ref.PA_ID = paH.PA_ID		
				INNER JOIN redemption redeem ON ref.REFUND_ID = redeem.REFUND_ID
				INNER JOIN cp_product prod ON redeem.PROD_ID = prod.SERVICE_ID	
			".$where."
			GROUP BY redeem.PROD_ID
			ORDER BY redeem.PROD_ID asc
			");
		return $result;
	}

	/*
	** QUERY ACTION FOR TBL temp_refund
	*/
	public function i_tmprefund($arr){
		if(empty($arr)) return '';		
			$this->db->insert('temp_refund',$arr);
		return $this->db->insert_id();
	}
	public function u_tmprefund($whereArr, $update){
		$this->db->where($whereArr);
		$this->db->update('temp_refund',$update);
	}
		
	/*
	** -------------- 
	QUERY ACTION FOR UPDATE STAGE/REDEEM STATUS 
	-------------- ** 
	*/
public function bulk_RECONCILED(){
		$result = $this->db->query("
			UPDATE redemption T2
			INNER JOIN reconcilation T3 ON T2.REDEEM_ID = T3.REDEEM_ID 
			SET 
				T2.STAGE = 'RECONCILED',
				T3.STAGE = 'RECONCILED'
			WHERE 
			T3.PA_ID = 0
			and T3.REFUND_ID = 0
			and T2.STAGE = 'REDEEMED'			
			and T2.PROD_ID = T3.PROD_ID 
			AND T2.VOUCHER_CODE = T3.VOUCHER_CODE
			AND T2.TRANSACTION_VALUE = T3.TRANSACTION_VALUE
			");
		return $result;
	}		
	public function bulk_REVERSED(){
		/*$result = $this->db->query("
			UPDATE refund T1
			INNER JOIN redemption T2 ON T2.REDEEM_ID = T1.REDEEM_ID
			INNER JOIN reconcilation T3 ON T3.REFUND_ID = T1.REFUND_ID
			SET 
			T1.REDEEM_STATUS = 'REVERSED',
			T2.STAGE = 'REVERSED',
			T3.STAGE = 'REVERSED'
			WHERE 
			T1.PROD_ID = T2.PROD_ID
			and T1.TRANSACTION_ID = T2.TRANSACTION_ID
			and T1.PA_ID = 0");*/
		//ELIN MODIFIED QUERY REMOVED reconcilation and change last condition from t1 to t2 
		$result = $this->db->query("
			UPDATE refund T1
			INNER JOIN redemption T2 ON T2.REDEEM_ID = T1.REDEEM_ID
			SET 
			T1.REDEEM_STATUS = 'REVERSED',
			T2.STAGE = 'REVERSED'
			WHERE 
			T1.PROD_ID = T2.PROD_ID
			and T1.TRANSACTION_ID = T2.TRANSACTION_ID
			and T2.PA_ID = 0
			and T1.PA_ID = 0");
			
		return $result;
	}
	public function bulk_INVALID(){
		$result = $this->db->query("
			UPDATE refund T1
			SET  T1.REDEEM_STATUS = 'INVALID'
			WHERE 
			T1.REDEEM_ID not in (select REDEEM_ID from redemption group by REDEEM_ID)
			and T1.PA_ID = 0");
		
		if($result == true){
			$result2 = $this->_bulk_INVALID();
		}else $result2 = $result;
		
		return $result2;
	}	
		public function _bulk_INVALID(){
			$result = $this->db->query("
				UPDATE refund T1
				INNER JOIN reconcilation T3 ON T1.REDEEM_ID = T3.REDEEM_ID
				SET T1.REDEEM_STATUS = 'INVALID'
				WHERE 
				T1.PROD_ID = T3.PROD_ID
				and T1.REDEEM_ID not in (select REDEEM_ID from redemption group by REDEEM_ID)
				and T1.PA_ID = 0");
			return $result;
		}
	
	public function bulk_VOID(){
		$result = $this->db->query("
			UPDATE refund T1
			INNER JOIN redemption T2 ON T2.REDEEM_ID = T1.REDEEM_ID
			SET 
			T1.REDEEM_STATUS = 'VOID',
			T2.STAGE = 'VOID'
			WHERE 
			T1.PROD_ID = T2.PROD_ID
			and T1.TRANSACTION_ID = T2.TRANSACTION_ID
			and T1.RECON_ID = ''");
		return $result;
	}	
	
	/**
	 * FUNCTION TO ASSIGN  - PA_ID
	 */

	public function assign_redeemPAID(){
		$result = $this->db->query("
			UPDATE redemption T2 
			INNER JOIN reconcilation T3 ON  T3.REDEEM_TBL_ID = T2.ID
			SET 
			T2.PA_ID = T3.PA_ID
			WHERE 			
			T2.REDEEM_ID = T3.REDEEM_ID 
			AND T2.PROD_ID = T3.PROD_ID
			AND T2.MERCHANT_ID = T3.MERCHANT_ID
			AND T2.BRANCH_ID = T3.BRANCH_ID
			AND T3.RECON_ID <> ''
			AND T3.PA_ID <> 0 
			AND T2.PA_ID = 0 			
		"); // AND T2.STAGE = 'RECONCILED'
		return $result;
	}

	/*public function reassign_redeemPAID($PA_ID){
		if(empty($PA_ID)) return false;
		
		$result = $this->db->query("
			UPDATE redemption T2 
			INNER JOIN reconcilation T3 ON T2.REDEEM_ID = T3.REDEEM_ID 	 
			SET 
			T2.PA_ID = T3.PA_ID
			WHERE 
			T2.PROD_ID = T3.PROD_ID
			AND T2.MERCHANT_ID = T3.MERCHANT_ID
			AND T2.BRANCH_ID = T3.BRANCH_ID
			AND T3.RECON_ID <> ''
			AND T3.PA_ID <> 0 
			AND T2.PA_ID = 0 
			AND T3.PA_ID <> T2.PA_ID
			AND T3.PA_ID < T2.PA_ID
			AND T2.PA_ID = ".$PA_ID."
		"); 
		return $result;
	}*/

	public function assign_reconPA_to_redeemPA($PA_ID){
		if(empty($PA_ID)) return false;
		
		$result = $this->db->query("
			UPDATE redemption T2 
			INNER JOIN reconcilation T3 ON T3.REDEEM_TBL_ID = T2.ID 	 
			SET 
			T2.PA_ID = T3.PA_ID
			WHERE 
			T3.PA_ID = ".$PA_ID." 
			AND T2.PA_ID = 0 	
			AND T2.REFUND_ID = 0 		 
		"); 
		return $result;
	}


	/**
	 * FUNCTION TO ASSIGN  - REDEEM_TBL_ID
	 */
	
	public function assign_redeemTblID(){
		/*
			AND T2.ID not in (select REDEEM_TBL_ID from reconcilation where REDEEM_TBL_ID <> 0 group by REDEEM_TBL_ID)

			AND T2.ID not in (select REDEEM_TBL_ID from (SELECT REDEEM_TBL_ID 
									FROM   reconcilation 
									WHERE  REDEEM_TBL_ID <> 0
									group by REDEEM_TBL_ID) t )
		*/
		$result = $this->db->query("
			UPDATE reconcilation T3 
			INNER JOIN redemption T2 ON T2.REDEEM_ID = T3.REDEEM_ID  	
			SET 
			T3.REDEEM_TBL_ID = T2.ID, 
			T3.STAGE = T2.STAGE
			WHERE 
			T2.PROD_ID = T3.PROD_ID
			AND T2.VOUCHER_CODE = T3.VOUCHER_CODE
			AND T2.TRANSACTION_VALUE = T3.TRANSACTION_VALUE
			AND T3.REDEEM_TBL_ID = 0
			AND T3.PA_ID = 0 	
			AND T2.PA_ID = 0
			AND T2.ID not in (select REDEEM_TBL_ID from (SELECT REDEEM_TBL_ID 
									FROM   reconcilation 
									WHERE  REDEEM_TBL_ID <> 0
									group by REDEEM_TBL_ID) t )
		");
		return $result;
	}
	
	public function get_duplicateRedeemTBLID(){	
		$result = $this->db->query("
			SELECT recon.REDEEM_TBL_ID, COUNT(recon.id) total, min(recon.id) MIN_ID
			FROM reconcilation recon,
			payment_cutoff pcf
			where 
			pcf.merchant_id = recon.merchant_id
			and recon.REDEEM_TBL_ID <> 0
			and recon.PA_ID = 0
			and pcf.DIGITALSETTLEMENTTYPE = ''
			GROUP BY recon.REDEEM_TBL_ID desc
			HAVING COUNT(recon.id) > 1
		"); 
		return $result;
	}

	public function noredeemID_reconPA($MERCHANT_ID){
		if(empty($MERCHANT_ID)) return false;

		$result = $this->db->query("
		select 
			recon.MERCHANT_ID, recon.PA_ID
		from 
			reconcilation recon
		where
			recon.PA_ID <> 0
			and recon.REDEEM_TBL_ID = 0
			and recon.MERCHANT_ID = ".$MERCHANT_ID."
		group by recon.MERCHANT_ID, recon.PA_ID
		"); 
		return  $result;
	}
	public function get_clean_redeemTBLID($MERCHANT_ID, $PA_ID){
		if(empty($MERCHANT_ID) || empty($PA_ID)) return false;

		$result = $this->db->query("
		select 
			redeem.id as re_redeem_id, redeem.STAGE re_stage, recon.*
		from 
			reconcilation recon,
			redemption redeem
		where
			redeem.REDEEM_ID = recon.REDEEM_ID
			and redeem.PROD_ID = recon.PROD_ID
			and redeem.BRANCH_ID = recon.BRANCH_ID
			and redeem.PA_ID = recon.PA_ID
			and recon.REDEEM_TBL_ID = 0
			and recon.MERCHANT_ID = ".$MERCHANT_ID."
			and recon.PA_ID = ".$PA_ID."
		order by redeem.id asc
		"); 
		return  $result;
	}
	
	public function checkRecon_RedeemTBLID($REDEEM_TBL_ID){
		if(empty($REDEEM_TBL_ID)) return false;
		
		$result = $this->db->query("
			 SELECT *
			 FROM 
			 	reconcilation T3
			 WHERE 
			 T3.REDEEM_TBL_ID = ".$REDEEM_TBL_ID."			 
			 and T3.PA_ID = 0
			 and T3.ID NOT IN
			 ( SELECT MIN(T3_3.ID)
				 FROM reconcilation T3_3
				 WHERE 
				 T3_3.REDEEM_TBL_ID = ".$REDEEM_TBL_ID."
				 GROUP BY T3_3.REDEEM_TBL_ID
			 ) 
		"); 
		return $result;
	}

	public function removeDuplicate_RedeemTBLID($REDEEM_TBL_ID, $MIN_ID){
		if(empty($REDEEM_TBL_ID)) return false;
		
		$this->db->where('ID <>',$MIN_ID);
		$this->db->where('PA_ID',0);
		$this->db->where('REDEEM_TBL_ID',$REDEEM_TBL_ID);
		
			$update['REDEEM_TBL_ID'] = 0;
			$update['STAGE'] = '';
		$this->db->update('reconcilation',$update);
		return $this->db->last_query();
	}
	
	/**
	 * FUNCTION TO CORRECT RECON TBL -> OLD PROCESSED PA WITH MISSING REDEEM_TBL_ID
	 */
	public function correct_duplicateRedeemTBLID($PA_ID){
		if(empty($PA_ID)) return false;	
		$result = $this->db->query("
		SELECT T3.REDEEM_TBL_ID, T3.REDEEM_ID, T3.PROD_ID, T3.STAGE, T3.VOUCHER_CODE, T3.TRANSACTION_VALUE, 
		T2.PA_ID RE_PAID, T2.STAGE RE_STAGE
			from reconcilation T3 
			INNER JOIN redemption T2 ON T2.ID = T3.REDEEM_TBL_ID  	
		WHERE 
			T3.STAGE = T2.STAGE
			AND T3.PA_ID <> T2.PA_ID
			and T2.PA_ID <> 0
			AND T3.PA_ID in (".$PA_ID.")
		"); 
		return $result;
	}
	
	function getExportData($PA_ID){
		// $this->db->from('redemption');	
		// $this->db->limit(10);
		// $data =  $this->db->get()->result();

		$result = $this->db->query("
		select 
		mer.CP_ID as 'DIGITALID',
		mer.LegalName as 'MERCHANTNAME',
		mer.TIN as 'TINNUMBER',
		br.BRANCH_ID as 'BRANCHID',
		br.BRANCH_NAME as 'BRANCHNAME',
		redeem.REDEEM_ID as 'REDEEMID',
		redeem.MERCHANT_ID as 'MERCHANTID',
		redeem.BRANCH_ID as 'BRANCHID',
		redeem.POS_ID as 'POSID',
		redeem.VOUCHER_CODE as 'VOUCHERCODE',
		redeem.TRANSACTION_DATE_TIME as 'REDEEMDATE',
		redeem.TRANSACTION_VALUE as 'AMOUNT',
		redeem.STAGE as 'REDEEMSTATUS',
		redeem.POS_TXN_ID  as 'POSTXNID',
		paH.PA_ID as 'PAYMENTADVICEID',
		paH.REIMBURSEMENT_DATE as 'PADATE',	
		paH.ExpectedDueDate as 'PADUEDATE',			
		prod.SERVICE_NAME as 'PRODUCT'
	from
		redemption redeem
		INNER join cp_product prod on prod.SERVICE_ID = redeem.PROD_ID
		INNER join branches br ON br.MERCHANT_ID = redeem.MERCHANT_ID	
		INNER join cp_merchant mer ON mer.CP_ID = br.CP_ID
		INNER join pa_header paH on  paH.PA_ID	= redeem.PA_ID
	WHERE	
		br.BRANCH_ID = redeem.BRANCH_ID
		and paH.MERCHANT_ID	= redeem.MERCHANT_ID
		AND paH.vatcond <> ''
		AND redeem.PA_ID =  ". $PA_ID ."
	GROUP BY redeem.ID
	order by redeem.MERCHANT_ID, redeem.BRANCH_ID asc"); 

		return $result->result();
	}

	function getLastPaDate() {
		$query = $this->db->query("
			select DATE(REIMBURSEMENT_DATE) as `date`
			from pa_header
			where GENERATED = 1
			ORDER BY REIMBURSEMENT_DATE DESC
			limit 1
			"); 

		return $query->result();
	}

	function getPaIdFromLastPaDate($date){
		$query = $this->db->query("
				select PA_ID from pa_header
				where DATE_CREATED
				like '%" . $date . "%'
				and GENERATED = 1
			"); 

		return $query->result();
	}
	

}

