<div class="row-div search-div">
	<form method="get" class="search-wrapper" id="search-form" action="javascript:">
		<div class="search-txt-div">
			<input type="text" name="search" class="form-search form-control" placeholder="FILTER ORDERID, USERNAME OR EMAIL " />
			<select class="form-control" name="type">
				<option disabled selected>USER TYPE</option>
				<?php foreach($select_utype as $urow): ?>
				<option value="<?php echo $urow->utype_id ?>"><?php echo strtoupper($urow->utype_desc)?></option>
				<?php endforeach;?>
			</select>
			<select class="form-control" name="mod" >
				<option disabled selected>--- MODULE ---</option>		
				<?php foreach($select_module as $modrow): ?>
				<option value="<?php echo $modrow->module_id ?>"><?php echo strtoupper($modrow->module_desc)?></option>
				<?php endforeach;?>
			</select>
			<select class="form-control" name="stat" >
				<option disabled selected>--- ACTION ---</option>		
				<?php foreach($select_action as $statrow): ?>
				<option value="<?php echo $statrow->auc_id ?>"><?php echo strtoupper($statrow->auc_name)?></option>
				<?php endforeach;?>
			</select>
			<input class="search-date form-control datetimepicker" name="datef" placeholder="DATE FROM" type="text"/>
			<input class="search-date form-control datetimepicker" name="datet" placeholder="DATE TO" type="text"/>
			<button type="submit" class="btn btn-info" aria-label="Search">
			  <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
			</button>			
			<button type="button" class="clear-all btn btn-info"> SHOW ALL </button>	
		</div>
	</form>
</div>

<div class="row-div serve-tbl">
<table id="queue-tbl" class="queue-tbl display table-bordered" cellspacing="0" >
<thead class="thead-default">
	<tr>
		<th>#</th>
		<th>USER INFO<div class="queue-th-span"><span>USERNAME</span><span>EMAIL</span><span>USERTYPE</span></div></th>
		<th>MODULE - ACTION<div class="queue-th-span"><span>MODULE</span><span>ACTION</span></div></th>
		<th>DETAILS<div class="queue-th-span"><span>ORDER ID</span><span> ---- </span></div></th>
		<th>DATETIME</th>
	</tr>
</thead>
<tbody>
</tbody>
</table>

</div>



