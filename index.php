<?php

require_once(dirname(__FILE__)."/class/backend.class.php");

if (!isset($_SESSION)){
	session_start();
}

$b = new Backend($_REQUEST);

$form = "";
$opcion = $b->get_option();
$opciones_posibles = $b->get_options();

if (!is_null($opcion)){
	//La primer validación es: que la opción seleccionada esté entre las posibles.
	//De dónde salen las opciones posibles no corresponde a este script, y
	//deberá ser abstraido y/o encapsulado en alguna clase al caso.
	if (array_search($opcion,$opciones_posibles) !== false){
		
		try{
			//Luego, busco la clase para el ABM de esa opción.
			//Si no existe, instancio una clase ABM genérica.
			//LINEAMIENTO:
			//Se pretende que las clases de ABM se llamen ABM[tabla] o [tabla]ABM.
			$clase_abm = (class_exists("ABM".$opcion)) ?  "ABM".$opcion : null;
			$clase_abm = (class_exists($opcion."ABM")) ?  $opcion."ABM" : $clase_abm;
			$abm = (!is_null($clase_abm)) ?  new $clase_abm() : new ABM($opcion);
		} catch(Exception $e){
			$me = new MensajeOperacion("\"".$opcion."\" no existe o el usuario no tiene permiso para verlo. Pruebe desloguearse y vuelva a intentar.",1000);
			$form = $me->render();
			//$form = "<div class=\"mensaje_error\">".$e->getMessage()."</div>";
		}
		
		//Ahora, con el ABM ya instanciado, trabajo sobre los campos y el listado.
		if (isset($abm) && $abm instanceOf ABM){
			$campos = $abm->get_fields();
			$abm->analizar_operacion($_REQUEST);
			if ($abm->get_operacion() == "lista"){
				$abm->search(Array($abm->get_campo_id() . " != 0"),$campos,null,true);
			}
			
			$form = $abm->render_form(Array("solo_con_rotulo"=>(!is_null($clase_abm))));
		}
	} else {
		$me = new MensajeOperacion("\"".$opcion."\" no existe o el usuario no tiene permiso para verlo. Pruebe desloguearse y vuelva a intentar.",1000);
		$form = $me->render();
		//$form = "<div class=\"mensaje_error\">\"".$opcion."\" no existe o el usuario no tiene permiso para verlo.</div>";
	}
} else {
	$form = $b->get_form();
}

$menu = $b->get_options_menu();

if (isset($_REQUEST["metodo_serializacion"]) && $_REQUEST["metodo_serializacion"] == "json"){
	die($form);
}

include("header.php");

echo("<div id=\"contenido\">");
echo($menu);
echo($form);
echo("</div>");

include("footer.php");

?>
