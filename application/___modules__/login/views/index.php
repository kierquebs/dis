<div class="page-title"></div>
<div class="row form-col">
	<form method="post" action="login<?php echo ($activate == true ? '/activate/'.$this->uri->segment(3):'')?>" class="form-wrapper">
		<div class="form-div">
			<div><?php echo form_error('username'); ?><br /></div>
			<div class="form-group">
				<label for="cname">USERNAME OR EMAIL: </label>
				<?php echo form_input($username); ?>
			</div>
			<div class="form-group">
				<label for="cperson">PASSWORD: <?php echo form_error('password'); ?></label>
				<?php echo form_input($password); ?>
			</div>
			<div class="form-group">
			<div class="col-sm-10">
				<a href="login/forgot">FORGOT PASSWORD</a>
			</div>
			<div class="submit-wrapper col-sm-2">
				<input type="submit" id="clickSubmit" name="submit" value="LOGIN" /> 
			</div>
			</div>
		</div>
	</form>
</div>
</div>
