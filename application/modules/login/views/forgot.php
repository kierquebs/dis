<div class="page-title"></div>
<div class="row form-col">
	<form method="post" action="login/forgot" class="form-wrapper">
		<div class="form-div">
			<?php if(isset($alert))echo $alert;?>
			<div class="form-group">
				<label for="cname">EMAIL ADDRESS: <?php echo form_error('email'); ?></label>
				<?php echo form_input($email); ?>
			</div>
			<div class="form-group">
			<div class="col-sm-10">
				<a href="login">Login your account</a>
			</div>
			<div class="submit-wrapper col-sm-3">
				<input type="submit" name="submit" value="SEND EMAIL" class="btn btn-info"/> 
			</div>
			</div>
		</div>
	</form>
</div>
</div>
