<div class="row-div">
	<form method="post" action="admin" class="form-wrapper">
		<div class="form-title">ADD USER</div>
		<div class="form-div form-half">
			<label for="orderid">USERNAME <?php echo form_error('username'); ?></label>
			<?php echo form_input($input_user); ?>
		</div>
		<div class="form-div form-half">
			<label for="orderid">EMAIL ADD <?php echo form_error('email'); ?></label>
			<?php echo form_input($input_email); ?>
		</div>
		<div class="form-div form-half">
			<label for="orderid">FULL NAME <?php echo form_error('full_name'); ?></label>
			<?php echo form_input($input_name); ?>
		</div>
		<div class="form-div form-half">
			<label for="orderid">USER TYPE <?php echo form_error('type'); ?></label>			
			<select class="form-control" name="type">
				<option disabled selected>SELECT</option>
				<option value="1" <?php echo (set_value('type') == 1 ? 'selected' : '')?>>IT Admin</option>
				<option value="2" <?php echo (set_value('type') == 2 ? 'selected' : '')?>>Reimbursement</option>
				<option value="3" <?php echo (set_value('type') == 3 ? 'selected' : '')?>>Read Only</option>
				<option value="4" <?php echo (set_value('type') == 4 ? 'selected' : '')?>>Finance</option>
			</select>
		</div>
		<div class="form-div">
			<input type="submit" name="submit" value="ADD" /> 
		</div>
	</form>
</div>

<div class="row-div search-div">
	<form method="get" class="search-wrapper" id="search-form" action="javascript:">
		<div class="search-txt-div">
			<input type="text" name="search" class="form-search form-control" placeholder="FILTER USER NAME or EMAIL ADDRESS" />
			<select class="form-control" name="type">
				<option disabled selected>USER TYPE</option>
				<option value="1">IT Admin</option>
				<option value="2">Reimbursement</option>
				<option value="3">Read Only</option>
				<option value="4">Finance</option>
			</select>
			<select class="form-control"name="status" >
				<option disabled selected>STATUS</option>		
				<option value="1">ACTIVE</option>
				<option value="2">INACTIVE</option>
			</select>
			<button type="submit" class="btn btn-info" aria-label="Search">
			  <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
			</button>			
			<button type="button" class="clear-all btn btn-info"> SHOW ALL </button>		
		</div>
	</form>
</div>

<div class="row-div serve-tbl">
<table id="comment-tbl" class="queue-tbl display table-bordered" cellspacing="0" width="95%">
<thead class="thead-default">
	<tr>
		<th class="text-center">#</th>
		<th class="text-center">USER NAME</th>
		<th class="text-center">EMAIL ADDRESS</th>
		<th class="text-center">STATUS</th>
		<th class="text-center">  --- </th>
	</tr>
</thead>
<tbody>
</tbody>
</table>

</div>




