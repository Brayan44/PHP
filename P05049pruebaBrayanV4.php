<?
#######################################################################
#            MODULO DE CREACION DE ¿?      							  #
#            LUIS ACOSTA	                                   		  #
#            FECHA CREACION 2011-03-04                                #
#            FECHA MODIFICACION 2011-03-04                            #
#######################################################################
	/* Inclusion de ruta de librerias PEAR, para suso de funciones extendidas. */
ini_set("include_path",realpath(dirname(__FILE__).'/../system/lib').'/');
	/*
	 *	Carga de archivo de configuracion del sistema.
	 *	Es obligatorio su inclusion, de no obtenerlo se debe detener el script.
	 */
if(file_exists("../system/config.php")){
	require_once("../system/config.php");
}else{
	die("<strong>No existe archivo de configuracion.</strong> [<em>system/config.php</em>]");
}
	/* Carga de archivo de funciones. */
	 require_once($GLOBALS['_IV_ruta_sitio']."system/P00003.php");
	/* Validacion de sesion del sistema. */
	$GloX0002->validarIngresoSistema(getPath());
	/*
	 *	Carga de archivo de configuracion del modulo.
	 *	Es obligatorio su inclusion, de no obtenerlo se debe detener el script.
	 */
if(file_exists($GLOBALS['_IV_ruta_sitio']."pqr/config.php")){
	require_once($GLOBALS['_IV_ruta_sitio']."pqr/config.php");
}else{
	die("<strong>No existe archivo de configuracion.</strong> [<em>".$GLOBALS['_IV_ruta_sitio']."pqr/config.php"."</em>]");
}
	/*
	 *	Carga de archivo de manejo de codigos de generos del sistema.
	 *	Es obligatorio su inclusion, de no obtenerlo se debe detener el script.
	 */
	if(file_exists($GLOBALS['_IV_ruta_sitio']."system/P00005.php"))
	{
		require_once($GLOBALS['_IV_ruta_sitio']."system/P00005.php");
	}else{
		die("<strong>No existe archivo de configuracion.</strong> [<em>system/P00005.php</em>]");
	}

	/*
	 * Carga de archivo manejador de la base de datos y consultas.
	 */
	require_once($GLOBALS['_IV_ruta_sitio']."system/P00001.php");
	/*
	 * Creacion del objeto que maneja la conexion a la base de datos
	 */
	$varX0001 = new X0001($GLOBALS['_IV_xml_conexion']);


//SOLO APARECEN PQRS DE LA CIUDAD DEL USUARIO
	$usu_log = $varX0001->generarConsulta($GLOBALS['_IV_modulo']['xml'],'209',array($_SESSION['usuario']));
	$id_usuario = $usu_log->fetchRow(MDB2_FETCHMODE_ASSOC);


	$ciudad_usu = $varX0001->generarConsulta($GLOBALS['_IV_modulo']['xml'],'210',array($id_usuario['ciudad']));
	$ciudad_corta = $ciudad_usu->fetchRow(MDB2_FETCHMODE_ASSOC);
	$codigo_solo=$ciudad_corta['cod_pqr'];

	
	$meses2 = array('nada','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');	
	

	$mesP050="";
	$estados[10]="Registro";
	$estados[20]="Ejecucion";
	$estados[30]="Por Aprobar";
	$estados[40]="Aprobado";
	$estados[99]="Cancelado";
	$cantDiasXmes="";
	$varFiltro="";
	$Contador=0;

	if(isset($_GET['servicioP05050']) && strlen($_GET['servicioP05050']) > 0 )
	{
		$varFiltro 	.= " AND idservicio='".$_GET['servicioP05050']."'";
		$servicioOS = $_GET['servicioP05050'];
	}
//********************************************* OBTENER LA CANTIDAD DE DIAS POR MES **************************************************
function getMonthDays($Month, $Year)
{
   //Si la extensión que mencioné está instalada, usamos esa.
   if( is_callable("cal_days_in_month"))
   {
      return cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
   }
   else
   {
      //Lo hacemos a mi manera.
      return date("d",mktime(0,0,0,$Month+1,0,$Year));
   }
}
//************************************************************************************************************************************

	/***	Obtenemos LOS PQRS  (PQRS DE ESA CIUDAD)****/

	$resTotalOPs = $varX0001->generarConsulta(
		$GLOBALS['_IV_modulo']['xml'],	// Archivo de configuracion en xml del modulo en uso.
		'102b',							// identificador de la consulta en archivo xml.
		array(" where prefijo = '".$codigo_solo."'")							// Arreglo de parametros para consulta.
	);

	//echo $varX0001->getsql();
	 

	if (PEAR::isError($resTotalOPs)) {
		die($resTotalOPs->getMessage());
	}
// SE OBTIENE LA CANTIDAD DE PQRS DE ESA CIUDAD
	$totalOPs=$resTotalOPs->numRows();//fetchRow(MDB2_FETCHMODE_ASSOC);
//se obtiene el año seleccionado en el select del año
	$anio=date('Y');
	if(!empty($_GET['anio']))
	{
		$anio = $_GET['anio'];
	}

//se obtiene el valor del mes seleccionado en el select del mes

	
	if(!empty($_GET['mesconsulta']))
	{
		$mesP050 = $_GET['mesconsulta'];
	}else{
		$mesP050 = "01";
	}

// SE OBTIENE LA CANTIDAD DE DIAS DE ESE MES
$cantDiasXmes=getMonthDays($mesP050,$anio);
//echo $cantDiasXmes."</br>";
// SE CREAn ARREGLOs SEGUN LA CANTIDAD DE DIAS QUE TENGA EL MES POR CADA ESTADO
    $ArregloRegistro10= new SplFixedArray($cantDiasXmes+1);
	//echo $ArregloRegistro->getSize()."</br>";
	$ArregloEjecucion20= new SplFixedArray($cantDiasXmes+1);
	//echo $ArregloEjecucion->getSize()."</br>";
	$ArregloPorAprobar30= new SplFixedArray($cantDiasXmes+1);
	//echo $ArregloPorAprobar->getSize()."</br>";
	$ArregloAprobado40= new SplFixedArray($cantDiasXmes+1);
	//echo $ArregloAprobado->getSize()."</br>";
	$ArregloCancelado99= new SplFixedArray($cantDiasXmes+1);

	$ArregloCancelado99= new SplFixedArray($cantDiasXmes+1);
//	echo $ArregloCancelado->getSize()."</br>";
// SE OBTIENE EL TIPO DE GRAFICA
	$grafica=$_GET['tipografica'];
/*******************************************************************DATOS POR DIA Y ESTADO BRAYAN**********************************************/
// FOR BrAYAN
//$cont = 0;
for($l=10;$l<=50;$l+=10){
	// SE CAMBIA EL VALOR DE L POR 99 DEBIDO QUE AL MOMENTO DE REALIZAR LA CONSULA NO EXISTE UN ESTADO 50 SI NO EL 99
	$cont++;
	if($l==50){
		$l=99;
	}
	//echo "<br>$cont";
			// echo $r;
  //recorremos los dias del mes seleccionado
  for($k=1;$k<=$cantDiasXmes;$k++){

//si el dia es menor de 10 le agregamos el 0 a la izquierda para no tener problemas en la consulta y generamos la ficha de filtro
  	if($k<10){
			$vardata=$anio."-".$mesP050."-0".$k;
		}else{
			$vardata=$anio."-".$mesP050."-".$k;
		}

		
		$resPQRSxdia = $varX0001->generarConsulta(
			$GLOBALS['_IV_modulo']['xml'],	// Archivo de configuracion en xml del modulo en uso.
			'103b',							// identificador de la consulta en archivo xml.
			array($l,$vardata,$varFiltro, " and and prefijo = '".$codigo_solo."'")				// Arreglo de parametros para consulta.
		);

		//echo $varX0001->getsql()."<br> ";

		if (PEAR::isError($resPQRSxdia)) {
			die($resPQRSxdia->getMessage());
		}
//guardamos en cant la cantidad de pqrs que hay en ese dia y en ese estado
	$cant=$resPQRSxdia->fetchRow(MDB2_FETCHMODE_ASSOC);
//guardamos la cantidad de cada dia en su arreglo correspondiente de esstado
	
		 if($l==10){
		 	$ArregloRegistro10[$k]=$cant['cant'];
		 }elseif($l==20){
		 	$ArregloEjecucion20[$k]=$cant['cant'];
		 }elseif($l==30){
		 	$ArregloPorAprobar30[$k]=$cant['cant'];
		 }elseif($l==40){
		 	$ArregloAprobado40[$k]=$cant['cant'];
		 }elseif($l==99){
		 	$ArregloCancelado99[$k]=$cant['cant'];
		 }
	}	

}
	
	


	/***	CONSULTA DE TIPOS DE SERVICIO	***/
	$servicios = $varX0001->generarConsulta($GLOBALS['_IV_modulo']['xml'],'6',NULL);



		if (PEAR::isError($servicios)){
			die($servicios->getMessage());
		}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><? echo $GLOBALS['_IV_sistema']; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="<? echo $GLOBALS['_IV_sitio']; ?>css/testb.css" type="text/css" media="screen" title="Test Stylesheet" charset="utf-8" />
	<link rel="stylesheet" type="text/css" href="<? echo $GLOBALS['_IV_sitio']; ?>css/estilos.css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous"><!-- Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script><!-- Bootstrap JS-->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script><!-- Bootstrap JS-->
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> <!--para graficas google chart-->
	<script src="<? echo $GLOBALS['_IV_sitio']; ?>js/textboxlist.js" type="text/javascript" charset="utf-8"></script>
	<script src="<? echo $GLOBALS['_IV_sitio']; ?>js/test.js" type="text/javascript" charset="utf-8"></script>
	<script language="javascript" type="text/javascript">
	    

/*********************************************************************** GRAFICAS BRAYAN ***************************************************************/

		//archivos de carga para las graficas
		//USAN LAS SENTENCIAS PARA INVOCACION DE LOS METODOS DE LAS GRAFICAS SEGUN LA QUE SE DESES EN ESTE CASO BARRAS 
		 google.charts.load('current', {'packages':['bar']});
		 //SE EJECUTA LA GRAFICA CUANDO INICIE EL FORM OBVIAMENTE ENVIANDOLE LA FUNCION QUE LA EJECUTA EN ESTE CASO BARRAS
         google.charts.setOnLoadCallback(drawBarras);
         //USAN LAS SENTENCIAS PARA INVOCACION DE LOS METODOS DE LAS GRAFICAS SEGUN LA QUE SE DESES EN ESTE CASO TORTA
         google.charts.load("current", {packages:["corechart"]});
         //SE EJECUTA LA GRAFICA CUANDO INICIE EL FORM OBVIAMENTE ENVIANDOLE LA FUNCION QUE LA EJECUTA EN ESTE CASO TORTA
      	 google.charts.setOnLoadCallback(drawTorta);
      	 //USAN LAS SENTENCIAS PARA INVOCACION DE LOS METODOS DE LAS GRAFICAS SEGUN LA QUE SE DESES EN ESTE CASO LINEAS
         google.charts.load('current', {'packages':['line']});
         //SE EJECUTA LA GRAFICA CUANDO INICIE EL FORM OBVIAMENTE ENVIANDOLE LA FUNCION QUE LA EJECUTA EN ESTE CASO LINEAS
      	 google.charts.setOnLoadCallback(drawLineas);
	

        
/**************************************************************** GRAFICA DE BARRAS ********************************************************/
        function drawBarras(){
        	//traer el mes en que estamos posicionados
			<?php $mesgraf=$meses2[(int)$mesP050]; ?>
			//funcion de construccion de la grafica la primera fila es de los titulos o encabezados y el for es para llenar los doatos
        	var data = google.visualization.arrayToDataTable([
        	['Dias de '+<?php echo ("'".$mesgraf."'"); ?>+' del '+<?php echo $anio; ?>, 'Registro', 'Ejecucion', 'Por aprobar', 'Aprobado', 'Cancelado'],
        	 <?php 
        		for ($i=1; $i < $cantDiasXmes ; $i++) {
        		//El if es para indicar que cuando llegue al ultimo dato quite la coma ,  
        			if ($i  == $cantDiasXmes) {
        			   echo "[ 'Dia: ".$i."', ".$ArregloRegistro10[$i].", ".$ArregloEjecucion20[$i].", ".$ArregloPorAprobar30[$i].", ".$ArregloAprobado40[$i].", ".$ArregloCancelado99[$i]."]";
        			}else{
        			  echo "[ 'Dia: ".$i."', ".$ArregloRegistro10[$i].", ".$ArregloEjecucion20[$i].", ".$ArregloPorAprobar30[$i].", ".$ArregloAprobado40[$i].", ".$ArregloCancelado99[$i]."],";
        			}	
        		}
        	 ?> 
            ]);
        //OPCIONES COMO TITULO SUBTITULO ENTRE OTARS QUE SE LE PUEDEN AÑADIR AL GRAFICO
        var options = {
          chart: {
            title: 'INFORME DE PQRS POR MES',
            subtitle: ''+<?php echo $anio ?>+'',
          }
        };
        //SE CONSTRUYE EL GRAFICO EN EL DIV QUE QUERRAMOS CON EL TIPO DE GRAFICA QUE ES
        var chart = new google.charts.Bar(document.getElementById('GRAFICABARRAS'));
        //SE ENVIAN LOS DATOS A LA FUNCION DE DIBUJO PARA QUE LA CREE CON TIPO DE GRAFICA QUE ES
        chart.draw(data, google.charts.Bar.convertOptions(options));
}
/**************************************************************** FIN GRAFICA DE BARRAS ********************************************************/


      
/**************************************************************** GRAFICA DE TORTA ********************************************************/
      function drawTorta(){
		//traer el mes en que estamos posicionados
			<?php $mesgraf=$meses2[(int)$mesP050]; ?>
			//funcion de construccion de la grafica la primera fila es de los titulos o encabezados y el for es para llenar los doatos
        	var data = google.visualization.arrayToDataTable([
        	['Dias de '+<?php echo ("'".$mesgraf."'"); ?>+' del '+<?php echo $anio; ?>, 'Registro', 'Ejecucion', 'Por aprobar', 'Aprobado', 'Cancelado'],
        	 <?php 
        		for ($i=1; $i < $cantDiasXmes ; $i++) {
        		//El if es para indicar que cuando llegue al ultimo dato quite la coma ,  
        			if ($i  == $cantDiasXmes) {
        			   echo "[ 'Dia: ".$i."', ".$ArregloRegistro10[$i].", ".$ArregloEjecucion20[$i].", ".$ArregloPorAprobar30[$i].", ".$ArregloAprobado40[$i].", ".$ArregloCancelado99[$i]."]";
        			}else{
        			  echo "[ 'Dia: ".$i."', ".$ArregloRegistro10[$i].", ".$ArregloEjecucion20[$i].", ".$ArregloPorAprobar30[$i].", ".$ArregloAprobado40[$i].", ".$ArregloCancelado99[$i]."],";
        			}	
        		}
        	 ?> 
            ]);
        //OPCIONES COMO TITULO SUBTITULO ENTRE OTARS QUE SE LE PUEDEN AÑADIR AL GRAFICO
        var options = {
          chart: {
            title: 'INFORME DE PQRS POR MES',
            subtitle: ''+<?php echo $anio ?>+'',
            is3D: true,
          }
        };
        //SE CONSTRUYE EL GRAFICO EN EL DIV QUE QUERRAMOS
        var chart = new google.visualization.PieChart(document.getElementById('GRAFICATORTA'));
        //SE ENVIAN LOS DATOS A LA FUNCION DE DIBUJO PARA QUE LA CREE
        chart.draw(data, options);
}
/**************************************************************** FIN GRAFICA DE TORTA *******************************************************************/



/**************************************************************** GRAFICA DE LINEAS ********************************************************/
      function drawLineas(){
		//traer el mes en que estamos posicionados
			<?php $mesgraf=$meses2[(int)$mesP050]; ?>
			//funcion de construccion de la grafica la primera fila es de los titulos o encabezados y el for es para llenar los doatos
        	var data = google.visualization.arrayToDataTable([
        	['Dias de '+<?php echo ("'".$mesgraf."'"); ?>+' del '+<?php echo $anio; ?>, 'Registro', 'Ejecucion', 'Por aprobar', 'Aprobado', 'Cancelado'],
        	 <?php 
        		for ($i=1; $i < $cantDiasXmes ; $i++) {
        		//El if es para indicar que cuando llegue al ultimo dato quite la coma ,  
        			if ($i  == $cantDiasXmes) {
        			   echo "[ 'Dia: ".$i."', ".$ArregloRegistro10[$i].", ".$ArregloEjecucion20[$i].", ".$ArregloPorAprobar30[$i].", ".$ArregloAprobado40[$i].", ".$ArregloCancelado99[$i]."]";
        			}else{
        			  echo "[ 'Dia: ".$i."', ".$ArregloRegistro10[$i].", ".$ArregloEjecucion20[$i].", ".$ArregloPorAprobar30[$i].", ".$ArregloAprobado40[$i].", ".$ArregloCancelado99[$i]."],";
        			}	
        		}
        	 ?> 
            ]);
        //OPCIONES COMO TITULO SUBTITULO ENTRE OTARS QUE SE LE PUEDEN AÑADIR AL GRAFICO
        var options = {
          chart: {
            title: 'INFORME DE PQRS POR MES',
            subtitle: ''+<?php echo $anio ?>+'',
            axes: {
          		x: {
            		0: {side: 'top'}
          		}
        	}	
          }
        };
        //SE CONSTRUYE EL GRAFICO EN EL DIV QUE QUERRAMOS
        var chart = new google.charts.Line(document.getElementById('GRAFICALINEAS'));
        //SE ENVIAN LOS DATOS A LA FUNCION DE DIBUJO PARA QUE LA CREE
        chart.draw(data, google.charts.Line.convertOptions(options));
}
/**************************************************************** FIN GRAFICA DE LINEAS *******************************************************************/
      

/*******************************************************************FIN GRAFICAS****************************************************************************/
</script>

		
</head>
<body >

<form action="<? echo $GLOBALS['_IV_sitio']; ?>pqr/P05049pruebaBrayanV4.php"  name="GRAFICAPQR" id="GRAFICAPQR" method="GET" enctype="multipart/form-data">
<!--CONTENEDOR DE LAS FILAS Y COLUMNAS DEL FORM-->
<div class="container-fluid">

<div class="row">
	<div class="col-lg-12">
		<ol class="breadcrumb breadcrumb-success">
  			<li class="breadcrumb-item active"><h2><span class="badge badge-secondary">Busqueda</span></h2></li>
		</ol>
	</div>
</div>
<!--filas-->
<div class="row">


	<div class="col-md-3">
	    <label for="anio" class="label label-default"><h3><span class="badge badge-primary">AÑO</span></h3></label>
	    <!--Dropdown DEL AÑO A SELECCIONAR-->
		<select name="anio" id="anio" class="form-control form-control"><option value="">Seleccione</option>
				<?
					for($i=0;$i<5;$i++)
					{
						if($anio==(date("Y")-$i))
						{
							$seleccion='selected="selected"';
						}
						else
						{
							$seleccion='';
						}
						echo '<option value="'.(date("Y")-$i).'" '.$seleccion.' >'.(date("Y")-$i).'</option>';
					}
				?>
		</select>
	</div>


	<div class="col-md-3">
		<label for="mesconsulta" class="col-form-label"><h3><span class="badge badge-primary">MES</span></h3></label>
	    <!--Dropdown DEL MES A SELECIONAR-->
		<select name="mesconsulta" id="mesconsulta" class="form-control form-control">
			 <option value="01" <?=(isset($_GET['mesconsulta']) && $_GET['mesconsulta'] == '01') ? 'selected' : ''?>>ENERO</option>
			 <option value="02" <?=(isset($_GET['mesconsulta']) && $_GET['mesconsulta'] == '02') ? 'selected' : ''?>>FEBRERO</option>
			 <option value="03" <?=(isset($_GET['mesconsulta']) && $_GET['mesconsulta'] == '03') ? 'selected' : ''?>>MARZO</option>
			 <option value="04" <?=(isset($_GET['mesconsulta']) && $_GET['mesconsulta'] == '04') ? 'selected' : ''?>>ABRIL</option>
			 <option value="05" <?=(isset($_GET['mesconsulta']) && $_GET['mesconsulta'] == '05') ? 'selected' : ''?>>MAYO</option>
			 <option value="06" <?=(isset($_GET['mesconsulta']) && $_GET['mesconsulta'] == '06') ? 'selected' : ''?>>JUNIO</option>
			 <option value="07" <?=(isset($_GET['mesconsulta']) && $_GET['mesconsulta'] == '07') ? 'selected' : ''?>>JULIO</option>
			 <option value="08" <?=(isset($_GET['mesconsulta']) && $_GET['mesconsulta'] == '08') ? 'selected' : ''?>>AGOSTO</option>
			 <option value="09" <?=(isset($_GET['mesconsulta']) && $_GET['mesconsulta'] == '09') ? 'selected' : ''?>>SEPTIEMBRE</option>
			 <option value="10" <?=(isset($_GET['mesconsulta']) && $_GET['mesconsulta'] == '10') ? 'selected' : ''?>>OCTUBRE</option>
			 <option value="11" <?=(isset($_GET['mesconsulta']) && $_GET['mesconsulta'] == '11') ? 'selected' : ''?>>NOVIEMBRE</option>
			 <option value="12" <?=(isset($_GET['mesconsulta']) && $_GET['mesconsulta'] == '12') ? 'selected' : ''?>>DICIEMBRE</option>

			</select>

	</div>

	<div class="col-md-3">
		<label for="servicioP05050" class="col-form-label"><h3><span class="badge badge-primary">SERVICIOS</span></h3></label>
		<!--Dropdown DE SERVICIOS-->
		<?php $tr = $servicios->numRows(); ?>
		<select name="servicioP05050" id="servicioP05050" class="form-control form-control">
					<option value="">Seleccione</option>
					<?php
					for($a=0; $a <$tr; $a++){
						$datos = $servicios->fetchRow(MDB2_FETCHMODE_ASSOC);
					?><option value="<?=$datos['idservicio']?>" <? if($datos['idservicio']==$servicioOS) { echo 'selected="selected"'; } ?> ><?= ucwords($datos['nombre']);?></option>
					<?php } ?>
		</select>
	</div>


	<div class="col-md-3">
	</div>	
</div>

<div class="row">
</br>
</div>



<div class="row">
</br>
</div>

<div class="row">
	<div class="col-lg-12">
		<!--BOTON PARA RECARGAR EL FORM CON LOS DATOS DE LOS FILTROS-->
		<button type="submit" class="btn btn-success btn-lg" >Generar</button>
	</div>	
</div>

<div class="row">
</br></br>
</div>

<div class="row">
	<div class="col-lg-12">
		<ol class="breadcrumb">
  			<li class="breadcrumb-item active"><h2><span class="badge badge-secondary">Grafica Principal</span></h2></li>
		</ol>
	</div>
</div>

<div class="row">
</br>
</div>

<div class="row">

	<div class="col-lg-12">

		<div id="GRAFICABARRAS" style="width: 100%; height: 500px;"></div>

	</div>	

</div>

<div class="row">
</br></br>
</div>

<div class="row">
 	<div class="col-lg-6">
		<ol class="breadcrumb">
  			<li class="breadcrumb-item active"><h2><span class="badge badge-secondary">Grafica Torta</span></h2></li>
		</ol>
	</div>
	<div class="col-lg-6">
		<ol class="breadcrumb">
  			<li class="breadcrumb-item active"><h2><span class="badge badge-secondary">Grafica Lineas</span></h2></li>
		</ol>
	</div>	
</div>


<div class="row">
</div>

<div class="row">

	<div class="col-lg-6">
		<div id="GRAFICATORTA" style="width: 100%; height: 500px;"></div>
	</div>
	<div class="col-lg-6">
		<div id="GRAFICALINEAS" style="width: 100%; height: 500px;"></div>
	</div>		

</div>



  
</div><!--FIN CONTAINER-->



</form>

</body>
</html>
<?

/* Se verifica si le objeto de conexion esta en uso, para su debido procedimiento de cierre de conexion. */
	if($varX0001 != NULL){
		$varX0001->cerrar();
	}
?>






