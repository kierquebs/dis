<div class="row-div search-div">
<form method="get" id="search-form" action="javascript:" class="search-wrapper">
	<div class="search-txt-div">
		<input type="text" name="search" class="form-search form-control" style="width:350px" placeholder="ENTER LEGAL NAME, BRANCH NAME" />
		<input type="text" name="voucher" class="form-search form-control" style="width:300px" placeholder="ENTER VOUCHER CODE" />
		<select class="form-control" name="stat">
			<option disabled selected>STATUS</option>
			<option value="1" <?php echo (isset($_POST['stat']) && $_POST['stat'] == "1" ? 'selected' : '')?>>Billed</option>
			<option value="2" <?php echo (isset($_POST['stat']) && $_POST['stat'] == "2" ? 'selected' : '')?>>Redeemed</option>
			<option value="3" <?php echo (isset($_POST['stat']) && $_POST['stat'] == "3" ? 'selected' : '')?>>Reconciled</option>
		</select>
		<input class="search-date form-control datetimepicker" name="datef" placeholder="FROM" type="text"/>
		<input class="search-date form-control datetimepicker" name="datet" placeholder="TO" type="text"/>
		<button type="submit" class="btn btn-info" title="Search" aria-label="Search">
		<span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>	
	</div>		
</form>
</div>

<div class="row-div serve-tbl">
<table id="queue-tbl" class="queue-tbl display table-bordered" cellspacing="0" data-form="queue">
<thead class="thead-default">
	<tr>
		<th>#</th>
		<th class="trans">MERCHANT<div class="queue-th-span"><span>ID</span><span>NAME</span></div></th>
		<th class="trans">BRANCH<div class="queue-th-span"><span>ID</span><span>NAME</span></div></th>
		<th>VOUCHER CODE</th>
		<th>POS ID</th>
		<th>AMOUNT</th>
		<th class="trans">REDEEM<div class="queue-th-span"><span>ID</span><span>DATE</span><span>STATUS</span></div></th>
		<th class="trans">RECON<div class="queue-th-span"><span>ID</span><span>DATE</span><span>STATUS</span></div></th>
		<th class="trans">PA<div class="queue-th-span"><span>ID</span><span>DATE</span><span>STATUS</span></div></th>
	</tr>
</thead>
<tbody>
</tbody>
</table>

</div>

