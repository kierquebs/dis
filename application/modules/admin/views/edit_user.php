<div class="row-div profile-div">
<h3>EDIT PROFILE</h3>
<form method="post" action="admin/edit/<?php echo $user_id?>">
	<div class="prof-ul">
		<ul class="list-group">
			<li class="list-group-item">
				<div class="title">EMAIL ADDRESS</div>
				<div class="content-span"><?php echo $data_user->row('email'); ?></div>
			</li>
			<li class="list-group-item">
				<div class="title">USERNAME</div>
				<div class='form-div'><?php echo form_error('username'); ?><?php echo form_input($input_user); ?></div>
			</li>
			<li class="list-group-item">
				<div class="title">FULLNAME</div>
				<div class='form-div'><?php echo form_error('fullname'); ?><?php echo form_input($input_fullname); ?></div>
			</li>
		</ul>
	</div>
	<div class="prof-ul">
		<ul class="list-group">
			<li class="list-group-item">
				<div class="title">ROLE</div>
				<div class="content-span">	
					<select class="form-control" name="type">
						<option disabled selected>SELECT</option>
						<option value="1" <?php echo ($data_user->row('utype_id') == 1 ? 'selected' : '')?>>Admin</option>
						<option value="2" <?php echo ($data_user->row('utype_id') == 2 ? 'selected' : '')?>>Reimbursement</option>
						<option value="3" <?php echo ($data_user->row('utype_id') == 3 ? 'selected' : '')?>>Read Only</option>
						<option value="4" <?php echo ($data_user->row('utype_id') == 4 ? 'selected' : '')?>>Finance</option>
					</select>					
				</div>				
			</li>
			<li class="list-group-item">
				<div class="title">STATUS</div>
				<div class="content-span">
				<select class="form-control" name="status" >
				<?php $status = $data_user->row('status'); ?>
					<option disabled>SELECT</option>		
					<option value="1" <?php echo ($status == 1 ? 'selected' :'')?>>ACTIVE</option>
					<option value="0" <?php echo ($status == 0 ? 'selected' :'')?>>INACTIVE</option>
				</select>				
				</div>
			</li>
		</ul>
	</div>
	<div class="btn-div">
		<input type="submit" class="btn-submit btn btn-info" name="submit" value="SAVE" /> 
		<a href="admin" class="btn-cancel btn btn-danger">CANCEL</a>
	</div>
</form>	
</div>



