<div class="row-div search-div">
<form method="get" id="search-form" action="javascript:" class="search-wrapper" autocomplete="off">
	<div class="search-txt-div">
		<input type="text" name="search" class="form-search form-control" style="width:300px" placeholder="Merchant ID or NAME" />
		<input type="text" name="branch" class="form-search form-control" style="width:300px" placeholder="Branch ID or NAME" />
		<input type="text" name="panumber" class="form-search form-control" style="width:150px" placeholder="PA NUMBER" <?php if(!empty($PA_NUMBER)) echo 'value="'.htmlspecialchars($PA_NUMBER, ENT_QUOTES, 'UTF-8').'"';?> />
		<input type="text" name="voucher" class="form-search form-control" style="width:500px" placeholder="VOUCHER CODE" />
		<br />
		<select class="form-control" name="stat">
			<option disabled selected>Status</option>
			<option value="">All</option>
			<option value="1" <?php echo (isset($_POST['stat']) && $_POST['stat'] == "1" ? 'selected' : '')?>>Billed</option>
			<option value="2" <?php echo (isset($_POST['stat']) && $_POST['stat'] == "2" ? 'selected' : '')?>>Redeemed</option>
			<option value="3" <?php echo (isset($_POST['stat']) && $_POST['stat'] == "3" ? 'selected' : '')?>>Reconciled</option>
			<option value="4" <?php echo (isset($_POST['stat']) && $_POST['stat'] == "4" ? 'selected' : '')?>>Reversal</option>
		</select>
		<input class="search-date form-control datetimepicker" name="datef" placeholder="FROM" type="text"/>
		<input class="search-date form-control datetimepicker" name="datet" placeholder="TO" type="text"/>
		<button type="submit" class="btn btn-info searchTxt" title="Search" aria-label="Search"> SEARCH 
		<span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
		<button type="button" class="clear-all btn btn-info" title="Remove Filter">CLEAR</button>	
	</div>		
</form>
</div>

<div class="row-div serve-tbl">
<div class="search-notif" style="width: 90%; margin: 0 auto; text-align: center; padding: 20px; font-size: 14px; font-weight: bold;">Enter PA Number OR Merchant ID or Merchant Name</div>
<table id="queue-tbl" class="queue-tbl display table-bordered" cellspacing="0" data-form="queue">
<thead class="thead-default">
	<tr>
		<th>DIGITAL ID</th>
		<th>MERCHANT ID</th>
		<th width="500px">MERCHANT NAME</th>
		<th>TIN NUMBER</th>
		<th>BRANCH ID</th>
		<th width="500px">BRANCH NAME</th>
		<th width="250px">VOUCHER CODE</th>
		<th width="50px">PRODUCT</th>
		<th width="50px">POS ID</th>
		<th>AMOUNT</th>
		<th width="500px">REDEEM ID</th>
		<th class="trans">REDEEM<div class="queue-th-span"><span>STATUS</span><span>DATE</span></div></th>
		<th width="500px">RECON ID</th>
		<th width="250px">RECON DATE</th>
		<th width="150px">PAYMENT ADVICE ID</th>
		<th class="trans">PAYMENT ADVICE<div class="queue-th-span"><span>STATUS</span><span>DATE</span></div></th>
		<th width="250px">PAYMENT DUE DATE</th>
		<th width="500px">REVERSAL ID</th>
		<th width="250px">REVERSAL DATE</th>
		<th width="150px">REVERSAL P.A ID</th>
		<th class="trans">REVERSAL P.A<div class="queue-th-span"><span>STATUS</span><span>DATE</span></div></th>
	</tr>
</thead>
<tbody>
</tbody>
</table>

</div>

