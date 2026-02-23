<div class="row-div profile-div">
<h3>CHANGE PASSWORD <span>[password must be at least 7 characters in length.]</span></h3>
<?php if($curr_pass != 0):?>
<div class="default-pass">The current password is a default password. Please change this password to a more secure value.</div>
<?php endif;?>
<?php if(isset($alert))echo $alert;?>
<form method="post" action="account/password" name="password">
	<ul class="list-group pass-ul">
		<li class="list-group-item">
			<div class="title">OLD PASSWORD</div>
			<div class='form-div'><?php echo form_input($input_passold); ?><?php echo form_error('passold'); ?></div>
		</li>
		<li class="list-group-item">
			<div class="title">NEW PASSWORD</div>
			<div class='form-div'><?php echo form_input($input_passnew); ?><?php echo form_error('passnew'); ?></div>
		</li>
		<li class="list-group-item">
			<div class="title">PASSWORD CONFIRMATION</div>
			<div class='form-div'><?php echo form_input($input_passconf); ?><?php echo form_error('passconf'); ?></div>
		</li>
	</ul>
	<div class="btn-div">
		<input type="submit" class="btn-submit btn btn-info" name="submit" value="SAVE" /> 
		<a href="account" class="btn-cancel btn btn-danger">CANCEL</a>
	</div>
</form>	
</div>



