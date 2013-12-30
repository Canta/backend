<?php

require_once(dirname(__FILE__)."/../util/conexion.class.php");
require_once(dirname(__FILE__)."/../orm.class.php");


class perfilABM extends ABM{
	
	public function __construct($tabla = null){
		parent::__construct("perfil");
	}
	
	public function render_form($data = null){
		/*
		if ($this->get_operacion() == "lista"){
			$ret = parent::render_form();
		} else {
			$ret = "<img src=\"http://hypelife.tv/wp-content/uploads/2010/11/abmDONe.jpg\" />";
		}*/
		
		$ret = parent::render_form();
		
		return $ret;
	}
	
}

?>
