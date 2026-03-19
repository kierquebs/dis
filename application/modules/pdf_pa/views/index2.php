<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body style="font-family:Arial,sans-serif; font-size:10px; margin:0; padding:0;">

<table width="100%" border="0" cellspacing="0" cellpadding="0">

<!-- ===== COMPANY HEADER ===== -->
<tr>
  <td align="center" style="padding-bottom:6px;">
    <strong style="font-size:14px;">Pluxee Philippines Incorporated</strong><br/>
    8747 Paseo de Roxas Street, 11TH Floor, B.A. Lepanto Condominium, Makati City (1200), Metro Manila, Philippines<br/>
    Tel. no: 8689-4700. Fax no: 86894777<br/>
    TIN: 223-183-726-00000
  </td>
</tr>

<?php foreach($merchantInfo as $mer_row):
	$REIMBURSEMENT_DATE = $mer_row->REIMBURSEMENT_DATE;
	$EXPECTED_DUEDATE   = $mer_row->ExpectedDueDate;
	$REIMBURSEMENT_USER = $mer_row->full_name;

	$PayeeName        = $mer_row->PayeeName;
	$MeanofPayment    = $mer_row->MeanofPayment;
	$BankName         = $mer_row->BankName;
	$BankAccountNumber= $mer_row->BankAccountNumber;
	$Address          = $mer_row->Address;

	if($mer_row->AffiliateGroupCode <> $mer_row->brAffCode){
		$whereAFFCODE['CP_ID']             = $mer_row->CP_ID;
		$whereAFFCODE['AffiliateGroupCode']= $mer_row->brAffCode;
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

<!-- ===== PA NUMBER ===== -->
<tr>
  <td align="center" style="padding:10px 0 8px 0;">
    <strong style="font-size:13px;">Payment Advice # <?php echo $PA_NUM; ?></strong>
  </td>
</tr>

<!-- ===== MERCHANT INFO TABLE ===== -->
<tr>
  <td style="padding-bottom:6px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
      <tr>
        <!-- Left column -->
        <td width="55%" valign="top" style="border:1px solid #000; border-right:none; padding:0;">
          <table width="100%" border="0" cellspacing="0" cellpadding="4" style="border-collapse:collapse;">
            <tr><td style="font-weight:bold; border-bottom:1px solid #ccc;">Advice To:</td><td style="border-bottom:1px solid #ccc;"></td></tr>
            <tr><td colspan="2" style="border-bottom:1px solid #ccc;"><?php echo $LegalName; ?></td></tr>
            <tr><td colspan="2" style="border-bottom:1px solid #ccc;"><strong>T.I.N.:</strong> <?php echo $TIN; ?></td></tr>
            <tr><td colspan="2" style="border-bottom:1px solid #ccc;"><?php echo $TradingName; ?></td></tr>
            <tr><td colspan="2" style="border-bottom:1px solid #ccc;"><?php echo $Address; ?></td></tr>
            <tr>
              <td style="font-weight:bold; white-space:nowrap;">Reimbursement date:</td>
              <td><?php echo $EXPECTED_DUEDATE; ?></td>
            </tr>
          </table>
        </td>
        <!-- Right column -->
        <td width="45%" valign="top" style="border:1px solid #000; padding:0;">
          <table width="100%" border="0" cellspacing="0" cellpadding="4" style="border-collapse:collapse;">
            <tr>
              <td width="45%" style="font-weight:bold; border-bottom:1px solid #ccc;">Payee Name:</td>
              <td width="55%" style="border-bottom:1px solid #ccc;"><?php echo $PayeeName; ?></td>
            </tr>
            <tr>
              <td style="font-weight:bold; border-bottom:1px solid #ccc;">Mode of payment:</td>
              <td style="border-bottom:1px solid #ccc;"><?php echo $MeanofPayment; ?></td>
            </tr>
            <tr>
              <td style="font-weight:bold; border-bottom:1px solid #ccc;">Bank Name:</td>
              <td style="border-bottom:1px solid #ccc;"><?php echo $BankName; ?></td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Acct number:</td>
              <td><?php echo $BankAccountNumber; ?></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>

<?php endforeach; ?>

<!-- ===== BRANCH DETAIL TABLE(S) ===== -->
<tr>
  <td style="padding-bottom:6px;">
    <?php for ($xy = 1; $xy <= $totalNewPage; $xy++): ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="4" style="border-collapse:collapse;"
      <?php if(($xy == 1 || $xy % 3 == 0) && $branchNum > 15) echo ' style2="page-break-after:always;"'; ?>>
      <?php if(($xy == 1 || $xy % 2 == 0) && !empty($branchLi[$xy])): ?>
      <tr>
        <td style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Branch</td>
        <td align="center" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000; white-space:nowrap;">Rate %</td>
        <td align="center" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">No. of Code/s</td>
        <td align="right"  style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Total Face Value</td>
        <td align="right"  style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Total Refund</td>
        <td align="right"  style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Marketing Fee</td>
        <td align="right"  style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">VAT</td>
        <td align="right"  style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Net Due</td>
      </tr>
      <?php endif; ?>
      <?php foreach($branchLi[$xy] as $br_row): ?>
      <tr>
        <td style="border-bottom:1px solid #ccc;"><?php echo $br_row->BRANCH_ID.' - '.$br_row->BRANCH_NAME; ?></td>
        <td align="center" style="border-bottom:1px solid #ccc;"><?php echo number_format($br_row->RATE, 2); ?></td>
        <td align="center" style="border-bottom:1px solid #ccc;"><?php echo number_format($br_row->NUM_PASSES); ?></td>
        <td align="right"  style="border-bottom:1px solid #ccc;"><?php echo number_format($br_row->TOTAL_FV, 2); ?></td>
        <td align="right"  style="border-bottom:1px solid #ccc;"><?php echo number_format($br_row->TOTAL_REFUND ?? 0, 2); ?></td>
        <td align="right"  style="border-bottom:1px solid #ccc;"><?php echo number_format($br_row->MARKETING_FEE, 2); ?></td>
        <td align="right"  style="border-bottom:1px solid #ccc;"><?php echo number_format($br_row->VAT, 2); ?></td>
        <td align="right"  style="border-bottom:1px solid #ccc;"><?php echo number_format($br_row->NET_DUE, 2); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endfor; ?>
  </td>
</tr>

<!-- ===== SUMMARY TABLE ===== -->
<tr>
  <td style="padding-bottom:6px;">
    <strong>Summary:</strong><br/>&nbsp;
    <table width="100%" border="0" cellspacing="0" cellpadding="4" style="border-collapse:collapse;">
      <tr>
        <td style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Service</td>
        <td align="right" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Adjustment</td>
        <td align="right" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Marketing Fee</td>
        <td align="right" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Total Face Value</td>
        <td align="right" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">VAT</td>
        <td align="right" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Net Due</td>
      </tr>
      <?php foreach($serviceSummary as $sr): ?>
      <tr>
        <td style="border-bottom:1px solid #ccc;"><?php echo $sr['SERVICE_NAME']; ?></td>
        <td align="right" style="border-bottom:1px solid #ccc;"><?php echo $sr['TOTAL_REFUND']; ?></td>
        <td align="right" style="border-bottom:1px solid #ccc;"><?php echo $sr['MF']; ?></td>
        <td align="right" style="border-bottom:1px solid #ccc;"><?php echo $sr['TOTAL_FV']; ?></td>
        <td align="right" style="border-bottom:1px solid #ccc;"><?php echo $sr['VAT']; ?></td>
        <td align="right" style="border-bottom:1px solid #ccc;"><?php echo $sr['NET_DUE']; ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </td>
</tr>

<!-- ===== RECEIVED BY + TOTALS ===== -->
<tr>
  <td style="padding-bottom:6px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <!-- Left: Received by -->
        <td width="50%" valign="bottom" style="padding-right:10px;">
          Received by:<br/>
          <br/><br/>
          Signature over Printed Name<br/>
          <br/>
          Date:&nbsp;&nbsp;______________________
        </td>
        <!-- Right: Totals -->
        <td width="50%" valign="top">
          <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <tr>
              <td style="font-weight:bold;">Service</td>
              <td align="right" style="font-weight:bold;">Face Value</td>
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
              <td colspan="2"><hr style="border:0; border-top:1px solid #000; margin:2px 0;"/></td>
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
            <tr>
              <td style="font-weight:bold;">Total Net Due:</td>
              <td align="right" style="font-weight:bold;"><?php echo number_format($sumND, 2); ?></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>

<!-- ===== GENERATED BY / PRINTED BY ===== -->
<tr>
  <td style="padding-top:4px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
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
  </td>
</tr>

<!-- ===== COPY LABEL ===== -->
<tr>
  <td align="center" style="padding:8px 0;">
    <?php if($copy == false): ?>
      ORIGINAL COPY OF PAYMENT ADVICE
    <?php else: ?>
      PRINTED COPY OF PAYMENT ADVICE
    <?php endif; ?>
  </td>
</tr>

</table><!-- end main table -->

<?php if($refundRow <> 0): ?>
<!-- ========================================================= -->
<!-- REFUND PAGE                                                -->
<!-- ========================================================= -->
<div style="page-break-before:always;"></div>

<table width="100%" border="0" cellspacing="0" cellpadding="0">

<!-- Company header -->
<tr>
  <td align="center" style="padding-bottom:6px;">
    <strong style="font-size:14px;">Pluxee Philippines Incorporated</strong><br/>
    8747 Paseo de Roxas Street, 11TH Floor, B.A. Lepanto Condominium, Makati City (1200), Metro Manila, Philippines<br/>
    Tel. no: 8689-4700. Fax no: 86894777<br/>
    TIN: 223-183-726-00000
  </td>
</tr>

<!-- PA Number - Refund -->
<tr>
  <td align="center" style="padding:10px 0 8px 0;">
    <strong style="font-size:13px;">Payment Advice # <?php echo $PA_NUM; ?> - Refund Adjustment details</strong>
  </td>
</tr>

<!-- Merchant info -->
<tr>
  <td style="padding-bottom:6px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
      <tr>
        <td width="55%" valign="top" style="border:1px solid #000; border-right:none; padding:0;">
          <table width="100%" border="0" cellspacing="0" cellpadding="4" style="border-collapse:collapse;">
            <tr><td style="font-weight:bold; border-bottom:1px solid #ccc;">Advice To:</td><td style="border-bottom:1px solid #ccc;"></td></tr>
            <tr><td colspan="2" style="border-bottom:1px solid #ccc;"><?php echo $LegalName; ?></td></tr>
            <tr><td colspan="2" style="border-bottom:1px solid #ccc;"><strong>T.I.N.:</strong> <?php echo $TIN; ?></td></tr>
            <tr><td colspan="2" style="border-bottom:1px solid #ccc;"><?php echo $TradingName; ?></td></tr>
            <tr><td colspan="2" style="border-bottom:1px solid #ccc;"><?php echo $Address; ?></td></tr>
            <tr>
              <td style="font-weight:bold; white-space:nowrap;">Reimbursement date:</td>
              <td><?php echo $EXPECTED_DUEDATE; ?></td>
            </tr>
          </table>
        </td>
        <td width="45%" valign="top" style="border:1px solid #000; padding:0;">
          <table width="100%" border="0" cellspacing="0" cellpadding="4" style="border-collapse:collapse;">
            <tr>
              <td width="45%" style="font-weight:bold; border-bottom:1px solid #ccc;">Payee Name:</td>
              <td width="55%" style="border-bottom:1px solid #ccc;"><?php echo $PayeeName; ?></td>
            </tr>
            <tr>
              <td style="font-weight:bold; border-bottom:1px solid #ccc;">Mode of payment:</td>
              <td style="border-bottom:1px solid #ccc;"><?php echo $MeanofPayment; ?></td>
            </tr>
            <tr>
              <td style="font-weight:bold; border-bottom:1px solid #ccc;">Bank Name:</td>
              <td style="border-bottom:1px solid #ccc;"><?php echo $BankName; ?></td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Acct number:</td>
              <td><?php echo $BankAccountNumber; ?></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>

<!-- Refund branch table -->
<tr>
  <td style="padding-bottom:6px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="4" style="border-collapse:collapse;">
      <tr>
        <td style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Branch</td>
        <td align="right" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Total Passes</td>
        <td align="right" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Total Refund</td>
      </tr>
      <?php foreach($refundLi as $ref_row): ?>
      <tr>
        <td style="border-bottom:1px solid #ccc;"><?php echo $ref_row->BRANCH_NAME; ?></td>
        <td align="right" style="border-bottom:1px solid #ccc;"><?php echo $ref_row->NUM_PASSES; ?></td>
        <td align="right" style="border-bottom:1px solid #ccc;"><?php echo number_format($ref_row->TOTALREF_FV, 2); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </td>
</tr>

<!-- Refund summary -->
<tr>
  <td style="padding-bottom:6px;">
    <strong>Summary:</strong><br/>&nbsp;
    <table width="100%" border="0" cellspacing="0" cellpadding="4" style="border-collapse:collapse;">
      <tr>
        <td style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Service</td>
        <td align="right" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Adjustment</td>
        <td align="right" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Marketing Fee</td>
        <td align="right" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">VAT</td>
        <td align="right" style="font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Net Due</td>
      </tr>
      <?php foreach($serviceREF as $sref_row): $totalREFFV = $sref_row->TOTAL_FV; ?>
      <tr>
        <td style="border-bottom:1px solid #ccc;"><?php echo $sref_row->SERVICE_NAME; ?></td>
        <td align="right" style="border-bottom:1px solid #ccc;"><?php echo number_format($totalREFFV, 2); ?></td>
        <td align="right" style="border-bottom:1px solid #ccc;"></td>
        <td align="right" style="border-bottom:1px solid #ccc;"></td>
        <td align="right" style="border-bottom:1px solid #ccc;"><?php echo number_format($totalREFFV, 2); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </td>
</tr>

<!-- Footer -->
<tr>
  <td style="padding-top:4px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
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
  </td>
</tr>

<tr>
  <td align="center" style="padding:8px 0;">
    <?php if($copy == false): ?>
      ORIGINAL COPY OF PAYMENT ADVICE
    <?php else: ?>
      PRINTED COPY OF PAYMENT ADVICE
    <?php endif; ?>
  </td>
</tr>

</table><!-- end refund table -->
<?php endif; ?>

</body>
</html>
