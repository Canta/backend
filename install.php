<?php
require_once(dirname(__FILE__)."/class/util/conexion.class.php");
require_once(dirname(__FILE__)."/class/orm.class.php");

if (!isset($_SESSION)){
	session_start();
}


/* pequeño código para que no jodan las warnings (que no son excepciones) */
/*------------------------------------------------------------------------*/
function errorHandler($errno, $errstr, $errfile, $errline) {
	throw new Exception($errstr, $errno);
}
set_error_handler('errorHandler');
/*------------------------------------------------------------------------*/


$op = (!isset($_REQUEST["operacion"])) ? "" : $_REQUEST["operacion"];
$ret = Array();

$ret["status"] = "OK";
$ret["codigo_error"] = 0;

switch ($op){
	case "check_database":
		$ret["mensaje"] = "La configuración se probó con éxito.";
		$ret["tablas"] = Array();
		
		$user = (isset($_REQUEST["db_user"])) ? $_REQUEST["db_user"] : null;
		$pass = (isset($_REQUEST["db_pass"])) ? $_REQUEST["db_pass"] : null;
		$db = (isset($_REQUEST["db_name"])) ? $_REQUEST["db_name"] : null; 
		$server = (isset($_REQUEST["db_host"])) ? $_REQUEST["db_host"] : null;
		$tipo_base = (isset($_REQUEST["db_engine"])) ? $_REQUEST["db_engine"] : null;
		
		$_SESSION["db_user"] = $user;
		$_SESSION["db_pass"] = $pass;
		$_SESSION["db_name"] = $db;
		$_SESSION["db_host"] = $server;
		$_SESSION["db_engine"] = $tipo_base;
		
		try{
			$c = Conexion::get_instance(true, $user, $pass, $db, $server, $tipo_base);
			
			$cconfig_string = "<?php\n\t\$cconfig = Array(
			\"user\" => \"".$user."\",
			\"pass\" => \"".$pass."\",
			\"db\" => \"".$db."\",
			\"server\" => \"".$server."\",
			\"tipo_base\" => \"".$tipo_base."\"
			); \n ?>";
			
			$tmp = fopen(dirname(__FILE__)."/class/util/conexion.config","w");
			fwrite($tmp, $cconfig_string);
			fclose($tmp);
			
			$r = $c->execute("select TABLE_NAME from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA = '".$db."';");
			$ret["tablas"] = $r;
		} catch (Exception $e){
			$ret["status"] = "ERROR";
			$ret["codigo_error"] = $e->getCode();
			$ret["mensaje"] = $e->getMessage();
		}
		
		die(json_encode($ret));
	case "tabla_usuarios":
		$ret["mensaje"] = "La tabla de usuarios se configuró con éxito.";
		$ret["campos"] = Array();
		
		$tipo = (isset($_REQUEST["tu_tipo"])) ? $_REQUEST["tu_tipo"] : null;
		$tabla = (isset($_REQUEST["tu_tabla"])) ? $_REQUEST["tu_tabla"] : null;
		
		$_SESSION["tu_tipo"] = $tipo;
		$_SESSION["tu_tabla"] = $tabla;
		
		if ($tipo == "nueva"){
			$_SESSION["campo_usuario"] = "user";
			$_SESSION["campo_password"] = "pass";
			$_SESSION["modo_password"] = "password";
			$_SESSION["salt"] = "";
			$_SESSION["salt_where"] = "antes";
		}
		
		try{
			if ($tipo == "externa"){
				$o = new ORM($tabla);
				$ret["campos"] = $o->datos["campos"];
			}
		} catch (Exception $e){
			$ret["status"] = "ERROR";
			$ret["codigo_error"] = $e->getCode();
			$ret["mensaje"] = $e->getMessage();
		}
		
		die(json_encode($ret));
	case "config_tabla_usuarios":
		$ret["mensaje"] = "Datos de la tabla de usuarios recibidos.";
		$ret["campos"] = Array();
		
		
		$campo_usuario = (isset($_REQUEST["utd_username_field"])) ? $_REQUEST["utd_username_field"] : null;
		$campo_password = (isset($_REQUEST["utd_password_field"])) ? $_REQUEST["utd_password_field"] : null;
		$modo_password = (isset($_REQUEST["utd_password_algorithm"])) ? $_REQUEST["utd_password_algorithm"] : null;
		$salt = (isset($_REQUEST["utd_password_salt"])) ? $_REQUEST["utd_password_salt"] : null;
		$salt_where = (isset($_REQUEST["utd_password_salt_where"])) ? $_REQUEST["utd_password_salt_where"] : null;
		
		$_SESSION["campo_usuario"] = $campo_usuario;
		$_SESSION["campo_password"] = $campo_password;
		$_SESSION["modo_password"] = $modo_password;
		$_SESSION["salt"] = $salt;
		$_SESSION["salt_where"] = $salt_where;
		
		die(json_encode($ret));
	case "guardar_datos":
		$ret["mensaje"] = "Los datos se guardaron con éxito.";
		$ret["usuarios"] = Array();
		
		$qs_drop_BUT = "DROP TABLE IF EXISTS backend_user_tabla;";
		$qs_create_BUT = "CREATE TABLE backend_user_tabla (`id` int(11) NOT NULL AUTO_INCREMENT,
`username` varchar(255) NOT NULL,`tabla` varchar(255) NOT NULL,
`accion` varchar(6) NOT NULL,PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
		$qs_drop_BC = "DROP TABLE IF EXISTS backend_config;";
		$qs_create_BC = "CREATE TABLE backend_config (`id` int(11) NOT NULL AUTO_INCREMENT primary key,
`name` varchar(255) NOT NULL,`value` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
		$qs_inserts_BC = Array(
			"insert into backend_config(name,value) values ('USER_TABLE','".$_SESSION["tu_tabla"]."');",
			"insert into backend_config(name,value) values ('USER_FIELD','".$_SESSION["campo_usuario"]."');",
			"insert into backend_config(name,value) values ('PASS_FIELD','".$_SESSION["campo_password"]."');",
			"insert into backend_config(name,value) values ('PASS_ALGORITM','".$_SESSION["modo_password"]."');",
			"insert into backend_config(name,value) values ('PASS_SALT','".$_SESSION["salt"]."');",
			"insert into backend_config(name,value) values ('PASS_SALT_WHERE','".$_SESSION["salt_where"]."');"
		);
		
		
		try{
			//Escribo las configuraciones de tabla de usuarios.
			/*
			$uconfig_string = "<?php\n\t\$uconfig = Array(
			\"campo_username\" => \"".$_SESSION["campo_usuario"]."\",
			\"campo_password\" => \"".$_SESSION["campo_password"]."\",
			\"modo_password\" => \"".$_SESSION["modo_password"]."\",
			\"salt\" => \"".$_SESSION["salt"]."\",
			\"salt_where\" => \"".$_SESSION["salt_where"]."\"
			); \n ?>";
			$tmp = fopen(dirname(__FILE__)."/class/util/usuario.config","w");
			fwrite($tmp, $uconfig_string);
			fclose($tmp);
			*/
			
			//Ahora, obtengo el listado de usuarios disponibles, para el próximo paso del wizard.
			$c = Conexion::get_instance();
			$c->execute($qs_drop_BUT);
			$r1 = $c->execute($qs_create_BUT);
			$c->execute($qs_drop_BC);
			$r2 = $c->execute($qs_create_BC);
			foreach ($qs_inserts_BC as $qi){
				$c->execute($qi);
			}
			
			$r3 = $c->execute("select ".$_SESSION["campo_usuario"]." as user from ".$_SESSION["tu_tabla"]." order by ".$_SESSION["campo_usuario"].";");
			
			$ret["usuarios"] = $r3;
			
		} catch (Exception $e){
			$ret["status"] = "ERROR";
			$ret["codigo_error"] = $e->getCode();
			$ret["mensaje"] = $e->getMessage();
		}
		
		die(json_encode($ret));
	case "guardar_datos_usuario":
		try{
			$c = Conexion::get_instance();
			
			$user = (isset($_REQUEST["ius_username"])) ? $_REQUEST["ius_username"] : null;
			
			$ret["mensaje"] = "Los permisos para el usuario ".$user." se guardaron con éxito.";
			
			if (!is_null($user)){
				foreach ($_REQUEST as $k=>$v){
					$d = explode("-",$k);
					if (count($d) == 3 && $d[0] == "ius_permiso"){
						
						$qs = "delete from backend_user_tabla where username = '".$user."' and tabla='".$d[2]."' and accion='".strtoupper($d[1])."';";
						$c->execute($qs);
						
						$qs = "insert into backend_user_tabla(username, tabla, accion) values ('".$user."','".$d[2]."','".strtoupper($d[1])."');";
						$c->execute($qs);
					}
				}
			} else {
				throw new Exception("Debe seleccionar un usuario.");
			}
			
		} catch (Exception $e){
			$ret["status"] = "ERROR";
			$ret["codigo_error"] = $e->getCode();
			$ret["mensaje"] = $e->getMessage();
		}
		
		die(json_encode($ret));
	default:
		break;
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>/backend - Instalación</title>
		<!--Scripts acá -->
		<script type="text/javascript" src="./js/jquery.js" ></script>
		<script type="text/javascript" src="./js/app.lib.js" ></script>
		<script type="text/javascript" src="./js/fields.js" ></script>
		<script type="text/javascript" src="./js/backend.js" ></script>
		<script type="text/javascript" src="./js/install.js" ></script>
		<!-- CSS acá -->
		<style media="all" type="text/css">
			@import url("./css/app.lib.css"); 
			@import url("./css/fields.css"); 
			@import url("./css/backend.css"); 
			@import url("./css/install.css");
		</style>
	</head>
	<body>
		<header>/Backend</header>
		<div id="cuerpo">
			<section id="welcome" nombre="Pantalla de Bienvenida" validation="true" >
				<h1>Bienvenido a /Backend</h1>
				<p>
					Este instalador lo guiará por los diferentes pasos necesarios para instalar /backend en su sistema.
				</p>
				<p>
					Presione "siguiente" para continuar.
				</p>
			</section>
			
			<section id="database" nombre="Base de datos" validation="$APP.validate_database();">
				
				<h1>Configuración de la base de datos</h1>
				<p>Para iniciar el proceso de instalación, necesita ingresar datos de la base sobre la que desea trabajar con /Backend.</p>
				<form id="form_database" >
					<p>Hostname o dirección IP: <input type="text" name="db_host" value="localhost" /></p>
					<p>Usuario de la base de datos: <input type="text" name="db_user" value="root" /></p>
					<p class="nota rojo">Atención: el usuario que provea debe tener permisos de lectura, escritura, modificación, y creación, en la base de datos. Esto es así porque /Backend utiliza este usuario internamente para la gestión de tablas. Típicamente, el usuario es <i>root</i>.</p>
					<p>&nbsp;<input type="hidden" name="operacion" value="check_database" /></p>
					<p>Password del usuario: <input type="password" name="db_pass" value="" /></p>
					<p>&nbsp;</p>
					<p>Nombre de la base de datos: <input type="text" name="db_name" value="" /></p>
					<p>Motor de base de datos: <select name="db_engine"><option value="mysql">MySQL</option><option value="postgre">PostgreSQL</option></select></p>
					<p><input type="button" onclick="$APP.check_data_database();" value=" probar datos " /></p>
				</form>
			</section>
			
			<section id="users_table" nombre="Tabla de Usuarios" validation="$APP.validate_tabla_usuarios();" >
				<h1>Configuración de la Tabla de Usuarios</h1>
				<p>/Backend necesita saber cuál es la tabla de usuarios que se utiliza en el sistema con el que debe trabajar.</p>
				<p>Esto es necesario porque /Backend administra permisos de acuerdo a usuarios del sistema, permitiéndoles modificar o no diferentes tablas.</p>
				<p>Para esto, /Backend puede adaptarse a una tabla de usuarios actualmente en uso, o crear una nueva propia que puede o no ser utilizada por el sistema.</p>
				<p>¿Qué desea hacer?:</p>
				<p>&nbsp;</p>
				<form id="form_tabla_usuario" >
					<p>&nbsp;<input type="hidden" name="operacion" value="tabla_usuarios" /></p>
					<p>Dejar que /Backend cree una tabla de usuarios nueva: <input type="radio" name="tu_tipo" value="nueva" /></p>
					<p> ó </p>
					<p>Seleccionar una tabla de usuarios entre las ya existentes en la base de datos: <input type="radio" name="tu_tipo" value="externa" /></p>
					<p><select name="tu_tabla" ></select></p>
				</form>
			</section>
			<section id="users_table_details" nombre="Detalles de la tabla" validation="$APP.check_users_table_details();" >
				<h1>Configuración de la Tabla de Usuarios</h1>
				<p>Al seleccionar una tabla de usuarios ya existente, /Backend necesita saber algunos datos adicionales para poder utilizarla efectivamente.</p>
				<p>&nbsp;</p>
				<form id="form_tabla_usuario_detalles" >
					<p>&nbsp;<input type="hidden" name="operacion" value="config_tabla_usuarios" /></p>
					<p>¿Cuál es el campo correspondiente al nombre de usuario? <select name="utd_username_field" ></select></p>
					<p>¿Cuál es el campo correspondiente al password? <select name="utd_password_field" ></select></p>
					<p>&nbsp;</p>
					<p>¿De qué modo se guarda el password? <select name="utd_password_algorithm" ><option value="plain">Texto plano</option><option value="md5">Un hash MD5</option><option value="password">Función Password() de la base de datos</option><option value="sha1">Un hash SHA1</option></select></p>
					<p>Si el password tiene un <i>salt</i> estable, por favor ingréselo aquí: <input name="utd_password_salt" type="text" /></p>
					<p>¿El <i>salt</i> (de haberlo) se concatena antes o después del password? <select name="utd_password_salt_where" ><option value="before">Antes</option><option value="after">Después</option></select></p>
					<p>&nbsp;</p>
				</form>
			</section>
			<section id="confirm" nombre="Confirmación de los datos" validation="$APP.confirm_data();" >
				<h1>Confirmación de los datos</h1>
				<p>Por favor, revise los datos ingresados y haga click en "confirmar" si son correctos.</p>
				<p>De lo contrario, puede volver a pantallas previas mediante el botón "Anterior".</p>
				<p class="rojo">Tenga en cuenta que una vez confirmados los datos, impactarán directamente en la base de datos y esa acción no puede ser cancelada.</p>
				<form id="form_confirmacion" >
					<p>&nbsp;<input type="hidden" name="operacion" value="guardar_datos" /></p>
					<p><b>Datos de la base:</b></p>
					<p>Hostname: <i id="c_db_host"></i> - Base de datos: <i id="c_db_name"></i></p>
					<p>Usuario: <i id="c_db_user"></i> - Password: <i id="c_db_pass"></i></p>
					<p>Engine (tipo de base de datos): <i id="c_db_engine"></i> </p>
					<p>&nbsp;</p>
					<p><b>Tabla de usuarios:</b></p>
					<p>¿Crear nueva o usar una existente?: <i id="c_tu_tipo"></i> - Nombre de la tabla: <i id="c_tu_tabla"></i></p>
					<p>Campo Usename: <i id="c_utd_username_field"></i> - Campo Password: <i id="c_utd_password_field"></i></p>
					<p>Modo del password: <i id="c_utd_password_algorithm"></i></p>
					<p>Salt: <i id="c_utd_password_salt"></i> - ¿Dónde vá el salt?: <i id="c_utd_password_salt_where"></i></p>
					<p>&nbsp;</p>
				</form>
			</section>
			<section id="initial_users_setup" nombre="Configuración inicial de usuarios" validation="true" >
				<h1>Configuración de usuarios</h1>
				<p>El último paso de este wizard de instalación es la configuración inicial de los permisos de usuarios del sistema.</p>
				<p>En esta pantalla se seleccionan los usuarios y se define qué acciones pueden realizar sobre qué tablas.</p>
				<p class="nota">Recuerde que en caso de loguearse al sistema con el usuario y password de la base de datos, /backend entiende que se trata de un usuario especial del sistema, llamado <i>root</i>, y ese usuario tiene permiso para realizar todas las acciones en todas las tablas.</p>
				<form id="form_users_setup" >
					<p>Seleccione un usuario del sistema <select name="ius_username" onchange="$APP.clear_permisosxtabla();"></select><input type="hidden" name="operacion" value="guardar_datos_usuario" /></p>
					<p>&nbsp;</p>
					<p>Seleccione los permisos para ese usuario:</p>
					<p>
					<table class="permisosxtabla">
						<thead>
							<tr>
								<td>Nombre de la tabla</td>
								<td>SELECT <input type="checkbox" onclick="$APP.select_column(2, $(this).attr('checked'));" /></td>
								<td>INSERT <input type="checkbox" onclick="$APP.select_column(3, $(this).attr('checked'));" /></td>
								<td>UPDATE <input type="checkbox" onclick="$APP.select_column(4, $(this).attr('checked'));" /></td>
								<td>DELETE <input type="checkbox" onclick="$APP.select_column(5, $(this).attr('checked'));" /></td>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							<tr><td><button onclick="$APP.save_users_data(); return false;" > Guardar datos del usuario </button></td></tr>
						</tfoot>
					</table>
					</p>
				</form>
			</section>
			<section id="finish" nombre="Pantalla de finalización" validation="true" >
				<h1>/Backend instalado</h1>
				<p>
					El wizard de instalación de /Backend ha finalizado.
				</p>
				<p>
					Haga click <a href="./">aquí</a> para acceder a /Backend.
				</p>
			</section>
		</div>
		<footer>
			<div>
				<button id="back_button" onclick="$APP.back()">&lt; Anterior</button>
				<span id="nombre_seccion"></span>
				<button id="next_button" onclick="$APP.next()">Siguiente &gt;</button>
			</div>
		</footer>
	</body>
</html>
