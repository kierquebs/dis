<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body style="font-family:sans-serif; font-size:11px; margin:0; padding:0;">

<table width="100%" border="0" cellspacing="0" cellpadding="0">

<!-- ===== COMPANY HEADER ===== -->
<tr>
  <td align="center" style="padding:8px 0;">
    <strong style="font-size:16px;">Pluxee Philippines Incorporated</strong><br/>
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
			$rowAFFCODE       = $getAFFCODE->row();
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
  <td align="center" style="padding:6px 0;">
    <strong style="font-size:14px;">Payment Advice # <?php echo $PA_NUM; ?></strong>
  </td>
</tr>

<!-- ===== MERCHANT INFO TABLE ===== -->
<tr>
  <td style="padding:4px 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <!-- Left: stacked merchant details -->
        <td width="52%" valign="top" style="padding:4px 6px 4px 0;">
          <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <tr><td style="font-weight:bold;">Advice To:</td></tr>
            <tr><td><?php echo $mer_row->LegalName; ?></td></tr>
            <tr><td style="font-weight:bold;">T.I.N.: <?php echo $mer_row->TIN; ?></td></tr>
            <tr><td><?php echo $mer_row->TradingName; ?></td></tr>
            <tr><td><?php echo $Address; ?></td></tr>
            <tr><td><strong>Reimbursement date:</strong>&nbsp;&nbsp;<?php echo $EXPECTED_DUEDATE; ?></td></tr>
          </table>
        </td>
        <!-- Right: payee / bank label+value pairs -->
        <td width="48%" valign="top" style="padding:4px 0;">
          <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <tr>
              <td width="44%" style="font-weight:bold; vertical-align:top;">Payee Name:</td>
              <td width="56%" style="vertical-align:top;"><?php echo $PayeeName; ?></td>
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
  </td>
</tr>

<?php endforeach; ?>

<!-- ===== BRANCH DETAIL TABLE(S) ===== -->
<tr>
  <td style="padding:6px 0;">
    <?php for ($xy = 1; $xy <= $totalNewPage; $xy++): ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="4"
      <?php if(($xy == 1 || $xy % 3 == 0) && $branchNum > 15) echo 'style="page-break-after:always;"'; ?>>
      <?php if(($xy == 1 || $xy % 2 == 0) && !empty($branchLi[$xy])): ?>
      <tr style="font-weight:bold; border-bottom:1px solid #999;">
        <td width="120">Branch</td>
        <td width="30"  align="center">Rate %</td>
        <td width="55"  align="center">No. of Code/s</td>
        <td width="65"  align="right">Total Face Value</td>
        <td width="55"  align="right">Total Refund</td>
        <td width="60"  align="right">Marketing Fee</td>
        <td width="40"  align="right">VAT</td>
        <td width="60"  align="right">Net Due</td>
      </tr>
      <?php endif; ?>
      <?php foreach($branchLi[$xy] as $br_row): ?>
      <tr>
        <td width="120"><?php echo $br_row->BRANCH_ID.' - '.$br_row->BRANCH_NAME; ?></td>
        <td width="30"  align="center"><?php echo number_format($br_row->RATE, 2); ?></td>
        <td width="55"  align="center"><?php echo number_format($br_row->NUM_PASSES); ?></td>
        <td width="65"  align="right"><?php echo number_format($br_row->TOTAL_FV, 2); ?></td>
        <td width="55"  align="right"><?php echo number_format($br_row->TOTAL_REFUND, 2); ?></td>
        <td width="60"  align="right"><?php echo number_format($br_row->MARKETING_FEE, 2); ?></td>
        <td width="40"  align="right"><?php echo number_format($br_row->VAT, 2); ?></td>
        <td width="60"  align="right"><?php echo number_format($br_row->NET_DUE, 2); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endfor; ?>
  </td>
</tr>

<!-- ===== SUMMARY TABLE ===== -->
<tr>
  <td style="padding:6px 0;">
    <p style="margin:0 0 4px 0; padding:0; font-weight:bold;">Summary:</p>
    <table width="100%" border="0" cellspacing="0" cellpadding="4">
      <tr style="font-weight:bold; border-bottom:1px solid #999;">
        <td width="160">Service</td>
        <td align="right">Adjustment</td>
        <td align="right">Marketing Fee</td>
        <td align="right" width="120">Total Face Value</td>
        <td align="right">VAT</td>
        <td align="right">Net Due</td>
      </tr>
      <?php foreach($serviceSummary as $sr): ?>
      <tr>
        <td><?php echo $sr['SERVICE_NAME']; ?></td>
        <td align="right"><?php echo $sr['TOTAL_REFUND']; ?></td>
        <td align="right"><?php echo $sr['MF']; ?></td>
        <td align="right"><?php echo $sr['TOTAL_FV']; ?></td>
        <td align="right"><?php echo $sr['VAT']; ?></td>
        <td align="right"><?php echo $sr['NET_DUE']; ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </td>
</tr>

<!-- ===== RECEIVED BY + TOTALS ===== -->
<tr>
  <td style="padding:6px 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="4">
      <tr>

        <!-- Left: Received by -->
        <td width="55%" style="vertical-align:bottom; padding-left:8px;">
          <br/>Received by:<br/>
          <br/><br/>Signature over Printed Name<br/>
          <br/>Date:&nbsp;&nbsp;______________________
        </td>

        <!-- Right: Totals -->
        <td width="45%" style="vertical-align:top;">
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
            <tr><td colspan="2"><hr style="border:0; border-top:1px solid #999; margin:2px 0;"/></td></tr>
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
  </td>
</tr>

<!-- ===== GENERATED BY / PRINTED BY ===== -->
<tr>
  <td style="padding:4px 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="4">
      <tr>
        <td width="25%" style="font-weight:bold;">Generated by:</td>
        <td width="25%"><?php echo $REIMBURSEMENT_USER; ?></td>
        <td width="25%" style="font-weight:bold;">Printed by:</td>
        <td width="25%"><?php echo $data_user->full_name; ?></td>
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
  <td align="center" style="padding:6px 0;">
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
  <td align="center" style="padding:8px 0;">
    <strong style="font-size:16px;">Pluxee Philippines Incorporated</strong><br/>
    8747 Paseo de Roxas Street, 11TH Floor, B.A. Lepanto Condominium, Makati City (1200), Metro Manila, Philippines<br/>
    Tel. no: 8689-4700. Fax no: 86894777<br/>
    TIN: 223-183-726-00000
  </td>
</tr>

<!-- PA Number - Refund -->
<tr>
  <td align="center" style="padding:6px 0;">
    <strong style="font-size:14px;">Payment Advice # <?php echo $PA_NUM; ?> - Refund Adjustment details</strong>
  </td>
</tr>

<!-- Merchant info -->
<tr>
  <td style="padding:4px 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="52%" valign="top" style="padding:4px 6px 4px 0;">
          <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <tr><td style="font-weight:bold;">Advice To:</td></tr>
            <tr><td><?php echo $LegalName; ?></td></tr>
            <tr><td style="font-weight:bold;">T.I.N.: <?php echo $TIN; ?></td></tr>
            <tr><td><?php echo $TradingName; ?></td></tr>
            <tr><td><?php echo $Address; ?></td></tr>
            <tr><td><strong>Reimbursement date:</strong>&nbsp;&nbsp;<?php echo $EXPECTED_DUEDATE; ?></td></tr>
          </table>
        </td>
        <td width="48%" valign="top" style="padding:4px 0;">
          <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <tr>
              <td width="44%" style="font-weight:bold; vertical-align:top;">Payee Name:</td>
              <td width="56%" style="vertical-align:top;"><?php echo $PayeeName; ?></td>
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
  </td>
</tr>

<!-- Refund branch table -->
<tr>
  <td style="padding:6px 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="4">
      <tr style="font-weight:bold; border-bottom:1px solid #999;">
        <td>Branch</td>
        <td align="right">Total Passes</td>
        <td align="right">Total Refund</td>
      </tr>
      <?php foreach($refundLi as $ref_row): ?>
      <tr>
        <td><?php echo $ref_row->BRANCH_NAME; ?></td>
        <td align="right"><?php echo $ref_row->NUM_PASSES; ?></td>
        <td align="right"><?php echo number_format($ref_row->TOTALREF_FV, 2); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </td>
</tr>

<!-- Refund summary -->
<tr>
  <td style="padding:6px 0;">
    <p style="margin:0 0 4px 0; padding:0; font-weight:bold;">Summary:</p>
    <table width="100%" border="0" cellspacing="0" cellpadding="4">
      <tr style="font-weight:bold; border-bottom:1px solid #999;">
        <td width="160">Service</td>
        <td align="right">Adjustment</td>
        <td align="right">Marketing Fee</td>
        <td align="right">VAT</td>
        <td align="right">Net Due</td>
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
  </td>
</tr>

<!-- Footer -->
<tr>
  <td style="padding:4px 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="4">
      <tr>
        <td width="25%" style="font-weight:bold;">Generated by:</td>
        <td width="25%"><?php echo $REIMBURSEMENT_USER; ?></td>
        <td width="25%" style="font-weight:bold;">Printed by:</td>
        <td width="25%"><?php echo $data_user->full_name; ?></td>
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
  <td align="center" style="padding:6px 0;">
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
