<style type="text/css">

a {
	color: #003399;
	background-color: transparent;
	font-weight: normal;
}

h1 {
	color: #444;
	background-color: transparent;
	border-bottom: 1px solid #D0D0D0;
	font-size: 19px;
	font-weight: normal;
	margin: 0 0 14px 0;
	padding: 14px 15px 10px 15px;
}

#container {
	margin: 10px;
	border: 1px solid #D0D0D0;
	box-shadow: 0 0 8px #D0D0D0;
	padding: 25px;
}

p {
	margin: 12px 15px 12px 15px;
}
#footer {
    position: absolute;
    bottom: 0;
}
</style>
<div id="container">
	<h1><?php echo $heading; ?></h1>
	<p>
	<?php echo $message; ?>		
	<a href="login" class="btn btn-info"><span class="glyphicon glyphicon-home"></span> BACK TO HOMEPAGE </a>
	</p>
</div>
