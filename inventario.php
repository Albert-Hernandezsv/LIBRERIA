<?php

if($_SESSION["rol"] == "Admin" || $_SESSION["rol"] == "Bodega"){
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
      
      Administrar inventario
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i>Inicio </a></li>
      
      <li class="active">&nbsp;Administrar inventario</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalCrearCategoria">Crear categoría</button>
        <button class="btn btn-success" data-toggle="modal" data-target="#modalVerCategorias">Ver categorías</button>
        <button class="btn btn-danger" data-toggle="modal" data-target="#modalCrearProducto">Crear producto</button>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalCrearProveedor">Crear proveedor</button>
        <button class="btn btn-info" data-toggle="modal" data-target="#modalVerProveedores">  Ver proveedores</button>
        <button class="btn btn-primary" id="imprimirInventario" onclick="descargarPdf2()">Descargar reporte como pdf</button>
        <button class="btn btn-success" onclick="exportarExcel2()">Descargar reporte como Excel</button> 
        <button class="btn btn-dark" onclick="location.href='kardex'">Sistema kardex</button>
<br><br>
      </div>

      <div class="box-body">
      <?php
            // Verificar si los parámetros existen y asignarlos a variables
            $filtroNombre = isset($_GET['filtroNombre']) ? $_GET['filtroNombre'] : 'todos';
            $filtroTipo = isset($_GET['filtroTipo']) ? $_GET['filtroTipo'] : 'todos';
            $filtroCategoria = isset($_GET['filtroCategoria']) ? $_GET['filtroCategoria'] : 'todos';
            $filtroCodigo = isset($_GET['filtroCodigo']) ? $_GET['filtroCodigo'] : 'todos';
            $filtroStock = isset($_GET['filtroStock']) ? $_GET['filtroStock'] : 'todos';

            $filtroNombreF = "";
            $filtroTipoF = "";
            $filtroCategoriaF = "";

            if($filtroNombre != "todos"){
              $item = "id";
              $valor = $filtroNombre;

              $productos = ControladorProductos::ctrMostrarProductos($item, $valor, $optimizacion);
              $filtroNombreF = $productos["nombre"];
            }

            if($filtroTipo != "todos"){
              if($filtroTipo == "1"){
                $filtroTipoF = "Bienes";
              }
              if($filtroTipo == "2"){
                $filtroTipoF = "Servicios";
              }
              if($filtroTipo == "3"){
                $filtroTipoF = "Ambos";
              }
              if($filtroTipo == "4"){
                $filtroTipoF = "Otros";
              }
            }

            if($filtroCategoria != "todos"){
              $item1 = "id";
              $valor1 = $filtroCategoria;
      
              $categorias1 = ControladorCategorias::ctrMostrarCategorias($item1, $valor1);

              $filtroCategoriaF = $categorias1["nombre"];
            }


            echo '
                <h4>Filtros aplicados actualmente</h4>
                <div class="row">
                  <div class="col-xl-2 col-xs-12">
                    Nombre de producto: '.$filtroNombreF.'
                  </div>
                  <div class="col-xl-2 col-xs-12">
                    Tipo de producto: '.$filtroTipoF.'
                  </div>
                  <div class="col-xl-2 col-xs-12">
                    Categoría del producto: '.$filtroCategoriaF.'
                  </div>
                  <div class="col-xl-2 col-xs-12">
                    Código del producto: '.$filtroCodigo.'
                  </div>
                  <div class="col-xl-2 col-xs-12">
                    Stock máximo del producto: '.$filtroStock.'
                  </div>
                </div>
            ';
        ?>
        <hr>
        <br>
        <h5>Filtrar productos:</h5>
        <form role="form" method="get" action="index.php?ruta=inventario" enctype="multipart/form-data">
          <input type="hidden" name="ruta" value="inventario">

          <div class="row">

            <div class="col-xl-2 col-xs-12">

                <!-- ENTRADA PARA FILTRO DEL NOMBRE -->
                <div class="form-group">
                <p>Filtrar por nombre de producto:</p>
                <br>
                  <div class="input-group mb-3">
                        <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <select class="form-control" name="filtroNombre" value="" required>
                          <option value="todos">Todos</option>
                          <?php
                              $item = null;
                              $valor = null;
                      
                              $productos = ControladorProductos::ctrMostrarProductos($item, $valor, $optimizacion);
                      
                            foreach ($productos as $key => $value){
                                echo '
                                    <option value="'.$value["id"].'">'.$value["nombre"].'</option>
                                ';
                            }
                          ?>
                        </select>
                  </div>

                </div>

            </div>

            <div class="col-xl-2 col-xs-12">

                <!-- ENTRADA PARA FILTRO DE TIPO -->
                <div class="form-group">
                <p>Filtrar por tipo de producto:</p>
                <br>
                  <div class="input-group mb-3">
                        <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <select class="form-control" name="filtroTipo" value="" required>
                          <option value="todos">Todos</option>
                          <option value="1">Bienes</option>
                          <option value="2">Servicios</option>
                          <option value="3">Ambos</option>
                          <option value="4">Otros</option>
                        </select>
                  </div>

                </div>

            </div>

            <div class="col-xl-2 col-xs-12">

                <!-- ENTRADA PARA FILTRO DEL NOMBRE -->
                <div class="form-group">
                <p>Filtrar por categoría:</p>
                <br>
                  <div class="input-group mb-3">
                        <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <select class="form-control" name="filtroCategoria" value="" required>
                          <option value="todos">Todas</option>
                          <?php

                              $item1 = null;
                              $valor1 = null;
                      
                              $categorias1 = ControladorCategorias::ctrMostrarCategorias($item1, $valor1);
                      
                            foreach ($categorias1 as $key => $value){
                              
                                echo '
                                    <option value="'.$value["id"].'">'.$value["nombre"].'</option>
                                ';
                            }
                          ?>
                        </select>
                  </div>

                </div>

            </div>

            <div class="col-xl-2 col-xs-12">

                <!-- ENTRADA PARA FILTRO DEL CODIGO -->
                <div class="form-group">
                <p>Filtrar por código de producto:</p>
                <br>
                  <div class="input-group mb-3">
                        <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <select class="form-control" name="filtroCodigo" value="" required>
                          <option value="todos">Todos</option>
                          <?php
                              $item = null;
                              $valor = null;
                      
                              $productos = ControladorProductos::ctrMostrarProductos($item, $valor, $optimizacion);
                      
                            foreach ($productos as $key => $value){
                                echo '
                                    <option value="'.$value["codigo"].'">'.$value["codigo"].'</option>
                                ';
                            }
                          ?>
                        </select>
                  </div>

                </div>

            </div>

            <div class="col-xl-2 col-xs-12">

                <!-- ENTRADA PARA FILTRO DEL STOCK -->
                <div class="form-group">
                <p>Stock máximo del producto (en blanco para mostrar todos):</p>
                  <div class="input-group mb-3">
                        <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <input type="number" class="form-control" name="filtroStock" value="">
                  </div>

                </div>

            </div>


          </div>
          <button type="submit" class="btn btn-dark">Aplicar filtros</button>
        </form>

        <br>
        <?php
              $optimizacion;
              if(!isset($_GET["optimizar"])){
                $optimizacion = "si";
                echo "<div class='form-check form-switch'>
                  <input class='form-check-input' type='checkbox' role='switch' id='flexSwitchCheckChecked' onclick=\"location.href='index.php?ruta=inventario&filtroNombre=".$filtroNombre."&filtroTipo=".$filtroTipo."&filtroCategoria=".$filtroCategoria."&filtroCodigo=".$filtroCodigo."&filtroStock=".$filtroStock."&optimizar=no'\" checked>
                  <label class='form-check-label' for='flexSwitchCheckChecked'>Optimizar tabla</label>
                </div>";
              } else {
                $optimizacion = "no";
                echo "<div class='form-check form-switch'>
                  <input class='form-check-input' type='checkbox' role='switch' id='flexSwitchCheckChecked' onclick=\"location.href='index.php?ruta=inventario&filtroNombre=".$filtroNombre."&filtroTipo=".$filtroTipo."&filtroCategoria=".$filtroCategoria."&filtroCodigo=".$filtroCodigo."&filtroStock=".$filtroStock."'\">
                  <label class='form-check-label' for='flexSwitchCheckChecked'>Optimizar tabla</label>
                </div>";
              }
          ?>
       <table class="table table-bordered table-striped dt-responsive tablas" id="inventario" width="100%">
         
        <thead>
         
         <tr>
           
           <th style="width:10px">#</th>
           <th>Nombre</th>
           <th>Tipo</th>
           <th>Categoría</th>
           <th>Precio de compra</th>
           <th>Precio de venta</th>
           <th>Stock</th>
           <th>Descripción</th>
           <th>Código</th>
           <th>Exento</th>
           <th>Imágen</th>
           <th>Acciones</th>

         </tr> 

        </thead>

        <tbody>

        <?php

        $item = null;
        $valor = null;

        $productos = ControladorProductos::ctrMostrarProductos($item, $valor, $optimizacion);

        foreach ($productos as $key => $value){
          $item1 = "id";
          $valor1 = $value["categoria_id"];
  
          $categorias1 = ControladorCategorias::ctrMostrarCategorias($item1, $valor1);

          if (
            ($filtroNombre == "todos" || $value["id"] == $filtroNombre) &&
            ($filtroTipo == "todos" || $value["tipo"] == $filtroTipo) &&
            ($filtroCategoria == "todos" || $categorias1["id"] == $filtroCategoria) &&
            ($filtroCodigo == "todos" || $value["codigo"] == $filtroCodigo) &&
            ($filtroStock == "" || $value["stock"] <= $filtroStock)
            ){

              $tipoTexto = "";
              if($value["tipo"] == "1"){
                $tipoTexto = "Bienes";
              }
              if($value["tipo"] == "2"){
                $tipoTexto = "Servicios";
              }
              if($value["tipo"] == "3"){
                $tipoTexto = "Bienes y servicios";
              }
              if($value["tipo"] == "4"){
                $tipoTexto = "Otros";
              }
                echo ' <tr>
                        <td>'.($key+1).'</td>
                        <td>'.$value["nombre"].'</td>
                        <td>'.$tipoTexto.'</td>';

                        echo '
                        <td>'.$categorias1["nombre"].'</td>
                        <td>$'.$value["precio_compra"].'</td>
                        <td>$'.$value["precio_venta"].'</td>
                        <td>'.$value["stock"].'</td>
                        <td>'.$value["descripcion"].'</td>
                        <td>'.$value["codigo"].'</td>
                        <td>'.$value["exento_iva"].'</td>';
                        

                        if($value["imagen"] != ""){

                          echo '<td><img src="'.$value["imagen"].'" class="img-thumbnail" width="40px"></td>';

                        }else{

                          echo '<td><img src="vistas/img/anonimo.png" class="img-thumbnail" width="40" alt="Usuario"></td>';

                        }
              
                        echo '
                        <td>

                          <div class="btn-group">
                            
                            <a class="btn btn-primary btnCodigoBarra" idProducto="'.$value["id"].'"><i class="fa fa-barcode"></i></a>

                            <button class="btn btn-warning btnEditarProducto" idProducto="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarProducto"><i class="fa fa-pencil"></i></button>

                            <button class="btn btn-danger btnEliminarProducto" idProducto="'.$value["id"].'" disabled><i class="fa fa-times"></i></button>

                            <button class="btn btn-info btnAgregarStock" idProducto="'.$value["id"].'" data-toggle="modal" data-target="#modalAgregarStock"><i class="fa fa-plus"></i></button>

                            <button class="btn btn-success btnVerAgregacionesStock" idProducto="'.$value["id"].'" data-toggle="modal" data-target="#modalVerAgregacionesStock"><i class="fa fa-history"></i></button>

                          </div>  

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
MODAL CREAR CATEGORIA
======================================-->

<div id="modalCrearCategoria" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Crear categoría</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            
            <div class="form-group">

              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" class="form-control" name="nuevoNombreCategoria" placeholder="Ingresar nombre de categoría" required>
              </div>

            </div>

            <!-- ENTRADA PARA LA DESCRIPCIÓN -->

            <div class="form-group">

              <div class="input-group mb-3">
                    <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                    </div>
                    <input type="text" class="form-control" name="nuevaDescripcionCategoria" placeholder="Ingresar descripción" required>
              </div>

            </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-dark">Guardar categoría</button>

        </div>

        <?php

          $crearCategoria = new ControladorCategorias();
          $crearCategoria -> ctrCrearCategoria();

        ?>

      </form>

      </div>

    </div>

    </div>

  </div>

</div>

<!--=====================================
MODAL VER CATEGORIAS
======================================-->
<div id="modalVerCategorias" class="modal" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Ver categorías</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">
          <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
         
         <thead>
          
          <tr>
            
            <th style="width:10px">#</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Acciones</th>
 
          </tr> 
 
         </thead>
 
         <tbody>
 
         <?php
 
         $item = null;
         $valor = null;
 
         $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
 
        foreach ($categorias as $key => $value){
          
           echo ' <tr>
                   <td>'.($key+1).'</td>
                   <td>'.$value["nombre"].'</td>
                   <td>'.$value["descripcion"].'</td>';
 
                             
                   echo '
                   <td>
 
                     <div class="btn-group">
                         
                       <button class="btn btn-warning btnEditarCategoria" idCategoria="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarCategoria"><i class="fa fa-pencil"></i></button>
 
                       <button class="btn btn-danger btnEliminarCategoria" idCategoria="'.$value["id"].'" idCategoriaValidar="'.$value["id"].'"><i class="fa fa-times"></i></button>
 
                     </div>  
 
                   </td>
 
                 </tr>';
         }
 
 
         ?> 
 
         </tbody>
 
        </table>
            

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
MODAL EDITAR CATEGORIA
======================================-->
<div id="modalEditarCategoria" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Editar categoría</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->

            <div class="form-group">

              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" class="form-control" id="editarNombreCategoria" name="editarNombreCategoria" value="" required readonly>
                <input type="text" class="form-control" id="editarIdCategoria" name="editarIdCategoria" hidden>
              </div>

            </div>

            <!-- ENTRADA PARA LA DESCRIPCION -->

            <div class="form-group">

              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" class="form-control" id="editarDescripcionCategoria" name="editarDescripcionCategoria" value="">
              </div>

            </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Modificar categoría</button>

        </div>

     <?php
          $editarCategoria = new ControladorCategorias();
          $editarCategoria -> ctrEditarCategoria();
        ?> 

      </form>

    </div>
    </div>
    </div>

  </div>

</div>

<!--=====================================
MODAL CREAR PRODUCTO
======================================-->
<div id="modalCrearProducto" class="modal fade" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Crear producto</h4>
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
                      <input type="text" class="form-control" name="nuevoNombreProducto" placeholder="Ingresar nombre de producto" required>
                    </div>

                  </div>
              </div>

              <div class="col-xl-6 col-xs-12">
                <!-- ENTRADA PARA LA DESCRIPCIÓN -->
                <div class="form-group">

                  <div class="input-group mb-3">
                        <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" name="nuevaDescripcionProducto" placeholder="Ingresar descripción" required>
                  </div>

                </div>
              </div>
              
              <div class="col-xl-6 col-xs-12">
                  <!-- ENTRADA PARA LA CATEGORIA -->            
                  <div class="form-group">
                      <p>Categoría</p>
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-line-chart"></i></span>
                        </div>
                        <select name="nuevaCategoriaProducto" id="nuevaCategoriaProducto" class="form-control">
                        <?php
        
                          $item = null;
                          $valor = null;
                  
                          $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
                  
                          foreach ($categorias as $key => $value){
                            
                            echo '  <option value="'.$value["id"].'">'.$value["nombre"].'</option>
                                ';
                          }
                  
                  
                          ?> 
                          </select>
                      </div>

                  </div>
              </div>

              <div class="col-xl-6 col-xs-12">
                <!-- ENTRADA PARA EL TIPO DE ITEM -->            
                <div class="form-group">
                  <p>Tipo de producto</p>
                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="basic-addon1"><i class="fa fa-line-chart"></i></span>
                    </div>
                    <select name="nuevoNombreTipo" id="nuevoNombreTipo" class="form-control">
                      <option value="1">Bienes</option>
                      <option value="2">Servicios</option>
                      <option value="3">Ambos (Bienes y servicios)</option>
                      <option value="4">Otros</option>
                    </select>
                  </div>

                </div>
              </div>
              
              <div class="col-xl-6 col-xs-12">
                    <!-- ENTRADA PARA LA UNIDAD DE MEDIDA -->            
                    <div class="form-group">
                      Unidad de medida
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-line-chart"></i></span>
                        </div>
                        <select name="nuevaUnidadMedida" id="nuevaUnidadMedida" class="form-control">
                          <option value="59">Unidad</option>
                          <option value="57">Ciento</option>
                          <option value="58">Docena</option>
                          <option value="1">Metro</option>
                          <option value="2">Yarda</option>
                          <option value="6">Milímetro</option>
                          <option value="9">Kilómetro cuadrado</option>
                          <option value="10">Hectárea</option>
                          <option value="13">Metro cuadrado</option>
                          <option value="15">Vara cuadrada</option>
                          <option value="18">Metro cúbico</option>
                          <option value="20">Barril</option>
                          <option value="22">Galón</option>
                          <option value="23">Litro</option>
                          <option value="24">Botella</option>
                          <option value="26">Mililitro</option>
                          <option value="30">Tonelada</option>
                          <option value="32">Quintal</option>
                          <option value="33">Arroba</option>
                          <option value="34">Kilogramo</option>
                          <option value="36">Libra</option>
                          <option value="37">Onza troy</option>
                          <option value="38">Onza</option>
                          <option value="39">Gramo</option>
                          <option value="40">Miligramo</option>
                          <option value="42">Megawatt</option>
                          <option value="43">Kilowatt</option>
                          <option value="44">Watt</option>
                          <option value="45">Megavoltio-amperio</option>
                          <option value="46">Kilovoltio-amperio</option>
                          <option value="47">Voltio-amperio</option>
                          <option value="49">Gigawatt-hora</option>
                          <option value="50">Megawatt-hora</option>
                          <option value="51">Kilowatt-hora</option>
                          <option value="52">Watt-hora</option>
                          <option value="53">Kilovoltio</option>
                          <option value="54">Voltio</option>
                          <option value="55">Millar</option>
                          <option value="56">Medio millar</option>
                          <option value="99">Otra</option>
                        </select>
                      </div>

                    </div>
              </div>

              <div class="col-xl-6 col-xs-12">
                  <!-- ENTRADA PARA EL STOCK INICIAL -->            
                  <div class="form-group">
                    Stock inicial
                    <div class="input-group mb-3">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                      </div>
                      <input type="text" class="form-control" name="nuevoStockProducto" placeholder="Ingresar stock inicial" required>
                    </div>

                </div>
              </div>

              <div class="col-xl-6 col-xs-12">
                <!-- ENTRADA PARA EL PRECIO DE COMPRA-->            
                <div class="form-group">
                  Precio de compra individual SIN IVA:
                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                    </div>
                    <input type="text" class="form-control" name="nuevoPrecioCompraProducto" id="nuevoPrecioCompraProducto" placeholder="Ingresar precio de compra" required>
                  </div>

                </div>

                <div class="row" hidden>

                  <div class="col-xl-6 col-xs-12">
                      <!-- ENTRADA PARA EL MARGEN DE GANANCIA EN PORCENTAJE-->
                      <div class="form-group">
                          Margen de ganancia en porcentaje:
                          <div class="input-group mb-3">
                            <div class="input-group-prepend">
                              <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                            </div>
                            <input type="text" class="form-control" name="nuevaGananciaProductoPorcentaje" id="nuevaGananciaProductoPorcentaje" value="0" min="0" required>
                          </div>

                      </div>
                  </div>

                    <div class="col-xl-6 col-xs-12">
                        <!-- ENTRADA PARA EL MARGEN DE GANANCIA EN MONTO-->
                        <div class="form-group">
                          Margen de ganancia en monto:
                        <div class="input-group mb-3">
                          <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                          </div>
                          <input type="text" class="form-control" name="nuevaGananciaProductoMonto" id="nuevaGananciaProductoMonto" value="0" required>
                        </div>

                    </div>
                  </div>

                </div>
                
              </div>

              <div class="col-xl-6 col-xs-12">
                  <!-- ENTRADA PARA FECHA VENCIMIENTO -->            
                  <div class="form-group">
                      Productos perecederos
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1">¿Es un producto perecedero?</span>
                        </div>
                        <input class="form-control" type="checkbox" value="" id="flexCheckDefault">
                      </div>

                  </div>
              
                <!-- ENTRADA PARA FECHA VENCIMIENTO -->
                <div class="form-group fechaVencimiento" hidden>
                      Ingrese la fecha de vencimiento: <br><br>
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                        </div>
                        <input type="date" class="form-control" id="nuevaFechaVencimiento" name="nuevaFechaVencimiento">
                      </div>

                </div>
              </div>

              <div class="col-xl-6 col-xs-12">
                  <!-- ENTRADA PARA EL PRECIO DE VENTA -->            
                  <div class="form-group">
                    Precio de venta SIN IVA:
                    <div class="input-group mb-3">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                      </div>
                      <input type="text" class="form-control" name="nuevoPrecioVentaProducto" id="nuevoPrecioVentaProducto" placeholder="Precio de venta" required readonly>
                    </div>

                  </div>
              </div>

              <div class="col-xl-6 col-xs-12">
                  <!-- ENTRADA PARA EL CÓDIGO -->            
                  <div class="form-group">
                      Código del producto
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-code"></i></span>
                        </div>
                        <input type="text" class="form-control" id="nuevoCodigoProducto" name="nuevoCodigoProducto" placeholder="Ingresar código de producto" required>
                      </div>

                  </div>
              </div>
              
              <div class="col-xl-6 col-xs-12">
                <!-- ENTRADA PARA SUBIR IMAGEN -->
                <div class="form-group">
                  
                  <div class="panel">SUBIR IMAGEN</div>
                  <input type="file" class="nuevaImagenProducto" name="nuevaImagenProducto">
                  <p class="help-block">Peso máximo de la imagen 2MB</p>
                  <img src="vistas/img/anonimo.jpg" class="img-thumbnail previsualizarImagenProducto" width="100px">

                </div>
              </div>

              <div class="col-xl-6 col-xs-12">
                  <!-- ENTRADA PARA EXENTO DE IVA -->            
                  <div class="form-group">
                      Exento de IVA
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-code"></i></span>
                        </div>
                        <select class="form-control" name="nuevoExentoIva" id="nuevoExentoIva">
                          <option value="no">No</option>
                          <option value="si">Si</option>
                        </select>
                      </div>

                  </div>
              </div> 
              

            </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-dark">Guardar producto nuevo</button>

        </div>

        <?php

          $crearProducto = new ControladorProductos();
          $crearProducto -> ctrCrearProducto();

        ?>

      </form>

      </div>

    </div>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR PRODUCTO
======================================-->
<div id="modalEditarProducto" class="modal fade" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Editar producto</h4>
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
                        <input type="text" class="form-control" name="editarNombreProducto" id="editarNombreProducto" placeholder="Ingresar nombre de producto" required>
                        <input type="text" class="form-control" name="editarIdProducto" id="editarIdProducto" hidden>
                      </div>

                    </div>
                </div>

                <div class="col-xl-6 col-xs-12">
                    <!-- ENTRADA PARA LA DESCRIPCIÓN -->
                    <div class="form-group">

                      <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                            </div>
                            <input type="text" class="form-control" name="editarDescripcionProducto" id="editarDescripcionProducto" placeholder="Ingresar descripción" required>
                      </div>

                    </div>
                </div>

                <div class="col-xl-6 col-xs-12">
                    <!-- ENTRADA PARA LA CATEGORIA -->            
                    <div class="form-group">
                    <p>Categoría</p>
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-line-chart"></i></span>
                        </div>
                        <select name="editarCategoriaProducto" class="form-control">
                          <option id="editarCategoriaProducto" value=""></option>
                        <?php
        
                          $item = null;
                          $valor = null;
                  
                          $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
                  
                          foreach ($categorias as $key => $value){
                            
                            echo '  <option value="'.$value["id"].'">'.$value["nombre"].'</option>
                                ';
                          }
                  
                  
                          ?> 
                          </select>
                      </div>

                    </div>
                </div>

                <div class="col-xl-6 col-xs-12">
                    <!-- ENTRADA PARA EL TIPO DE ITEM -->            
                    <div class="form-group">
                      <p>Tipo de producto</p>
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-line-chart"></i></span>
                        </div>
                        <select name="editarNombreTipo" class="form-control">
                          <option id="editarNombreTipo"></option>
                          <option value="1">Bienes</option>
                          <option value="2">Servicios</option>
                          <option value="3">Ambos (Bienes y servicios)</option>
                          <option value="4">Otros</option>
                        </select>
                      </div>

                    </div>
                </div> 

                <div class="col-xl-6 col-xs-12">
                    <!-- ENTRADA PARA LA UNIDAD DE MEDIDA -->            
                    <div class="form-group">
                      Unidad de medida
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-line-chart"></i></span>
                        </div>
                        <select name="editarUnidadMedida" class="form-control">
                          <option id="editarUnidadMedida"></option>
                          <option value="59">Unidad</option>
                          <option value="57">Ciento</option>
                          <option value="58">Docena</option>
                          <option value="1">Metro</option>
                          <option value="2">Yarda</option>
                          <option value="6">Milímetro</option>
                          <option value="9">Kilómetro cuadrado</option>
                          <option value="10">Hectárea</option>
                          <option value="13">Metro cuadrado</option>
                          <option value="15">Vara cuadrada</option>
                          <option value="18">Metro cúbico</option>
                          <option value="20">Barril</option>
                          <option value="22">Galón</option>
                          <option value="23">Litro</option>
                          <option value="24">Botella</option>
                          <option value="26">Mililitro</option>
                          <option value="30">Tonelada</option>
                          <option value="32">Quintal</option>
                          <option value="33">Arroba</option>
                          <option value="34">Kilogramo</option>
                          <option value="36">Libra</option>
                          <option value="37">Onza troy</option>
                          <option value="38">Onza</option>
                          <option value="39">Gramo</option>
                          <option value="40">Miligramo</option>
                          <option value="42">Megawatt</option>
                          <option value="43">Kilowatt</option>
                          <option value="44">Watt</option>
                          <option value="45">Megavoltio-amperio</option>
                          <option value="46">Kilovoltio-amperio</option>
                          <option value="47">Voltio-amperio</option>
                          <option value="49">Gigawatt-hora</option>
                          <option value="50">Megawatt-hora</option>
                          <option value="51">Kilowatt-hora</option>
                          <option value="52">Watt-hora</option>
                          <option value="53">Kilovoltio</option>
                          <option value="54">Voltio</option>
                          <option value="55">Millar</option>
                          <option value="56">Medio millar</option>
                          <option value="99">Otra</option>
                        </select>
                      </div>

                    </div>
                </div>

                <div class="col-xl-6 col-xs-12">
                    <!-- ENTRADA PARA EL STOCK INICIAL -->            
                    <div class="form-group">
                      Stock individual
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                        </div>
                        <input type="text" class="form-control" name="editarStockProducto" id="editarStockProducto" placeholder="Ingresar stock inicial" required readonly>
                      </div>

                    </div>
                </div>

                <div class="col-xl-6 col-xs-12">
                    <!-- ENTRADA PARA EL PRECIO DE COMPRA-->            
                    <div class="form-group">
                      Precio de compra individual SIN IVA:
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                        </div>
                        <input type="text" class="form-control" name="editarPrecioCompraProducto" id="editarPrecioCompraProducto" placeholder="Ingresar precio de compra" required>
                      </div>

                    </div>

                      <div class="row" hidden>

                        <div class="col-xl-6 col-xs-12">
                            <!-- ENTRADA PARA EL MARGEN DE GANANCIA EN PORCENTAJE-->
                            <div class="form-group">
                                Margen de ganancia en porcentaje:
                                <div class="input-group mb-3">
                                  <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                  </div>
                                  <input type="text" class="form-control" name="editarGananciaProductoPorcentaje" id="editarGananciaProductoPorcentaje" value="0" min="0" required>
                                </div>

                            </div>
                        </div>

                        <div class="col-xl-6 col-xs-12">
                            <!-- ENTRADA PARA EL MARGEN DE GANANCIA EN MONTO-->
                            <div class="form-group">
                              Margen de ganancia en monto:
                              <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                                </div>
                              <input type="text" class="form-control" name="editarGananciaProductoMonto" id="editarGananciaProductoMonto" value="0" required>
                            </div>

                        </div>
                      </div>

                  </div>

                </div>

                <div class="col-xl-6 col-xs-12">
                  <!-- ENTRADA PARA FECHA VENCIMIENTO -->            
                  <div class="form-group">
                      Productos perecederos
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1">¿Es un producto perecedero?</span>
                        </div>
                        <input class="form-control" type="checkbox" value="" id="flexCheckDefault1">
                      </div>

                  </div>
              
                  <!-- ENTRADA PARA FECHA VENCIMIENTO -->
                  <div class="form-group fechaVencimiento1" hidden>
                        Ingrese la fecha de vencimiento: <br><br>
                        <div class="input-group mb-3">
                          <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                          </div>
                          <input type="date" class="form-control" id="editarFechaVencimiento" name="editarFechaVencimiento">
                        </div>

                  </div>
                </div>

                <div class="col-xl-6 col-xs-12">
                    <!-- ENTRADA PARA EL PRECIO DE VENTA -->            
                    <div class="form-group">
                      Precio de venta SIN IVA:
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                        </div>
                        <input type="text" class="form-control" name="editarPrecioVentaProducto" id="editarPrecioVentaProducto" placeholder="Ingresar precio de venta" required readonly>
                      </div>

                    </div>
                </div>

                <div class="col-xl-6 col-xs-12">
                    <!-- ENTRADA PARA EL CÓDIGO -->            
                    <div class="form-group">

                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-code"></i></span>
                        </div>
                        <input type="text" class="form-control" id="editarCodigoProducto" name="editarCodigoProducto" placeholder="Ingresar código de producto" required readonly>
                      </div>

                    </div>
                </div>

                <div class="col-xl-6 col-xs-12">
                    <!-- ENTRADA PARA SUBIR IMAGEN -->
                    <div class="form-group">
                      
                      <div class="panel">SUBIR IMAGEN</div>
                      <input type="file" class="editarImagenProducto" name="editarImagenProducto">
                      <p class="help-block">Peso máximo de la imagen 2MB</p>
                      <img class="img-thumbnail previsualizarEditarImagenProducto" width="100px">
                      <input type="hidden" name="imagenActualProducto" id="imagenActualProducto">
                    </div>
                </div>

                <div class="col-xl-6 col-xs-12">
                  <!-- ENTRADA PARA EXENTO DE IVA -->            
                  <div class="form-group">
                      Exento de IVA
                      <div class="input-group mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-code"></i></span>
                        </div>
                        <select class="form-control" name="editarExentoIva">
                          <option id="editarExentoIva" value=""></option>
                          <option value="no">No</option>
                          <option value="si">Si</option>
                        </select>
                      </div>

                  </div>
              </div> 

            </div>
        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Modificar producto</button>

        </div>

     <?php
          $editarProducto = new ControladorProductos();
          $editarProducto -> ctrEditarProducto();
        ?> 

      </form>

    </div>
    </div>
    </div>

  </div>

</div>

<!--=====================================
MODAL AGREGAR STOCK
======================================-->
<div id="modalAgregarStock" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Agregar stock</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA LA CANTIDAD -->
            
            <div class="form-group">

              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-sort-numeric-desc"></i></span>
                </div>
                <input type="number" class="form-control" name="nuevaCantidadStock" id="nuevaCantidadStock" placeholder="Ingresar cantidad a sumar de stock" required>
                <input type="text" class="form-control" name="idProductoCantidadStock" id="idProductoCantidadStock" hidden>
                <input type="number" class="form-control" name="cantidadStockActual" id="cantidadStockActual" hidden>
              </div>

            </div>

            <!-- ENTRADA PARA EL PROVEEDOR -->

            <div class="form-group">

              <div class="input-group mb-3">
                    <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                    </div>
                    <select name="nuevoProveedorStock" class="form-control">
                      <?php
                        $item = null;
                        $valor = null;
                        $orden = "id";
                
                        $proveedores = ControladorClientes::ctrMostrarProveedores($item, $valor, $orden);

                        foreach($proveedores as $proveedor){
                          echo '<option value="'.$proveedor["nombre"].'">'.$proveedor["nombre"].'</option>';
                        }
                      ?>
                      
                    </select>
              </div>

            </div>

                <!-- ENTRADA PARA EL PRECIO DE COMPRA-->            
                <div class="form-group">
                  Precio de compra individual SIN IVA:
                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                    </div>
                    <input type="text" class="form-control" name="nuevoPrecioCompraProducto1" id="nuevoPrecioCompraProducto1" placeholder="Ingresar precio de compra" required>
                  </div>

                </div>

                <div class="row" hidden>

                  <div class="col-xl-6 col-xs-12">
                      <!-- ENTRADA PARA EL MARGEN DE GANANCIA EN PORCENTAJE-->
                      <div class="form-group">
                          Margen de ganancia en porcentaje:
                          <div class="input-group mb-3">
                            <div class="input-group-prepend">
                              <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                            </div>
                            <input type="text" class="form-control" name="nuevaGananciaProductoPorcentaje1" id="nuevaGananciaProductoPorcentaje1" value="0" min="0" required>
                          </div>

                      </div>
                  </div>

                    <div class="col-xl-6 col-xs-12">
                        <!-- ENTRADA PARA EL MARGEN DE GANANCIA EN MONTO-->
                        <div class="form-group">
                          Margen de ganancia en monto:
                        <div class="input-group mb-3">
                          <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                          </div>
                          <input type="text" class="form-control" name="nuevaGananciaProductoMonto1" id="nuevaGananciaProductoMonto1" value="0" required>
                        </div>

                    </div>
                  </div>

                </div>

            <!-- ENTRADA PARA EL PRECIO DE VENTA -->            
            <div class="form-group">
                Precio de venta SIN IVA:
                <div style="color:red">Nota: este nuevo precio se aplicara al producto en general, si el cambio es muy grande espera a que todas las existencias del producto actual se hayan agotado o realiza un descuento en
                  facturación hasta agotar existencias
                </div>
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-usd"></i></span>
                  </div>
                  <input type="text" class="form-control" name="nuevoPrecioVentaProducto1" id="nuevoPrecioVentaProducto1" placeholder="Precio de venta" required readonly>
                </div>

              </div>

            <!-- ENTRADA PARA LA FECHA -->

            <div class="form-group">
            <span>Fecha y hora de ingreso:</span>
              <div class="input-group mb-3">
                    <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                    </div>
                    <input type="datetime-local" class="form-control" name="nuevaFechaStock" id="nuevaFechaStock" placeholder="Fecha de ingreso de stock" required>
              </div>

            </div>

            <!-- ENTRADA PARA COMENTARIOS -->

            <div class="form-group">
            <span>Comentarios:</span>
              <div class="input-group mb-3">
                    <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                    </div>
                    <textarea class="form-control" name="nuevoComentarioStock" id="nuevoComentarioStock" placeholder="Comentarios"></textarea>
              </div>

            </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-dark">Agregar stock</button>

        </div>

        <?php

          $crearStock = new ControladorStocks();
          $crearStock -> ctrCrearStock();

        ?>

      </form>

      </div>

    </div>

    </div>

  </div>

</div>

<!--=====================================
MODAL VER AGREGACIONES DE STOCK
======================================-->
<div id="modalVerAgregacionesStock" class="modal" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:grey; color:white">
        <h4 class="modal-title">Ver agregaciones de stock</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>

      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <div class="box-body">

        <table class="table table-bordered table-striped dt-responsive tablas" id="tablaStocks" width="100%">
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

<!--=====================================
MODAL CREAR PROVEEDOR
======================================-->

<div id="modalCrearProveedor" class="modal fade" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Crear proveedor</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">
      
            <!-- ENTRADA PARA EL NOMBRE -->            
            <div class="form-group">
              Nombre del proveedor
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" class="form-control" name="nuevoNombreProveedor" id="nuevoNombreProveedor" placeholder="Ingresar nombre del proveedor" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL NIT -->            
            <div class="form-group">
              NIT o NRC sin guiones:
              <div class="input-group mb-3">
                
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" class="form-control" name="nuevoNitProveedor" id="nuevoNitProveedor" placeholder="Ingresar NIT o NR del proveedor" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL NUMERO -->            
            <div class="form-group">
              Número telefónico sin guiones:
              <div class="input-group mb-3">
                
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" class="form-control" name="nuevoNumeroProveedor" id="nuevoNumeroProveedor" placeholder="Ingresar número telefónico del proveedor" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL CORREO -->            
            <div class="form-group">
              Correo electrónico:
              <div class="input-group mb-3">
                
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="email" class="form-control" name="nuevoCorreoProveedor" id="nuevoCorreoProveedor" placeholder="Ingresar correo del proveedor" required>
              </div>
            </div>

            <!-- ENTRADA PARA LA DIRECCIÓN -->            
            <div class="form-group">
              Dirección física:
              <div class="input-group mb-3">
                
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" class="form-control" name="nuevaDireccionProveedor" id="nuevaDireccionProveedor" placeholder="Ingresar dirección del proveedor" required>
              </div>
            </div>

            <!-- ENTRADA PARA CONDICION DE PAGO -->
            <div class="form-group">
              Condición de pago:
              <div class="input-group mb-3">
                
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" class="form-control" name="nuevaCondicionProveedor" id="nuevoNitProveedor" placeholder="Ingresar condición de pago" required>
              </div>
            </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-dark">Guardar proveedor</button>

        </div>

        <?php

          $crearProveedor = new ControladorClientes();
          $crearProveedor -> ctrCrearProveedor();

        ?>

      </form>

      </div>

    </div>

    </div>

  </div>

</div>

<!--=====================================
MODAL VER PROVEEDORES
======================================-->

<div id="modalVerProveedores" class="modal fade bd-example-modal-lg" role="dialog" style="width: 100% !important">
  
  <div class="modal-dialog modal-lg" style="max-width: 70%;">

    <div class="modal-content">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Proveedores registrados</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">
          <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
         
         <thead>
          
          <tr>
            
            <th style="width:10px">#</th>
            <th>Nombre</th>
            <th>NIT o NRC</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Dirección</th>
            <th>Condicion de pago</th>
            <th>Acciones</th>
 
          </tr> 
 
         </thead>
 
         <tbody>
 
         <?php
 
         $item = null;
         $valor = null;
         $orden = "id";
 
         $motorista = ControladorClientes::ctrMostrarProveedores($item, $valor, $orden);
 
        foreach ($motorista as $key => $value){
            
           echo ' <tr>
                   <td>'.($key+1).'</td>
                   <td>'.$value["nombre"].'</td>
                   <td>'.$value["nit"].'</td>
                   <td>'.$value["telefono"].'</td>
                   <td>'.$value["correo"].'</td>
                   <td>'.$value["direccion"].'</td>
                   <td>'.$value["condicion_pago"].'</td>
                   ';
 
                             
                   echo '
                   <td>
 
                     <div class="btn-group">
                         
                       <button class="btn btn-warning btnEditarProveedor" idProveedor="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarProveedor"><i class="fa fa-pencil"></i></button>
 
                       <button class="btn btn-danger btnEliminarProveedor" idProveedor="'.$value["id"].'"><i class="fa fa-times"></i></button>';
 
                     echo '</div>  
 
                   </td>
 
                 </tr>';
         }
 
 
         ?> 
 
         </tbody>
 
        </table>
            

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
MODAL EDITAR PROVEEDOR
======================================-->

<div id="modalEditarProveedor" class="modal fade" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:grey; color:white">
          <h4 class="modal-title">Editar proveedor</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">
      
            <!-- ENTRADA PARA EL NOMBRE -->            
            <div class="form-group">
              <div class="input-group mb-3">
                
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" name="editarIdProveedor" id="editarIdProveedor" hidden>
                <input type="text" class="form-control" name="editarnombreProveedor" id="editarNombreProveedor" placeholder="Ingresar nombre del proveedor" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL NIT O NRC -->            
            <div class="form-group">
              NIT o NRC sin guiones:
              <div class="input-group mb-3">
                
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" class="form-control" name="editarNitProveedor" id="editarNitProveedor" placeholder="Ingresar NIT o NRC del Proveedor" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL NUMERO -->            
            <div class="form-group">
              Número telefónico sin guiones:
              <div class="input-group mb-3">
                
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" class="form-control" name="editarNumeroProveedor" id="editarNumeroProveedor" placeholder="Ingresar número telefónico del proveedor" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL CORREO -->            
            <div class="form-group">
              Correo electrónico:
              <div class="input-group mb-3">
                
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="email" class="form-control" name="editarCorreoProveedor" id="editarCorreoProveedor" placeholder="Ingresar correo del proveedor" required>
              </div>
            </div>

            <!-- ENTRADA PARA LA DIRECCIÓN -->            
            <div class="form-group">
              Dirección física:
              <div class="input-group mb-3">
                
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" class="form-control" name="editarDireccionProveedor" id="editarDireccionProveedor" placeholder="Ingresar dirección del proveedor" required>
              </div>
            </div>

            <!-- ENTRADA PARA CONDICION DE PAGO -->
            <div class="form-group">
              Condición de pago:
              <div class="input-group mb-3">
                
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                </div>
                <input type="text" class="form-control" name="editarCondicionProveedor" id="editarCondicionProveedor" placeholder="Ingresar condición de pago" required>
              </div>
            </div>
        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-dark">Guardar proveedor</button>

        </div>

        <?php

          $editarMotorista = new ControladorClientes();
          $editarMotorista -> ctrEditarProveedor();

        ?>

      </form>

      </div>

    </div>

    </div>

  </div>

</div>


<?php

  $borrarCategoria = new ControladorCategorias();
  $borrarCategoria -> ctrBorrarCategoria();

?> 


<?php

  $borrarProducto = new ControladorProductos();
  $borrarProducto -> ctrBorrarProducto();

?> 

<?php

  $borrarProveedor = new ControladorClientes();
  $borrarProveedor -> ctrBorrarProveedor();

?>