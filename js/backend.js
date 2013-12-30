
function opcion_menu($nombre){
	$url = location.href.split("?")[0];
	$form = $("<form method=\"post\" action=\""+$url+"\">");
	$input = $form.append("<input type='hidden' value='"+$nombre+"' name='backend_option' />");
	$("body").append($form);
	
	$form[0].submit();
}


$(document).ready(
	function(){
		app.ui.setup_fields();
	}
);
