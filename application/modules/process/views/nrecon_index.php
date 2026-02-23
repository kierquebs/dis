<?php if($checkUpload == 0): ?>
<div class="row-div serve-tbl">
<div class="search-notif" style="width: 90%; margin: 0 auto; text-align: center; padding: 20px; font-size: 14px; font-weight: bold;">
<h2>UNDER MAINTENANCE</h2>Transaction Update - In Progress .... <br /> <br />
<small>( Refresh this page to check status )</small>
</div>
</div>
<?php else: ?>

<center><h3>NO Recon Transaction Merchants</h3></center>
<div class="row-div search-div">
<form method="get" id="search-form" action="javascript:" class="search-wrapper" autocomplete="off" data-page="nrecon">
	<div class="search-txt-div">
		<label>PAYMENT TERMS</label>
		<select class="form-control" name="terms" id="p_terms">
			<option disabled selected>SELECT</option>
			<option value="1" <?php echo (isset($_POST['terms']) && $_POST['terms'] == "1" ? 'selected' : '')?>>Monthly</option>
			<option value="2" <?php echo (isset($_POST['terms']) && $_POST['terms'] == "2" ? 'selected' : '')?>>Semi-monthly</option>
			<option value="3" <?php echo (isset($_POST['terms']) && $_POST['terms'] == "3" ? 'selected' : '')?>>Weekly</option>
			<option value="4" <?php echo (isset($_POST['terms']) && $_POST['terms'] == "4" ? 'selected' : '')?>>Every 10 days</option>
		</select>
		<span class="hide p_date">		
			<label>Specific Date</label>
			<select class="form-control p_date" name="date" >
				<option disabled selected>-</option>
				<?php for($sd = 0; $sd < count($specificDate); $sd++): ?>
				<option value="<?php echo $specificDate[$sd]?>" <?php echo (isset($_POST['date']) && $_POST['date'] == $specificDate[$sd] ? 'selected' : '')?>><?php echo $specificDate[$sd]?></option>
				<?php endfor;?>
			</select>
		</span>	
		<span class="hide p_day">	
			<label>Days of Week</label>	
			<select class="form-control p_day" name="day">
				<option disabled selected>-</option>
				<?php for($dd = 0; $dd < count($daysOfWeek); $dd++):?>
				<option value="<?php echo $daysOfWeek[$dd]?>" <?php echo (isset($_POST['days']) && $_POST['days'] == $daysOfWeek[$dd] ? 'selected' : '')?>><?php echo $daysOfWeek[$dd]?></option>
				<?php endfor;?>
			</select>
		</span>	
		<input type="text" name="search" class="auto-company form-search form-control" style="width:450px" placeholder="ENTER MERCHANT NAME" />
		<button type="submit" class="btn btn-info" title="Search" aria-label="Search"> SEARCH
		<span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
		<button type="button" class="clear-all btn btn-info" title="Remove Filter">CLEAR</button>
	</div>		
</form>
</div>

<div class="row-div serve-tbl" id="list_tbl">
<p class="date_coverage">Date coverage up to <span id="date_coverage"></span></p>
<form method="post" action="process/nrecon/gen_pa" class="form-loader" name="submitForm" id="submitForm">
<input type="hidden" name="terms"  /><input type="hidden" name="day"  /><input type="hidden" name="date"  />
<table id="queue-tbl" class="queue-tbl display table-bordered" cellspacing="0" data-form="queue">
<thead class="thead-default">				
	<tr>
		<th width="25"><input type="checkBox" id="checkAll" disabled></th>
		<th>DIGITAL ID</th>
		<th>MERCHANT ID</th>
		<th>MERCHANT NAME</th>
		<th width="100">TOTAL AMOUNT</th>
		<th width="100">TOTAL BRANCHES</th>
		<th width="100">TOTAL TRANSACTION</th>
		<th width="100">TOTAL REFUND TRANSACTION</th>
		<th width="100">TOTAL REFUND AMOUNT</th>
		<th width="150">CALCULATED <br /> EXPECTED DUE DATE</th>
		<th width="30"> - </th>
	</tr>
</thead>
<tbody>
</tbody>
</table>

<div class="process-tbl hide">
<div class="process-left"> 
	<p>Total Amount of Valid Transaction: <span class="number" id="tVA">0</span></p>
	<p>Total Amount of Unknown Transaction: <span class="number" id="tUA">0</span></p>
</div>
<div class="process-right">
	<button type="button" class="btn btn-success" id="create-req">GENERATE PAYMENT ADVICE</button>
</div>
</div>
</form>
</div>

<div class="row-div serve-tbl branch_li">
<p>LIST OF BRANCHES <span class="merchant_name"></span> <span class="close-branch">X</span></p>
<table id="comment-tbl" class="queue-tbl display table-bordered branch-tbl" cellspacing="0">
<tr id="tbl-head">
	<th width="200">MERCHANT ID</th>
	<th width="200">BRANCH ID</th>
	<th>BRANCH NAME</th>
	<th width="150">TOTAL AMOUNT</th>
	<th width="150">NUMBER OF TRANSACTIONS</th>
</tr>
</table>
</div>
<?php endif; ?>


