<div class="row-div profile-div">
<?php if(isset($alert))echo $alert;?>
<h3>PROFILE</h3>
<form method="post" action="account" name="edit">
	<div class="prof-ul">
		<ul class="list-group">
			<li class="list-group-item">
				<div class="title">USERNAME</div>
				<div class="content <?php echo (isset($_POST['submit'])? 'hide':'')?>"><?php echo $data_user->user_name; ?></div>
				<div class='form-div <?php echo (isset($_POST['submit'])? '':'hide')?>'><?php echo form_error('username'); ?><?php echo form_input($input_user); ?></div>
			</li>
			<li class="list-group-item">
				<div class="title">FULLNAME</div>
				<div class="content <?php echo (isset($_POST['submit'])? 'hide':'')?>"><?php echo $data_user->full_name; ?></div>
				<div class='form-div <?php echo (isset($_POST['submit'])? '':'hide')?>'><?php echo form_error('fullname'); ?><?php echo form_input($input_fullname); ?></div>
			</li>
		</ul>
	</div>
	<div class="prof-ul">
		<ul class="list-group">
			<li class="list-group-item">
				<div class="title">EMAIL ADDRESS</div>
				<div class="content-span"><?php echo $data_user->email; ?></div>
			</li>
			<li class="list-group-item">
				<div class="title">STATUS</div>
				<div class="content-span"><?php echo ($data_user->status == 1 ? 'ACTIVE' : 'INACTIVE' ); ?></div>
			</li>
		</ul>
	</div>
	<div class="btn-div">
		<span <?php echo (!isset($_POST['submit']) ? 'class="hide"' : '')?>>
			<input type="submit" class="btn-submit btn btn-info" name="submit" value="SAVE" /> 
			<input type="button" class="btn-cancel btn btn-danger" name="cancel" value="CANCEL" /> 
		</span>
		<a href="account/password" class="btn btn-warning <?php echo (isset($_POST['submit']) ? 'hide' : '')?>">CHANGE PASSWORD</a>
		<button type="button" class="btn-edit btn btn-info <?php echo (isset($_POST['submit']) ? 'hide' : '')?>">EDIT PROFILE</button>
	</div>
</form>	
</div>



