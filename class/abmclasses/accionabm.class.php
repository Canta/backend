<?php

require_once(dirname(__FILE__)."/../util/conexion.class.php");
require_once(dirname(__FILE__)."/../orm.class.php");


class accionABM extends ABM{
	
	public function __construct($tabla = null){
		parent::__construct("accion");
	}
	
	public function setup_fields(){
		$fs = $this->get_fields();
		
		$fs["NOMBRE"]->set_rotulo("Nombre: ");
		$fs["DESCRIPCION"]->set_rotulo("Descripción: ");
		$fs["IS_MENU"]->set_rotulo("¿Es menú?: ");
		//$fs["IS_MENU"] = new SiNoEnumField($fs["IS_MENU"]->get_id(), "¿Es menú?: ", $fs["IS_MENU"]->get_valor());
		$fs["ID_PADRE"]->set_rotulo("Padre: ");
		$fs["URL"]->set_rotulo("URL: ");
		$fs["NIVEL"]->set_rotulo("Nivel: ");
		$fs["ORDEN"]->set_rotulo("Orden: ");
		
		$this->set_fields($fs);
	}
	
}

?>
