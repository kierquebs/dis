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
	?>
	<div class="row center">
		<p class="title">Payment Advice # <?php echo $this->my_lib->paNumber($mer_row->PA_ID);?> </p>
	</div>
	
	<div class="row">
		<table class="tbl">
		<tr>
			<td class="tbl_title">Advice To:</td>
			<td class="tbl_title">Payee Name:</td>
			<td><?php echo $mer_row->PayeeName?></td>
		</tr>	
		<tr>
			<td><?php echo $mer_row->LegalName?></td>
			<td colspan="2"></td>
		</tr>
		<tr>
			<td>T.I.N.: <?php echo $mer_row->TIN?></td>
			<td class="tbl_title">Mode of payment:</td>
			<td><?php echo $mer_row->MeanofPayment?></td>
		</tr>		
		<tr>
			<td><?php echo $mer_row->TradingName?></td>
			<td colspan="2"></td>
		</tr>
		<tr>
			<td><?php echo $mer_row->Address?></td>
			<td class="tbl_title">Bank Name:</td>
			<td><?php echo $mer_row->BankName?></td>
		</tr>
		<tr>
			<td class="tbl_title">Reimbursement date: <span class="toRight"><?php echo $EXPECTED_DUEDATE?></span></td>
			<td class="tbl_title">Acct number:</td>
			<td><?php echo $mer_row->BankAccountNumber?></td>
		</tr>
		</table>
	</div>
	<?php endforeach;?>
	<div class="block_row">
		<table class="tbl_2">
			<thead>
				<tr>
					<td width=120>Branch</td>
					<td width=100>Recon ID</td>
					<td class="center">Rate %</td>
					<td class="center">No. of Code/s</td>
					<td width=80 class="number">Total Face Value</td>
					<td class="number">Marketing Fee</td>
					<td class="center">VAT</td>
					<td class="center">Net Due</td>
				</tr>
			</thead>
			<tbody>
			<?php 
			$sumVAT = $sumND = $sumMF = $sumFV = 0;
			foreach($branchLi  as $br_row):
			?>
			<tr>
				<td><?php echo $br_row->BRANCH_ID.' - '.$br_row->BRANCH_NAME?></td>
				<td><?php echo $br_row->RECON_ID?></td>
				<td class="center"><?php echo number_format($br_row->RATE, 2)?></td>
				<td class="center"><?php echo number_format($br_row->NUM_PASSES)?></td>
				<td class="number"><?php echo number_format($br_row->TOTAL_FV, 2)?></td>
				<td class="number"><?php echo $br_row->MARKETING_FEE ?></td>
				<td class="number"><?php echo number_format($br_row->VAT, 2)?></td>
				<td class="number"><?php echo number_format($br_row->NET_DUE, 2)?></td>
			</tr>
			<?php 			
				$sumFV += $br_row->TOTAL_FV;
				$sumMF += $br_row->MARKETING_FEE;
				$sumND +=  $br_row->NET_DUE;
				$sumVAT += $br_row->VAT;
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
					<td class="number">Marketing Fee</td>
					<td width=100 class="number">Total Face Value</td>
					<td class="number center">VAT</td>
					<td class="number center">Net Due</td>
				</tr>
			</thead>
			<tbody>
			<?php 
			$prod_arr = array();
			foreach($serviceLi as $sr_row):
				$totalFV = $sr_row->TOTAL_FV;
				$vatcond = $this->my_lib->checkVAT($sr_row->vatcond);
				$percentMF = $this->my_lib->convertMFRATE($sr_row->MerchantFee, true); 
				$MF = $this->my_lib->computeMF($totalFV, $percentMF); 
				$VAT = $this->my_lib->computeVAT($totalFV, $percentMF, $vatcond);
				$NET_DUE = $this->my_lib->computeNETDUE($totalFV, $percentMF, $vatcond);
			?>
			<tr>
				<td><?php echo $sr_row->SERVICE_NAME ?></td>
				<td class="number"><?php echo $MF?></td>
				<td class="number"><?php echo number_format($totalFV, 2)?></td>
				<td class="number"><?php echo $VAT ?></td>
				<td class="number"><?php echo number_format($NET_DUE, 2)?></td>
			</tr>
			<?php 
				$arr['name'] = $sr_row->SERVICE_NAME;
				$arr['fv'] = number_format($totalFV, 2);
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
				</tbody>
			</table>
			<table class="tbl_3">		
				<tr>
					<td>Total Face Value:</td>
					<td class="number"><?php echo number_format($sumFV, 2);?></td>
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

</body>
