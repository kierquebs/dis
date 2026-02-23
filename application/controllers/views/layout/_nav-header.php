<nav class="navbar navbar-default">
  <div class="container-fluid">
    <ul class="nav navbar-nav">
	<?php if($admin_nav == true):?>
      <li <?php echo ($page_active == 8 ? 'class="active"':'')?>><a href="admin">ADMIN MANAGEMENT</a></li>
      <li <?php echo ($page_active == 10 ? 'class="active"':'')?>><a href="admin/access">ACCESS CONTROL</a></li>
      <li <?php echo ($page_active == 11 ? 'class="active"':'')?>><a href="admin/status">ORDER STATUS</a></li>
      <li <?php echo ($page_active == 9 ? 'class="active"':'')?>><a href="admin/audit">AUDIT TRAIL</a></li>
	  <?php else: ?>	  
      <li <?php echo ($page_active == 1 ? 'class="active"':'')?>><a href="dashboard">QUEUE BOARD</a></li>
      <li <?php echo ($page_active == 5 ? 'class="active"':'')?>><a href="dashboard/rwindow">RELEASING WINDOW</a></li>
      <li <?php echo ($page_active == 2 ? 'class="active"':'')?>><a href="order">ACTIVITY BOARD</a></li>
      <li <?php echo ($page_active == 3 ? 'class="active"':'')?>><a href="order/binloc">BIN LOCATION</a></li>
      <li <?php echo ($page_active == 6 ? 'class="active"':'')?>><a href="order/delsched">DELIVERY SCHED</a></li>
      <li <?php echo ($page_active == 4 ? 'class="active"':'')?>><a href="order/resoa">RETURNED SOA</a></li>
	  <?php endif; ?>
    </ul>
    <ul class="nav navbar-nav navbar-right">
	  <li class="dropdown <?php echo ($page_active == 7 ? 'active':'')?>">
        <a class="dropdown-toggle  glyphicon glyphicon-user" data-toggle="dropdown" href="#">
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="account">MY PROFILE</a></li>
          <li><a href="account/password">CHANGE PASSWORD</a></li>
        </ul>
      </li>
      <li><a href="login/out" aria-hidden="true" data-toggle="tooltip" title="LOGOUT"><span class="glyphicon glyphicon-log-out"></span></a></li>
    </ul>
  </div>
</nav>
<div class="queue_title"><?php echo $page_title;?>
<?php if(in_array($module_class, $backAr)):?>
<a class="fa fa-arrow-left" href="javascript:history.go(-1)"> BACK</a>
<?php endif;?>
</div>
<div class="queue_wrapper">
