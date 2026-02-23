<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>COREPASS UPDATE FUNCTIONS</title>

	<style type="text/css">

	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

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

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	.body, .result {
		margin: 0 15px 0 15px;
	}

	p.footer {
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}

	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}
	
	hr{
		border: 1px solid #D0D0D0
	}
	form input[type="text"]{
		width: 50%;
		padding: 5px;
		margin-bottom: 10px;
	}
	form input[type="submit"]{
		display: inline-block;
		padding: 5px;
	}
	ul i{
		color: red;
	}
	</style>
</head>
<body>

<div id="container">
	<h1>SALESFORCE CHECKER IN COREPASS</h1>
	<form method="post" action="/mp_dis/sfchecker">
	<h1>AGREEMENT</h1>
	<div class="body">					
		<input type="text" name="agr_input" placeholder="ENTER AGREEMENT_ID"/>
		<input type="submit" name="delpoint_id" value="GET DELIVERY POINT ID" />
		<input type="submit" name="sproleag_id" value="GET SPROLE ID" />
	</div>	
	<hr />	
	<h1>ACCOUNT</h1>
	<div class="body">		
		<input type="text" name="acc_input" placeholder="ENTER COREPASS_ID" />
		<input type="submit" name="people_id" value="GET PEOPLE ID" />
		<input type="submit" name="sprole_id" value="GET SPROLE ID" />
	</div>		
	</form>	
	<hr />	
	<div class="result">
		<h3>RESULT HERE</h3>
		<?php
			if($resultRow != 0):
			$fields = $result->list_fields(); 
		?>
		<h4><?php 
		if(isset($_POST['agr_input'])) echo 'AGREEMENT ID: '.$_POST['agr_input'];
		else if(isset($_POST['acc_input'])) echo 'COREPASS ID: '.$_POST['acc_input'];
		?></h4>
		<ul>
		<?php foreach($result->result() as $data): ?>
		<li>
		<?php foreach ($fields as $field): ?>
			<b><?php echo $field?></b>: <?php  echo $data->$field;?>	
		<?php endforeach;?>
		</li>
		<?php endforeach; ?>
		</ul>
		<?php
			endif;
		?>
	</div>
	<p class="footer">TESTING SITE</p>
</div>

</body>
</html>
