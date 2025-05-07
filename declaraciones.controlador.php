<?php

class ControladorDeclaraciones{

	/*=============================================
	REGISTRO DE CATEGORIA
	=============================================*/

	static public function ctrCrearDeclaracion(){

		if(isset($_POST["nuevaFechaDeclaracion"])){

			if(isset($_POST["nuevaFechaDeclaracion"])){

				$fechaInput = $_POST["nuevaFechaDeclaracion"];  // ej.: "2025-05"

				// Agrega el día "01" para formar una fecha completa "YYYY-MM-DD"
				$fechaCompleta = $fechaInput . '-01';

				$item = null;
				$valor = null;
				$orden = "id";

				// Obtenemos las declaraciones con la fecha indicada
				$declaracionesFecha = ControladorDeclaraciones::ctrMostrarDeclaraciones($item, $valor, $orden);

				// Bandera para duplicados
				$duplicado = false;
				$localizacion = "1";
				$tipo = $_POST["tipo"];

				// Recorremos el resultado y validamos la localizacion
				if(!empty($declaracionesFecha)){
					foreach ($declaracionesFecha as $declaracion) {
						if ($declaracion["fecha"] == $fechaCompleta &&
							$declaracion["localizacion"] == $localizacion &&
							$declaracion["tipo"] == $tipo) {
							$duplicado = true;
							break;
						}
						
					}
				}

				if(!$duplicado){
					$tabla = "declaraciones_iva";
	
						// Asumiendo que $_POST["nuevaFechaDeclaracion"] es "05-2025"
						// Obtén el valor del input, que estará en formato "YYYY-MM"
						$fechaInput = $_POST["nuevaFechaDeclaracion"];  // ej.: "2025-05"
	
						// Agrega el día "01" para formar una fecha completa "YYYY-MM-DD"
						$fechaCompleta = $fechaInput . '-01';
	
						$url = $_POST['url']; 
	
						$datos = array("facturas" => $_POST["facturas"],
										"fecha" => $fechaCompleta,
										"estado" => "Transmitida",
										"localizacion" => $localizacion,
										"tipo" => $_POST["tipo"],
										"pago_cuentas" => $_POST["pago_cuentas"]
									);
	
						$respuesta = ModeloDeclaraciones::mdlIngresarDeclaracion($tabla, $datos);
					
				
					if($respuesta == "ok"){
	
						echo '<script>
	
						swal({
	
							type: "success",
							title: "¡La declaración ha sido creada correctamente!",
							showConfirmButton: false, // Se elimina el botón de confirmación
							timer: 1000, // Tiempo en milisegundos antes de redirigir (2 segundos en este ejemplo)
							allowOutsideClick: false
	
						}).then(function(result){
	
							
								window.location = "'.$url.'";

	
						});
					
	
						</script>';
						return;
	
					}	
					
				} else {
					echo '<script>
					swal({
						type: "error",
						title: "¡Ya existe una declaración con esa fecha de esta sucursal!",
						showConfirmButton: false, // Se elimina el botón de confirmación
							timer: 1000, // Tiempo en milisegundos antes de redirigir (2 segundos en este ejemplo)
							allowOutsideClick: false
					}).then(function(result){
						
							window.location = "'.$url. '";
					});
				</script>';
				}

				
				


			}else{

				echo '<script>
					swal({
						type: "error",
						title: "¡La declaración no se pudo crear!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if(result.value){
							window.location = "index.php?ruta=ventas&filtroFechaInicio=&filtroFechaFin=' . date("Y-m-d") . '";
						}
					});
				</script>';


			}


		}


	}

	/*=============================================
	MOSTRAR DECLARACIONES
	=============================================*/

	static public function ctrMostrarDeclaraciones($item, $valor){

		$tabla = "declaraciones_iva";

		$respuesta = ModeloDeclaraciones::MdlMostrarDeclaraciones($tabla, $item, $valor);

		return $respuesta;
	}

	/*=============================================
	EDITAR CATEGORÍAS
	=============================================*/

	static public function ctrEditarCategoria(){

		if(isset($_POST["editarDescripcionCategoria"])){  // Verifica que el campo exista

			if(trim($_POST["editarDescripcionCategoria"]) === ""){  // Verifica si el campo está vacío
				// Si el campo está vacío, muestra un error y evita el guardado
				echo'<script>
		
					swal({
						  type: "error",
						  title: "¡La categoría no puede ir vacía!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result) {
							if (result.value) {
		
							window.location = "inventario";
		
							}
						})
		
				  </script>';
		
			} else {  // Si el campo no está vacío, procede a guardar los datos
				$tabla = "categorias";
		
				$datos = array("descripcion" => $_POST["editarDescripcionCategoria"],
								"id" => $_POST["editarIdCategoria"]);
		
				$respuesta = ModeloCategorias::mdlEditarCategoria($tabla, $datos);
		
				if($respuesta == "ok"){
		
					echo'<script>
		
					swal({
						  type: "success",
						  title: "La categoría ha sido editada correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result) {
								if (result.value) {
		
								window.location = "inventario";
		
								}
							})
		
					</script>';
				}
			}
		}
		

	}

	/*=============================================
	BORRAR DECLARACION
	=============================================*/

	static public function ctrBorrarDeclaracion(){

		if(isset($_GET["idDeclaracionEliminar"])){

			$tabla ="declaraciones_iva";
			$datos = $_GET["idDeclaracionEliminar"];

			$respuesta = ModeloDeclaraciones::mdlBorrarDeclaracion($tabla, $datos);

			if($respuesta == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "La declaracion ha sido borrada correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar",
					  closeOnConfirm: false
					  }).then(function(result) {
								if (result.value) {

								window.location = "index.php?ruta=ventas-globales&filtroFechaInicio=&filtroFechaFin=' . date("Y-m-d") . '";

								}
							})

				</script>';

			}		

		}

	}


}
	


