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

	#body {
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
	</style>
</head>
<body>

<div id="container">
	<h1>COREPASS FUNCTIONS</h1>
	<div id="body">
		<ul>
			<li>Merchant Onboarding <a href="/mp_dis/syscore/get_corepass_account" target="_new">run module</a></li>
			<li>Update Corepass Product <a href="/mp_dis/syscore/get_corepass_product" target="_new">run module</a></li>
		<ul>
	</div>	
	<hr />	
	<h1>DIS FUNCTIONS</h1>
	<div id="body">
		<ul>
			<li>Upload Branches <a href="/mp_dis/automate/upload_branches" target="_new">run module</a></li>
			<li>Upload Payment Cut-off <a href="/mp_dis/automate/upload_cutoff" target="_new">run module</a></li>
		<ul>
	</div>
	<hr />	
	<h1>ZETA FUNCTIONS</h1>
	<div id="body">
		<ul>
			<li>Upload Redemption <a href="/mp_dis/redeem" target="_new">run module</a></li>
			<li>Upload Reconciliation <a href="/mp_dis/recon" target="_new">run module</a></li>
		<ul>
	</div>
	<hr />	
	<h1>NAVISION INTERFACE</h1>
	<div id="body">
		<ul>
			<li>GENERATE MERCHANT INTERFACE <a href="/mp_dis/automate/navision/merchant" target="_new">run module</a></li>
			<li>GENERATE AFFILIATE INTERFACE <a href="/mp_dis/automate/navision/remittance" target="_new">run module</a></li>
		<ul>
		<i>Disclaimer: For UAT : affiliate remittance interface file - date coverage is not yet configured!</i>
	</div>
	<p class="footer"><i>GUI FOR UAT TESTING</i></p>
</div>

</body>
</html>
