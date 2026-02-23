<div class="row-div search-div">
<form method="get" id="search-form" action="javascript:" class="search-wrapper" autocomplete="off">
	<div class="search-txt-div">
		<input type="text" name="search" class="form-search form-control" style="width:150px" placeholder="PA NUMBER" /><input type="text" name="mid" class="number form-search form-control" style="width:150px" placeholder="MERCHANT ID" /><input type="text" name="mname" class="form-search form-control" style="width:350px" placeholder="MERCHANT NAME" />
		<button type="submit" class="btn btn-info" title="Search" aria-label="Search"> SEARCH
		<span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>	
		<button type="button" class="clear-all btn btn-info" title="Remove Filter">CLEAR</button>
		<button type="button" class="btn btn-success" id="print_pa" title="PRINT PA" disabled>PRINT COPY PAYMENT ADVICE</button>
	</div>		
</form>
</div>

<div class="row-div serve-tbl">
<form method="post" action="pdf_pa/print_copy" class="form-loader" name="printForm" id="printForm">
<div class="search-notif" style="width: 90%; margin: 0 auto; text-align: center; padding: 20px; font-size: 14px; font-weight: bold;">Enter PA Number OR Merchant ID or Merchant Name</div>
<table id="queue-tbl" class="queue-tbl display table-bordered" cellspacing="0" data-form="queue">
<thead class="thead-default">
	<tr>
		<th width="25"><input type="checkBox" id="checkAll"></th>
		<th width="200px">PAYMENT ADVICE ID</th>
		<th width="200px">MERCHANT ID</th>
		<th>MERCHANT NAME</th>
		<th width="200px">TOTAL AMOUNT</th>
		<th width="200px">PAYMENT DUE DATE</th>
	</tr>
</thead>
<tbody>
</tbody>
</table>
</form>
</div>

