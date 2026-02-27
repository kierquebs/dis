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
		<p>Tel. no: 689-4700. Fax no: 6894777</p>
		<p>TIN: 223-183-726-00000 </p>
	</div>
	<?php foreach($merchantInfo  as $mer_row):?>
	<div class="row center">
		<p class="title">Payment Advice #<?php echo $mer_row->PA_ID?> </p>
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
			<td class="tbl_title">Mean of payment:</td>
			<td><?php echo $mer_row->TIN?></td>
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
			<td class="tbl_title">Reimbursement date: <span class="toRight"><?php echo $mer_row->REIMBURSEMENT_DATE?></span></td>
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
					<td class="center">Voucher Code</td>
					<td width=80 class="number">Total Face Value</td>
					<td class="number">Marketing Fee</td>
					<td class="center">VAT</td>
					<td class="center">Net Due</td>
				</tr>
			</thead>
			<tbody>
			<?php 
			$prod_arr = array();
			$totalND = $totalVAT = $totalMF = $totalFV = 0;
			foreach($branchLi  as $br_row):
			$prod_br['id'] = $br_row->prod_id;
			$prod_br['fv'] = number_format($br_row->TOTAL_FV, 2);
			$prod_br['vat'] = number_format($br_row->VAT, 2);
			$prod_br['nd'] = number_format($br_row->NET_DUE, 2);
			$prod_br['mf'] = $br_row->MARKETING_FEE;
			?>
			<tr>
				<td><?php echo $br_row->br_id.' - '.$br_row->br_name?></td>
				<td><?php echo $br_row->recon_id?></td>
				<td class="center"><?php echo number_format($br_row->RATE, 2)?></td>
				<td class="center"><?php echo $br_row->voucher_code?></td>
				<td class="number"><?php echo $prod_br['fv']?></td>
				<td class="number"><?php echo $prod_br['mf'] ?></td>
				<td class="number"><?php echo $prod_br['vat']?></td>
				<td class="number"><?php echo $prod_br['nd']?></td>
			</tr>
			<?php 
			$prod_arr[] = $prod_br;
			
			$totalFV += $br_row->TOTAL_FV;
			$totalMF += $br_row->MARKETING_FEE;
			$totalVAT += $br_row->VAT;
			$totalND += $br_row->NET_DUE;
			endforeach;					
			asort($prod_arr);
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
					<td>Pieces</td>
					<td class="number">Marketing Fee</td>
					<td width=100 class="number">Total Face Value</td>
					<td class="number center">VAT</td>
					<td class="number center">Net Due</td>
				</tr>
			</thead>
			<tbody>
			<?php 
			$checkArr = array();
			for($xx=0;$xx<count($prod_arr);$xx++):			
			?>
			<tr>
				<td><?php echo $prod_arr[$xx]['id'] ?></td>
				<td></td>
				<td class="number"><?php echo $prod_arr[$xx]['mf'] ?></td>
				<td class="number"><?php echo $prod_arr[$xx]['fv'] ?></td>
				<td class="number"><?php echo $prod_arr[$xx]['vat'] ?></td>
				<td class="number"><?php echo $prod_arr[$xx]['nd'] ?></td>
			</tr>
			<?php endfor;?>
			</tbody>
		</table>
	</div>
	
	<div class="row">
		<div class="sum_left">
			<p>Passes Verified By:</p>
			<p class="sign_box">Reimbursement</p>
			<p>Received by:</p>
			<p class="sign_box">Signature over Printed Name</p>
		</div>
		<div class="sum_right">
			<table class="tbl_3">
				<thead>
					<tr>
						<td>Service</td>
						<td width=20>Pieces</td>
						<td class="number">Face Value</td>
					</tr>
				</thead>				
				<tbody>
				<?php 
				for($zz=0;$zz<count($prod_arr);$zz++):					
				?>
				<tr>
					<td><?php echo $prod_arr[$zz]['id'] ?></td>
					<td></td>
					<td class="number"><?php echo $prod_arr[$zz]['fv'] ?></td>
				</tr>
				<?php endfor;?>				
				</tbody>
			</table>
			<table class="tbl_3">		
				<tr>
					<td>Total Face Value:</td>
					<td class="number"><?php echo number_format($totalFV, 2);?></td>
				</tr>
				<tr>
					<td>(A)Marketing Fee:</td>
					<td class="number"><?php echo number_format($totalMF, 2);?></td>
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
					<td class="number"><?php echo number_format($totalVAT, 2);?></td>
				</tr>
				<tr>
					<td>Total Net Due:</td>
					<td class="number"><?php echo number_format($totalND, 2);?></td>
				</tr>
			</table>
		</div>
		<div class="sum_center"> <p>Date: <span>______________________</span></p></div>
	</div>	
	
	<div class="row">
		<table class="tbl">
		<tr>
			<td class="tbl_title">Printed by:</td>
			<td></td>
		</tr>	
		<tr>
			<td class="tbl_title">Date printed:</td>
			<td><?php echo $this->my_lib->setDate('','',true)?></td>
		</tr>
		</table>
	</div>
</div>

</body>
