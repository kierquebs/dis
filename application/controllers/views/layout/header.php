<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Sodexo Digital Interface Service</title>
<base href="<?php echo base_url();?>"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon" />
<link rel="stylesheet" href="assets/css/bootstrap.min.css?<?php echo time();?>">  
<?php 
/*FOR DATA TABLES*/
if(isset($cssDT) && count($cssDT) != 0){
	for($icssDT=0;$icssDT<count($cssDT);$icssDT++){
		if(!empty($cssDT[$icssDT]))echo '<link rel="stylesheet" href="assets/dataTables/'.$cssDT[$icssDT].'?'.time().'">';
	}
}	
?>
<?php if(isset($nav_div) && $nav_div == 1){ ?>
<link rel="stylesheet" href="assets/css/font-awesome.min.css">
<link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.css">
<link rel="stylesheet" href="assets/css/bootstrap-select.css">
<?php }?>
<link rel="stylesheet" href="assets/css/main.css?<?php echo time();?>">
<?php 
if(isset($css) && count($css) != 0){
	for($icss=0;$icss<count($css);$icss++){
		if(!empty($css[$icss]))echo '<link rel="stylesheet" href="assets/css/modules/'.$css[$icss].'?'.time().'">';
	}
}	
?>
</head>
<body>
<header id="header">
    <div class="wrapper clearfix">
        <i class="icon-mob-menu visiblePhone">&nbsp;</i>
        <div class="logo">
            <a href="#" target="_self"><img src="assets/images/logo.png" alt="sodexo" /></a>
        </div>		
		<h2>Digital Interface Service (DIS)</h2>
    </div>
</header>
<div class="container-fluid">
