<nav class="navbar navbar-default">
<div class="container-fluid">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
		<span class="sr-only">Toggle navigation</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		</button>			
	</div>
	<div id="navbar" class="navbar-collapse collapse">
		<ul class="nav navbar-nav">
		<?php if(in_array(5, $access_arr)):?>
		  <li <?php echo ($page_active == 5 ? 'class="active"':'')?>>
		   <a class="dropdown-toggle" data-toggle="dropdown" href="#">ADMIN<span class="caret"></span></a>
			<ul class="dropdown-menu">
			  <li><a href="admin">MANAGEMENT</a></li>
			</ul>
		  </li>
		<?php endif; ?>
		  <li <?php echo ($page_active == 1 ? 'class="active"':'')?>><a href="transaction">Transaction Report</a></li>
		<?php if(in_array(2, $access_arr)):?>
		  <li <?php echo ($page_active == 2 ? 'class="active"':'')?>><a href="process">PA Process</a></li>
		<?php endif; ?>	
		<?php if(in_array(3, $access_arr)):?>	
		  <li <?php echo ($page_active == 3 ? 'class="active"':'')?>><a href="summary">PA Summary</a></li>
		<?php endif; ?>
		</ul>  
		<ul class="nav navbar-nav navbar-right">
		<li class="dropdown <?php echo ($page_active == 8 ? 'active':'')?>">
		<a class="dropdown-toggle" data-toggle="dropdown" href="#">Welcome <?php echo $this->auth->get_username()?> <span class="caret"></span></a>
			<ul class="dropdown-menu">
			  <li><a href="account"><span class="glyphicon glyphicon-user"></span> VIEW PROFILE</a></li>
			  <li><a href="account/password"><span class="glyphicon glyphicon-lock"></span> CHANGE PASSWORD</a></li>
			  <li role="separator" class="divider"></li>
			  <li><a href="login/out" aria-hidden="true" title="LOGOUT"><span class="glyphicon glyphicon-log-out"></span> LOG OUT</a></li>
			</ul>
		</li>
		</ul>
		
	</div><!--/.nav-collapse -->
</div><!--/.container-fluid -->
</nav>
<div class="queue_title"><?php echo $page_title;?>
<?php if(!empty($backAr)):?>
<a class="fa fa-arrow-left" href="javascript:history.go(-1)"> BACK</a>
<?php endif;?>
</div>
<div class="queue_wrapper">
