<div class="row-div search-div">
<form method="get" id="search-form" action="javascript:" class="search-wrapper" autocomplete="off">
	<div class="search-txt-div">
		<input type="text" name="search" class="form-search form-control" style="width:300px" placeholder="Merchant ID or NAME" />
		<input type="text" name="branch" class="form-search form-control" style="width:300px" placeholder="Branch ID or NAME" />
		<input type="text" name="panumber" class="form-search form-control" style="width:150px" placeholder="RS NUMBER" <?php if(!empty($RS_NUMBER)) echo 'value='.$RS_NUMBER;?> />
		<input type="text" name="voucher" class="form-search form-control" style="width:500px" placeholder="VOUCHER CODE" />
		<br />
		<select class="form-control" name="stat">
			<option disabled selected>Status</option>
			<option value="">All</option>
			<option value="1" <?php echo (isset($_POST['stat']) && $_POST['stat'] == "1" ? 'selected' : '')?>>CONVERTED</option>
			<option value="2" <?php echo (isset($_POST['stat']) && $_POST['stat'] == "2" ? 'selected' : '')?>>PROCESSED</option>
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
<table id="queue-tbl" class="queue-tbl display table-bordered" cellspacing="0" data-form="queue">
<thead class="thead-default">
	<tr>
		<th>COREPASS ID</th>
		<th width="500px">MERCHANT NAME</th>
		<th>TIN NUMBER</th>
		<th width="250px">BRANCH ID</th>
		<th width="250px">BRANCH NAME</th>
		<th class="trans">USER<div class="queue-th-span"><span>ID</span><span>NAME</span></div></th>
		<th width="100px">PRODUCT</th>
		<th width="250px">VOUCHER CODE</th>
		<th>DENO</th>
		<th>AMOUNT</th>
		<th>STATUS</th>
		<th width="250px">CONVERTED DATETIME</th>
		<th class="trans">RS<div class="queue-th-span"><span>NUMBER</span><span>DATE GENERATED</span></div></th>
	</tr>
</thead>
<tbody>
</tbody>
</table>

</div>

