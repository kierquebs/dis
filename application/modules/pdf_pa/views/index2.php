<html>
<head>
<base href="<?php echo base_url();?>"/>
<link rel="stylesheet" href="assets/css/mpdf2.css?<?php echo time(); ?>">  
</head>
<body>
<div class="main">
	<div class="row center">
		<p class="title">Pluxee Philippines Incorporated</p>
		<p>8747 Paseo de Roxas Street, 11TH Floor, B.A. Lepanto Condominium, Makati City (1200), Metro Manila, Philippines </p>
		<p>Tel. no: 8689-4700. Fax no: 86894777</p>
		<p>TIN: 223-183-726-00000 </p>
	</div>
	<?php foreach($merchantInfo  as $mer_row):
		$REIMBURSEMENT_DATE = $mer_row->REIMBURSEMENT_DATE;
		$EXPECTED_DUEDATE = $mer_row->ExpectedDueDate;
		$REIMBURSEMENT_USER = $mer_row->full_name;
		//$date_printed = $this->my_lib->setDate($mer_row->DATE_CREATED, FALSE, FALSE);
		
		$PayeeName = $mer_row->PayeeName;
		$MeanofPayment = $mer_row->MeanofPayment;
		$BankName = $mer_row->BankName;
		$BankAccountNumber = $mer_row->BankAccountNumber;
		$Address = $mer_row->Address;
		
		if($mer_row->AffiliateGroupCode <> $mer_row->brAffCode){
			$whereAFFCODE['CP_ID'] = $mer_row->CP_ID;
			$whereAFFCODE['AffiliateGroupCode'] = $mer_row->brAffCode;
			$getAFFCODE =  $this->Sys_model->v_agreement($whereAFFCODE, false);	
		
			if($getAFFCODE->num_rows() <> 0){ 
				$rowAFFCODE = $getAFFCODE->row();	
				$PayeeName = $rowAFFCODE->PayeeName;
				$MeanofPayment = $rowAFFCODE->MeanofPayment;
				$BankName = $rowAFFCODE->BankName; 
				$BankAccountNumber = $rowAFFCODE->BankAccountNumber;
				$Address = $rowAFFCODE->Address;
			}
		}
		
		$PA_NUM = $this->my_lib->paNumber($mer_row->PA_ID);
		$LegalName = $mer_row->LegalName;
		$TIN = $mer_row->TIN;
		$TradingName = $mer_row->TradingName;
		
	?>
	<div class="row center">
		<p class="title">Payment Advice # <?php echo $PA_NUM?> </p>
	</div>
	
	<div class="row">
		<table class="tbl">
		<tr>
			<td class="tbl_title">Advice To:</td>
			<td class="tbl_title">Payee Name:</td>
			<td><?php echo $PayeeName?></td>
		</tr>	
		<tr>
			<td><?php echo $mer_row->LegalName?></td>
			<td colspan="2"></td>
		</tr>
		<tr>
			<td>T.I.N.: <?php echo $mer_row->TIN?></td>
			<td class="tbl_title">Mode of payment:</td>
			<td><?php echo $MeanofPayment?></td>
		</tr>		
		<tr>
			<td><?php echo $mer_row->TradingName?></td>
			<td colspan="2"></td>
		</tr>
		<tr>
			<td><?php echo $Address?></td>
			<td class="tbl_title">Bank Name:</td>
			<td><?php echo $BankName?></td>
		</tr>
		<tr>
			<td class="tbl_title">Reimbursement date: <span class="toRight"><?php echo $EXPECTED_DUEDATE?></span></td>
			<td class="tbl_title">Acct number:</td>
			<td><?php echo $BankAccountNumber?></td>
		</tr>
		</table>
	</div>
	<?php endforeach;?>
	
	<div class="block_row">
	<?php 	
	for ($xy = 1; $xy <= $totalNewPage; $xy++) :
	?>
		<table class="tbl_2 <?php if(($xy == 1 || $xy % 3 == 0) && $branchNum > 15) echo 'page-break';?>"> 
			<?php if(($xy == 1 || $xy % 2 == 0) && !empty($branchLi[$xy])):?>
			<thead>
				<tr>
					<td width=120>Branch</td>
					<td width=20 class="center">Rate %</td>
					<td width=60 class="center">No. of Code/s</td>
					<td width=60 class="number">Total Face Value</td>
					<td width=60 class="number">Total Refund</td>
					<td width=60 class="number">Marketing Fee</td>
					<td width=60 class="number">VAT</td>
					<td width=60 class="number"">Net Due</td>
				</tr>
			</thead>
			<?php endif;?>
			<tbody>
			<?php 
			foreach($branchLi[$xy]  as $br_row):
			?>
			<tr>
				<td width=120><?php echo $br_row->BRANCH_ID.' - '.$br_row->BRANCH_NAME?></td>
				<td width=20 class="center"><?php echo number_format($br_row->RATE, 2)?></td>
				<td width=60 class="center"><?php echo number_format($br_row->NUM_PASSES)?></td>
				<td width=60 class="number"><?php echo number_format($br_row->TOTAL_FV, 2)?></td>
				<td width=60 class="number"><?php echo number_format($br_row->TOTAL_REFUND, 2)?></td>
				<td width=60 class="number"><?php echo number_format($br_row->MARKETING_FEE, 2) ?></td>
				<td width=60 class="number"><?php echo number_format($br_row->VAT, 2)?></td>
				<td width=60 class="number"><?php echo number_format($br_row->NET_DUE, 2)?></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endfor;?>
	</div>
<?php 
$sumREFV = 0;
if($refundRow <> 0):
	foreach($refundLi as $ref_row): 
		$sumREFV += $ref_row->TOTALREF_FV;
	endforeach;	
endif;
?>
	
	<div class="block_row">
		<p>Summary:</p>
		<table class="tbl_2">
			<thead>
				<tr>
					<td width=120>Service</td>
					<td class="number">Adjustment</td>
					<td class="number">Marketing Fee</td>
					<td width=100 class="number">Total Face Value</td>
					<td class="number center">VAT</td>
					<td class="number center">Net Due</td>
				</tr>
			</thead>
			<tbody>
			<?php 
			$prod_arr = array(); 			
			$sumVAT = $sumND = $sumMF = $sumFV = 0;
			foreach($serviceLi as $sr_row):
				$totalFV = $sr_row->TOTAL_FV;
				$TOTAL_REFUND = $sr_row->TOTAL_REFUND;				
				$totalMFV = $totalFV - $TOTAL_REFUND; //$totalMFV = $totalFV;
				$vatcond = $this->my_lib->checkVAT($sr_row->vatcond);
				$percentMF = $this->my_lib->convertMFRATE($sr_row->MerchantFee, TRUE); 								
				$MF = $this->my_lib->computeMF($totalMFV, $percentMF, '', FALSE); //$MF = $this->my_lib->computeMF($totalMFV, $percentMF, 2, false); 
				$VAT = $this->my_lib->computeVAT($totalMFV, $percentMF, $vatcond, FALSE);
				$NET_DUE = $this->my_lib->computeNETDUE($totalMFV, $percentMF, $vatcond, FALSE);
			?>
			<tr>
				<td><?php echo $sr_row->SERVICE_NAME ?></td>
				<td class="number"><?php echo number_format($TOTAL_REFUND, 2)?></td>
				<td class="number"><?php echo number_format($MF, 2)?></td>
				<td class="number"><?php echo number_format($totalFV, 2)?></td>
				<td class="number"><?php echo number_format($VAT, 2) ?></td>
				<td class="number"><?php echo number_format($NET_DUE, 2)?></td>
			</tr>
			<?php 
				$arr['name'] = $sr_row->SERVICE_NAME;
				$arr['fv'] = number_format($totalFV, 2);
				$sumFV += $totalFV;				
				$sumMF += $MF;
				$sumND +=  $NET_DUE;
				$sumVAT += $VAT;
			$prod_arr[] = $arr;
			endforeach;					
			?>
			</tbody>
		</table>
	</div>
	
	<div class="row">
		<div class="sum_left">
			<p>Received by:</p>
			<p class="sign_box">Signature over Printed Name</p>
		</div>
		<div class="sum_right">
			<table class="tbl_3">
				<thead>
					<tr>
						<td>Service</td>
						<td class="number">Face Value</td>
					</tr>
				</thead>				
				<tbody>
				<?php 
				for($zz=0;$zz<count($prod_arr);$zz++):					
				?>
				<tr>
					<td><?php echo $prod_arr[$zz]['name'] ?></td>
					<td class="number"><?php echo $prod_arr[$zz]['fv'] ?></td>
				</tr>
				<?php endfor;?>		
				<tr>
					<td>Refund Adjustment:</td>
					<td class="number">(<?php echo number_format($sumREFV, 2);?>)</td>
				</tr>			
				</tbody>
			</table>
			<table class="tbl_3">		
				<tr> 
					<td>Total Face Value:</td>
					<td class="number"><?php echo number_format(($sumFV - $sumREFV) , 2);?></td>
				</tr>
				<tr>
					<td>(A)Marketing Fee:</td>
					<td class="number"><?php echo number_format($sumMF, 2);?></td>
				</tr>
				<tr>
					<td>(B)Delivery Fee:</td>
					<td class="number"><?php echo number_format(0, 2);?></td>
				</tr>
				<tr>
					<td>(C)Voucher Pick Up Fee:</td>
					<td class="number"><?php echo number_format(0, 2);?></td>
				</tr>
				<tr>
					<td>(D)Other Fees:</td>
					<td class="number"><?php echo number_format(0, 2);?></td>
				</tr>
				<tr>
					<td>(E)Other Non Vat Fees:</td>
					<td class="number"><?php echo number_format(0, 2);?></td>
				</tr>
				<tr>
					<td>VAT 12% (A+B+C+D):</td>
					<td class="number"><?php echo number_format($sumVAT, 2);?></td>
				</tr>
				<tr>
					<td>Total Net Due:</td>
					<td class="number"><?php echo number_format($sumND, 2);?></td>
				</tr>
			</table>
		</div>
		<div class="sum_center"> <p>Date: <span>______________________</span></p></div>
	</div>	
	
	<div class="row">
		<table class="tbl">
		<tr>
			<td class="tbl_title">Generated by:</td>
			<td><?php echo $REIMBURSEMENT_USER?></td>
			<td class="tbl_title">Printed by:</td>
			<td><?php echo $data_user->full_name;?></td>
		</tr>	
		<tr>
			<td class="tbl_title">Date Generated:</td>
			<td><?php echo $REIMBURSEMENT_DATE?></td>
			<td class="tbl_title">Date Printed: </td>
			<td><?php echo $date_printed?></td>
		</tr>
		</table>
	</div>
	
	<div class="row">
		<p class="center">
		<?php if($copy == false):?>
			ORIGINAL COPY OF PAYMENT ADVICE
		<?php else:?>		
			PRINTED COPY OF PAYMENT ADVICE
		<?php endif;?>	
		</p>
	</div>
</div>

<br />
<br />

<?php if($refundRow <> 0):?>
<div class="main page-break-before">
	<div class="row center">
		<p class="title">Pluxee Philippines Incorporated</p>
		<p>8747 Paseo de Roxas Street, 11TH Floor, B.A. Lepanto Condominium, Makati City (1200), Metro Manila, Philippines </p>
		<p>Tel. no: 8689-4700. Fax no: 86894777</p>
		<p>TIN: 223-183-726-00000 </p>
	</div>
	<div class="row center">
		<p class="title">Payment Advice # <?php echo $PA_NUM;?> - Refund Adjustment details </p>
	</div>	
	<div class="row">
		<table class="tbl">
		<tr>
			<td class="tbl_title">Advice To:</td>
			<td class="tbl_title">Payee Name:</td>
			<td><?php echo $PayeeName?></td>
		</tr>	
		<tr>
			<td><?php echo $LegalName?></td>
			<td colspan="2"></td>
		</tr>
		<tr>
			<td>T.I.N.: <?php echo $TIN?></td>
			<td class="tbl_title">Mode of payment:</td>
			<td><?php echo $MeanofPayment?></td>
		</tr>		
		<tr>
			<td><?php echo $TradingName?></td>
			<td colspan="2"></td>
		</tr>
		<tr>
			<td><?php echo $Address?></td>
			<td class="tbl_title">Bank Name:</td>
			<td><?php echo $BankName?></td>
		</tr>
		<tr>
			<td class="tbl_title">Reimbursement date: <span class="toRight"><?php echo $EXPECTED_DUEDATE?></span></td>
			<td class="tbl_title">Acct number:</td>
			<td><?php echo $BankAccountNumber?></td>
		</tr>
		</table>
	</div>
	
	<div class="block_row">
		<table class="tbl_2">
			<thead>
				<tr>
					<td>Branch</td>
					<td class="number">Total Passes</td>
					<td class="number">Total Refund</td>
				</tr>
			</thead>
			<tbody>
			<?php $sumREFV = 0; foreach($refundLi as $ref_row):?>
			<tr>
				<td><?php echo $ref_row->BRANCH_NAME ?></td>
				<td class="number"><?php echo $ref_row->NUM_PASSES?></td>
				<td class="number"><?php echo number_format($ref_row->TOTALREF_FV, 2)?></td>
			</tr>
			<?php 
			$sumREFV += $ref_row->TOTALREF_FV;
			endforeach;					
			?>
			</tbody>
		</table>
	</div>

	<div class="block_row">
		<p>Summary:</p>
		<table class="tbl_2">
			<thead>
				<tr>
					<td width=120>Service</td>
					<td class="number">Adjustment</td>
					<td class="number">Marketing Fee</td>
					<td class="number center">VAT</td>
					<td class="number center">Net Due</td>
				</tr>
			</thead>
			<tbody>
			<?php 
			foreach($serviceREF as $sref_row):
				$totalREFFV = $sref_row->TOTAL_FV;
			?>
			<tr>
				<td><?php echo $sref_row->SERVICE_NAME ?></td>
				<td class="number"><?php echo number_format($totalREFFV, 2)?></td>
				<td class="number"></td>
				<td class="number"></td>
				<td class="number"><?php echo number_format($totalREFFV, 2)?></td>
			</tr>
			<?php endforeach;?>
			</tbody>
		</table>
	</div>
	
	<div class="row">
		<table class="tbl">
		<tr>
			<td class="tbl_title">Generated by:</td>
			<td><?php echo $REIMBURSEMENT_USER?></td>
			<td class="tbl_title">Printed by:</td>
			<td><?php echo $data_user->full_name;?></td>
		</tr>	
		<tr>
			<td class="tbl_title">Date Generated:</td>
			<td><?php echo $REIMBURSEMENT_DATE?></td>
			<td class="tbl_title">Date Printed: </td>
			<td><?php echo $date_printed?></td>
		</tr>
		</table>
	</div>
	
	<div class="row">
		<p class="center">
		<?php if($copy == false):?>
			ORIGINAL COPY OF PAYMENT ADVICE
		<?php else:?>		
			PRINTED COPY OF PAYMENT ADVICE
		<?php endif;?>	
		</p>
	</div>
</div>
<?php endif;?>

</body>
