<?php

require_once(dirname(__FILE__)."/../util/conexion.class.php");
require_once(dirname(__FILE__)."/../orm.class.php");


class paisABM extends ABM{
	
	public function __construct($tabla = null){
		parent::__construct("pais");
	}
	
	public function setup_fields(){
		parent::setup_fields();
		
		$fs = $this->get_fields();
		
		$fs["NOMBRE"]->set_rotulo("Nombre");
		//$fs["NOMBRE"] = new GoogleMapsField($fs["NOMBRE"]->get_id(), "Chupala gil: ", $fs["NOMBRE"]->get_valor());
		
		$this->set_fields($fs);
		
	}
	
}

?>
