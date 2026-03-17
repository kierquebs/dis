<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body style="font-family:sans-serif; font-size:11px; margin:10px 15px; padding:0; color:#000;">

<?php foreach($merchantInfo as $mer_row):
	$REIMBURSEMENT_DATE = $mer_row->REIMBURSEMENT_DATE;
	$EXPECTED_DUEDATE   = $mer_row->ExpectedDueDate;
	$REIMBURSEMENT_USER = $mer_row->full_name;

	$PayeeName         = $mer_row->PayeeName;
	$MeanofPayment     = $mer_row->MeanofPayment;
	$BankName          = $mer_row->BankName;
	$BankAccountNumber = $mer_row->BankAccountNumber;
	$Address           = $mer_row->Address;

	if($mer_row->AffiliateGroupCode <> $mer_row->brAffCode){
		$whereAFFCODE['CP_ID']              = $mer_row->CP_ID;
		$whereAFFCODE['AffiliateGroupCode'] = $mer_row->brAffCode;
		$getAFFCODE = $this->Sys_model->v_agreement($whereAFFCODE, false);
		if($getAFFCODE->num_rows() <> 0){
			$rowAFFCODE        = $getAFFCODE->row();
			$PayeeName         = $rowAFFCODE->PayeeName;
			$MeanofPayment     = $rowAFFCODE->MeanofPayment;
			$BankName          = $rowAFFCODE->BankName;
			$BankAccountNumber = $rowAFFCODE->BankAccountNumber;
			$Address           = $rowAFFCODE->Address;
		}
	}

	$PA_NUM      = $this->my_lib->paNumber($mer_row->PA_ID);
	$LegalName   = $mer_row->LegalName;
	$TIN         = $mer_row->TIN;
	$TradingName = $mer_row->TradingName;
?>

<!-- ============================================================ -->
<!-- COMPANY HEADER                                               -->
<!-- ============================================================ -->
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td align="center" style="padding-bottom:2px;">
      <strong style="font-size:15px;">Pluxee Philippines Incorporated</strong>
    </td>
  </tr>
  <tr>
    <td align="center" style="font-size:10px;">
      8747 Paseo de Roxas Street, 11TH Floor, B.A. Lepanto Condominium, Makati City (1200), Metro Manila, Philippines
    </td>
  </tr>
  <tr>
    <td align="center" style="font-size:10px;">Tel. no: 8689-4700. Fax no: 86894777</td>
  </tr>
  <tr>
    <td align="center" style="font-size:10px; padding-bottom:6px;">TIN: 223-183-726-00000</td>
  </tr>
  <tr>
    <td align="center" style="padding:8px 0 10px 0;">
      <strong style="font-size:14px;">Payment Advice # <?php echo $PA_NUM; ?></strong>
    </td>
  </tr>
</table>

<!-- ============================================================ -->
<!-- MERCHANT INFO SECTION (2-column)                            -->
<!-- ============================================================ -->
<table width="100%" border="0" cellspacing="0" cellpadding="4" style="margin-bottom:8px;">
  <tr>
    <!-- LEFT: Advice To details -->
    <td width="52%" valign="top" style="border:0; padding-right:10px;">
      <table width="100%" border="0" cellspacing="0" cellpadding="2">
        <tr>
          <td style="font-weight:bold; padding-bottom:2px;">Advice To:</td>
        </tr>
        <tr>
          <td style="padding-bottom:2px;"><?php echo $mer_row->LegalName; ?></td>
        </tr>
        <tr>
          <td style="font-weight:bold; padding-bottom:2px;">T.I.N.: <?php echo $mer_row->TIN; ?></td>
        </tr>
        <tr>
          <td style="padding-bottom:2px;"><?php echo $mer_row->TradingName; ?></td>
        </tr>
        <tr>
          <td style="padding-bottom:6px;"><?php echo $Address; ?></td>
        </tr>
        <tr>
          <td style="padding-bottom:2px;">
            <strong>Reimbursement date:</strong>&nbsp;&nbsp;<?php echo $EXPECTED_DUEDATE; ?>
          </td>
        </tr>
      </table>
    </td>

    <!-- RIGHT: Payee / Bank details -->
    <td width="48%" valign="top">
      <table width="100%" border="0" cellspacing="0" cellpadding="4">
        <tr>
          <td width="45%" style="font-weight:bold; vertical-align:top;">Payee Name:</td>
          <td width="55%" style="vertical-align:top;"><?php echo $PayeeName; ?></td>
        </tr>
        <tr>
          <td style="font-weight:bold; vertical-align:top;">Mode of payment:</td>
          <td style="vertical-align:top;"><?php echo $MeanofPayment; ?></td>
        </tr>
        <tr>
          <td style="font-weight:bold; vertical-align:top;">Bank Name:</td>
          <td style="vertical-align:top;"><?php echo $BankName; ?></td>
        </tr>
        <tr>
          <td style="font-weight:bold; vertical-align:top;">Acct number:</td>
          <td style="vertical-align:top;"><?php echo $BankAccountNumber; ?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<?php endforeach; ?>

<!-- ============================================================ -->
<!-- BRANCH DETAIL TABLE                                          -->
<!-- ============================================================ -->
<?php for ($xy = 1; $xy <= $totalNewPage; $xy++): ?>
<table width="100%" border="0" cellspacing="0" cellpadding="4"
  style="margin-bottom:4px; <?php if(($xy == 1 || $xy % 3 == 0) && $branchNum > 15) echo 'page-break-after:always;'; ?>">
  <?php if(($xy == 1 || $xy % 2 == 0) && !empty($branchLi[$xy])): ?>
  <tr style="font-weight:bold; border-bottom:1px solid #333;">
    <td>Branch</td>
    <td width="35" align="center">Rate<br/>%</td>
    <td width="55" align="center">No. of<br/>Code/s</td>
    <td width="70" align="right">Total Face<br/>Value</td>
    <td width="60" align="right">Total<br/>Refund</td>
    <td width="65" align="right">Marketing<br/>Fee</td>
    <td width="40" align="right">VAT</td>
    <td width="60" align="right">Net Due</td>
  </tr>
  <?php endif; ?>
  <?php foreach($branchLi[$xy] as $br_row): ?>
  <tr>
    <td><?php echo $br_row->BRANCH_ID.' - '.$br_row->BRANCH_NAME; ?></td>
    <td width="35" align="center"><?php echo number_format($br_row->RATE, 2); ?></td>
    <td width="55" align="center"><?php echo number_format($br_row->NUM_PASSES); ?></td>
    <td width="70" align="right"><?php echo number_format($br_row->TOTAL_FV, 2); ?></td>
    <td width="60" align="right"><?php echo number_format($br_row->TOTAL_REFUND, 2); ?></td>
    <td width="65" align="right"><?php echo number_format($br_row->MARKETING_FEE, 2); ?></td>
    <td width="40" align="right"><?php echo number_format($br_row->VAT, 2); ?></td>
    <td width="60" align="right"><?php echo number_format($br_row->NET_DUE, 2); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endfor; ?>

<?php
$sumREFV = 0;
if($refundRow <> 0){
	foreach($refundLi as $ref_row){
		$sumREFV += $ref_row->TOTALREF_FV;
	}
}
?>

<!-- ============================================================ -->
<!-- SUMMARY TABLE                                                -->
<!-- ============================================================ -->
<table width="100%" border="0" cellspacing="0" cellpadding="4" style="margin-top:4px; margin-bottom:4px;">
  <tr>
    <td colspan="6" style="font-weight:bold; padding-bottom:2px;">Summary:</td>
  </tr>
  <tr style="font-weight:bold; border-bottom:1px solid #333;">
    <td>Service</td>
    <td align="right" width="80">Adjustment</td>
    <td align="right" width="80">Marketing Fee</td>
    <td align="right" width="90">Total Face Value</td>
    <td align="right" width="60">VAT</td>
    <td align="right" width="70">Net Due</td>
  </tr>
  <?php
  $prod_arr = array();
  $sumVAT = $sumND = $sumMF = $sumFV = 0;
  foreach($serviceLi as $sr_row):
    $totalFV      = $sr_row->TOTAL_FV;
    $TOTAL_REFUND = $sr_row->TOTAL_REFUND;
    $totalMFV     = $totalFV - $TOTAL_REFUND;
    $vatcond      = $this->my_lib->checkVAT($sr_row->vatcond);
    $percentMF    = $this->my_lib->convertMFRATE($sr_row->MerchantFee, TRUE);
    $MF           = $this->my_lib->computeMF($totalMFV, $percentMF, '', FALSE);
    $VAT          = $this->my_lib->computeVAT($totalMFV, $percentMF, $vatcond, FALSE);
    $NET_DUE      = $this->my_lib->computeNETDUE($totalMFV, $percentMF, $vatcond, FALSE);
  ?>
  <tr>
    <td><?php echo $sr_row->SERVICE_NAME; ?></td>
    <td align="right"><?php echo number_format($TOTAL_REFUND, 2); ?></td>
    <td align="right"><?php echo number_format($MF, 2); ?></td>
    <td align="right"><?php echo number_format($totalFV, 2); ?></td>
    <td align="right"><?php echo number_format($VAT, 2); ?></td>
    <td align="right"><?php echo number_format($NET_DUE, 2); ?></td>
  </tr>
  <?php
    $arr['name'] = $sr_row->SERVICE_NAME;
    $arr['fv']   = number_format($totalFV, 2);
    $sumFV      += $totalFV;
    $sumMF      += $MF;
    $sumND      += $NET_DUE;
    $sumVAT     += $VAT;
    $prod_arr[]  = $arr;
  endforeach;
  ?>
</table>

<!-- ============================================================ -->
<!-- RECEIVED BY (left)  +  TOTALS (right)                       -->
<!-- ============================================================ -->
<table width="100%" border="0" cellspacing="0" cellpadding="4" style="margin-top:6px;">
  <tr>
    <!-- LEFT: Received by -->
    <td width="52%" valign="bottom" style="padding-right:10px;">
      <table width="100%" border="0" cellspacing="0" cellpadding="2">
        <tr>
          <td style="padding-bottom:30px;">Received by:</td>
        </tr>
        <tr>
          <td style="border-top:1px solid #333; width:200px; padding-top:2px;">
            Signature over Printed Name
          </td>
        </tr>
        <tr>
          <td style="padding-top:10px;">Date:&nbsp;&nbsp;______________________</td>
        </tr>
      </table>
    </td>

    <!-- RIGHT: Service face value + Fee totals -->
    <td width="48%" valign="top">
      <table width="100%" border="0" cellspacing="0" cellpadding="3">
        <tr style="font-weight:bold;">
          <td>Service</td>
          <td align="right">Face Value</td>
        </tr>
        <?php for($zz = 0; $zz < count($prod_arr); $zz++): ?>
        <tr>
          <td><?php echo $prod_arr[$zz]['name']; ?></td>
          <td align="right"><?php echo $prod_arr[$zz]['fv']; ?></td>
        </tr>
        <?php endfor; ?>
        <tr>
          <td>Refund Adjustment:</td>
          <td align="right">(<?php echo number_format($sumREFV, 2); ?>)</td>
        </tr>
        <tr>
          <td>Total Face Value:</td>
          <td align="right"><?php echo number_format(($sumFV - $sumREFV), 2); ?></td>
        </tr>
        <tr>
          <td>(A)Marketing Fee:</td>
          <td align="right"><?php echo number_format($sumMF, 2); ?></td>
        </tr>
        <tr>
          <td>(B)Delivery Fee:</td>
          <td align="right"><?php echo number_format(0, 2); ?></td>
        </tr>
        <tr>
          <td>(C)Voucher Pick Up Fee:</td>
          <td align="right"><?php echo number_format(0, 2); ?></td>
        </tr>
        <tr>
          <td>(D)Other Fees:</td>
          <td align="right"><?php echo number_format(0, 2); ?></td>
        </tr>
        <tr>
          <td>(E)Other Non Vat Fees:</td>
          <td align="right"><?php echo number_format(0, 2); ?></td>
        </tr>
        <tr>
          <td>VAT 12% (A+B+C+D):</td>
          <td align="right"><?php echo number_format($sumVAT, 2); ?></td>
        </tr>
        <tr style="font-weight:bold;">
          <td>Total Net Due:</td>
          <td align="right"><?php echo number_format($sumND, 2); ?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<!-- ============================================================ -->
<!-- GENERATED / PRINTED BY                                      -->
<!-- ============================================================ -->
<table width="100%" border="0" cellspacing="0" cellpadding="4" style="margin-top:8px;">
  <tr>
    <td width="20%" style="font-weight:bold;">Generated by:</td>
    <td width="30%"><?php echo $REIMBURSEMENT_USER; ?></td>
    <td width="20%" style="font-weight:bold;">Printed by:</td>
    <td width="30%"><?php echo $data_user->full_name; ?></td>
  </tr>
  <tr>
    <td style="font-weight:bold;">Date Generated:</td>
    <td><?php echo $REIMBURSEMENT_DATE; ?></td>
    <td style="font-weight:bold;">Date Printed:</td>
    <td><?php echo $date_printed; ?></td>
  </tr>
</table>

<!-- ============================================================ -->
<!-- COPY LABEL                                                   -->
<!-- ============================================================ -->
<table width="100%" border="0" cellspacing="0" cellpadding="6" style="margin-top:6px;">
  <tr>
    <td align="center" style="font-weight:bold;">
      <?php if($copy == false): ?>
        ORIGINAL COPY OF PAYMENT ADVICE
      <?php else: ?>
        PRINTED COPY OF PAYMENT ADVICE
      <?php endif; ?>
    </td>
  </tr>
</table>

<?php if($refundRow <> 0): ?>
<!-- ============================================================ -->
<!-- REFUND PAGE                                                  -->
<!-- ============================================================ -->
<div style="page-break-before:always;"></div>

<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr><td align="center" style="padding-bottom:2px;"><strong style="font-size:15px;">Pluxee Philippines Incorporated</strong></td></tr>
  <tr><td align="center" style="font-size:10px;">8747 Paseo de Roxas Street, 11TH Floor, B.A. Lepanto Condominium, Makati City (1200), Metro Manila, Philippines</td></tr>
  <tr><td align="center" style="font-size:10px;">Tel. no: 8689-4700. Fax no: 86894777</td></tr>
  <tr><td align="center" style="font-size:10px; padding-bottom:6px;">TIN: 223-183-726-00000</td></tr>
  <tr><td align="center" style="padding:8px 0 10px 0;">
    <strong style="font-size:14px;">Payment Advice # <?php echo $PA_NUM; ?> - Refund Adjustment details</strong>
  </td></tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="4" style="margin-bottom:8px;">
  <tr>
    <td width="52%" valign="top" style="padding-right:10px;">
      <table width="100%" border="0" cellspacing="0" cellpadding="2">
        <tr><td style="font-weight:bold; padding-bottom:2px;">Advice To:</td></tr>
        <tr><td style="padding-bottom:2px;"><?php echo $LegalName; ?></td></tr>
        <tr><td style="font-weight:bold; padding-bottom:2px;">T.I.N.: <?php echo $TIN; ?></td></tr>
        <tr><td style="padding-bottom:2px;"><?php echo $TradingName; ?></td></tr>
        <tr><td style="padding-bottom:6px;"><?php echo $Address; ?></td></tr>
        <tr><td><strong>Reimbursement date:</strong>&nbsp;&nbsp;<?php echo $EXPECTED_DUEDATE; ?></td></tr>
      </table>
    </td>
    <td width="48%" valign="top">
      <table width="100%" border="0" cellspacing="0" cellpadding="4">
        <tr>
          <td width="45%" style="font-weight:bold; vertical-align:top;">Payee Name:</td>
          <td width="55%" style="vertical-align:top;"><?php echo $PayeeName; ?></td>
        </tr>
        <tr>
          <td style="font-weight:bold; vertical-align:top;">Mode of payment:</td>
          <td style="vertical-align:top;"><?php echo $MeanofPayment; ?></td>
        </tr>
        <tr>
          <td style="font-weight:bold; vertical-align:top;">Bank Name:</td>
          <td style="vertical-align:top;"><?php echo $BankName; ?></td>
        </tr>
        <tr>
          <td style="font-weight:bold; vertical-align:top;">Acct number:</td>
          <td style="vertical-align:top;"><?php echo $BankAccountNumber; ?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="4" style="margin-bottom:4px;">
  <tr style="font-weight:bold; border-bottom:1px solid #333;">
    <td>Branch</td>
    <td align="right" width="80">Total Passes</td>
    <td align="right" width="90">Total Refund</td>
  </tr>
  <?php $sumREFV = 0; foreach($refundLi as $ref_row): ?>
  <tr>
    <td><?php echo $ref_row->BRANCH_NAME; ?></td>
    <td align="right"><?php echo $ref_row->NUM_PASSES; ?></td>
    <td align="right"><?php echo number_format($ref_row->TOTALREF_FV, 2); ?></td>
  </tr>
  <?php $sumREFV += $ref_row->TOTALREF_FV; endforeach; ?>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="4" style="margin-top:4px;">
  <tr>
    <td colspan="5" style="font-weight:bold; padding-bottom:2px;">Summary:</td>
  </tr>
  <tr style="font-weight:bold; border-bottom:1px solid #333;">
    <td width="160">Service</td>
    <td align="right" width="90">Adjustment</td>
    <td align="right" width="90">Marketing Fee</td>
    <td align="right" width="70">VAT</td>
    <td align="right" width="80">Net Due</td>
  </tr>
  <?php foreach($serviceREF as $sref_row): $totalREFFV = $sref_row->TOTAL_FV; ?>
  <tr>
    <td><?php echo $sref_row->SERVICE_NAME; ?></td>
    <td align="right"><?php echo number_format($totalREFFV, 2); ?></td>
    <td align="right"></td>
    <td align="right"></td>
    <td align="right"><?php echo number_format($totalREFFV, 2); ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="4" style="margin-top:8px;">
  <tr>
    <td width="20%" style="font-weight:bold;">Generated by:</td>
    <td width="30%"><?php echo $REIMBURSEMENT_USER; ?></td>
    <td width="20%" style="font-weight:bold;">Printed by:</td>
    <td width="30%"><?php echo $data_user->full_name; ?></td>
  </tr>
  <tr>
    <td style="font-weight:bold;">Date Generated:</td>
    <td><?php echo $REIMBURSEMENT_DATE; ?></td>
    <td style="font-weight:bold;">Date Printed:</td>
    <td><?php echo $date_printed; ?></td>
  </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="6" style="margin-top:6px;">
  <tr>
    <td align="center" style="font-weight:bold;">
      <?php if($copy == false): ?>
        ORIGINAL COPY OF PAYMENT ADVICE
      <?php else: ?>
        PRINTED COPY OF PAYMENT ADVICE
      <?php endif; ?>
    </td>
  </tr>
</table>
<?php endif; ?>

</body>
</html>
