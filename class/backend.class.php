<?php

require_once(dirname(__FILE__)."/util/conexion.class.php");
require_once(dirname(__FILE__)."/orm.class.php");

/* Incluyo TODAS las clases de ABM que existan declaradas */
foreach (glob(dirname(__FILE__)."/abmclasses/*.php") as $filename){
	require_once($filename);
}


class Backend {
	
	public $datos;
	
	public function __construct($data = null){
		if (is_null($data)){
			$data = Array();
		}
		
		/*
			Primero detecto algunas variables y establezco sus valores por defecto 
		*/
		$this->datos = Array();
		$this->datos["form"] = "";
		
		$opcion = isset($data["backend_option"]) ? $data["backend_option"] : null;
		$opcion = (is_null($opcion) && isset($_SESSION["backend_option"])) ? $_SESSION["backend_option"] : $opcion;
		$_SESSION["backend_option"] = $opcion;
		$user = (isset($_SESSION["backend_user"])) ? $_SESSION["backend_user"] : null;
		$_SESSION["backend_user"] = $user;
		//línea agregada para la capa ORM
		$_SESSION["user"] = $user;
		$opciones_posibles = ($user instanceOf UsuarioBackend) ? $user->get_tablas() : Array();
		$_SESSION["backend_options"] = $opciones_posibles;
		
		$data["form_operacion"] = (isset($data["form_operacion"])) ? strtolower($data["form_operacion"]) : "";
		$op = $data["form_operacion"];
		
		/*
			analizo la operación
		*/
		if ($op == "logout"){
			$this->clear();
			$this->datos["form"] = Backend::render_login_form("Usuario desconectado.");
		} else if ($op == "login" && is_null($user)){
			$this->datos["form"] = $this->login($data);
		} else if (is_null($user)){
			$this->datos["form"] = Backend::render_login_form();
		}
	}
	
	private function clear(){
		$_SESSION["backend_option"] = null;
		$_SESSION["backend_user"] = null;
		$_SESSION["backend_options"] = Array();
		session_destroy();
	}
	
	public function get_user(){
		return $_SESSION["backend_user"];
	}
	
	public function get_options(){
		return $_SESSION["backend_options"];
	}
	
	public function get_option(){
		return $_SESSION["backend_option"];
	}
	
	public function get_form(){
		return $this->datos["form"];
	}
	
	public static function render_login_form($mensaje = ""){
		$form = "<div class=\"frmABM\">
		<form method=\"post\">
			<input type=\"hidden\" value=\"login\" name=\"form_operacion\" />
			<b>/Backend - Login</b><br/><br/>
			
			<b style=\"color:#ff0000;\">".$mensaje."</b><br/><br/>
			
			Username: <input type=\"text\" value=\"\" name=\"login_username\" /><br/>
			Password: <input type=\"password\" value=\"\" name=\"login_password\" /><br/>
			<input type=\"submit\" value=\" login \" />
		</form></div>";
		return $form;
	}
	
	public function login($data){
		
		if (!is_array($data)){
			throw new Exception("Clase Backend, método login(): Se esperaba un Array.\n");
		}
		
		$data["login_username"] = (isset($data["login_username"])) ? $data["login_username"] : "";
		$data["login_password"] = (isset($data["login_password"])) ? $data["login_password"] : "";
		$p = Array("username"=>$data["login_username"], "password"=>$data["login_password"]);
		$user = new UsuarioBackend($p);
		if ($user->get_user_name() !== "ANON") {
			$_SESSION["backend_user"] = $user;
			//línea agregada para la capa de ORM
			$_SESSION["user"] = $user;
			$tablas = $user->get_tablas();
			$_SESSION["backend_options"] = $tablas;
			$tabla = (count($tablas) > 0) ? $tablas[0] : "";
			$form = "<div class=\"frmABM\">
			<form method=\"post\" id=\"post_login_form\">
				<input type=\"hidden\" value=\"lista\" name=\"form_operacion\" />
				<input type=\"hidden\" value=\"".$tabla."\" name=\"backend_opcion\" />
				<b>/Backend - Login</b><br/><br/>
				
				<b style=\"color:#00ff00;\">Login exitoso</b><br/><br/>
			</form></div>";
			return $form;
		} else {
			$_SESSION["backend_user"] = null;
			$_SESSION["user"] = null;
			return Backend::render_login_form("Usuario o contraseña incorrectos.");
		}
	}
	
	public function get_options_menu(){
		$opciones_posibles = $this->get_options();
		
		if (count($opciones_posibles) > 0){
			$menu = "<div id=\"menu_principal\">\n<label>Opciones:</label>\n";
			foreach ($opciones_posibles as $o){
				$menu .= "<div class=\"menu_item\" type=\"command\" title=\"".$o."\" onclick=\"opcion_menu(this.title);\">".$o."</div>";
			}
			$menu .= "</div>\n";
		} else {
			$menu = "";
		}
		return $menu;
	}
	
}

//Se agrega un usuario para el proyecto Backend.
class UsuarioBackend Extends Model{
	
	public function __construct($parms = null){
		//Primero establezco algunos valores por defecto
		$parms = (is_null($parms)) ? Array() : $parms;
		$parms["username"] = (!isset($parms)) ? "" : $parms["username"];
		$parms["password"] = (!isset($parms)) ? "" : $parms["password"];
		require("util/conexion.config");
		$id_usuario = null;
		
		//cargo datos de la tabla de usuarios.
		$tmp1 = BackendConfig::get_field("USER_TABLE");
		$tmp2 = BackendConfig::get_field("USER_FIELD");
		$tmp3 = BackendConfig::get_field("PASS_FIELD");
		$tmp4 = BackendConfig::get_field("PASS_ALGORITM");
		$tmp5 = BackendConfig::get_field("PASS_SALT");
		$tmp6 = BackendConfig::get_field("PASS_SALT_WHERE");
		$uconfig = Array(
			"tabla_usuario" 	=> $tmp1["value"],
			"campo_username" 	=> $tmp2["value"],
			"campo_password" 	=> $tmp3["value"],
			"modo_password" 	=> $tmp4["value"],
			"salt" 				=> $tmp5["value"],
			"salt_where" 		=> $tmp6["value"]
		);
		
		//Luego, cargo la data del modelo.
		parent::__construct($uconfig["tabla_usuario"]);
		$this->datos["uconfig"] = $uconfig;
		$this->datos["cconfig"] = $cconfig;
		$this->datos["permisos"] = Array();
		
		//Reviso si tengo información para login
		if ($parms["username"] != ""){
			$pass_qs = $this->get_password_query_string($parms["password"]);
			$qs = "select ".$this->get_campo_id()." from ".$this->get_tabla()." where ".$uconfig["campo_username"]."='".$parms["username"]."' and ".$pass_qs;
			$c = Conexion::get_instance();
			$r = $c->execute($qs);
			if (count($r) > 0){
				$id_usuario = $r[0][$this->get_campo_id()];
			} else {
				$this->datos["fields"][strtoupper($uconfig["campo_username"])]->set_valor("ANON");
			}
		} else {
			//Si no hay información, establezco el nombre de usuario a ANON.
			//Eso es, usuario anónimo.
			//Se pretende que sea un usuario especial, tal y como lo es root.
			$this->datos["fields"][strtoupper($uconfig["campo_username"])]->set_valor("ANON");
		}
		
		//Y por último, si se realizó un login, cargo el item correspondiente.
		if ((!is_null($id_usuario)) && ($id_usuario > 0)){
			$this->load($id_usuario);
		}
	}
	
	
	public function load($id=0){
		parent::load($id);
		$this->load_permisos();
	}
	
	public function load_permisos(){
		$c = Conexion::get_instance();
		$uname = $this->get_user_name();
		$qs = "select * from backend_user_tabla where username = '".$uname."'";
		$r = $c->execute($qs);
		
		$this->datos["permisos"] = $r;
	}
	
	public function get_user_name(){
		$conf = $this->datos["uconfig"];
		$ffs = $this->get_fields();
		$uname = isset($ffs[strtoupper($conf["campo_username"])]) ? $ffs[strtoupper($conf["campo_username"])]->get_valor() : "";
		return $uname;
	}
	
	public function puede($tabla = "", $accion = ""){
		
		$ret = false;
		foreach ($this->datos["permisos"] as $permiso){
			if (
				(strtoupper($tabla) == strtoupper($permiso["tabla"])) 
				&& (strtoupper($accion) == strtoupper($permiso["accion"]))
				&& (strtoupper($this->get_user_name()) == strtoupper($permiso["username"]))
				){
				$ret = true;
				break;
			}
		}
		return $ret;
	}
	
	//Función get_password_query_string()
	//Dado un password, devuelve la sintaxis de SQL para el WHERE del query que lo chequea.
	//Típicamente se usa durante un login.
	//El password puede estar guardado de diferentes maneras, y eso 
	//se configura en la instalación de /backend.
	protected function get_password_query_string($pass){
		//Por defecto, devuelve algo que siempre falla, forzando así que se configure el sistema.
		$ret = " (1 = 0) "; 
		
		$conf = $this->datos["uconfig"];
		
		if ($conf["salt"] != ""){
			$pass = ($conf["salt_where"] == "after") ? $pass.$conf["salt"] : $conf["salt"].$pass;
		}
		
		switch ($conf["modo_password"]){
			case "plain":
				$ret = " (".$conf["campo_password"]." = '".$pass."') ";
				break;
			case "md5":
				$ret = " ( ".$conf["campo_password"]." = md5('".$pass."')) ";
				break;
			case "password":
				$ret = " ( ".$conf["campo_password"]." = password('".$pass."')) ";
				break;
			case "sha1":
				$ret = " ( ".$conf["campo_password"]." = sha1('".$pass."')) ";
				break;
			default:
				break;
		}
		
		return $ret;
	}
	
	
	public function get_tablas(){
		$ret = Array();
		$c = Conexion::get_instance();
		$r = $c->execute("select distinct tabla from backend_user_tabla where username = '".$this->get_user_name()."';");
		foreach ($r as $t){
			$ret[] = $t["tabla"];
		}
		return $ret;
	}
	
}


class BackendConfig{
	
	public static function get_field($fieldName = ""){
		$return = array(array());
		
		if ((trim($fieldName) != "") && ($fieldName !== NULL)){
			$c = Conexion::get_instance();
			$return = $c->execute("select * from backend_config where name = '$fieldName';");
		}
		
		return $return[0];
	}
	
	public static function set_field($fieldName, $value){
		$r = NULL;
		if ((trim($fieldName) != "") && ($fieldName !== NULL)){
			$c = Conexion::get_instance();
			$tmp = array(); //temporal empty array.
			if (get_field($fieldName) == $tmp) {
				$r = $c->execute("insert into backend_config (field_name, field_value) values ('$fieldName', '$value') ");
			} else {
				$r = $c->execute("update backend_config set field_value = '$value' where field_name = '$fieldName' ");
			}
		}
	}
	
}

?>
