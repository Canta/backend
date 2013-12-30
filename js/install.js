
$APP = {};
$APP.current_section = -1;
$APP.sections = [];
$APP.database_validated = false;
$APP.tablas = [];

$(document).ready(
	function(a,b){
		$APP.sections = $("section");
		$APP.back_button = $("#back_button");
		$APP.next_button = $("#next_button");
		window.setTimeout("$APP.next()",500);
	}
)

$APP.next = function(){
	
	$valid = eval($($APP.sections[$APP.current_section]).attr("validation"));
	
	if ($valid === undefined){
		$valid = true;
	}
	
	if ($valid === true){
		if ($APP.current_section >= 0){
			$APP.hide_section($APP.current_section);
			$APP.back_button.removeAttr("disabled");
		} else {
			$APP.back_button.attr("disabled","disabled");
		}
		
		$APP.current_section = ($APP.current_section < $APP.sections.length -1) ? $APP.current_section + 1 : $APP.current_section;
		$APP.show_section($APP.current_section);
		
		if ($APP.current_section < $APP.sections.length - 1) {
			$APP.next_button.removeAttr("disabled");
		} else {
			$APP.next_button.attr("disabled", "");
		}
	} else {
		$APP.mostrar_error("Hay datos inválidos.\nPor favor, revise los datos y vuelva a intentar.");
	}
}

$APP.back = function(){
	
	if ($APP.current_section < $APP.sections.length) {
		$APP.hide_section($APP.current_section, "left");
		$APP.next_button.removeAttr("disabled");
	} else {
		$APP.next_button.attr("disabled", "");
	}
	
	$APP.current_section = ($APP.current_section > 0) ? $APP.current_section - 1 : $APP.current_section;
	$APP.show_section($APP.current_section, "right");
	
	if ($APP.current_section > 0){
		$APP.back_button.removeAttr("disabled");
	} else {
		$APP.back_button.attr("disabled","disabled");
	}
	
}

$APP.show_section = function($i, $direccion){
	if ($direccion==undefined){
		$direccion = "left";
	}
	$opt = {"duration":500, "left":"15%"};
	$origen = "";
	if ($direccion=="left"){
		$origen = "115%";
	} else if ($direccion == "right"){
		$origen = "-115%";
	}
	
	
	$($APP.sections[$i]).css("left",$origen).animate($opt, function(){
		$("#nombre_seccion").html( 
			$($APP.sections[$APP.current_section]).attr("nombre") 
		);
	});
}

$APP.hide_section = function($i, $direccion){
	if ($direccion==undefined){
		$direccion = "right";
	}
	$opt = {};
	if ($direccion=="left"){
		$opt = {"duration":500, "left":"115%"};
	} else if ($direccion == "right"){
		$opt = {"duration":500, "left":"-115%"};
	}
	
	$($APP.sections[$i]).css("left","15%").animate($opt);
}

$APP.mostrar_error = function ($msg){
	alert("Mensaje de error:\n\n" + $msg);
}

$APP.mostrar_mensaje = function ($msg){
	alert("Mensaje del sistema:\n\n" + $msg);
}

$APP.validate_database = function(){
	return $APP.database_validated;
}

$APP.check_data_database = function(){
	$("body").css("cursor", "wait");
	$APP.database_validated = false;
	
	if ($.trim($("[name='db_name']").val()) == ""){
		$("body").css("cursor", "default");
		$APP.mostrar_error("Debe establecer un nombre de base de datos.");
		$("[name='db_name']").focus();
		return false;
	}
	
	$.ajax({
		url: "./install.php",
		type: "post",
		data: $("#form_database").serialize(),
		cache: false,
		async: true,
		dataType: "json",
		success: function($data,$status_text,$objXHR){
			$APP.database_validated = ($data.status == "OK");
			$("body").css("cursor", "default");
			$sh = $("[name='tu_tabla']");
			$sh.html("");
			$tmp_html = "";
			for ($i = 0; $i < $data.tablas.length; $i++){
				$tmp_html += "<option value=\""+$data.tablas[$i].TABLE_NAME+"\">"+$data.tablas[$i].TABLE_NAME+"</option>";
			}
			$sh.html($tmp_html);
			$APP.tablas = $data.tablas;
			
			if ($APP.database_validated){
				$("#c_db_name").html($("[name='db_name']").val());
				$("#c_db_pass").html("**NO SE MUESTRA**");
				$("#c_db_host").html($("[name='db_host']").val());
				$("#c_db_user").html($("[name='db_user']").val());
				$("#c_db_engine").html($("[name='db_engine']").val());
				//algunos valores por defecto para más adelante
				$("#c_tu_tipo").html("nueva");
				$("#c_tu_tabla").html("user");
				$("#c_utd_username_field").html("user");
				$("#c_utd_password_field").html("pass");
				$APP.mostrar_mensaje("Los datos de la base son correctos.");
			} else {
				$APP.mostrar_error("Error #"+$data.codigo_error+":\n\""+$data.mensaje+"\"");
			}
		},
		error: function($a, $b, $c){
			$("body").css("cursor", "default");
			$APP.database_validated = false;
			$APP.mostrar_error("No se pudo conectar a la base de datos.");
		}
	});
}

$APP.validate_tabla_usuarios = function(){
	$arr = $("[name='tu_tipo']");
	if ($arr[0].checked || $arr[1].checked){
		
		$("body").css("cursor", "wait");
		
		if ($arr[0].checked){
			$APP.current_section++;
		}
		
		$res = $.ajax({
			url: "./install.php",
			type: "post",
			data: $("#form_tabla_usuario").serialize(),
			cache: false,
			async: false,
		}).responseText;
		
		$obj = JSON.parse($res);
		
		$("body").css("cursor", "default");
		
		if ($obj.status == "OK"){
			$tmp_html = "";
			$("[name='utd_username_field']").html("");
			$("[name='utd_password_field']").html("");
			for ($i = 0; $i < $obj.campos.length; $i++){
				$tmp_html += "<option value=\""+$obj.campos[$i].COLUMN_NAME+"\">"+$obj.campos[$i].COLUMN_NAME+"</option>\n";
			}
			$("[name='utd_username_field']").html($tmp_html);
			$("[name='utd_password_field']").html($tmp_html);
			
			$("#c_tu_tipo").html($("[name='tu_tipo']:checked").val());
			$("#c_tu_tabla").html( ($("[name='tu_tipo']").val() == "externa") ? $("[name='tu_tipo']").val() : "user");
			
			return true;
		} else {
			if ($arr[0].checked){
				$APP.current_section--;
			}
			$APP.mostrar_error("Error #"+$obj.codigo_error+":\n\""+$obj.mensaje+"\"");
			return false;
		}
	} else {
		$APP.mostrar_error("Debe seleccionar una de las dos opciones de tabla de usuarios.");
		return false;
	}
}

$APP.check_users_table_details = function(){
	$campo_username = $("[name='utd_username_field']").val();
	$campo_password = $("[name='utd_password_field']").val();
	
	if ($campo_username != $campo_password){
		
		$("body").css("cursor", "wait");
		
		$res = $.ajax({
			url: "./install.php",
			type: "post",
			data: $("#form_tabla_usuario_detalles").serialize(),
			cache: false,
			async: false,
		}).responseText;
		
		$obj = JSON.parse($res);
		
		$("body").css("cursor", "default");
		
		if ($obj.status == "OK"){
			
			$("#c_utd_username_field").html($("[name='utd_username_field']").val());
			$("#c_utd_password_field").html($("[name='utd_password_field']").val());
			$("#c_utd_password_algorithm").html($("[name='utd_password_algorithm']").val());
			$("#c_utd_password_salt").html($("[name='utd_password_salt']").val());
			$("#c_utd_password_salt_where").html($("[name='utd_password_salt_where']").val());
			
			return true;
		} else {
			$APP.mostrar_error("Error #"+$obj.codigo_error+":\n\""+$obj.mensaje+"\"");
			return false;
		}
		
		return true;
	} else {
		$APP.mostrar_error("Los campos Usuario y Password deben ser diferentes.");
		return false;
	}
}

$APP.confirm_data = function () {
		
	$("body").css("cursor", "wait");
	
	$res = $.ajax({
		url: "./install.php",
		type: "post",
		data: $("#form_confirmacion").serialize(),
		cache: false,
		async: false,
	}).responseText;
	
	$obj = JSON.parse($res);
	
	$("body").css("cursor", "default");
	
	if ($obj.status == "OK"){
		
		$tmp_html = "<option value='ANON'>ANON</option>\n";
		for ($i = 0; $i < $obj.usuarios.length; $i++){
				$tmp_html += "<option value=\""+$obj.usuarios[$i].user+"\">"+$obj.usuarios[$i].user+"</option>\n";
			}
		$("[name='ius_username']").html($tmp_html);
		$APP.clear_permisosxtabla();
		return true;
	} else {
		$APP.mostrar_error("Error #"+$obj.codigo_error+":\n\""+$obj.mensaje+"\"");
		return false;
	}
	
}

$APP.clear_permisosxtabla = function () {
	$tmp_html = "";
	
	for($i = 0; $i < $APP.tablas.length; $i++){
		
		$clase = ($i % 2 == 0) ? "par" : "impar";
		
		$tmp_html += "<tr class=\""+$clase+"\">";
		
		$tmp_html += "<td>"+$APP.tablas[$i].TABLE_NAME+"</td>";
		$tmp_html += "<td><input type=\"checkbox\" name=\"ius_permiso-select-"+$APP.tablas[$i].TABLE_NAME+"\" /></td>";
		$tmp_html += "<td><input type=\"checkbox\" name=\"ius_permiso-insert-"+$APP.tablas[$i].TABLE_NAME+"\" /></td>";
		$tmp_html += "<td><input type=\"checkbox\" name=\"ius_permiso-update-"+$APP.tablas[$i].TABLE_NAME+"\" /></td>";
		$tmp_html += "<td><input type=\"checkbox\" name=\"ius_permiso-delete-"+$APP.tablas[$i].TABLE_NAME+"\" /></td>";
		
		$tmp_html += "</tr>\n";
	}
	
	$("[class='permisosxtabla'] tbody").html($tmp_html);
}

$APP.select_column = function($numero, $checked){
	
	$selector = "tr>td:nth-child("+$numero+")>input";
	
	$checkall = function($i, $e){
		$($e).attr("checked", "checked");
	}
	
	$uncheckall = function($i, $e){
		$($e).removeAttr("checked");
	}
	
	if ($checked !== undefined){
		$($selector).each($checkall);
	} else {
		$($selector).each($uncheckall);
	}
	
}

$APP.save_users_data = function(){
	$("body").css("cursor", "wait");
	
	$.ajax({
		url: "./install.php",
		type: "POST",
		data: $("#form_users_setup").serialize(),
		cache: false,
		async: true,
		dataType: "JSON",
		success: function($data,$status_text,$objXHR){
			$("body").css("cursor", "default");
			if ($data.status == "OK"){
				$APP.mostrar_mensaje($data.mensaje);
			} else {
				$APP.mostrar_error("Error #"+$data.codigo_error+":\n\""+$data.mensaje+"\"");
			}
		},
		error: function($a, $b, $c){
			$("body").css("cursor", "default");
			$APP.mostrar_error("No se pudo conectar al servidor.");
		}
	});
	
	
	return true;
}
