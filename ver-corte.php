<?php

  if($_SESSION["rol"] == "Admin" || $_SESSION["rol"] == "Facturación" || $_SESSION["rol"] == "Contabilidad" || $_SESSION["rol"] == "Facturador" || $_SESSION["rol"] == "Vendedor"){
  } else {
      echo '<script>
      window.location = "inicio";
      </script>';
    return;
  }
// Configurar la zona horaria de El Salvador
date_default_timezone_set('America/El_Salvador');
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
    
    <h1>
      
      Corte de caja
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i>Inicio </a></li>
      
      <li class="active">&nbsp;Sistema de facturación</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">
    
      <div class="box-header with-border">
        <button class="btn btn-success" onclick="location.href='escoger-corte'">Regresar</button>
        <button class="btn btn-info" onclick="location.href='extensiones/TCPDF-main/examples/imprimir-corte-ticket.php?idCorte='+<?php echo $_GET['idCorte'] ?>">Generar ticket</button>
      </div>
<br>
      <div class="box-body">        

       <?php
               $item = "id";
               $valor = $_GET["idCorte"];
               $orden = "id";
       
               $corte = ControladorFacturas::ctrMostrarCortes($item, $valor, $orden, "no");
   
                $item = "id";
                $valor = $corte["id_facturador"];
        
                $facturador = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

                $autorizacion = "No";
                $cuadrada = "No";

                if($corte["autorizacion"] != ""){
                    $autorizacion = "Si";
                }

                if($corte["cuadrada"] != ""){
                    $cuadrada = "Si";
                }
        ?> 

        <div class="row">

            <div class="col-xl-3 col-xs-12">
                <b>Vendedor :</b> <?php echo $facturador["nombre"] ?>
            </div>

            <div class="col-xl-5 col-xs-12">
                <b>Fechal del corte :</b> <?php echo $corte["fecha"] ?>
            </div>

            <div class="col-xl-2 col-xs-12">
                <b>Autorizada :</b> <?php echo $autorizacion ?>
            </div>

            <div class="col-xl-2 col-xs-12">
                <b>Cuadrada :</b> <?php echo $cuadrada ?>
            </div>

        </div>
<br>
        <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
         
              <thead>
                
                <tr>
                  
                  <th style="width:10px">#</th>
                  <th>Facturador</th>
                  <th>Tipo DTE</th>
                  <th>Numero de control</th>
                  <th>Cliente</th>
                  <th>Monto</th>
    
                </tr> 
      
              </thead>
 
              <tbody>
      
                  <?php
                        // Convertir el string JSON a un array de PHP
                        $idsFacturas = json_decode($corte["ids_facturas"], true);  // Convertimos el JSON en array
                        $totalF = 0.0;
                        $totalFGeneral = 0.0;
                        $totalPorVendedor = array();

                        // Verificar si la decodificación fue exitosa
                        if (is_array($idsFacturas)) {
                            // Iterar sobre cada ID de factura
                            foreach ($idsFacturas as $key => $id) {
                                
                                $item = "id";
                                $valor = $id;
                                $orden = "fecEmi";
                                $optimizacion = "no";
                
                                $factura = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

                                $item = "id";
                                $valor = $factura["id_cliente"];
                                $orden = "id";

                                $cliente = ControladorClientes::ctrMostrarClientes($item, $valor, $orden);

                                if($cliente["tipo_cliente"] ==  "00" && $factura["tipoDte"] == "01"){
                                    $totalF = $factura["total"];
                                }
                                if($cliente["tipo_cliente"] ==  "00" && $factura["tipoDte"] == "03"){
                                    $totalF = $factura["total"];
                                }
                                if($cliente["tipo_cliente"] ==  "00" && $factura["tipoDte"] == "11"){
                                    $totalF = $factura["total"] + $factura["seguro"] + $factura["flete"];
                                }
                                if($cliente["tipo_cliente"] ==  "00" && $factura["tipoDte"] == "14"){
                                    $totalF = ($factura["totalSinIva"]-($factura["totalSinIva"]*0.1))*-1;
                                }

                                if($cliente["tipo_cliente"] ==  "01" && $factura["tipoDte"] == "01"){
                                    $totalF = $factura["total"];
                                }
                                if($cliente["tipo_cliente"] ==  "01" && $factura["tipoDte"] == "03"){
                                    $totalF = $factura["total"];
                                }
                                if($cliente["tipo_cliente"] ==  "01" && $factura["tipoDte"] == "11"){
                                    $totalF = $factura["totalSinIva"] + $factura["seguro"] + $factura["flete"];
                                }

                                if($cliente["tipo_cliente"] ==  "02" && $factura["tipoDte"] == "01"){
                                    $totalF = $factura["totalSinIva"];
                                }
                                if($cliente["tipo_cliente"] ==  "02" && $factura["tipoDte"] == "03"){
                                    $totalF = $factura["totalSinIva"];
                                }
                                if($cliente["tipo_cliente"] ==  "02" && $factura["tipoDte"] == "11"){
                                    $totalF = $factura["totalSinIva"] + $factura["seguro"] + $factura["flete"];
                                }

                                if($cliente["tipo_cliente"] ==  "03" && $factura["tipoDte"] == "01"){
                                    $totalF = $factura["totalSinIva"];
                                }
                                if($cliente["tipo_cliente"] ==  "03" && $factura["tipoDte"] == "03"){
                                    $totalF = $factura["totalSinIva"];
                                }
                                if($cliente["tipo_cliente"] ==  "03" && $factura["tipoDte"] == "11"){
                                    $totalF = $factura["totalSinIva"] + $factura["seguro"] + $factura["flete"];
                                }

                                if($cliente["tipo_cliente"] ==  "01" && $factura["tipoDte"] == "05"){
                                    $totalF = $factura["total"];
                                }
                                if($cliente["tipo_cliente"] ==  "02" && $factura["tipoDte"] == "05"){
                                    $totalF = $factura["totalSinIva"];
                                }
                                if($cliente["tipo_cliente"] ==  "03" && $factura["tipoDte"] == "05"){
                                    $totalF = $factura["totalSinIva"];
                                }

                                if($cliente["tipo_cliente"] ==  "01" && $factura["tipoDte"] == "04"){
                                    $totalF = $factura["total"];
                                }
                                if($cliente["tipo_cliente"] ==  "02" && $factura["tipoDte"] == "04"){
                                    $totalF = $factura["totalSinIva"];
                                }
                                if($cliente["tipo_cliente"] ==  "03" && $factura["tipoDte"] == "04"){
                                    $totalF = $factura["totalSinIva"];
                                }
                                
                                $item = "id";
                                $valor = $factura["id_vendedor"];

                                $usuario = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
                                echo ' <tr>
                                        <td>'.($key+1).'</td>
                                        <td>'.$usuario["nombre"].'</td>
                                        <td>'.$factura["tipoDte"].'</td>
                                        <td>'.$factura["numeroControl"].'</td>
                                        <td>'.$cliente["nombre"].'</td>
                                        <td>$'.$totalF.'</td>';
                                $totalFGeneral += $totalF;
                                 // Para el acumulador por vendedor:
                                // Usamos el nombre del vendedor (también podrías usar el id)
                                $nombreVendedor = $usuario["nombre"];

                                // Verificamos si el vendedor ya tiene una suma parcial, si no, lo inicializamos en 0
                                if (!isset($totalesPorVendedor[$nombreVendedor])) {
                                    $totalesPorVendedor[$nombreVendedor] = 0;
                                }

                                // Sumamos el total de la factura actual al total acumulado para ese vendedor
                                $totalesPorVendedor[$nombreVendedor] += $totalF;

                            }
                        }
          
                  ?> 
      
              </tbody>
 
          </table>

          <br>
          <form role="form" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-xl-4 col-xs-12">
                    <!-- ENTRADA PARA EL TOTAL GENERAL -->
                    <div class="form-group">
                        <b>Total del día a tener en efectivo:</b>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                            </div>
                            <input type="text" name="idCorte" value="<?php echo $_GET["idCorte"]?>" hidden>
                            <input type="text" class="form-control" name="nuevoMontoTotalCorte" value="<?php echo $totalFGeneral ?>" readonly>
                        </div>
                    </div>
                </div>

                <!-- AQUÍ MOSTRAMOS LOS TOTALES POR VENDEDOR -->
                <div class="col-xl-8 col-xs-12">
                    <div class="form-group">
                        <b>Totales por Vendedor:</b>
                        <?php if(isset($totalesPorVendedor) && !empty($totalesPorVendedor)): ?>
                            <?php foreach ($totalesPorVendedor as $vendedor => $total): ?>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><?php echo $vendedor; ?></span>
                                    </div>
                                    <input type="text" class="form-control" value="$<?php echo $total; ?>" readonly>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay totales por vendedor para mostrar.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-xl-2 col-xs-12">
                    <!-- ENTRADA PARA CUADRADA-->
                    <div class="form-group">
                        <b>¿Caja cuadrada?:</b>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-text-width"></i></span>
                            </div>
                            <?php
                                if($corte["cuadrada"] == ""){
                                    echo '<select class="form-control" name="nuevoCuadradaCorte">
                                            <option value="No">No</option>
                                            <option value="Si">Si</option>
                                        </select>';
                                } else {
                                    echo '<select class="form-control" name="nuevoCuadradaCorte" disabled>
                                            <option>'.$corte["cuadrada"].'</option>
                                        </select>';
                                }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-xs-12">
                    <!-- ENTRADA PARA AUTORIZACION-->
                    <div class="form-group">
                        <b>Si el corte de caja está cuadrado autorizar:</b>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-text-width"></i></span>
                            </div>
                            <?php
                                if($corte["autorizacion"] == ""){
                                    echo '<select class="form-control" name="nuevaAutorizacionCorte">
                                            <option value="No">No</option>
                                            <option value="Si">Si</option>
                                        </select>';
                                } else {
                                    echo '<select class="form-control" name="nuevaAutorizacionCorte" disabled>
                                            <option>'.$corte["autorizacion"].'</option>
                                        </select>';
                                }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-xs-12">
                    <!-- ENTRADA PARA COMENTARIOS-->
                    <div class="form-group">
                        <b>Comentarios sobre el corte de caja:</b>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-text-width"></i></span>
                            </div>
                            <textarea class="form-control" name="comentariosCorte" placeholder="Comentarios"></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-xs-12">
                    <!-- ENTRADA PARA CONTRASEÑA-->
                    <div class="form-group">
                        <b>Contraseña de autorización:</b>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-text-width"></i></span>
                            </div>
                            <input type="password" class="form-control" id="contraCorte">
                        </div>
                    </div>
                </div>

            </div>
                    
            <div class="modal-footer">
                <button type="submit" class="btn btn-dark" id="btnGuardarAproba" disabled>Guardar corte</button>
            </div>

            <?php
                $aprobarCorte = new ControladorFacturas();
                $aprobarCorte -> ctrEditarCorte();
            ?>
        </form>

                        
          

      </div>

    </div>

  </section>

</div>