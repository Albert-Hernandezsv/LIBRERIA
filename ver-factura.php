<?php

    if($_SESSION["rol"] == "Admin" || $_SESSION["rol"] == "Facturación" || $_SESSION["rol"] == "Contabilidad" || $_SESSION["rol"] == "Vendedor"){
    } else {
        echo '<script>
        window.location = "inicio";
        </script>';
    return;
    }

?>
<div id="loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(148, 148, 148, 0.37); display: flex; justify-content: center; align-items: center; z-index: 9999;">
    <h2>Cargando...</h2>
</div>
<script>
    $(window).on('load', function() {
        $('#loader').fadeOut();
    });
</script>
<div class="main-content content-wrapper">

  <section class="content-header">
    <?php

        $item = "id";
        $valor = $_GET["idFacturaEditar"];
        $orden = "fecEmi";
        $optimizacion = "no";

        $factura = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

        $item = "id";
        $valor = $factura["id_cliente"];
        $orden = "id";

        $cliente = ControladorClientes::ctrMostrarClientes($item, $valor, $orden);

        $tipoDteTaba = $factura["tipoDte"];

        $tipoFacturaTexto = "";

        switch ($tipoDteTaba) {
            case "01":
                $tipoFacturaTexto = "Factura ".$factura["numeroControl"];
                break;
            case "03":
                $tipoFacturaTexto = "Comprobante de crédito fiscal ".$factura["numeroControl"];
                break;
            case "04":
                $tipoFacturaTexto = "Nota de remisión ".$factura["numeroControl"];
                break;
            case "05":
                $tipoFacturaTexto = "Nota de crédito ".$factura["numeroControl"];
                break;
            case "06":
                $tipoFacturaTexto = "Nota de débito ".$factura["numeroControl"];
                break;
            case "07":
                $tipoFacturaTexto = "Comprobante de retención ".$factura["numeroControl"];
                break;
            case "08":
                $tipoFacturaTexto = "Comprobante de liquidación ".$factura["numeroControl"];
                break;
            case "09":
                $tipoFacturaTexto = "Documento contable de liquidación ".$factura["numeroControl"];
                break;
            case "11":
                $tipoFacturaTexto = "Factura de exportación ".$factura["numeroControl"];
                break;
            case "14":
                $tipoFacturaTexto = "Factura de sujeto excluido ".$factura["numeroControl"];
                break;
            case "15":
                $tipoFacturaTexto = "Comprobante de donación ".$factura["numeroControl"];
                break;

            default:
                echo "Factura no válida";
                break;
        }
        $url = "https://admin.factura.gob.sv/consultaPublica?ambiente=00&codGen=" . urlencode($factura["codigoGeneracion"]) . "&fechaEmi=" . urlencode($factura["fecEmi"]);
    ?>
    <?php
        if($factura["modo"] === "Normal"){
            echo '
                <button class="btn btn-success" onclick="location.href=\'facturacion\'">
          
          Regresar
          
    </button>
            ';
        } else {
            echo '
                <button class="btn btn-success" onclick="location.href=\'facturacion-contingencia\'">
          
          Regresar
          
    </button>
            ';
        }
    ?>

    
    <button class="btn btn-warning" onclick="location.href='<?php echo $url; ?>'">Ver en Hacienda</button>
    <button class="btn btn-info" onclick="location.href='extensiones/TCPDF-main/examples/imprimir-factura.php?idFactura='+<?php echo $factura['id'] ?>">Generar pdf</button>
    
    <button class="btn btn-primary btnEnviarFacturaCorreo" idFactura="<?php echo $factura['id'] ?>">Enviar factura a cliente</button>
    <?php
            if($factura["tipoDte"] === "04" || $factura["tipoDte"] === "05" || $factura["tipoDte"] === "06"){

            } else{
                echo '<button class="btn btn-dark" onclick="location.href=\'extensiones/TCPDF-main/examples/imprimir-ticket.php?idFactura=' . $factura['id']. '\'">Generar ticket</button>
                    <button class="btn btn-secondary editarTicket" data-toggle="modal" data-target="#modalEditarTicket">Configuración ticket</button>';
            }
        

        date_default_timezone_set('America/El_Salvador');

        // Fecha y hora de emisión
        $fecEmi = $factura["fecEmi"] . ' ' . $factura["horEmi"];
        
        // Obtener la fecha y hora actual
        $fechaActual = new DateTime(); // Fecha y hora actual
        
        // Crear un objeto DateTime con la fecha de emisión
        $fechaEmision = new DateTime($fecEmi);
        
        // Clonar la fecha de emisión y sumar 3 meses (para la primera validación)
        $fechaLimiteTresMeses = clone $fechaEmision;
        $fechaLimiteTresMeses->modify('+3 months')->setTime(23, 59, 59); // 3 meses después a las 23:59:59
        
        // Clonar la fecha de emisión y agregar 1 día (para la segunda validación)
        $fechaLimiteUnDia = clone $fechaEmision;
        $fechaLimiteUnDia->modify('+1 day')->setTime(23, 59, 59); // Día siguiente a las 23:59:59
        
        // Verificar si la fecha actual es anterior a 3 meses
        if ($fechaActual <= $fechaLimiteTresMeses) {
            // Aún no han pasado 3 meses
            // Verificar si la fecha actual coincide con el día que mh da
            if ($fechaActual <= $fechaLimiteUnDia) {
            
              if($factura["firmaDigital"] === ""){
                echo '<button class="btn btn-danger btnEliminarFactura" idFactura="'.$factura["id"].'">Eliminar factura</button>';
                } else {
                  if($factura["sello"] === ""){
                      echo '<button class="btn btn-danger btnEliminarFactura" idFactura="'.$factura["id"].'">Eliminar factura</button>';
                  } else {

                    if($_SESSION["rol"] == "Admin" || $_SESSION["rol"] == "Contabilidad"){
                      if($factura["estado"] != "Anulada"){
                        echo '<button class="btn btn-danger btnEliminarFacturaHacienda" idFactura="'.$factura["id"].'">Anular factura</button>';
                      } 
                    }
                  }
                  
              }
              if($factura["sello"] != "" && $factura["estado"] != "Anulada"){

                if($factura["tipoDte"] == "03") {
                  if($_SESSION["rol"] == "Admin" || $_SESSION["rol"] == "Contabilidad"){
                    echo '<button class="btn btn-info btnNotaCredito" idFactura="'.$factura["id"].'">NC</button>
                    <button class="btn btn-success btnNotaDebito" idFactura="'.$factura["id"].'">ND</button>
                    <button class="btn btn-dark btnNotaRemision" idFactura="'.$factura["id"].'">NR</button>';
                  }
                }
              }

            } else {
                if($factura["sello"] != "" && $factura["estado"] != "Anulada"){

                  if($factura["tipoDte"] == "03") {
                    if($_SESSION["rol"] == "Admin" || $_SESSION["rol"] == "Contabilidad"){
                      echo '<button class="btn btn-info btnNotaCredito" idFactura="'.$factura["id"].'">NC</button>
                      <button class="btn btn-success btnNotaDebito" idFactura="'.$factura["id"].'">ND</button>
                      <button class="btn btn-dark btnNotaRemision" idFactura="'.$factura["id"].'">NR</button>';
                    }
                  }

                    if($_SESSION["rol"] == "Admin" || $_SESSION["rol"] == "Contabilidad"){
                      if($factura["estado"] != "Anulada" && ($factura["tipoDte"] == "01" || $factura["tipoDte"] == "11")){
                        echo '<button class="btn btn-danger btnEliminarFacturaHacienda" idFactura="'.$factura["id"].'">Anular factura</button>';
                      }
                    }                   
                  
                } else {
                  echo '<button class="btn btn-danger btnEliminarFactura" idFactura="'.$factura["id"].'">Eliminar factura</button>';
                }
            }
        } else {
            // Han pasado más de 3 meses no hacer nada
        }
    
    
    ?>
    
    <?php
        if ($factura["sello"] == "" && $factura["firmaDigital"] != "") {
            echo '<button class="btn btn-danger btnEliminarFirma" idEliminarFirma="'.$factura["id"].'">Eliminar firma</button><br><br>';
        }
        
        
        if($_SESSION["tokenInicioSesionMh"] == ""){
        } else {
          echo '<button class="btn btn-warning" onclick="location.href=\'index.php?ruta=crear-factura&idCliente=1\'">Factura rápida</button>';
        }
        ?>
        <?php
            // Se recomienda iniciar el búfer de salida para evitar que cualquier contenido
            // accidental se imprima antes o después del HTML.
            ob_start();

            // Supongamos que ya tienes definido $factura con sus respectivos campos.
            $jwt = $factura["firmaDigital"];
            $codigoGeneracion = $factura["codigoGeneracion"];
        ?>
        <button class="btn btn-secondary" data-toggle="modal" data-target="#modalVerClientes">Ver clientes registrados - crear factura</button>
        <button class="btn btn-success" onclick="descargarJSON()">Descargar JSON</button>
        <script>
            // Se pasa la variable PHP a JavaScript de forma segura
            const jwt = "<?= addslashes($jwt) ?>";

            function descargarJSON() {
                // Separamos el JWT en partes y tomamos el payload (la parte central)
                const partes = jwt.split('.');
                if (partes.length < 2) {
                    alert('El formato del JWT no es válido.');
                    return;
                }
                const payloadBase64 = partes[1];

                // Convertir de base64url a base64 normal
                let base64 = payloadBase64.replace(/-/g, '+').replace(/_/g, '/');

                // Se añade el relleno si es necesario
                while (base64.length % 4 !== 0) {
                    base64 += '=';
                }

                // Decodificamos el base64 para obtener el JSON string
                const jsonStr = atob(base64);

                // Se crea un Blob a partir del JSON string
                const blob = new Blob([jsonStr], { type: 'application/json' });

                // Se genera una URL a partir del blob
                const url = URL.createObjectURL(blob);

                // Se crea un elemento <a> para forzar la descarga, sin redirigir la página
                const a = document.createElement('a');
                a.href = url;
                a.download = "<?= addslashes($codigoGeneracion) ?>.json";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                // Liberar la URL creada
                URL.revokeObjectURL(url);
            }
        </script>
    <br><br>
    <h1>
      
      <?php echo($tipoFacturaTexto); ?>
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i>Inicio </a></li>
      
      <li class="active">&nbsp;Sistema de facturación</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
        Datos de facturación
<br><br>
      </div>

      
        
      <div class="box-body">

        <?php
            if(isset($_GET["pagado"])){
                $item = "id_factura";
                $valor = $_GET["idFacturaEditar"];
                $orden = "id";
    
                $abonoLei = ControladorFacturas::ctrMostrarAbonos($item, $valor, $orden);
                ?>
                <style>
                    /* Estilo para el fondo semi-transparente de la superposición */
                    .overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.5);
                        z-index: 1000;
                    }
                    /* Estilo para el iframe "flotante" */
                    .iframe-popup {
                        position: fixed;
                        top: 50%;
                        left: 50%;
                        width: 800px;
                        height: 600px;
                        transform: translate(-50%, -50%);
                        z-index: 1001;
                        border: none;
                    }
                    /* Estilo para el botón de cierre */
                    .close-button {
                        position: fixed;
                        top: 150px;
                        right: 400px;
                        z-index: 1002;
                        background-color: #ff0000;
                        color: #fff;
                        border: none;
                        padding: 10px 15px;
                        font-size: 16px;
                        cursor: pointer;
                        border-radius: 4px;
                    }
                </style>

                <!-- Contenedor para la superposición -->
                <div class="overlay" id="popupOverlay">
                    <!-- Botón de cierre -->
                    <button class="close-button" id="closeButton">Cerrar</button>
                    <!-- Iframe que actúa como ventana emergente -->
                    <iframe class="iframe-popup" id="pdfFrame" src="extensiones/TCPDF-main/examples/imprimir-ticket.php?idFactura=<?php echo $factura['id'] ?>"></iframe>
                </div>

                <script>
                    // Cuando se carga el iframe, espera un momento y se invoca la impresión del contenido del iframe
                    document.getElementById("pdfFrame").onload = function() {
                        setTimeout(function(){
                            document.getElementById("pdfFrame").contentWindow.print();
                        }, 500);  // Ajusta este valor a 1000 u otro si es necesario
                    };

                    // Funcionalidad para cerrar la superposición al hacer clic en el botón
                    document.getElementById("closeButton").addEventListener("click", function() {
                        var overlay = document.getElementById("popupOverlay");
                        overlay.style.display = "none";
                    });
                </script>

            <?php
            }
        ?>

      

       <!--=====================================
        FORMULARIO CREAR FACTURA
        ======================================-->

        <form role="form" method="post" id="enviarFacturaLoca" enctype="multipart/form-data">

            <?php
                if($factura["tipoDte"] == "01" && ($cliente["tipo_cliente"] == "00" || $cliente["tipo_cliente"] == "01")){ //Factura, Persona normal y declarante de IVA
                    echo '
                        <form role="form" method="post" enctype="multipart/form-data">

                            <!--=====================================
                            CABEZA DEL MODAL
                            ======================================-->

                            <div class="modal-header" style="background:grey; color:white">
                            <h4 class="modal-title">Abonar factura</h4>

                            </div>

                            <!--=====================================
                            CUERPO DEL MODAL
                            ======================================-->

                            <div class="modal-body">

                            <div class="box-body">

                                <div class="row">
                                    <div class="col-xl-4 col-xs-12">
                                        Forma de pago:
                                        <br><br>
                                        <!-- ENTRADA PARA LA FORMA DE ABONO -->            
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoIdFacturaAbono" value="'.$factura["id"].'" hidden>
                                                <select class="form-control" name="nuevaFormaAbono" value="" required>
                                                    <option value="Efectivo">Efectivo</option>
                                                    <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                                                    <option value="Tarjeta de débito">Tarjeta de débito</option>
                                                    <option value="Cheque">Cheque</option>
                                                    <option value="Transferencia">Transferencia</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-xs-12">
                                        <!-- ENTRADA PARA LA GESTIÓN FINANCIERA -->            
                                        <div class="form-group">
                                            Gestión financiera, si es efectivo dejar en blanco, si tarjeta colocar el # de gestión, si es cheque el # de cheque y si es transferencia colocar el # de confirmación
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevaGestion" placeholder="Ingresar gestión financiera">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL BANCO -->
                                        Nombre del banco, si es efectivo dejar en blanco           
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoBanco" placeholder="Ingresar nombre del banco">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL MONTO -->            
                                        Monto a abonar
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoMontoAbono" id="nuevoMontoAbono" placeholder="Ingresar monto a abonar" value="'.$factura["total"]+$factura["flete"]+$factura["seguro"].'" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL DINERO -->            
                                        Monto dinero con que el cliente paga
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>';
                                                if(isset($_GET["pagado"])){
                                                    echo '<input type="text" class="form-control" name="nuevoMontoDinero" id="nuevoMontoDinero" placeholder="Ingresar monto a abonar" value="'.$abonoLei["dinero_cliente"].'" required>';
                                                } else {
                                                    echo '<input type="text" class="form-control" name="nuevoMontoDinero" id="nuevoMontoDinero" placeholder="Ingresar monto a abonar" required>';
                                                }
                                            echo '
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL VUELTO -->            
                                        Vuelto
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>';
                                                if(isset($_GET["pagado"])){
                                                    echo '<input type="text" class="form-control" name="nuevoMontoVuelto" id="nuevoMontoVuelto" value="'.$abonoLei["dinero_cliente"] - $abonoLei["monto"].'" placeholder="Ingresar monto a abonar" required readonly>';
                                                } else {
                                                    echo '<input type="text" class="form-control" name="nuevoMontoVuelto" id="nuevoMontoVuelto" placeholder="Ingresar monto a abonar" required readonly>';
                                                }
                                            echo '

                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                        
                            <!--=====================================
                            PIE DEL MODAL
                            ======================================-->

                            <div class="modal-footer">

                            <button type="submit" class="btn btn-dark">Guardar abono</button>

                            </div>
                        </form>';

                        $crearAbono = new ControladorFacturas();
                        $crearAbono -> ctrCrearAbono();
                    ?>
                
                    <?php
                        
                }

                if($factura["tipoDte"] == "01" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ //Factura, Beneficios y diplomáticos
                    echo '
                        <form role="form" method="post" enctype="multipart/form-data">

                            <!--=====================================
                            CABEZA DEL MODAL
                            ======================================-->

                            <div class="modal-header" style="background:grey; color:white">
                            <h4 class="modal-title">Abonar factura</h4>

                            </div>

                            <!--=====================================
                            CUERPO DEL MODAL
                            ======================================-->

                            <div class="modal-body">

                            <div class="box-body">

                                <div class="row">
                                    <div class="col-xl-4 col-xs-12">
                                        Forma de pago:
                                        <br><br>
                                        <!-- ENTRADA PARA LA FORMA DE ABONO -->            
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoIdFacturaAbono" value="'.$factura["id"].'" hidden>
                                                <select class="form-control" name="nuevaFormaAbono" value="" required>
                                                    <option value="Efectivo">Efectivo</option>
                                                    <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                                                    <option value="Tarjeta de débito">Tarjeta de débito</option>
                                                    <option value="Cheque">Cheque</option>
                                                    <option value="Transferencia">Transferencia</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-xs-12">
                                        <!-- ENTRADA PARA LA GESTIÓN FINANCIERA -->            
                                        <div class="form-group">
                                            Gestión financiera, si es efectivo dejar en blanco, si tarjeta colocar el # de gestión, si es cheque el # de cheque y si es transferencia colocar el # de confirmación
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevaGestion" placeholder="Ingresar gestión financiera">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL BANCO -->
                                        Nombre del banco, si es efectivo dejar en blanco           
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoBanco" placeholder="Ingresar nombre del banco">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL MONTO -->            
                                        Monto a abonar
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="text" class="form-control" id="nuevoMontoAbono" name="nuevoMontoAbono" placeholder="Ingresar monto a abonar" required value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL DINERO -->            
                                        Monto dinero con que el cliente paga
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>';
                                                if(isset($_GET["pagado"])){
                                                    echo '<input type="text" class="form-control" name="nuevoMontoDinero" id="nuevoMontoDinero" placeholder="Ingresar monto a abonar" value="'.$abonoLei["dinero_cliente"].'" required>';
                                                } else {
                                                    echo '<input type="text" class="form-control" name="nuevoMontoDinero" id="nuevoMontoDinero" placeholder="Ingresar monto a abonar" required>';
                                                }
                                            echo '
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL VUELTO -->            
                                        Vuelto
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>';
                                                if(isset($_GET["pagado"])){
                                                    echo '<input type="text" class="form-control" name="nuevoMontoVuelto" id="nuevoMontoVuelto" value="'.$abonoLei["dinero_cliente"] - $abonoLei["monto"].'" placeholder="Ingresar monto a abonar" required readonly>';
                                                } else {
                                                    echo '<input type="text" class="form-control" name="nuevoMontoVuelto" id="nuevoMontoVuelto" placeholder="Ingresar monto a abonar" required readonly>';
                                                }
                                            echo '

                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                        
                            <!--=====================================
                            PIE DEL MODAL
                            ======================================-->

                            <div class="modal-footer">

                            <button type="submit" class="btn btn-dark">Guardar abono</button>

                            </div>
                        </form>';

                        $crearAbono = new ControladorFacturas();
                        $crearAbono -> ctrCrearAbono();
                    ?>
                

                    <?php
                }

                if($factura["tipoDte"] == "03" && ($cliente["tipo_cliente"] == "01")){ // CCF, Contribuyentes
                    echo '
                        <form role="form" method="post" enctype="multipart/form-data">

                            <!--=====================================
                            CABEZA DEL MODAL
                            ======================================-->

                            <div class="modal-header" style="background:grey; color:white">
                            <h4 class="modal-title">Abonar factura</h4>

                            </div>

                            <!--=====================================
                            CUERPO DEL MODAL
                            ======================================-->

                            <div class="modal-body">

                            <div class="box-body">

                                <div class="row">
                                    <div class="col-xl-4 col-xs-12">
                                        Forma de pago:
                                        <br><br>
                                        <!-- ENTRADA PARA LA FORMA DE ABONO -->            
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoIdFacturaAbono" value="'.$factura["id"].'" hidden>
                                                <select class="form-control" name="nuevaFormaAbono" value="" required>
                                                    <option value="Efectivo">Efectivo</option>
                                                    <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                                                    <option value="Tarjeta de débito">Tarjeta de débito</option>
                                                    <option value="Cheque">Cheque</option>
                                                    <option value="Transferencia">Transferencia</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-xs-12">
                                        <!-- ENTRADA PARA LA GESTIÓN FINANCIERA -->            
                                        <div class="form-group">
                                            Gestión financiera, si es efectivo dejar en blanco, si tarjeta colocar el # de gestión, si es cheque el # de cheque y si es transferencia colocar el # de confirmación
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevaGestion" placeholder="Ingresar gestión financiera">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL BANCO -->
                                        Nombre del banco, si es efectivo dejar en blanco           
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoBanco" placeholder="Ingresar nombre del banco">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL MONTO -->            
                                        Monto a abonar
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="text" class="form-control" id="nuevoMontoAbono" name="nuevoMontoAbono" placeholder="Ingresar monto a abonar" value="'.($factura["total"]+$factura["flete"]+$factura["seguro"]).'" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL DINERO -->            
                                        Monto dinero con que el cliente paga
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>';
                                                if(isset($_GET["pagado"])){
                                                    echo '<input type="text" class="form-control" name="nuevoMontoDinero" id="nuevoMontoDinero" placeholder="Ingresar monto a abonar" value="'.$abonoLei["dinero_cliente"].'" required>';
                                                } else {
                                                    echo '<input type="text" class="form-control" name="nuevoMontoDinero" id="nuevoMontoDinero" placeholder="Ingresar monto a abonar" required>';
                                                }
                                            echo '
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL VUELTO -->            
                                        Vuelto
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>';
                                                if(isset($_GET["pagado"])){
                                                    echo '<input type="text" class="form-control" name="nuevoMontoVuelto" id="nuevoMontoVuelto" value="'.$abonoLei["dinero_cliente"] - $abonoLei["monto"].'" placeholder="Ingresar monto a abonar" required readonly>';
                                                } else {
                                                    echo '<input type="text" class="form-control" name="nuevoMontoVuelto" id="nuevoMontoVuelto" placeholder="Ingresar monto a abonar" required readonly>';
                                                }
                                            echo '

                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                        
                            <!--=====================================
                            PIE DEL MODAL
                            ======================================-->

                            <div class="modal-footer">

                            <button type="submit" class="btn btn-dark">Guardar abono</button>

                            </div>
                        </form>';

                        $crearAbono = new ControladorFacturas();
                        $crearAbono -> ctrCrearAbono();
                    ?>
        
                    <?php
                }

                if($factura["tipoDte"] == "03" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // CCF, Beneficios y diplomaicos
                    echo '
                        <form role="form" method="post" enctype="multipart/form-data">

                            <!--=====================================
                            CABEZA DEL MODAL
                            ======================================-->

                            <div class="modal-header" style="background:grey; color:white">
                            <h4 class="modal-title">Abonar factura</h4>

                            </div>

                            <!--=====================================
                            CUERPO DEL MODAL
                            ======================================-->

                            <div class="modal-body">

                            <div class="box-body">

                                <div class="row">
                                    <div class="col-xl-4 col-xs-12">
                                        Forma de pago:
                                        <br><br>
                                        <!-- ENTRADA PARA LA FORMA DE ABONO -->            
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoIdFacturaAbono" value="'.$factura["id"].'" hidden>
                                                <select class="form-control" name="nuevaFormaAbono" value="" required>
                                                    <option value="Efectivo">Efectivo</option>
                                                    <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                                                    <option value="Tarjeta de débito">Tarjeta de débito</option>
                                                    <option value="Cheque">Cheque</option>
                                                    <option value="Transferencia">Transferencia</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-xs-12">
                                        <!-- ENTRADA PARA LA GESTIÓN FINANCIERA -->            
                                        <div class="form-group">
                                            Gestión financiera, si es efectivo dejar en blanco, si tarjeta colocar el # de gestión, si es cheque el # de cheque y si es transferencia colocar el # de confirmación
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevaGestion" placeholder="Ingresar gestión financiera">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL BANCO -->
                                        Nombre del banco, si es efectivo dejar en blanco           
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoBanco" placeholder="Ingresar nombre del banco">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL MONTO -->            
                                        Monto a abonar
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="text" class="form-control" id="nuevoMontoAbono" name="nuevoMontoAbono" placeholder="Ingresar monto a abonar" value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL DINERO -->            
                                        Monto dinero con que el cliente paga
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>';
                                                if(isset($_GET["pagado"])){
                                                    echo '<input type="text" class="form-control" name="nuevoMontoDinero" id="nuevoMontoDinero" placeholder="Ingresar monto a abonar" value="'.$abonoLei["dinero_cliente"].'" required>';
                                                } else {
                                                    echo '<input type="text" class="form-control" name="nuevoMontoDinero" id="nuevoMontoDinero" placeholder="Ingresar monto a abonar" required>';
                                                }
                                            echo '
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL VUELTO -->            
                                        Vuelto
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>';
                                                if(isset($_GET["pagado"])){
                                                    echo '<input type="text" class="form-control" name="nuevoMontoVuelto" id="nuevoMontoVuelto" value="'.$abonoLei["dinero_cliente"] - $abonoLei["monto"].'" placeholder="Ingresar monto a abonar" required readonly>';
                                                } else {
                                                    echo '<input type="text" class="form-control" name="nuevoMontoVuelto" id="nuevoMontoVuelto" placeholder="Ingresar monto a abonar" required readonly>';
                                                }
                                            echo '

                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                        
                            <!--=====================================
                            PIE DEL MODAL
                            ======================================-->

                            <div class="modal-footer">

                            <button type="submit" class="btn btn-dark">Guardar abono</button>

                            </div>
                        </form>';

                        $crearAbono = new ControladorFacturas();
                        $crearAbono -> ctrCrearAbono();
                    ?>
                    <?php
                }

                if($factura["tipoDte"] == "11" && ($cliente["tipo_cliente"] == "01" || $cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // EXPOR, Contribuyentes, Beneficios y diplomaicos
                    echo '
                        <form role="form" method="post" enctype="multipart/form-data">

                            <!--=====================================
                            CABEZA DEL MODAL
                            ======================================-->

                            <div class="modal-header" style="background:grey; color:white">
                            <h4 class="modal-title">Abonar factura</h4>

                            </div>

                            <!--=====================================
                            CUERPO DEL MODAL
                            ======================================-->

                            <div class="modal-body">

                            <div class="box-body">

                                <div class="row">
                                    <div class="col-xl-4 col-xs-12">
                                        Forma de pago:
                                        <br><br>
                                        <!-- ENTRADA PARA LA FORMA DE ABONO -->            
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoIdFacturaAbono" value="'.$factura["id"].'" hidden>
                                                <select class="form-control" name="nuevaFormaAbono" value="" required>
                                                    <option value="Efectivo">Efectivo</option>
                                                    <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                                                    <option value="Tarjeta de débito">Tarjeta de débito</option>
                                                    <option value="Cheque">Cheque</option>
                                                    <option value="Transferencia">Transferencia</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-xs-12">
                                        <!-- ENTRADA PARA LA GESTIÓN FINANCIERA -->            
                                        <div class="form-group">
                                            Gestión financiera, si es efectivo dejar en blanco, si tarjeta colocar el # de gestión, si es cheque el # de cheque y si es transferencia colocar el # de confirmación
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevaGestion" placeholder="Ingresar gestión financiera">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL BANCO -->
                                        Nombre del banco, si es efectivo dejar en blanco           
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoBanco" placeholder="Ingresar nombre del banco">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL MONTO -->            
                                        Monto a abonar
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="text" class="form-control" id="nuevoMontoAbono" name="nuevoMontoAbono" placeholder="Ingresar monto a abonar" value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL DINERO -->            
                                        Monto dinero con que el cliente paga
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>';
                                                if(isset($_GET["pagado"])){
                                                    echo '<input type="text" class="form-control" name="nuevoMontoDinero" id="nuevoMontoDinero" placeholder="Ingresar monto a abonar" value="'.$abonoLei["dinero_cliente"].'" required>';
                                                } else {
                                                    echo '<input type="text" class="form-control" name="nuevoMontoDinero" id="nuevoMontoDinero" placeholder="Ingresar monto a abonar" required>';
                                                }
                                            echo '
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL VUELTO -->            
                                        Vuelto
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>';
                                                if(isset($_GET["pagado"])){
                                                    echo '<input type="text" class="form-control" name="nuevoMontoVuelto" id="nuevoMontoVuelto" value="'.$abonoLei["dinero_cliente"] - $abonoLei["monto"].'" placeholder="Ingresar monto a abonar" required readonly>';
                                                } else {
                                                    echo '<input type="text" class="form-control" name="nuevoMontoVuelto" id="nuevoMontoVuelto" placeholder="Ingresar monto a abonar" required readonly>';
                                                }
                                            echo '

                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                        
                            <!--=====================================
                            PIE DEL MODAL
                            ======================================-->

                            <div class="modal-footer">

                            <button type="submit" class="btn btn-dark">Guardar abono</button>

                            </div>
                        </form>';

                        $crearAbono = new ControladorFacturas();
                        $crearAbono -> ctrCrearAbono();
                    ?>
                    <?php
                }

                if($factura["tipoDte"] == "14" && $cliente["tipo_cliente"] == "00"){ // Sujeto excluido normal  
                    echo '
                        <form role="form" method="post" enctype="multipart/form-data">

                            <!--=====================================
                            CABEZA DEL MODAL
                            ======================================-->

                            <div class="modal-header" style="background:grey; color:white">
                            <h4 class="modal-title">Abonar factura</h4>

                            </div>

                            <!--=====================================
                            CUERPO DEL MODAL
                            ======================================-->

                            <div class="modal-body">

                            <div class="box-body">

                                <div class="row">
                                    <div class="col-xl-4 col-xs-12">
                                        Forma de pago:
                                        <br><br>
                                        <!-- ENTRADA PARA LA FORMA DE ABONO -->            
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoIdFacturaAbono" value="'.$factura["id"].'" hidden>
                                                <select class="form-control" name="nuevaFormaAbono" value="" required>
                                                    <option value="Efectivo">Efectivo</option>
                                                    <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                                                    <option value="Tarjeta de débito">Tarjeta de débito</option>
                                                    <option value="Cheque">Cheque</option>
                                                    <option value="Transferencia">Transferencia</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-xs-12">
                                        <!-- ENTRADA PARA LA GESTIÓN FINANCIERA -->            
                                        <div class="form-group">
                                            Gestión financiera, si es efectivo dejar en blanco, si tarjeta colocar el # de gestión, si es cheque el # de cheque y si es transferencia colocar el # de confirmación
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevaGestion" placeholder="Ingresar gestión financiera">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL BANCO -->
                                        Nombre del banco, si es efectivo dejar en blanco           
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="nuevoBanco" placeholder="Ingresar nombre del banco">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL MONTO -->            
                                        Monto a abonar
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="text" class="form-control" id="nuevoMontoAbono" name="nuevoMontoAbono" placeholder="Ingresar monto a abonar" value="'.($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"])-(($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"])*0.10).'" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL DINERO -->            
                                        Monto dinero con que el cliente paga
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>';
                                                if(isset($_GET["pagado"])){
                                                    echo '<input type="text" class="form-control" name="nuevoMontoDinero" id="nuevoMontoDinero" placeholder="Ingresar monto a abonar" value="'.$abonoLei["dinero_cliente"].'" required>';
                                                } else {
                                                    echo '<input type="text" class="form-control" name="nuevoMontoDinero" id="nuevoMontoDinero" placeholder="Ingresar monto a abonar" required>';
                                                }
                                            echo '
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-xs-12">
                                        <!-- ENTRADA PARA EL VUELTO -->            
                                        Vuelto
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                
                                                <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>';
                                                if(isset($_GET["pagado"])){
                                                    echo '<input type="text" class="form-control" name="nuevoMontoVuelto" id="nuevoMontoVuelto" value="'.$abonoLei["dinero_cliente"] - $abonoLei["monto"].'" placeholder="Ingresar monto a abonar" required readonly>';
                                                } else {
                                                    echo '<input type="text" class="form-control" name="nuevoMontoVuelto" id="nuevoMontoVuelto" placeholder="Ingresar monto a abonar" required readonly>';
                                                }
                                            echo '

                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                        
                            <!--=====================================
                            PIE DEL MODAL
                            ======================================-->

                            <div class="modal-footer">

                            <button type="submit" class="btn btn-dark">Guardar abono</button>

                            </div>
                        </form>';

                        $crearAbono = new ControladorFacturas();
                        $crearAbono -> ctrCrearAbono();
                    ?>
        
                        
                    <?php
                }
            ?>

            <div class="modal-header" style="background:grey; color:white">
                <h4 class="modal-title"><?php echo($tipoFacturaTexto); ?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!--=====================================
            CUERPO DEL MODAL
            ======================================-->

            <div class="modal-body">

                <div class="box-body">

                <div class="row">

                    <div class="col-xl-1 col-xs-12">
                        <p style="font-weight: bold;">Número de control:</p>
                    </div>

                    <div class="col-xl-3 col-xs-12">
                        <p><?php echo $factura["numeroControl"] ?></p>
                    </div>

                    <div class="col-xl-2 col-xs-12">
                        <p style="font-weight: bold;">Código de generación:</p>
                    </div>

                    <div class="col-xl-2 col-xs-12">
                        <p><?php echo $factura["codigoGeneracion"] ?></p>
                    </div>

                    <div class="col-xl-2 col-xs-12">
                        <p style="font-weight: bold;">Firma digital:</p>
                    </div>

                    <div class="col-xl-2 col-xs-12">
                        <p><?php
                                if($factura["firmaDigital"] != ""){
                                    echo "Firmado";
                                } else {
                                    echo "Sin firmar";
                                }
                            ?>
                        </p>
                    </div>

                </div>
                <div class="row">

                        <div class="col-xl-1 col-xs-12">
                            <p style="font-weight: bold;">Sello HM:</p>
                        </div>

                        <div class="col-xl-4 col-xs-12">
                            <p><?php echo $factura["sello"] ?></p>
                        </div>

                        <div class="col-xl-2 col-xs-12">
                            <p style="font-weight: bold;">Fecha de emisión:</p>
                        </div>

                        <div class="col-xl-2 col-xs-12">
                            <p><?php echo $factura["fecEmi"] ?></p>
                        </div>

                        <div class="col-xl-1 col-xs-12">
                            <p style="font-weight: bold;">Hora de emisión:</p>
                        </div>

                        <div class="col-xl-2 col-xs-12">
                            <p><?php echo $factura["horEmi"] ?></p>
                        </div>

                </div>
                
                <?php
                    $condicionTexto = "";
                    if($factura["condicionOperacion"] == "1"){
                        $condicionTexto = "Contado";
                    }
                    if($factura["condicionOperacion"] == "2"){
                        $condicionTexto = "Crédito";
                    }
                    if($factura["condicionOperacion"] == "3"){
                        $condicionTexto = "Otro";
                    }

                    $tipo = "";
                    if($cliente["tipo_cliente"] == "00"){
                        $tipo = "Persona normal";
                    }
                    if($cliente["tipo_cliente"] == "01"){
                        $tipo = "Declarante IVA";
                    }
                    if($cliente["tipo_cliente"] == "02"){
                        $tipo = "Empresa con beneficios fiscales";
                    }
                    if($cliente["tipo_cliente"] == "03"){
                        $tipo = "Diplomático";
                    }
                ?>

                <div class="row">

                        <div class="col-xl-2 col-xs-12">
                            <p style="font-weight: bold;">Condición de la operación:</p>
                        </div>

                        <div class="col-xl-2 col-xs-12">
                            <p><?php echo $condicionTexto ?></p>
                        </div>
                        <?php
                            $estado = "";
                            if($factura["estado"] != "Anulada"){
                                $estado = "Activa";
                            } else {
                                $estado = "Anulada";
                            }
                        ?>
                        <div class="col-xl-1 col-xs-12">
                            <p style="font-weight: bold;">Estado:</p>
                        </div>

                        <div class="col-xl-1 col-xs-12">
                            <p><?php echo $estado ?></p>
                        </div>

                        <?php
                             $item = null;
                             $valor = null;
                     
                             $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
                            
                             $nombreVendedor = "";
                             $nombreFacturador = "";
                            foreach ($usuarios as $key => $value){
                                if($value["id"] == $factura["id_vendedor"]){
                                    $nombreVendedor = $value["nombre"];
                                }

                                if($value["id"] == $factura["id_usuario"]){
                                    $nombreFacturador = $value["nombre"];
                                }
                            }
                        ?>

                        <div class="col-xl-1 col-xs-12">
                            <p style="font-weight: bold;">Vendedor:</p>
                        </div>

                        <div class="col-xl-2 col-xs-12">
                            <p><?php echo $nombreVendedor ?></p>
                        </div>

                        <div class="col-xl-1 col-xs-12">
                            <p style="font-weight: bold;">Facturador:</p>
                        </div>

                        <div class="col-xl-2 col-xs-12">
                            <p><?php echo $nombreFacturador ?></p>
                        </div>
                        

                </div>

                    <!-- ENTRADA PARA EL CLIENTE -->
                    <div class="form-group">
                        Seleccionar cliente
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                            </div>
                            <input type="text" name="editarTipoDte" id="tipoDte" value="<?php echo $factura["tipoDte"] ?>" hidden>
                            <input type="text" id="productos" name="productos" hidden>
                            <select name="editarClienteFactura" id="nuevoClienteFactura" class="form-control" required readonly>
                                <?php

                                    $item = "id";
                                    $valor = $factura["id_cliente"];
                                    $orden = "id";

                                    $cliente = ControladorClientes::ctrMostrarClientes($item, $valor, $orden);
                                    echo '<option value="'.$cliente["id"].'">'.$cliente["nombre"].' '.$tipo.' </option>';
                                ?> 
                            </select>
                        </div>
                    </div>
                    

                    <!-- Contenedor donde se agregarán los productos -->
                    <div id="productosContainer">
                        <?php
                            // JSON en $factura["id_cliente"]
                            $jsonProductos = $factura["productos"];

                            // Decodificar el JSON en un array PHP
                            $productos = json_decode($jsonProductos, true); // true convierte el JSON en un array asociativo
                            $contador = count($productos);

                            echo '<input type="number" id="contador" hidden value="'.$contador.'">';
                            // Verificar si la decodificación fue exitosa
                            if (is_array($productos)) {
                                $totalGravado = 0.0;
                                // Recorrer e imprimir cada producto
                                foreach ($productos as $producto) {
                                    if($factura["tipoDte"] == "01" && ($cliente["tipo_cliente"] == "00" || $cliente["tipo_cliente"] == "01")){ //Factura, Persona normal y declarante de IVA
                                        echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                            <div class="row" id="productosContainer">
                                                <div class="col-xl-6 col-xs-12">
                                                    <div class="form-group">
                                                        Producto
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1">
                                                                    <span>1</span>
                                                                </span>
                                                            </div>
                                                            <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                
                                                                    $item = "id";
                                                                    $valor = $producto["idProducto"];
                                                                    
                                                                    $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");

                                                                    echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                
                                                            echo '</select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Cantidad
                                                    
                                                    <div class="form-group">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12" hidden>
                                                    Precio unitario sin impuestos
                                                    
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioSinImpuestos"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Venta grabada individual (más IVA)
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioConIva"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12" hidden>
                                                    Total sin IVA
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["totalProducto"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-xs-12">
                                                    Total a disminuir por cada uno de los items - con iva
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control descuentoItem" name="descuentoItem[]" min="0" value="'.(($producto["descuento"]*0.13)+$producto["descuento"]).'" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-xs-12">
                                                    Porcentaje de descuento según lo ingresado (ejemplo 40, 33, SIN EL PORCENTAJE)
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control porcentajeDescuentoItem" name="porcentajeDescuento[]" min="0" value="'.round((($producto["descuento"] / $producto["precioSinImpuestos"]) * 100), 2).'%" readonly>
                                                    </div>
                                                </div>

                                                <!-- Total -->
                                                <div class="col-xl-3 col-xs-12">
                                                    Total con IVA
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["precioConIva"] - $producto["descuentoConIva"])*$producto["cantidad"].'">
                                                    </div>
                                                </div>

                                            </div>';
                                    }

                                    if($factura["tipoDte"] == "01" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ //Factura, Beneficios y Diplomático
                                        echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                            <div class="row" id="productosContainer">
                                                <div class="col-xl-6 col-xs-12">
                                                    <div class="form-group">
                                                        Producto
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1">
                                                                    <span>1</span>
                                                                </span>
                                                            </div>
                                                            <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                
                                                                    $item = "id";
                                                                    $valor = $producto["idProducto"];
                                                                    
                                                                    $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");

                                                                    echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                
                                                            echo '</select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Cantidad
                                                    
                                                    <div class="form-group">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Precio unitario sin impuestos
                                                    
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioSinImpuestos"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Descuento por cada item sin IVA
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["descuento"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12" hidden>
                                                    Venta grabada individual (más IVA)
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioConIva"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-xs-12">
                                                    Porcentaje de descuento según lo ingresado (ejemplo 40, 33, SIN EL PORCENTAJE)
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control porcentajeDescuentoItem" name="porcentajeDescuento[]" min="0" value="'.round((($producto["descuento"] / $producto["precioSinImpuestos"]) * 100), 2).'%" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Total sin IVA
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["precioSinImpuestos"] - $producto["descuento"])*$producto["cantidad"].'">
                                                    </div>
                                                </div>

                                                <!-- Total -->
                                                <div class="col-xl-2 col-xs-12" hidden>
                                                    Total con IVA
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["precioSinImpuestos"] - $producto["descuento"])*$producto["cantidad"].'">
                                                    </div>
                                                </div>

                                            </div>';
                                    }

                                    if($factura["tipoDte"] == "03" && ($cliente["tipo_cliente"] == "01")){ // CCF Contribuyente
                                        echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                            <div class="row" id="productosContainer">
                                                <div class="col-xl-6 col-xs-12">
                                                    <div class="form-group">
                                                        Producto
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1">
                                                                    <span>1</span>
                                                                </span>
                                                            </div>
                                                            <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                
                                                                    $item = "id";
                                                                    $valor = $producto["idProducto"];
                                                                    
                                                                    $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");

                                                                    echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                
                                                            echo '</select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Cantidad
                                                    
                                                    <div class="form-group">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Precio unitario sin impuestos
                                                    
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioSinImpuestos"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Precio unitario con IVA
                                                    
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioConIva"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-xs-12">
                                                    Total a disminuir por cada uno de los items con iva
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control descuentoItem" name="descuentoItem[]" min="0" value="'.$producto["descuentoConIva"].'" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-xs-12">
                                                    Porcentaje de descuento según lo ingresado (ejemplo 40, 33, SIN EL PORCENTAJE)
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control porcentajeDescuentoItem" name="porcentajeDescuento[]" min="0" value="'.round((($producto["descuento"] / $producto["precioSinImpuestos"]) * 100), 2).'%" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Venta grabada individual (más IVA)
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>';
                                                        if($product["exento_iva"] == "no"){
                                                            echo '<input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioConIva"]-$producto["descuentoConIva"].'">';
                                                        } else {
                                                            echo '<input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioSinImpuestos"]-$producto["descuento"].'">';
                                                        }
                                                        
                                                echo '</div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12" hidden>
                                                    Total sin IVA
                                                    
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioSinImpuestos"]*$producto["cantidad"].'">
                                                    </div>
                                                </div>

                                                <!-- Total -->
                                                <div class="col-xl-2 col-xs-12">
                                                    Total con IVA
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>';
                                                        if($product["exento_iva"] == "no"){
                                                            echo '<input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["precioConIva"] - $producto["descuentoConIva"])*$producto["cantidad"].'">';
                                                        } else {
                                                            echo '<input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["precioSinImpuestos"] - $producto["descuento"])*$producto["cantidad"].'">';
                                                        }
                                                        
                                                echo '</div>
                                                </div>
                                                
                                                

                                            </div>';
                                    }

                                    if($factura["tipoDte"] == "03" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // CCF Beneficios y diploma
                                        echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                            <div class="row" id="productosContainer">
                                                <div class="col-xl-6 col-xs-12">
                                                    <div class="form-group">
                                                        Producto
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1">
                                                                    <span>1</span>
                                                                </span>
                                                            </div>
                                                            <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                
                                                                    $item = "id";
                                                                    $valor = $producto["idProducto"];
                                                                    
                                                                    $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");

                                                                    echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                
                                                            echo '</select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Cantidad
                                                    
                                                    <div class="form-group">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Precio unitario sin impuestos
                                                    
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioSinImpuestos"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-xs-12">
                                                    Total a disminuir por cada uno de los items sin iva
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control descuentoItem" name="descuentoItem[]" min="0" value="'.$producto["descuento"].'" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-xs-12">
                                                    Porcentaje de descuento según lo ingresado (ejemplo 40, 33, SIN EL PORCENTAJE)
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control porcentajeDescuentoItem" name="porcentajeDescuento[]" min="0" value="'.round((($producto["descuento"] / $producto["precioSinImpuestos"]) * 100), 2).'%" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12" hidden>
                                                    Venta grabada individual (más IVA)
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioConIva"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Total sin IVA
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["precioSinImpuestos"]-$producto["descuento"])*$producto["cantidad"].'">
                                                    </div>
                                                </div>

                                                <!-- Total -->
                                                <div class="col-xl-2 col-xs-12" hidden>
                                                    Total con IVA
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioConIva"]*$producto["cantidad"].'">
                                                    </div>
                                                </div>

                                                

                                            </div>';
                                    }

                                    if($factura["tipoDte"] == "11" && ($cliente["tipo_cliente"] == "01" || $cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // Expor Contribuyentes, Beneficios y diploma
                                        echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                            <div class="row" id="productosContainer">
                                                <div class="col-xl-6 col-xs-12">
                                                    <div class="form-group">
                                                        Producto
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1">
                                                                    <span>1</span>
                                                                </span>
                                                            </div>
                                                            <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                
                                                                    $item = "id";
                                                                    $valor = $producto["idProducto"];
                                                                    
                                                                    $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");

                                                                    echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                
                                                            echo '</select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Cantidad
                                                    
                                                    <div class="form-group">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Precio unitario sin impuestos
                                                    
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioSinImpuestos"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-xs-12">
                                                    Total a disminuir por cada uno de los items sin iva
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control descuentoItem" name="descuentoItem[]" min="0" value="'.$producto["descuento"].'" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-xs-12">
                                                    Porcentaje de descuento según lo ingresado (ejemplo 40, 33, SIN EL PORCENTAJE)
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control porcentajeDescuentoItem" name="porcentajeDescuento[]" min="0" value="'.round((($producto["descuento"] / $producto["precioSinImpuestos"]) * 100), 2).'%" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12" hidden>
                                                    Venta grabada individual (más IVA)
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioConIva"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Total sin IVA
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["precioSinImpuestos"]-$producto["descuento"])*$producto["cantidad"].'">
                                                    </div>
                                                </div>

                                                <!-- Total -->
                                                <div class="col-xl-2 col-xs-12" hidden>
                                                    Total con IVA
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioConIva"]*$producto["cantidad"].'">
                                                    </div>
                                                </div>

                                            </div>';
                                    }
                                    
                                    if($factura["tipoDte"] == "14" && $cliente["tipo_cliente"] == "00"){ // Sujeto excluido normal
                                        echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                            <div class="row" id="productosContainer">
                                                <div class="col-xl-6 col-xs-12">
                                                    <div class="form-group">
                                                        Producto
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1">
                                                                    <span>1</span>
                                                                </span>
                                                            </div>
                                                            <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                
                                                                    $item = "id";
                                                                    $valor = $producto["idProducto"];
                                                                    
                                                                    $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");

                                                                    echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                
                                                            echo '</select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Cantidad
                                                    
                                                    <div class="form-group">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Precio unitario sin impuestos
                                                    
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioSinImpuestos"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-xs-12">
                                                    Total a disminuir por cada uno de los items - sin iva (si lleva iva se suma automaticamente)
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control descuentoItem" name="descuentoItem[]" min="0" value="'.$producto["descuento"].'" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-xs-12">
                                                    Porcentaje de descuento según lo ingresado (ejemplo 40, 33, SIN EL PORCENTAJE)
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control porcentajeDescuentoItem" name="porcentajeDescuento[]" min="0" value="'.round((($producto["descuento"] / $producto["precioSinImpuestos"]) * 100), 2).'%" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12" hidden>
                                                    Venta grabada individual (más IVA)
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioConIva"].'">
                                                    </div>
                                                </div>

                                                <div class="col-xl-2 col-xs-12">
                                                    Total sin IVA
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["precioSinImpuestos"]-$producto["descuento"])*$producto["cantidad"].'">
                                                    </div>
                                                </div>

                                                <!-- Total -->
                                                <div class="col-xl-2 col-xs-12" hidden>
                                                    Total con IVA
                                                    <br><br>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                        </div>
                                                        <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioConIva"]*$producto["cantidad"].'">
                                                    </div>
                                                </div>


                                            </div>';
                                    }

                                    if($factura["tipoDte"] == "04"){

                                        if($factura["idFacturaRelacionada"] != ""){
                                            $item = "id";
                                            $orden = "id";
                                            $valor = $factura["idFacturaRelacionada"];
                                            $optimizacion = "no";

                                            $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

                                            if($facturaOriginal["tipoDte"] == "03" && $cliente["tipo_cliente"] == "01"){ // Nota de remisión, ccf contribuyente
                                                echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                                    <div class="row" id="productosContainer">
                                                        <div class="col-xl-6 col-xs-12">
                                                            <div class="form-group">
                                                                Producto
                                                                
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <span>1</span>
                                                                        </span>
                                                                    </div>
                                                                    <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                        
                                                                            $item = "id";
                                                                            $valor = $producto["idProducto"];
                                                                            
                                                                            $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");
        
                                                                            echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                        
                                                                    echo '</select>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Cantidad
                                                            
                                                            <div class="form-group">
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                                    </div>
                                                                    <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Precio unitario sin impuestos
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioSinImpuestos"].'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Venta grabada individual (más IVA)
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioConIva"].'">
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-2 col-xs-12">
                                                            Descuento sin IVA
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["descuento"].'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Total sin IVA
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioSinImpuestos"]*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
                                                        
        
                                                        <!-- Total -->
                                                        <div class="col-xl-2 col-xs-12">
                                                            Total con IVA
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioConIva"]*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
                                                    </div>';
                                            }

                                            if($facturaOriginal["tipoDte"] == "03" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // Nota de remisión, ccf beneficios y diplomas
                                                echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                                    <div class="row" id="productosContainer">
                                                        <div class="col-xl-6 col-xs-12">
                                                            <div class="form-group">
                                                                Producto
                                                                
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <span>1</span>
                                                                        </span>
                                                                    </div>
                                                                    <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                        
                                                                            $item = "id";
                                                                            $valor = $producto["idProducto"];
                                                                            
                                                                            $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");
        
                                                                            echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                        
                                                                    echo '</select>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Cantidad
                                                            
                                                            <div class="form-group">
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                                    </div>
                                                                    <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Precio unitario sin impuestos
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioSinImpuestos"].'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Venta grabada individual (más IVA)
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioConIva"].'">
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-2 col-xs-12">
                                                            Descuento sin IVA
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["descuento"].'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Total sin IVA
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioSinImpuestos"]*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
        
                                                        <!-- Total -->
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Total con IVA
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioConIva"]*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
                                                    </div>';
                                            }

                                            if($facturaOriginal["tipoDte"] == "11" && $cliente["tipo_cliente"] == "01"){ // Nota de remisión, export contribuyentes
                                                echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                                    <div class="row" id="productosContainer">
                                                        <div class="col-xl-6 col-xs-12">
                                                            <div class="form-group">
                                                                Producto
                                                                
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <span>1</span>
                                                                        </span>
                                                                    </div>
                                                                    <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                        
                                                                            $item = "id";
                                                                            $valor = $producto["idProducto"];
                                                                            
                                                                            $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");
        
                                                                            echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                        
                                                                    echo '</select>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Cantidad
                                                            
                                                            <div class="form-group">
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                                    </div>
                                                                    <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Precio unitario sin impuestos
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioSinImpuestos"].'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Venta grabada individual (más IVA)
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioConIva"].'">
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-2 col-xs-12">
                                                            Descuento
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["descuento"].'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Total sin IVA
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["precioSinImpuestos"] - $producto["descuento"])*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
        
                                                        <!-- Total -->
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Total con IVA
                                                            <br><br>
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["precioConIva"] - $producto["descuentoConIva"])*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
                                                    </div>';
                                            }

                                            if($facturaOriginal["tipoDte"] == "11" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // Nota de remisión, export beneficios y diplomas
                                                echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                                    <div class="row" id="productosContainer">
                                                        <div class="col-xl-6 col-xs-12">
                                                            <div class="form-group">
                                                                Producto
                                                                
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <span>1</span>
                                                                        </span>
                                                                    </div>
                                                                    <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                        
                                                                            $item = "id";
                                                                            $valor = $producto["idProducto"];
                                                                            
                                                                            $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");
        
                                                                            echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                        
                                                                    echo '</select>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Cantidad
                                                            
                                                            <div class="form-group">
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                                    </div>
                                                                    <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Precio unitario sin impuestos
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioSinImpuestos"].'">
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-2 col-xs-12">
                                                            Descuento sin IVA
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["descuento"].'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Venta grabada individual (más IVA)
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioConIva"].'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Total sin IVA
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioSinImpuestos"]*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
        
                                                        <!-- Total -->
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Total con IVA
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioConIva"]*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
                                                    </div>';
                                            }
                                        }

                                        if($cliente["tipo_cliente"] == "01"){ // Nota de remisión, ccf contribuyente
                                            echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                                <div class="row" id="productosContainer">
                                                    <div class="col-xl-6 col-xs-12">
                                                        <div class="form-group">
                                                            Producto
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1">
                                                                        <span>1</span>
                                                                    </span>
                                                                </div>
                                                                <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                    
                                                                        $item = "id";
                                                                        $valor = $producto["idProducto"];
                                                                        
                                                                        $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");
    
                                                                        echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                    
                                                                echo '</select>
                                                            </div>
                                                        </div>
                                                    </div>
    
                                                    <div class="col-xl-2 col-xs-12">
                                                        Cantidad
                                                        
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                            </div>
                                                        </div>
                                                    </div>
    
                                                    <div class="col-xl-2 col-xs-12" hidden>
                                                        Precio unitario sin impuestos
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioSinImpuestos"].'">
                                                        </div>
                                                    </div>
    
                                                    <div class="col-xl-2 col-xs-12">
                                                        Venta grabada individual (más IVA)
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioConIva"].'">
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-2 col-xs-12">
                                                        Descuento sin IVA
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["descuento"].'">
                                                        </div>
                                                    </div>
    
                                                    <div class="col-xl-2 col-xs-12" hidden>
                                                        Total sin IVA
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioSinImpuestos"]*$producto["cantidad"].'">
                                                        </div>
                                                    </div>
                                                    
    
                                                    <!-- Total -->
                                                    <div class="col-xl-2 col-xs-12">
                                                        Total con IVA
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioConIva"]*$producto["cantidad"].'">
                                                        </div>
                                                    </div>
                                                </div>';
                                        }

                                        if(($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // Nota de remisión, ccf beneficios y diplomas
                                            echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                                <div class="row" id="productosContainer">
                                                    <div class="col-xl-6 col-xs-12">
                                                        <div class="form-group">
                                                            Producto
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1">
                                                                        <span>1</span>
                                                                    </span>
                                                                </div>
                                                                <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                    
                                                                        $item = "id";
                                                                        $valor = $producto["idProducto"];
                                                                        
                                                                        $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");
    
                                                                        echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                    
                                                                echo '</select>
                                                            </div>
                                                        </div>
                                                    </div>
    
                                                    <div class="col-xl-2 col-xs-12">
                                                        Cantidad
                                                        
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                            </div>
                                                        </div>
                                                    </div>
    
                                                    <div class="col-xl-2 col-xs-12">
                                                        Precio unitario sin impuestos
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["precioSinImpuestos"].'">
                                                        </div>
                                                    </div>
    
                                                    <div class="col-xl-2 col-xs-12" hidden>
                                                        Venta grabada individual (más IVA)
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["precioConIva"].'">
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-2 col-xs-12">
                                                        Descuento sin IVA
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["descuento"].'">
                                                        </div>
                                                    </div>
    
                                                    <div class="col-xl-2 col-xs-12">
                                                        Total sin IVA
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioSinImpuestos"]*$producto["cantidad"].'">
                                                        </div>
                                                    </div>
    
                                                    <!-- Total -->
                                                    <div class="col-xl-2 col-xs-12" hidden>
                                                        Total con IVA
                                                        
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                            </div>
                                                            <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioConIva"]*$producto["cantidad"].'">
                                                        </div>
                                                    </div>
                                                </div>';
                                        }

                                                  
                                    }
                                    
                                    if($factura["tipoDte"] == "05"){
                                        
                                        if($producto["descuento"] != "0"){

                                            $totalProD = (($producto["descuento"] * $producto["cantidad"]));
                                            $totalProF = floatval(number_format($totalProD, 2, '.', ''));
                                            $totalGravado += $totalProF;

                                            $item = "id";
                                            $orden = "id";
                                            $valor = $factura["idFacturaRelacionada"];
                                            $optimizacion = "no";

                                            $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

                                            if($facturaOriginal["tipoDte"] == "03" && $cliente["tipo_cliente"] == "01"){ // Nota de credito, ccf contribuyente
                                                echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                                    <div class="row" id="productosContainer">
                                                        <div class="col-xl-6 col-xs-12">
                                                            <div class="form-group">
                                                                Producto
                                                                
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <span>1</span>
                                                                        </span>
                                                                    </div>
                                                                    <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                        
                                                                            $item = "id";
                                                                            $valor = $producto["idProducto"];
                                                                            
                                                                            $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");
        
                                                                            echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                        
                                                                    echo '</select>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Cantidad
                                                            
                                                            <div class="form-group">
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                                    </div>
                                                                    <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Precio unitario sin impuestos
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["descuento"].'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Venta grabada individual (más IVA)
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["descuento"]+($producto["descuento"]*0.13).'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Total sin IVA
                                                            <br><br>
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioSinImpuestos"]*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
        
                                                        <!-- Total -->
                                                        <div class="col-xl-2 col-xs-12">
                                                            Total con IVA
                                                            <br><br>
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["descuento"]+($producto["descuento"]*0.13))*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
                                                    </div>';
                                            }

                                            if($facturaOriginal["tipoDte"] == "03" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // Nota de credito, ccf beneficios y diplomas
                                                echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                                    <div class="row" id="productosContainer">
                                                        <div class="col-xl-6 col-xs-12">
                                                            <div class="form-group">
                                                                Producto
                                                                
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <span>1</span>
                                                                        </span>
                                                                    </div>
                                                                    <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                        
                                                                            $item = "id";
                                                                            $valor = $producto["idProducto"];
                                                                            
                                                                            $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");
        
                                                                            echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                        
                                                                    echo '</select>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Cantidad
                                                            
                                                            <div class="form-group">
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                                    </div>
                                                                    <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Precio unitario sin impuestos
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["descuento"].'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Venta grabada individual (más IVA)
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["descuento"]+($producto["descuento"]*0.13).'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Total sin IVA
                                                            <br><br>
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["descuento"]*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
        
                                                        <!-- Total -->
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Total con IVA
                                                            <br><br>
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["descuento"]+($producto["descuento"]*0.13))*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
                                                    </div>';
                                            }
                                            
                                        }
                                        
                                    }

                                    if($factura["tipoDte"] == "06"){
                                        
                                        if($producto["descuento"] != "0"){

                                            $totalProD = (($producto["descuento"] * $producto["cantidad"]));
                                            $totalProF = floatval(number_format($totalProD, 2, '.', ''));
                                            $totalGravado += $totalProF;

                                            $item = "id";
                                            $orden = "id";
                                            $valor = $factura["idFacturaRelacionada"];
                                            $optimizacion = "no";

                                            $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

                                            if($facturaOriginal["tipoDte"] == "03" && $cliente["tipo_cliente"] == "01"){ // Nota de credito, ccf contribuyente
                                                echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                                    <div class="row" id="productosContainer">
                                                        <div class="col-xl-6 col-xs-12">
                                                            <div class="form-group">
                                                                Producto
                                                                
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <span>1</span>
                                                                        </span>
                                                                    </div>
                                                                    <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                        
                                                                            $item = "id";
                                                                            $valor = $producto["idProducto"];
                                                                            
                                                                            $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");
        
                                                                            echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                        
                                                                    echo '</select>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Cantidad
                                                            
                                                            <div class="form-group">
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                                    </div>
                                                                    <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Precio unitario sin impuestos
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["descuento"].'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Venta grabada individual (más IVA)
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["descuento"]+($producto["descuento"]*0.13).'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Total sin IVA
                                                            <br><br>
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["precioSinImpuestos"]*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
        
                                                        <!-- Total -->
                                                        <div class="col-xl-2 col-xs-12">
                                                            Total con IVA
                                                            <br><br>
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["descuento"]+($producto["descuento"]*0.13))*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
                                                    </div>';
                                            }

                                            if($facturaOriginal["tipoDte"] == "03" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // Nota de credito, ccf beneficios y diplomas
                                                echo '<!-- ENTRADA PARA EL PRODUCTO -->
                                                    <div class="row" id="productosContainer">
                                                        <div class="col-xl-6 col-xs-12">
                                                            <div class="form-group">
                                                                Producto
                                                                
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <span>1</span>
                                                                        </span>
                                                                    </div>
                                                                    <select name="nuevoIdProductoFactura[]" class="form-control select2 seleccionarProductoFactura" required readonly>';
                                                                        
                                                                            $item = "id";
                                                                            $valor = $producto["idProducto"];
                                                                            
                                                                            $product = ControladorProductos::ctrMostrarProductos($item, $valor, "no");
        
                                                                            echo '<option data-value="'.$product["id"].'" data-precio="'.$product["precio_venta"].'">'.$product["nombre"].'</option>';
                                                                        
                                                                    echo '</select>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Cantidad
                                                            
                                                            <div class="form-group">
                                                                <div class="input-group mb-3">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                                                                    </div>
                                                                    <input type="number" class="form-control nuevaCantidadProductoFactura" name="nuevaCantidadProductoFactura[]" required min="1" value="'.$producto["cantidad"].'" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Precio unitario sin impuestos
                                                            
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoPrecioProductoFacturaOriginal[]" readonly value="'.$producto["descuento"].'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Venta grabada individual (más IVA)
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoIvaProductoFactura[]" readonly value="'.$producto["descuento"]+($producto["descuento"]*0.13).'">
                                                            </div>
                                                        </div>
        
                                                        <div class="col-xl-2 col-xs-12">
                                                            Total sin IVA
                                                            <br><br>
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.$producto["descuento"]*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
        
                                                        <!-- Total -->
                                                        <div class="col-xl-2 col-xs-12" hidden>
                                                            Total con IVA
                                                            <br><br>
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control" name="nuevoTotalProductoFacturaIndividual[]" readonly value="'.($producto["descuento"]+($producto["descuento"]*0.13))*$producto["cantidad"].'">
                                                            </div>
                                                        </div>
                                                    </div>';
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                }
                            } else {
                                echo "Error: El formato de los datos de productos es incorrecto.";
                            }

                        ?>
                    </div>

                    <div class="row">

                            <div class="col-xl-2 col-xs-12 ml-auto">
                                <p>Flete:</p>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                    </div>
                                    <input type="number" class="form-control" readonly value="<?php echo $factura["flete"] ?>">
                                </div>

                                <p>Seguro:</p>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                    </div>
                                    <input type="number" class="form-control" readonly value="<?php echo $factura["seguro"] ?>">
                                </div>
                            </div>

                    </div>

                    <?php
                        if($factura["tipoDte"] == "01" && ($cliente["tipo_cliente"] == "00" || $cliente["tipo_cliente"] == "01")){ //Factura, Persona normal y declarante de IVA
                            echo '<div class="row">

                                    <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                        <p>Total factura sin IVA:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="<?php echo $factura["totalSinIva"]+$factura["flete"]+$factura["seguro"] ?>">
                                        </div>
                                    </div>
            
                                    <div class="col-xl-2 col-xs-12 ml-auto">
                                        <p>Total factura con IVA:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$factura["total"]+$factura["flete"]+$factura["seguro"].'">
                                        </div>
                                    </div>
            
                                </div>';
                            ?>
                                <div class="modal-header" style="background:grey; color:white">
                                    <h4 class="modal-title">Abonos a la factura</h4>
                                </div>
                                <br>

                                <table class="table table-bordered table-striped dt-responsive tablas" width="100%" style="font-size: 80%">
         
                                    <thead>
                                    
                                    <tr>
                                    
                                    <th style="width:10px">#</th>
                                    <th style="width:200px">Forma de abono</th>
                                    <th style="width:300px">Fecha y hora</th>
                                    <th style="width:300px">Gestión de financiera</th>
                                    <th style="width:20px !important">Banco</th>
                                    <th style="width:50px !important">Monto</th>
                            
                                    </tr> 
                            
                                    </thead>
                            
                                    <tbody>
                            
                                        <?php
                            
                                        $item = null;
                                        $valor = null;
                                        $orden = "fecha_abono";
                            
                                        $abonos = ControladorFacturas::ctrMostrarAbonos($item, $valor, $orden);

                                        $abonado = 0;
                                        if($abonos){
                                            foreach ($abonos as $key => $value){
                                                if($value["id_factura"] == $factura["id"]){
                                                    $abonado += $value["monto"];

                                                    echo ' <tr>
                                                        <td>'.($key+1).'</td>
                                                        <td>'.$value["forma_abono"].'</td>
                                                        <td>'.$value["fecha_abono"].'</td>
                                                        <td>'.$value["gestion_abono"].'</td>
                                                        <td>'.$value["banco"].'</td>
                                                        <td>$'.$value["monto"].'</td>
                                                        </tr>';
                                                    }
                                                }
                                        }
                                        
                            
                                        ?> 
                            
                                    </tbody>

                                    <h1>Monto abonado $<?php echo $abonado ?></h1>
                                    <h1>Monto restante $<?php echo (($factura["total"]+$factura["flete"]+$factura["seguro"]) - $abonado) ?></h1>
                            
                                </table>
                            <?php
                                
                        }

                        if($factura["tipoDte"] == "01" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ //Factura, Beneficios y diplomáticos
                            echo '<div class="row">

                                    <div class="col-xl-2 col-xs-12 ml-auto">
                                        <p>Total factura sin IVA:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'">
                                        </div>
                                    </div>
            
                                    <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                        <p>Total factura:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["total"]+$factura["flete"]+$factura["seguro"]).'">
                                        </div>
                                    </div>
                                </div>';

                            ?>
                                <div class="modal-header" style="background:grey; color:white">
                                    <h4 class="modal-title">Abonos a la factura</h4>
                                </div>
                                <br>

                                <table class="table table-bordered table-striped dt-responsive tablas" width="100%" style="font-size: 80%">
         
                                    <thead>
                                    
                                    <tr>
                                    
                                    <th style="width:10px">#</th>
                                    <th style="width:200px">Forma de abono</th>
                                    <th style="width:300px">Fecha y hora</th>
                                    <th style="width:300px">Gestión de financiera</th>
                                    <th style="width:20px !important">Banco</th>
                                    <th style="width:50px !important">Monto</th>
                            
                                    </tr> 
                            
                                    </thead>
                            
                                    <tbody>
                            
                                        <?php
                            
                                        $item = null;
                                        $valor = null;
                                        $orden = "fecha_abono";
                            
                                        $abonos = ControladorFacturas::ctrMostrarAbonos($item, $valor, $orden);

                                        $abonado = 0;
                                        if($abonos){
                                            foreach ($abonos as $key => $value){
                                                if($value["id_factura"] == $factura["id"]){
                                                    $abonado += $value["monto"];

                                                    echo ' <tr>
                                                        <td>'.($key+1).'</td>
                                                        <td>'.$value["forma_abono"].'</td>
                                                        <td>'.$value["fecha_abono"].'</td>
                                                        <td>'.$value["gestion_abono"].'</td>
                                                        <td>'.$value["banco"].'</td>
                                                        <td>$'.$value["monto"].'</td>
                                                        </tr>';
                                                    }
                                                }
                                        }
                                        
                            
                                        ?> 
                            
                                    </tbody>

                                    <h1>Monto abonado $<?php echo $abonado ?></h1>
                                    <h1>Monto restante $<?php echo (($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"]) - $abonado) ?></h1>
                            
                                </table>
                            <?php
                        }

                        if($factura["tipoDte"] == "03" && ($cliente["tipo_cliente"] == "01")){ // CCF, Contribuyentes
                            echo '<div class="row">

                                    <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                        <p>Total factura sin IVA:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'">
                                        </div>
                                    </div>
            
                                    <div class="col-xl-2 col-xs-12 ml-auto">
                                        <p>Total factura:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["total"]+$factura["flete"]+$factura["seguro"]).'">
                                        </div>
                                    </div>
            
                                </div>';
                            ?>
                                <div class="modal-header" style="background:grey; color:white">
                                    <h4 class="modal-title">Abonos a la factura</h4>
                                </div>
                                <br>

                                <table class="table table-bordered table-striped dt-responsive tablas" width="100%" style="font-size: 80%">
         
                                    <thead>
                                    
                                    <tr>
                                    
                                    <th style="width:10px">#</th>
                                    <th style="width:200px">Forma de abono</th>
                                    <th style="width:300px">Fecha y hora</th>
                                    <th style="width:300px">Gestión de financiera</th>
                                    <th style="width:20px !important">Banco</th>
                                    <th style="width:50px !important">Monto</th>
                            
                                    </tr> 
                            
                                    </thead>
                            
                                    <tbody>
                            
                                        <?php
                            
                                        $item = null;
                                        $valor = null;
                                        $orden = "fecha_abono";
                            
                                        $abonos = ControladorFacturas::ctrMostrarAbonos($item, $valor, $orden);

                                        $abonado = 0;
                                        if($abonos){
                                            foreach ($abonos as $key => $value){
                                                if($value["id_factura"] == $factura["id"]){
                                                    $abonado += $value["monto"];

                                                    echo ' <tr>
                                                        <td>'.($key+1).'</td>
                                                        <td>'.$value["forma_abono"].'</td>
                                                        <td>'.$value["fecha_abono"].'</td>
                                                        <td>'.$value["gestion_abono"].'</td>
                                                        <td>'.$value["banco"].'</td>
                                                        <td>$'.$value["monto"].'</td>
                                                        </tr>';
                                                    }
                                                }
                                        }
                                        
                            
                                        ?> 
                            
                                    </tbody>

                                    <h1>Monto abonado $<?php echo $abonado ?></h1>
                                    <h1>Monto restante $<?php echo (($factura["total"]+$factura["flete"]+$factura["seguro"]) - $abonado) ?></h1>
                            
                                </table>
                            <?php
                        }

                        if($factura["tipoDte"] == "03" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // CCF, Beneficios y diplomaicos
                            echo '<div class="row">

                                    <div class="col-xl-2 col-xs-12 ml-auto">
                                        <p>Total factura sin IVA:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'">
                                        </div>
                                    </div>
            
                                    <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                        <p>Total factura:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["total"]+$factura["flete"]+$factura["seguro"]).'">
                                        </div>
                                    </div>
            
                                </div>';
                            ?>
                                <div class="modal-header" style="background:grey; color:white">
                                    <h4 class="modal-title">Abonos a la factura</h4>
                                </div>
                                <br>

                                <table class="table table-bordered table-striped dt-responsive tablas" width="100%" style="font-size: 80%">
         
                                    <thead>
                                    
                                    <tr>
                                    
                                    <th style="width:10px">#</th>
                                    <th style="width:200px">Forma de abono</th>
                                    <th style="width:300px">Fecha y hora</th>
                                    <th style="width:300px">Gestión de financiera</th>
                                    <th style="width:20px !important">Banco</th>
                                    <th style="width:50px !important">Monto</th>
                            
                                    </tr> 
                            
                                    </thead>
                            
                                    <tbody>
                            
                                        <?php
                            
                                        $item = null;
                                        $valor = null;
                                        $orden = "fecha_abono";
                            
                                        $abonos = ControladorFacturas::ctrMostrarAbonos($item, $valor, $orden);

                                        $abonado = 0;
                                        if($abonos){
                                            foreach ($abonos as $key => $value){
                                                if($value["id_factura"] == $factura["id"]){
                                                    $abonado += $value["monto"];

                                                    echo ' <tr>
                                                        <td>'.($key+1).'</td>
                                                        <td>'.$value["forma_abono"].'</td>
                                                        <td>'.$value["fecha_abono"].'</td>
                                                        <td>'.$value["gestion_abono"].'</td>
                                                        <td>'.$value["banco"].'</td>
                                                        <td>$'.$value["monto"].'</td>
                                                        </tr>';
                                                    }
                                                }
                                        }
                                        
                            
                                        ?> 
                            
                                    </tbody>

                                    <h1>Monto abonado $<?php echo $abonado ?></h1>
                                    <h1>Monto restante $<?php echo (($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"]) - $abonado) ?></h1>
                            
                                </table>
                            <?php
                        }

                        if($factura["tipoDte"] == "11" && ($cliente["tipo_cliente"] == "01" || $cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // EXPOR, Contribuyentes, Beneficios y diplomaicos
                            echo '<div class="row">

                                    <div class="col-xl-2 col-xs-12 ml-auto">
                                        <p>Total factura sin IVA:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'">
                                        </div>
                                    </div>
            
                                    <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                        <p>Total factura:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["total"]+$factura["flete"]+$factura["seguro"]).'">
                                        </div>
                                    </div>
            
                                </div>';
                            ?>
                                <div class="modal-header" style="background:grey; color:white">
                                    <h4 class="modal-title">Abonos a la factura</h4>
                                </div>
                                <br>

                                <table class="table table-bordered table-striped dt-responsive tablas" width="100%" style="font-size: 80%">
         
                                    <thead>
                                    
                                    <tr>
                                    
                                    <th style="width:10px">#</th>
                                    <th style="width:200px">Forma de abono</th>
                                    <th style="width:300px">Fecha y hora</th>
                                    <th style="width:300px">Gestión de financiera</th>
                                    <th style="width:20px !important">Banco</th>
                                    <th style="width:50px !important">Monto</th>
                            
                                    </tr> 
                            
                                    </thead>
                            
                                    <tbody>
                            
                                        <?php
                            
                                        $item = null;
                                        $valor = null;
                                        $orden = "fecha_abono";
                            
                                        $abonos = ControladorFacturas::ctrMostrarAbonos($item, $valor, $orden);

                                        $abonado = 0;
                                        if($abonos){
                                            foreach ($abonos as $key => $value){
                                                if($value["id_factura"] == $factura["id"]){
                                                    $abonado += $value["monto"];

                                                    echo ' <tr>
                                                        <td>'.($key+1).'</td>
                                                        <td>'.$value["forma_abono"].'</td>
                                                        <td>'.$value["fecha_abono"].'</td>
                                                        <td>'.$value["gestion_abono"].'</td>
                                                        <td>'.$value["banco"].'</td>
                                                        <td>$'.$value["monto"].'</td>
                                                        </tr>';
                                                    }
                                                }
                                        }
                                        
                            
                                        ?> 
                            
                                    </tbody>

                                    <h1>Monto abonado $<?php echo $abonado ?></h1>
                                    <h1>Monto restante $<?php echo (($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"]) - $abonado) ?></h1>
                            
                                </table>
                            <?php
                        }

                        if($factura["tipoDte"] == "14" && $cliente["tipo_cliente"] == "00"){ // Sujeto excluido normal  
                            echo '<div class="row">

                                    <div class="col-xl-2 col-xs-12 ml-auto">
                                        <p>Total factura sin Renta:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'">
                                        </div>
                                    </div>

                                    <div class="col-xl-2 col-xs-12 ml-auto">
                                        <p>Renta:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"])*0.10.'">
                                        </div>
                                    </div>

                                    <div class="col-xl-2 col-xs-12 ml-auto">
                                        <p>Total factura con renta:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"])-(($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"])*0.10).'">
                                        </div>
                                    </div>
            
                                    <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                        <p>Total factura:</p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["total"]+$factura["flete"]+$factura["seguro"]).'">
                                        </div>
                                    </div>
            
                                </div>';
                            ?>
                                <div class="modal-header" style="background:grey; color:white">
                                    <h4 class="modal-title">Abonos a la factura</h4>
                                </div>
                                <br>

                                <table class="table table-bordered table-striped dt-responsive tablas" width="100%" style="font-size: 80%">
         
                                    <thead>
                                    
                                    <tr>
                                    
                                    <th style="width:10px">#</th>
                                    <th style="width:200px">Forma de abono</th>
                                    <th style="width:300px">Fecha y hora</th>
                                    <th style="width:300px">Gestión de financiera</th>
                                    <th style="width:20px !important">Banco</th>
                                    <th style="width:50px !important">Monto</th>
                            
                                    </tr> 
                            
                                    </thead>
                            
                                    <tbody>
                            
                                        <?php
                            
                                        $item = null;
                                        $valor = null;
                                        $orden = "fecha_abono";
                            
                                        $abonos = ControladorFacturas::ctrMostrarAbonos($item, $valor, $orden);

                                        $abonado = 0;
                                        if($abonos){
                                            foreach ($abonos as $key => $value){
                                                if($value["id_factura"] == $factura["id"]){
                                                    $abonado += $value["monto"];

                                                    echo ' <tr>
                                                        <td>'.($key+1).'</td>
                                                        <td>'.$value["forma_abono"].'</td>
                                                        <td>'.$value["fecha_abono"].'</td>
                                                        <td>'.$value["gestion_abono"].'</td>
                                                        <td>'.$value["banco"].'</td>
                                                        <td>$'.$value["monto"].'</td>
                                                        </tr>';
                                                    }
                                                }
                                        }
                                        
                            
                                        ?> 
                            
                                    </tbody>

                                    <h1>Monto abonado $<?php echo $abonado ?></h1>
                                    <h1>Monto restante $<?php echo ((($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"]) - $abonado)-($factura["totalSinIva"]*0.1)) ?></h1>
                            
                                </table>
                            <?php
                        }

                        if($factura["tipoDte"] == "04"){
                            if($factura["idFacturaRelacionada"] != ""){
                                $item = "id";
                                $orden = "id";
                                $valor = $factura["idFacturaRelacionada"];
                                $optimizacion = "no";

                                $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);
                                
                                if($facturaOriginal["tipoDte"] == "03" && $cliente["tipo_cliente"] == "01"){ // Nota de remisión, ccf contribuyente
                                        echo '<div class="row">
        
                                            <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                                <p>Total factura sin IVA:</p>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                    </div>
                                                    <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'">
                                                </div>
                                            </div>
                    
                                            <div class="col-xl-2 col-xs-12 ml-auto">
                                                <p>Total factura:</p>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                    </div>
                                                    <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["total"]+$factura["flete"]+$factura["seguro"]).'">
                                                </div>
                                            </div>
                    
                                        </div>';
                                }

                                if($facturaOriginal["tipoDte"] == "03" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // Nota de remisión, ccf beneficios y diplomas
                                        echo '<div class="row">
        
                                            <div class="col-xl-2 col-xs-12 ml-auto">
                                                <p>Total factura sin IVA:</p>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                    </div>
                                                    <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'">
                                                </div>
                                            </div>
                    
                                            <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                                <p>Total factura:</p>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                    </div>
                                                    <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["total"]+$factura["flete"]+$factura["seguro"]).'">
                                                </div>
                                            </div>
                    
                                        </div>';
                                }

                                if($facturaOriginal["tipoDte"] == "11" && $cliente["tipo_cliente"] == "01"){ // Nota de remisión, export contribuyente
                                    echo '<div class="row">
        
                                            <div class="col-xl-2 col-xs-12 ml-auto">
                                                <p>Total factura sin IVA:</p>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                    </div>
                                                    <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"]).'">
                                                </div>
                                            </div>
                    
                                            <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                                <p>Total factura:</p>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                    </div>
                                                    <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"]).'">
                                                </div>
                                            </div>
                    
                                        </div>';
                                }

                                if($facturaOriginal["tipoDte"] == "11" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // Nota de remisión, export beneficios y diplomas
                                    echo '<div class="row">
        
                                            <div class="col-xl-2 col-xs-12 ml-auto">
                                                <p>Total factura sin IVA:</p>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                    </div>
                                                    <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"]).'">
                                                </div>
                                            </div>
                    
                                            <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                                <p>Total factura:</p>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                    </div>
                                                    <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["totalSinIva"]+$factura["flete"]+$factura["seguro"]).'">
                                                </div>
                                            </div>
                    
                                        </div>';
                                }
                            }
                            
                            if($cliente["tipo_cliente"] == "01"){ // Nota de remisión, ccf contribuyente
                                echo '<div class="row">
    
                                        <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                            <p>Total factura sin IVA:</p>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'">
                                            </div>
                                        </div>
                
                                        <div class="col-xl-2 col-xs-12 ml-auto">
                                            <p>Total factura:</p>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["total"]+$factura["flete"]+$factura["seguro"]).'">
                                            </div>
                                        </div>
                
                                    </div>';
                            }

                            if(($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // Nota de remisión, ccf beneficios y diplomas
                                echo '<div class="row">
    
                                        <div class="col-xl-2 col-xs-12 ml-auto">
                                            <p>Total factura sin IVA:</p>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'">
                                            </div>
                                        </div>
                
                                        <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                            <p>Total factura:</p>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.($factura["total"]+$factura["flete"]+$factura["seguro"]).'">
                                            </div>
                                        </div>
                
                                    </div>';
                            }
                        }

                        if($factura["tipoDte"] == "05"){
                            $item = "id";
                            $orden = "id";
                            $valor = $factura["idFacturaRelacionada"];
                            $optimizacion = "no";

                            $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);
                            
                            if($facturaOriginal["tipoDte"] == "03" && $cliente["tipo_cliente"] == "01"){ // Nota de crédito, ccf contribuyente
                                echo '<div class="row">
    
                                        <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                            <p>Total factura sin IVA:</p>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'">
                                            </div>
                                        </div>
                
                                        <div class="col-xl-2 col-xs-12 ml-auto">
                                            <p>Total factura:</p>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$totalGravado+($totalGravado*0.13).'">
                                            </div>
                                        </div>
                
                                    </div>';
                            }

                            if($facturaOriginal["tipoDte"] == "03" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // Nota de crédito, ccf beneficios diplomas
                                echo '<div class="row">
    
                                        <div class="col-xl-2 col-xs-12 ml-auto">
                                            <p>Total factura sin IVA:</p>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$totalGravado.'">
                                            </div>
                                        </div>
                
                                        <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                            <p>Total factura:</p>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$totalGravado+($totalGravado*0.13).'">
                                            </div>
                                        </div>
                
                                    </div>';
                            }
                        }

                        if($factura["tipoDte"] == "06"){
                            $item = "id";
                            $orden = "id";
                            $valor = $factura["idFacturaRelacionada"];
                            $optimizacion = "no";

                            $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);
                            
                            if($facturaOriginal["tipoDte"] == "03" && $cliente["tipo_cliente"] == "01"){ // Nota de crédito, ccf contribuyente
                                echo '<div class="row">
    
                                        <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                            <p>Total factura sin IVA:</p>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$factura["totalSinIva"]+$factura["flete"]+$factura["seguro"].'">
                                            </div>
                                        </div>
                
                                        <div class="col-xl-2 col-xs-12 ml-auto">
                                            <p>Total factura:</p>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$totalGravado+($totalGravado*0.13).'">
                                            </div>
                                        </div>
                
                                    </div>';
                            }

                            if($facturaOriginal["tipoDte"] == "03" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){ // Nota de crédito, ccf beneficios diplomas
                                echo '<div class="row">
    
                                        <div class="col-xl-2 col-xs-12 ml-auto">
                                            <p>Total factura sin IVA:</p>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$totalGravado.'">
                                            </div>
                                        </div>
                
                                        <div class="col-xl-2 col-xs-12 ml-auto" hidden>
                                            <p>Total factura:</p>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="nuevoTotalFactura" id="nuevaCantidadProductoFactura" readonly value="'.$totalGravado+($totalGravado*0.13).'">
                                            </div>
                                        </div>
                
                                    </div>';
                            }
                        }
                    ?>
                    
                    <div style="background-color: grey">
                        <h5 style="color: white; padding: 15px">Json de la factura</h5>
                        <?php
                            
                            // Decodificar JSON
                            $json_data = json_decode($factura["json_guardado"], true);
                            
                            // Si el JSON es válido
                            if ($json_data !== null) {
                                $json_pretty = json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                                $file_name = "factura.json";
                                file_put_contents($file_name, $json_pretty);
                                
                                // Convertimos el JSON en un array de líneas
                                $lines = explode("\n", $json_pretty);
                                $total_lines = count($lines);
                                
                                // Dividimos en 3 columnas equilibradas
                                $col1 = array_slice($lines, 0, ceil($total_lines / 3));
                                $col2 = array_slice($lines, ceil($total_lines / 3), ceil($total_lines / 3));
                                $col3 = array_slice($lines, 2 * ceil($total_lines / 3));
                            
                                echo "<style>
                                    .container { display: flex; gap: 10px; }
                                    .column { width: 33%; background: #f4f4f4; padding: 10px; white-space: pre-wrap; font-family: monospace; }
                                </style>";
                            
                                echo "<div class='container'>";
                                echo "<div class='column'>" . implode("\n", $col1) . "</div>";
                                echo "<div class='column'>" . implode("\n", $col2) . "</div>";
                                echo "<div class='column'>" . implode("\n", $col3) . "</div>";
                                echo "</div> <br>";
                                 // Botón de descarga
                                echo "<br><a style='padding: 20px' href='$file_name' download><button class='btn btn-info' type='button'>Descargar JSON</button></a><br><br>";
                            } else {
                                echo "Factura no firmada";
                            }
                                                    
                        ?>
                    </div> 
                    

                </div>
            </div>
            <!--=====================================
            PIE DEL MODAL
            ======================================-->

            
        </form>

        </div>

    </div>


  </section>

</div>

<!--=====================================
MODAL EDITAR MOTORISTA
======================================-->

<div id="modalEditarTicket" class="modal fade" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Editar ancho del ticket</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">
            Ancho del ticket en milimetros:
            <!-- ENTRADA PARA EL NOMBRE -->            
            <div class="form-group">
              <div class="input-group mb-3">
                
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                </div>
                <input type="number" class="form-control" name="editarAnchoTicket" id="editarAnchoTicket" min="1" required>
              </div>
            </div>
        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-dark">Guardar configuración</button>

        </div>

        <?php

          $editarTicket = new ControladorClientes();
          $editarTicket -> ctrEditarTicket();

        ?>

      </form>

      </div>

    </div>

    </div>

  </div>

</div>

<!--=====================================
MODAL VER CLIENTES
======================================-->

<div id="modalVerClientes" class="modal fade bd-example-modal-lg" role="dialog" style="width: 100% !important; font-size:80%">
  
  <div class="modal-dialog modal-lg" style="max-width: 90%;">

    <div class="modal-content">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Clientes registrados</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">
             <!-- Añadir el contenedor responsivo -->
             <div class="table-responsive">
                <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
              
                    <thead>
                      
                      <tr>
                        
                        <th style="width:10px">#</th>
                        <th>Nombre</th>
                        <th style="width:10px !Important">Correo</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>NIT</th>
                        <th>DUI</th>
                        <th>NRC</th>
                        <th>Tipo</th>
                        <th>País de envío</th>
                        <th>Tipo de persona</th>
                        <th>Acciones</th>
            
                      </tr> 
            
                    </thead>
            
                    <tbody>
            
                    <?php
            
                    $item = null;
                    $valor = null;
                    $orden = "id";
            
                    $clientes = ControladorClientes::ctrMostrarClientes($item, $valor, $orden);
            
                    foreach ($clientes as $key => $value){
                        $tipo = "";
                        if($value["tipo_cliente"] == "00"){
                          $tipo = "Persona normal";
                        }
                        if($value["tipo_cliente"] == "01"){
                          $tipo = "Declarante IVA";
                        }
                        if($value["tipo_cliente"] == "02"){
                          $tipo = "Empresa con beneficios fiscales";
                        }
                        if($value["tipo_cliente"] == "03"){
                          $tipo = "Diplomático";
                        }

                        $tipoPersona = "";
                        if($value["tipoPersona"] == "1"){
                          $tipoPersona = "Persona natural";
                        }
                        if($value["tipoPersona"] == "2"){
                          $tipoPersona = "Persona Juridica";
                        }
                      echo ' <tr>
                              <td>'.($key+1).'</td>
                              <td>'.$value["nombre"].'</td>
                              <td>'.$value["correo"].'</td>
                              <td>'.$value["telefono"].'</td>
                              <td>'.$value["departamento"].', '.$value["municipio"].', '.$value["direccion"].'</td>
                              <td>'.$value["NIT"].'</td>
                              <td>'.$value["DUI"].'</td>
                              <td>'.$value["NRC"].'</td>
                              <td>'.$tipo.'</td>
                              <td>'.$value["nombrePais"].'</td>
                              <td>'.$tipoPersona.'</td>';
            
                                        
                              echo '
                              <td>
            
                                <div class="btn-group">
                                    
                                  <button class="btn btn-warning btnEditarCliente" idCliente="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarCliente"><i class="fa fa-pencil"></i></button>
            
                                  <button class="btn btn-danger btnEliminarCliente" idCliente="'.$value["id"].'"><i class="fa fa-times"></i></button>';
                                    $conectado = @fsockopen("www.google.com", 80); 
                                      if ($conectado) {
                                        if($_SESSION["tokenInicioSesionMh"] == ""){
                                        
                                        } else {
                                          echo '<button class="btn btn-info btnEscogerFactura" idCliente="'.$value["id"].'"><i class="fa fa-file-text"></i></button>';
                                        }
                                      } else {
                                        echo '<button class="btn btn-info">Sin red</button>';
                                      }
            
                                echo '</div>  
            
                              </td>
            
                            </tr>';
                    }
            
            
                    ?> 
            
                    </tbody>

              </table>
            </div>
            
            

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-dark pull-left" data-dismiss="modal">Salir</button>

        </div>

      </div>
      
    </div>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR CLIENTE
======================================-->

<div id="modalEditarCliente" class="modal fade" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Editar cliente</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="row">

                <div class="col-xl-6 col-xs-12">
                    <!-- ENTRADA PARA EL NOMBRE -->

                  <div class="form-group">

                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                    </div>
                    <input type="text" class="form-control" id="editarNombreCliente" name="editarNombreCliente" value="" required>
                    <input type="text" class="form-control" id="editarIdCliente" name="editarIdCliente" hidden>
                  </div>

                  </div>

                  <!-- ENTRADA PARA EL DEPARTAMENTO-->

                  <div class="form-group">
                  <p>Seleccionar departamento</p>
                  <div class="input-group mb-3">
                        <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <select class="form-control" name="editarDepartamentoCliente" value="" required>
                        <option value="" id="editarDepartamentoCliente"></option>
                          <option value="00">EXTRANJERO</option>
                          <option value="01">AHUACHAPAN</option>
                          <option value="02">SANTA ANA</option>
                          <option value="03">SONSONATE</option>
                          <option value="04">CHALATENANGO</option>
                          <option value="05">LA LIBERTAD</option>
                          <option value="06">SAN SALVADOR</option>
                          <option value="07">CUSCATLAN</option>
                          <option value="08">LA PAZ</option>
                          <option value="09">CABAÑAS</option>
                          <option value="10">SAN VICENTE</option>
                          <option value="11">USULUTAN</option>>
                          <option value="12">SAN MIGUEL</option>
                          <option value="13">MORAZAN</option>
                          <option value="14">LA UNION</option>
                        </select>
                  </div>

                  </div>

                  <!-- ENTRADA PARA EL MUNICIPIO-->

                  <div class="form-group">
                  <p>Seleccionar municipio</p>
                  <div class="input-group mb-3">
                        <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <select class="form-control" name="editarMunicipioCliente" value="" required>
                        <option value="" id="editarMunicipioCliente"></option>
                          <option value="00">EXTRANJERO</option>
                          <option value="13">AHUACHAPAN NORTE</option>
                          <option value="14">AHUACHAPAN CENTRO</option>
                          <option value="15">AHUACHAPAN SUR</option>
                          <option value="14">SANTA ANA NORTE</option>
                          <option value="15">SANTA ANA CENTRO</option>
                          <option value="16">SANTA ANA ESTE</option>
                          <option value="17">SANTA ANA OESTE</option>
                          <option value="17">SONSONATE NORTE</option>
                          <option value="18">SONSONATE CENTRO</option>
                          <option value="19">SONSONATE ESTE</option>
                          <option value="20">SONSONATE OESTE</option>
                          <option value="34">CHALATENANGO NORTE</option>
                          <option value="35">CHALATENANGO CENTRO</option>
                          <option value="36">CHALATENANGO SUR</option>
                          <option value="23">LA LIBERTAD NORTE</option>
                          <option value="24">LA LIBERTAD CENTRO</option>
                          <option value="25">LA LIBERTAD OESTE</option>
                          <option value="26">LA LIBERTAD ESTE</option>
                          <option value="27">LA LIBERTAD COSTA</option>
                          <option value="28">LA LIBERTAD SUR</option>
                          <option value="20">SAN SALVADOR NORTE</option>
                          <option value="21">SAN SALVADOR OESTE</option>
                          <option value="22">SAN SALVADOR ESTE</option>
                          <option value="23">SAN SALVADOR CENTRO</option>
                          <option value="24">SAN SALVADOR SUR</option>
                          <option value="17">CUSCATLAN NORTE</option>
                          <option value="18">CUSCATLAN SUR</option>
                          <option value="23">LA PAZ OESTE</option>
                          <option value="24">LA PAZ CENTRO</option>
                          <option value="25">LA PAZ ESTE</option>
                          <option value="10">CABAÑAS OESTE</option>
                          <option value="11">CABAÑAS ESTE</option>
                          <option value="14">SAN VICENTE NORTE</option>
                          <option value="15">SAN VICENTE SUR</option>
                          <option value="24">USULUTAN NORTE</option>
                          <option value="25">USULUTAN ESTE</option>
                          <option value="26">USULUTAN OESTE</option>
                          <option value="21">SAN MIGUEL NORTE</option>
                          <option value="22">SAN MIGUEL CENTRO</option>
                          <option value="23">SAN MIGUEL OESTE</option>
                          <option value="27">MORAZAN NORTE</option>
                          <option value="28">MORAZAN SUR</option>
                          <option value="19">LA UNION NORTE</option>
                          <option value="20">LA UNION SUR</option>
                        </select>
                  </div>

                  </div>

                  <!-- ENTRADA PARA LA DIRECCION -->

                  <div class="form-group">

                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                    </div>
                    <input type="text" class="form-control" id="editarDireccionCliente" name="editarDireccionCliente" value="" required>
                  </div>

                  </div>

                  <!-- ENTRADA PARA EL CORREO-->

                  <div class="form-group">

                  <div class="input-group mb-3">
                        <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-envelope"></i></span>
                        </div>
                        <input type="email" class="form-control" id="editarCorreoCliente" name="editarCorreoCliente" placeholder="Ingresar correo electrónico" required>
                  </div>

                  </div>

                  <!-- ENTRADA PARA EL NUMERO -->

                  <div class="form-group">

                      <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-phone"></i></span>
                            </div>
                            <input type="text" class="form-control" name="editarNumeroCliente" id="editarNumeroCliente" placeholder="Ingresar número telefónico" required>
                      </div>

                  </div>

                  <!-- ENTRADA PARA EL PAIS A RECIBIR EL PRODUCTO -->

                  <div class="form-group">
                    <p>Seleccionar país a recibir producto:</p>
                    <div class="input-group mb-3">
                          <div class="input-group-prepend">
                                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-globe"></i></span>
                          </div>
                          <select class="form-control" name="editarPaisRecibir" value="" required>
                            <option id="editarPaisRecibir" value=""></option>
                            <option value="AF">Afganistán</option>
                            <option value="AX">Aland</option>
                            <option value="AL">Albania</option>
                            <option value="DE">Alemania</option>
                            <option value="AD">Andorra</option>
                            <option value="AO">Angola</option>
                            <option value="AI">Anguila</option>
                            <option value="AQ">Antártica</option>
                            <option value="AG">Antigua y Barbuda</option>
                            <option value="AW">Aruba</option>
                            <option value="SA">Arabia Saudita</option>
                            <option value="DZ">Argelia</option>
                            <option value="AR">Argentina</option>
                            <option value="AM">Armenia</option>
                            <option value="AU">Australia</option>
                            <option value="AT">Austria</option>
                            <option value="AZ">Azerbaiyán</option>
                            <option value="BS">Bahamas</option>
                            <option value="BH">Bahrein</option>
                            <option value="BD">Bangladesh</option>
                            <option value="BB">Barbados</option>
                            <option value="BE">Bélgica</option>
                            <option value="BZ">Belice</option>
                            <option value="BJ">Benin</option>
                            <option value="BM">Bermudas</option>
                            <option value="BY">Bielorrusia</option>
                            <option value="BO">Bolivia</option>
                            <option value="BQ">Bonaire, Sint Eustatius and Saba</option>
                            <option value="BA">Bosnia-Herzegovina</option>
                            <option value="BW">Botswana</option>
                            <option value="BR">Brasil</option>
                            <option value="BN">Brunei</option>
                            <option value="BG">Bulgaria</option>
                            <option value="BF">Burkina Faso</option>
                            <option value="BI">Burundi</option>
                            <option value="BT">Bután</option>
                            <option value="CV">Cabo Verde</option>
                            <option value="KY">Caimán, Islas</option>
                            <option value="KH">Camboya</option>
                            <option value="CM">Camerún</option>
                            <option value="CA">Canadá</option>
                            <option value="CF">Centroafricana, República</option>
                            <option value="TD">Chad</option>
                            <option value="CL">Chile</option>
                            <option value="CN">China</option>
                            <option value="CY">Chipre</option>
                            <option value="VA">Ciudad del Vaticano</option>
                            <option value="CO">Colombia</option>
                            <option value="KM">Comoras</option>
                            <option value="CG">Congo</option>
                            <option value="CI">Costa de Marfil</option>
                            <option value="CR">Costa Rica</option>
                            <option value="HR">Croacia</option>
                            <option value="CU">Cuba</option>
                            <option value="CW">Curazao</option>
                            <option value="DK">Dinamarca</option>
                            <option value="DM">Dominica</option>
                            <option value="DJ">Djiboutí</option>
                            <option value="EC">Ecuador</option>
                            <option value="EG">Egipto</option>
                            <option value="SV">El Salvador</option>
                            <option value="AE">Emiratos Árabes Unidos</option>
                            <option value="ER">Eritrea</option>
                            <option value="SK">Eslovaquia</option>
                            <option value="SI">Eslovenia</option>
                            <option value="ES">España</option>
                            <option value="US">Estados Unidos</option>
                            <option value="EE">Estonia</option>
                            <option value="ET">Etiopía</option>
                            <option value="FJ">Fiji</option>
                            <option value="PH">Filipinas</option>
                            <option value="FI">Finlandia</option>
                            <option value="FR">Francia</option>
                            <option value="GA">Gabón</option>
                            <option value="GM">Gambia</option>
                            <option value="GE">Georgia</option>
                            <option value="GH">Ghana</option>
                            <option value="GI">Gibraltar</option>
                            <option value="GD">Granada</option>
                            <option value="GR">Grecia</option>
                            <option value="GL">Groenlandia</option>
                            <option value="GP">Guadalupe</option>
                            <option value="GU">Guam</option>
                            <option value="GT">Guatemala</option>
                            <option value="GF">Guayana Francesa</option>
                            <option value="GG">Guernsey</option>
                            <option value="GN">Guinea</option>
                            <option value="GQ">Guinea Ecuatorial</option>
                            <option value="GW">Guinea-Bissau</option>
                            <option value="GY">Guyana</option>
                            <option value="HT">Haití</option>
                            <option value="HN">Honduras</option>
                            <option value="HK">Hong Kong</option>
                            <option value="HU">Hungría</option>
                            <option value="IN">India</option>
                            <option value="ID">Indonesia</option>
                            <option value="IQ">Irak</option>
                            <option value="IE">Irlanda</option>
                            <option value="BV">Isla Bouvet</option>
                            <option value="IM">Isla de Man</option>
                            <option value="NF">Isla Norfolk</option>
                            <option value="IS">Islandia</option>
                            <option value="CX">Islas Navidad</option>
                            <option value="CC">Islas Cocos</option>
                            <option value="CK">Islas Cook</option>
                            <option value="FO">Islas Faroe</option>
                            <option value="GS">Islas Georgias del Sur y Sandwich del Sur</option>
                            <option value="HM">Islas Heard y McDonald</option>
                            <option value="FK">Islas Malvinas</option>
                            <option value="MP">Islas Marianas del Norte</option>
                            <option value="MH">Islas Marshall</option>
                            <option value="PN">Islas Pitcairn</option>
                            <option value="TC">Islas Turcas y Caicos</option>
                            <option value="UM">Islas Ultramarinas de E.E.U.U</option>
                            <option value="VI">Islas Vírgenes</option>
                            <option value="IL">Israel</option>
                            <option value="IT">Italia</option>
                            <option value="JM">Jamaica</option>
                            <option value="JP">Japón</option>
                            <option value="JE">Jersey</option>
                            <option value="JO">Jordania</option>
                            <option value="KZ">Kazajistán</option>
                            <option value="KE">Kenia</option>
                            <option value="KG">Kirguistán</option>
                            <option value="KI">Kiribati</option>
                            <option value="KW">Kuwait</option>
                            <option value="LA">Laos, República Democrática</option>
                            <option value="LS">Lesotho</option>
                            <option value="LV">Letonia</option>
                            <option value="LB">Líbano</option>
                            <option value="LR">Liberia</option>
                            <option value="LY">Libia</option>
                            <option value="LI">Liechtenstein</option>
                            <option value="LT">Lituania</option>
                            <option value="LU">Luxemburgo</option>
                            <option value="MO">Macao</option>
                            <option value="MK">Macedonia</option>
                            <option value="MG">Madagascar</option>
                            <option value="MY">Malasia</option>
                            <option value="MW">Malawi</option>
                            <option value="MV">Maldivas</option>
                            <option value="ML">Malí</option>
                            <option value="MT">Malta</option>
                            <option value="MA">Marruecos</option>
                            <option value="MQ">Martinica</option>
                            <option value="MU">Mauricio</option>
                            <option value="MR">Mauritania</option>
                            <option value="YT">Mayotte</option>
                            <option value="MX">México</option>
                            <option value="FM">Micronesia</option>
                            <option value="MD">Moldavia</option>
                            <option value="MC">Mónaco</option>
                            <option value="MN">Mongolia</option>
                            <option value="ME">Montenegro</option>
                            <option value="MS">Montserrat</option>
                            <option value="MZ">Mozambique</option>
                            <option value="MM">Myanmar</option>
                            <option value="NA">Namibia</option>
                            <option value="NR">Nauru</option>
                            <option value="NP">Nepal</option>
                            <option value="NI">Nicaragua</option>
                            <option value="NE">Níger</option>
                            <option value="NG">Nigeria</option>
                            <option value="NU">Niue</option>
                            <option value="NO">Noruega</option>
                            <option value="NC">Nueva Caledonia</option>
                            <option value="NZ">Nueva Zelanda</option>
                            <option value="OM">Omán</option>
                            <option value="NL">Países Bajos</option>
                            <option value="PK">Pakistán</option>
                            <option value="PW">Palaos</option>
                            <option value="PS">Palestina</option>
                            <option value="PA">Panamá</option>
                            <option value="PG">Papúa Nueva Guinea</option>
                            <option value="PY">Paraguay</option>
                            <option value="PE">Perú</option>
                            <option value="PF">Polinesia Francesa</option>
                            <option value="PL">Polonia</option>
                            <option value="PT">Portugal</option>
                            <option value="PR">Puerto Rico</option>
                            <option value="QA">Qatar</option>
                            <option value="GB">Reino Unido</option>
                            <option value="KR">República de Corea</option>
                            <option value="CZ">República Checa</option>
                            <option value="DO">República Dominicana</option>
                            <option value="IR">República Islámica de Irán</option>
                            <option value="RE">Reunión</option>
                            <option value="RW">Ruanda</option>
                            <option value="RO">Rumania</option>
                            <option value="RU">Rusia</option>
                            <option value="EH">Sahara Occidental</option>
                            <option value="BL">Saint Barthélemy</option>
                            <option value="MF">Saint Martin (French part)</option>
                            <option value="SB">Salomón, Islas</option>
                            <option value="WS">Samoa</option>
                            <option value="AS">Samoa Americana</option>
                            <option value="KN">San Cristóbal y Nieves</option>
                            <option value="SM">San Marino</option>
                            <option value="PM">San Pedro y Miquelón</option>
                            <option value="VC">San Vicente y las Granadinas</option>
                            <option value="SH">Santa Elena</option>
                            <option value="LC">Santa Lucía</option>
                            <option value="ST">Santo Tomé y Príncipe</option>
                            <option value="SN">Senegal</option>
                            <option value="RS">Serbia</option>
                            <option value="SC">Seychelles</option>
                            <option value="SL">Sierra Leona</option>
                            <option value="SG">Singapur</option>
                            <option value="SX">Sint Maarten (Dutch part)</option>
                            <option value="SY">Siria</option>
                            <option value="SO">Somalía</option>
                            <option value="SS">South Sudan</option>
                            <option value="LK">Sri Lanka</option>
                            <option value="ZA">Sudáfrica</option>
                            <option value="SD">Sudán</option>
                            <option value="SE">Suecia</option>
                            <option value="CH">Suiza</option>
                            <option value="SR">Surinam</option>
                            <option value="SJ">Svalbard y Jan Mayen</option>
                            <option value="SZ">Swazilandia</option>
                            <option value="TH">Tailandia</option>
                            <option value="TW">Taiwán</option>
                            <option value="TZ">Tanzania</option>
                            <option value="TJ">Tayikistán</option>
                            <option value="IO">Territorio Británico Océano Índico</option>
                            <option value="TF">Territorios Australes Franceses</option>
                            <option value="TL">Timor Oriental</option>
                            <option value="TG">Togo</option>
                            <option value="TK">Tokelau</option>
                            <option value="TO">Tonga</option>
                            <option value="TT">Trinidad y Tobago</option>
                            <option value="TN">Túnez</option>
                            <option value="TM">Turkmenistán</option>
                            <option value="TR">Turquía</option>
                            <option value="TV">Tuvalu</option>
                            <option value="UA">Ucrania</option>
                            <option value="UG">Uganda</option>
                            <option value="UY">Uruguay</option>
                            <option value="UZ">Uzbekistán</option>
                            <option value="VU">Vanuatu</option>
                            <option value="VE">Venezuela</option>
                            <option value="VN">Vietnam</option>
                            <option value="VG">Islas Vírgenes Británicas</option>
                            <option value="WF">Wallis y Fortuna</option>
                            <option value="YE">Yemen</option>
                            <option value="ZM">Zambia</option>
                            <option value="ZW">Zimbabue</option>

                          </select>
                    </div>
                  </div>

                  <!-- ENTRADA PARA EL TIPO DE PERSONA-->

                  <div class="form-group">
                    <p>Tipo de persona:</p>
                    <div class="input-group mb-3">
                          <div class="input-group-prepend">
                                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                          </div>
                          <select class="form-control" name="editarTipoPersona" value="" required>
                            <option id="editarTipoPersona" value=""></option>
                            <option value="1">Natural</option>
                            <option value="2">Juridica</option>
                          </select>
                    </div>
                  </div>

                </div>

                <div class="col-xl-6 col-xs-12">
                  <!-- ENTRADA PARA EL TIPO DE CONTRIBUYENTE -->

                    <div class="form-group">
                    <p>Tipo de contribuyente:</p>
                      <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                            </div>
                            <select class="form-control" id="editarTipoContribuyentes" name="editarTipoContribuyentes" value="" required>
                              <option id="editarContribu" value=""></option>
                              <option value="00">Persona natural</option>
                              <option value="01">Persona natural /juridica declarante de Iva - empresa sin beneficios fiscales</option>
                              <option value="02">Empresa con beneficios fiscales</option>
                              <option value="03">Diplomática o institución pública</option>
                            </select>
                      </div>

                    </div>


                    <!-- ENTRADA PARA EL NIT -->

                    <div class="form-group">
                      <P>Colocarlo sin guiones (si NO es contribuyente colocar 0000  y si es extranjero colocar aquí su número de identificación tributaria)</P>
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" id="editarNITCliente" name="editarNITCliente" value="" required>
                      </div>

                    </div>

                    <!-- ENTRADA PARA EL DUI -->

                    <div class="form-group">
                      <P>DUI Colocarlo sin guiones</P>
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" id="editarDUICliente" name="editarDUICliente" value="">
                      </div>

                    </div>

                    <!-- ENTRADA PARA EL NRC -->

                    <div class="form-group">
                      <P>Colocarlo sin guiones y si es extranjero colocar aquí su número de identificación tributaria</P>
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" id="editarNRCCliente" name="editarNRCCliente" value="">
                      </div>

                    </div>

                    <!-- ENTRADA PARA EL CÓDIGO DE ACT-->

                    <div class="form-group">
                          <p>Código de actividad económica, si no es contribuyente o es extranjero colocar 0000</p>
                            <div class="input-group mb-3">
                                  <div class="input-group-prepend">
                                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                  </div>
                                  <input type="text" class="form-control" id="editarCodActividad" name="editarCodActividad" placeholder="Ingresar código de actividad" required>
                            </div>

                          </div>
                          
                          <!-- ENTRADA PARA LA ACT-->

                          <div class="form-group">
                          <p>Actividad económica, si no es contribuyente colocar 0000</p>
                            <div class="input-group mb-3">
                                  <div class="input-group-prepend">
                                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                                  </div>
                                  <input type="text" class="form-control" id="editarDescActividad" name="editarDescActividad" placeholder="Ingresar nombre de actividad económica" required>
                            </div>

                          </div>

                </div>

            </div>
            

            

            
              </div>
            </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Modificar cliente</button>

        </div>

     <?php
          $editarCliente = new ControladorClientes();
          $editarCliente -> ctrEditarCliente();
        ?> 

      </form>

    </div>
    </div>
    </div>

  </div>

</div>

<?php

  $envarFactura = new ControladorFacturas();
  $envarFactura -> ctrEnviarFacturaCorreo();

?>

<?php

  $eliminarFirma = new ControladorFacturas();
  $eliminarFirma -> ctrEliminarFirma();

?>