<?php
	if (!isset($_SESSION)){
		session_start();
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Backend</title>
<!--Scripts acá -->
<script type="text/javascript" src="../lib/js/jquery-1.7.min.js" ></script>
<script type="text/javascript" src="./js/app.lib.js" ></script>
<script type="text/javascript" src="./js/fields.js" ></script>
<script type="text/javascript" src="./js/backend.js" ></script>
<!-- CSS acá -->

<style media="all" type="text/css">
	@import url("../lib/css/default.css"); 
	@import url("./css/app.lib.css"); 
	@import url("./css/fields.css"); 
	@import url("./css/backend.css"); 
</style>

</head>
<body>
	<div id="layout-wrapper" class="tripel-123 aside-12">		
		<!--div class="bodySup"></div-->
			<div class="bodyLogos">
			<a href="/">
				<div style="text-align:right;"><a href="./?form_operacion=logout">LOGOUT</a></div>
			</div>
			
	</div>
		<div id="layout-navigation" class="group">
	<div class="bodyInf">
		
		<div class="bodyInfSombraLinea">
		<div id="layout-main-container">
			<div id="layout-main" class="group">
