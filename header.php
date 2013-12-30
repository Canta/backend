<?php
	if (!isset($_SESSION)){
		session_start();
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>A N S V: Sistema de Carga de Siniestros</title>
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
				<img style="float:left" title="Logo ANSV" alt="ANSV" src="../lib/images/logo-ansv.gif">
				</a>
				<a target="_blank" href="http://www.mininterior.gov.ar/">
				<img style="float:right" title="Logo Ministerio" alt="Ministerio del Interior" src="../lib/images/logo-ministerio.gif">
				</a>
				<div style="text-align:right;"><a href="./?form_operacion=logout">LOGOUT</a></div>
			</div>
			
	</div>
		<div id="layout-navigation" class="group">
	<div class="bodyInf">
		
		<div class="bodyInfSombraLinea">
		<div id="layout-main-container">
			<div id="layout-main" class="group">
