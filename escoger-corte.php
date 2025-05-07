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
    
    <h1>
      
      Cortes de caja
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i>Inicio </a></li>
      
      <li class="active">&nbsp;Sistema de facturación</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <button class="btn btn-success" onclick="location.href='facturacion'">Regresar</button>
        <button class="btn btn-info" data-toggle="modal" data-target="#modalCrearCorte">Crear corte de caja</button>
<br><br>
      </div>
      <div class="box-body">

          <?php
              $optimizacion;
              if(!isset($_GET["optimizar"])){
                $optimizacion = "si";
                echo "<div class='form-check form-switch'>
                  <input class='form-check-input' type='checkbox' role='switch' id='flexSwitchCheckChecked' onclick=\"location.href='index.php?ruta=escoger-corte&optimizar=no'\" checked>
                  <label class='form-check-label' for='flexSwitchCheckChecked'>Optimizar tabla</label>
                </div>";
              } else {
                $optimizacion = "no";
                echo "<div class='form-check form-switch'>
                  <input class='form-check-input' type='checkbox' role='switch' id='flexSwitchCheckChecked' onclick=\"location.href='escoger-corte'\">
                  <label class='form-check-label' for='flexSwitchCheckChecked'>Optimizar tabla</label>
                </div>";
              }
          ?>


      <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
         
         <thead>
           
           <tr>
             
             <th style="width:10px">#</th>
             <th>Fecha</th>
             <th>Usuario</th>
             <th>Autorizado</th>
             <th>Cuadrada</th>
             <th>Comentarios</th>
             <th>Acciones</th>
 
           </tr> 
 
         </thead>

         <tbody>
 
         <?php
 
             $item = null;
             $valor = null;
             $orden = "id";
     
             $cortes = ControladorFacturas::ctrMostrarCortes($item, $valor, $orden, $optimizacion);
     
             foreach ($cortes as $key => $corte){
               if($_SESSION["rol"] == "Admin" || $_SESSION["id"] == $corte["id_facturador"]){

                 $item = "id";
                 $valor = $corte["id_facturador"];
         
                 $facturador = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

                 $autorizacion = "No";
                 $cuadrada = "No";

                 if($corte["autorizacion"] == "" || $corte["autorizacion"] == "No"){
                   
                 } else {
                   $autorizacion = "Si";
                 }

                 if($corte["cuadrada"] == "" || $corte["cuadrada"] == "No"){
                   
                 } else {
                   $cuadrada = "Si";
                 }

                 echo ' <tr>
                         <td>'.($key+1).'</td>
                         <td>'.$corte["fecha"].'</td>
                         <td>'.$facturador["nombre"].'</td>
                         <td>'.$autorizacion.'</td>
                         <td>'.$cuadrada.'</td>
                         <td>'.$corte["comentarios"].'</td>';
       
                                   
                         echo '
                               <td>
                                   <div class="btn-group">
                                       <button class="btn btn-info btnVerCorteCaja" onclick="location.href=\'index.php?ruta=ver-corte&idCorte='.$corte["id"].'\'"><i class="fa fa-eye"></i></button>
                                   </div>
                               </td>
                           ';
       
                           echo '</div>  
       
                         </td>
       
                       </tr>';
               }
                 
             }
 
 
         ?> 
 
         </tbody>

       </table>

      </div>

    </div>

  </section>

</div>

<!--=====================================
MODAL CREAR CORTE DE CAJA
======================================-->

<div id="modalCrearCorte" class="modal fade bd-example-modal-lg" role="dialog" style="width: 100% !important">
  
  <div class="modal-dialog modal-lg" style="max-width: 70%;">

    <div class="modal-content">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Facturas creadas el día de hoy</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">
          <div class="form-group">
            <label for="fechaCorte">Seleccione la fecha del corte:</label>
            <input type="date" id="fechaCorte" name="fechaCorte" class="form-control" onchange="filtrarFacturasPorFecha()" value="<?php echo date('Y-m-d'); ?>">
          </div>
          <div id="tablaFacturasCorte">
          <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
         
              <thead>
                
                <tr>
                  
                  <th style="width:10px">#</th>
                  <th>Numero de control</th>
                  <th>Cliente</th>
    
                </tr> 
      
              </thead>
 
              <tbody>
      
                  <?php

                      date_default_timezone_set('America/El_Salvador');
                      // Obtener la fecha y hora actual
                      $fechaActual = new DateTime(); // Fecha y hora actual
                      $fechaFormateada = $fechaActual->format('Y-m-d');  // Formato '2025-03-08'
          
                      $item = "fecEmi";
                      $valor = $fechaFormateada;
                      $orden = "id";
                      $optimizacion = "no";
              
                      $facturas = ControladorFacturas::ctrMostrarFacturasCortes($item, $valor, $orden, $optimizacion);
              
                      foreach ($facturas as $key => $factura){
                        if($factura["sello"] != "" && $factura["estado"] != "Anulada" && ($factura["tipoDte"] == "01" || $factura["tipoDte"] == "03" || $factura["tipoDte"] == "11")){
                          $item = "id";
                          $valor = $factura["id_cliente"];
                          $orden = "id";
                  
                          $cliente = ControladorClientes::ctrMostrarClientes($item, $valor, $orden);
                          echo ' <tr>
                                    <td>'.($key+1).'</td>
                                    <td>'.$factura["numeroControl"].'</td>
                                    <td>'.$cliente["nombre"].'</td>
                                </tr>';
                        }
                      }
          
          
                  ?> 
      
              </tbody>
 
          </table>
        </div>
        <script>
          function filtrarFacturasPorFecha() {
            var fecha = document.getElementById("fechaCorte").value;

            if (fecha !== "") {
              var xhr = new XMLHttpRequest();
              xhr.open("POST", "ajax/filtrar-facturas.ajax.php", true);
              xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
              xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                  document.getElementById("tablaFacturasCorte").innerHTML = xhr.responseText;
                }
              };
              xhr.send("fecha=" + fecha);
            }
          }
        </script>

          
            

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-dark pull-left" data-dismiss="modal">Salir</button>
          <button type="button" class="btn btn-primary pull-left" onclick="generarCorte()">Generar corte de caja</button>

          <script>
          function generarCorte() {
            var fecha = document.getElementById("fechaCorte").value;
            if(fecha){
              location.href = 'index.php?ruta=facturacion&crearCorte=si&fecha=' + fecha;
            } else {
              alert("Por favor, selecciona una fecha antes de generar el corte.");
            }
          }
          </script>


        </div>

      </div>
      
    </div>

    </div>

  </div>

</div>

<?php

  $crearCorte = new ControladorFacturas();
  $crearCorte -> ctrCrearCorteCaja();

?>