<?php

require_once(dirname(__FILE__)."/../util/conexion.class.php");
require_once(dirname(__FILE__)."/../orm.class.php");


class organismoABM extends ABM{
	
	public function __construct($tabla = null){
		parent::__construct("organismo");
	}
	
	public function setup_fields(){
		$fs = $this->get_fields();
		//echo("antes: ".$fs["ID_PAIS"]->get_valor().",".$fs["ID_PROVINCIA"]->get_valor().",".$fs["ID_PARTIDO"]->get_valor().",".$fs["ID_LOCALIDAD"]->get_valor()."<br/>");
		$req = $this->get_request_data();
		
		$pais = 1;
		if ($fs["ID_PAIS"]->get_valor() != "" && $fs["ID_PAIS"]->get_valor() != 0 && !is_null($fs["ID_PAIS"]->get_valor())){
			$pais = $fs["ID_PAIS"]->get_valor();
		} else if (isset($req["ID_PAIS"])){
			$pais = $req["ID_PAIS"];
		}
		
		$fs["ID_PAIS"]->set_valor($pais);
		$fs["ID_PAIS"]->set_events(Array("onchange"=>"accion_update_fields(['ID_PROVINCIA','ID_PARTIDO','ID_LOCALIDAD'],'./')"));
		
		
		$prov = null;
		if ($fs["ID_PROVINCIA"]->get_valor() != "" && $fs["ID_PROVINCIA"]->get_valor() != 0 && !is_null($fs["ID_PROVINCIA"]->get_valor())){
			$prov = $fs["ID_PROVINCIA"]->get_valor();
		} else if (isset($req["ID_PROVINCIA"])){
			$prov = $req["ID_PROVINCIA"];
		}
		
		$data["query"] = "select id as id, Nombre as nombre from provincia where id_pais = '%1'";
		$data["valores"] = Array($pais);
		$data["campo_descriptivo"] = "nombre";
		$data["campo_indice"] = "id";
		$data["tipo_sql"] = "int";
		$fs["ID_PROVINCIA"] = new ConditionalQueryEnumField(
			$fs["ID_PROVINCIA"]->get_id(),
			"Provincia",
			$fs["ID_PROVINCIA"]->get_valor(),
			$data
		);
		$fs["ID_PROVINCIA"]->set_events(Array("onchange"=>"accion_update_fields(['ID_PARTIDO','ID_LOCALIDAD'],'./')"));
		$fs["ID_PROVINCIA"]->set_requerido(true);
		
		if ($fs["ID_PROVINCIA"]->get_valor() == ""){
			$tmp_items = $fs["ID_PROVINCIA"]->get_items();
			if (count($tmp_items) > 0){
				$prov = ($fs["ID_PROVINCIA"]->get_requerido()) ? $tmp_items[0][$fs["ID_PROVINCIA"]->get_campo_indice()] :  $tmp_items[1][$fs["ID_PROVINCIA"]->get_campo_indice()];
			}
		}
		
		$part = null;
		if ($fs["ID_PARTIDO"]->get_valor() != "" && $fs["ID_PARTIDO"]->get_valor() != 0 && !is_null($fs["ID_PARTIDO"]->get_valor())){
			$part = $fs["ID_PARTIDO"]->get_valor();
		} else if (isset($req["ID_PARTIDO"])){
			$part = $req["ID_PARTIDO"];
		}
		
		$data["query"] = "select id as id, Nombre as nombre from partido where id_Provincia = '%1'";
		$data["valores"] = Array($prov);
		$fs["ID_PARTIDO"] = new ConditionalQueryEnumField(
			$fs["ID_PARTIDO"]->get_id(),
			"Partido",
			$fs["ID_PARTIDO"]->get_valor(),
			$data
		);
		$fs["ID_PARTIDO"]->set_events(Array("onchange"=>"accion_update_fields(['ID_LOCALIDAD'],'./')"));
		$fs["ID_PARTIDO"]->set_requerido(true);
		
		if ($fs["ID_PARTIDO"]->get_valor() == ""){
			$tmp_items = $fs["ID_PARTIDO"]->get_items();
			if (count($tmp_items) > 0){
				$part = ($fs["ID_PARTIDO"]->get_requerido()) ? $tmp_items[0][$fs["ID_PARTIDO"]->get_campo_indice()] :  $tmp_items[1][$fs["ID_PARTIDO"]->get_campo_indice()];
			}
		}
		
		$loca = null;
		if ($fs["ID_LOCALIDAD"]->get_valor() != "" && $fs["ID_LOCALIDAD"]->get_valor() != 0 && !is_null($fs["ID_LOCALIDAD"]->get_valor())){
			$loca = $fs["ID_LOCALIDAD"]->get_valor();
		} else if (isset($req["ID_LOCALIDAD"])){
			$loca = $req["ID_LOCALIDAD"];
		}
		$data["query"] = "select id as id, Nombre as nombre from localidad where id_Partido = '%1'";
		$data["valores"] = Array($part);
		$fs["ID_LOCALIDAD"] = new ConditionalQueryEnumField(
			$fs["ID_LOCALIDAD"]->get_id(),
			"Localidad",
			$fs["ID_LOCALIDAD"]->get_valor(),
			$data
		);
		
		//echo("después: ".$fs["ID_PAIS"]->get_valor().",".$fs["ID_PROVINCIA"]->get_valor().",".$fs["ID_PARTIDO"]->get_valor().",".$fs["ID_LOCALIDAD"]->get_valor()."<br/>");
		
		$this->set_fields($fs);
	}
	
	public function render_form($data = null){
		$data = (is_array($data)) ? $data : Array();
		$data["solo_con_rotulo"] = false;
		return parent::render_form($data);
	}
}

?>
