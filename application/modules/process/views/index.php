<?php if($checkUpload == 0): ?>
<div class="row-div serve-tbl">
<div class="search-notif" style="width: 90%; margin: 0 auto; text-align: center; padding: 20px; font-size: 14px; font-weight: bold;">
<h2>UNDER MAINTENANCE</h2>Transaction Update - In Progress .... <br /> <br />
<small>( Refresh this page to check status )</small>
</div>
</div>
<?php else: ?>
	
<?php if($generate != 0):?>
<div class="row-div search-div">
	<div class="pdf_pa">
		<a class="btn btn-warning btn-pdf" href="pdf_pa/group_gen" target="_self">DOWNLOAD GENERATED PAYMENT ADVICE</a>
	</div>
</div>
<?php endif;?>



<div class="row-div">
<br /> <br /> <center>
<a class="btn btn-primary" href="process/wrecon" target="_self">Process Merchant w/ Recon Transaction</a>
&nbsp;
<a class="btn btn-info" href="process/nrecon" target="_self">Process Merchant No Recon Transaction</a>
</center>
<br /><br />
</div>

<?php endif; ?>


