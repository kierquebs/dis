<div class="row-div search-div">
<form method="post" action="refund" class="form-wrapper sched-div form-loader" enctype="multipart/form-data">	
	<div class="form-div">
		<label>UPLOAD REFUND TXT FILE:</label>		
		<?php if(isset($error)): ?>	<span class="error"><?php echo $error ?></span><?php endif;?>
		<input type="file" name="refund_file" class="btn btn-default">
		<a href="refund" class="bin-upload btn btn-danger cancel"> CANCEL </a> 
		<input type="submit" name="file_upload" value="UPLOAD" class="bin-upload btn btn-info" /> 
	</div>
</form>
</div>

<?php if(!empty($tbl_return)):?>
<div class="row-div serve-tbl">
<div id="queue-tbl_wrapper" class="refund_tblErr dataTables_wrapper">
<?php if(isset($success))echo $success;?>
<h3>List of invalid records! (<?php echo $invalid_num?>) <a href="refund" class="close">X</a></h3>
<table class="queue-tbl display table-bordered dataTable" cellspacing="0" >
<thead class="thead-default">
	<tr><th>REDEEM ID</th><th>STATUS</th></tr>
</thead>
<tbody>
<?php foreach($tbl_return as $row):?>
	<tr class="odd queue-tr transac-tr">
		<td><?php echo $row->redeem_id;?></td>
		<td><?php echo $row->status;?></td>
	</tr>	
<?php endforeach;?>
</tbody>
</table>
</div>
</div>
<?php endif;?>

