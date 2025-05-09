<?php
// Incluir TCPDF y demás dependencias (ajusta las rutas según tu configuración)
require_once "../../../controladores/facturas.controlador.php";
require_once "../../../modelos/facturas.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

require_once "../../../controladores/categorias.controlador.php";
require_once "../../../modelos/categorias.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once '../../phpqrcode/qrlib.php';
require_once('tcpdf_include.php');

// -------------------------
// Recuperar datos desde la BD
// -------------------------
$item   = "id";
$orden  = "id";
$valor  = "1";

// Obtiene los datos de la empresa
$empresa = ControladorClientes::ctrMostrarEmpresas($item, $valor, $orden);
$ancho   = "70";
$largo   = "350";

$item   = "id";
$valor  = $_GET["idCorte"];
$orden  = "id";

$corte = ControladorFacturas::ctrMostrarCortes($item, $valor, $orden, "no");

$item   = "id";
$valor  = $corte["id_facturador"];
$facturador = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

$autorizacion = ($corte["autorizacion"] != "") ? "Si" : "No";
$cuadrada     = ($corte["cuadrada"] != "") ? "Si" : "No";

// Obtener las facturas en base al JSON
$idsFacturas = json_decode($corte["ids_facturas"], true);

// Inicializamos los acumuladores globales
$ventaExentaTotal   = 0.0;
$ventaNoSujetaTotal = 0.0;
$ventaGravadaTotal  = 0.0;
$totalDescuentos = 0.00;
$ventaExentaFinal = 0.0;
$ventaExentaCredito = 0.0;
$ventaGravadaFinal = 0.0;
$ventaGravadaCredito = 0.0;


// Arreglo para acumular los totales por facturador (vendedor)
$ventasPorVendedor = array();

// Arreglo para acumular las ventas detalladas: [vendedor][categoria][tipoVenta]
$ventasDetalladas = array();

$ventasPorCategoriaGlobal = array();

// Definir la función auxiliar una sola vez (fuera del bucle)
if (!function_exists('inicializaAgrupacion')) {
    function inicializaAgrupacion(&$ventasDetalladas, $nombreVendedor, $claveCategoria) {
        if (!isset($ventasDetalladas[$nombreVendedor])) {
            $ventasDetalladas[$nombreVendedor] = array();
        }
        if (!isset($ventasDetalladas[$nombreVendedor][$claveCategoria])) {
            $ventasDetalladas[$nombreVendedor][$claveCategoria] = array(
                'exenta'    => 0.0,
                'no_sujeta' => 0.0,
                'gravada'   => 0.0
            );
        }
    }
}

if (!function_exists('inicializaCategoriaGlobal')) {
    function inicializaCategoriaGlobal(&$ventasPorCategoriaGlobal, $claveCategoria) {
        if (!isset($ventasPorCategoriaGlobal[$claveCategoria])) {
            $ventasPorCategoriaGlobal[$claveCategoria] = array(
                'exenta'    => 0.0,
                'no_sujeta' => 0.0,
                'gravada'   => 0.0
            );
        }
    }
}


// Procesamos cada factura
if (is_array($idsFacturas)) {
    foreach ($idsFacturas as $id) {

        // Recuperar la factura
        $factura = ControladorFacturas::ctrMostrarFacturas("id", $id, "fecEmi", "no");
        
        // Recuperar el cliente asociado
        $cliente = ControladorClientes::ctrMostrarClientes("id", $factura["id_cliente"], "id");
        
        // Decodificar los productos guardados en la factura (se asume que están en formato JSON)
        $productos = json_decode($factura["productos"], true);
        
        // Recuperar el vendedor de la factura y su nombre (para el agrupamiento)
        $usuario = ControladorUsuarios::ctrMostrarUsuarios("id", $factura["id_vendedor"]);
        $nombreVendedor = $usuario["nombre"];
        
        // Inicializamos los acumuladores para esta factura
        $ventaExenta   = 0.0;
        $ventaGravada  = 0.0;
        $ventaNoSujeta = 0.0;
        
        // --- Caso: tipo_cliente "00" y DTE "01"
        if($cliente["tipo_cliente"] == "00" && $factura["tipoDte"] == "01"){
            foreach ($productos as $producto) {
                $productoLeido = ControladorProductos::ctrMostrarProductos("id", $producto["idProducto"], "no");
                // Usamos el campo "categoria_id" (ajústalo si tu campo es diferente)
                $claveCategoriaInfo = ControladorCategorias::ctrMostrarCategorias("id", $productoLeido["categoria_id"]);
                $claveCategoriaId = $claveCategoriaInfo["id"];
                $claveCategoriaNombre = $claveCategoriaInfo["nombre"];
                $claveCategoria = str_pad($claveCategoriaId, 5, "0", STR_PAD_LEFT) . "||" . $claveCategoriaNombre;


                
                // Inicializamos la estructura para este vendedor y categoría
                inicializaAgrupacion($ventasDetalladas, $nombreVendedor, $claveCategoria);
                inicializaCategoriaGlobal($ventasPorCategoriaGlobal, $claveCategoria);

        
                if($productoLeido["exento_iva"] == "si"){
                    $monto = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
                    $ventaExenta   += $monto;
                    $ventasDetalladas[$nombreVendedor][$claveCategoria]['exenta'] += $monto;
                    $ventasPorCategoriaGlobal[$claveCategoria]['exenta'] += $monto;
                    $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                    $ventaExentaFinal += $monto;
                } else {
                    $monto = (($producto["precioConIva"] - $producto["descuentoConIva"]) * $producto["cantidad"]);
                    $ventaGravada  += $monto;
                    $ventasDetalladas[$nombreVendedor][$claveCategoria]['gravada'] += $monto;
                    $ventasPorCategoriaGlobal[$claveCategoria]['gravada'] += $monto;
                    $totalDescuentos += $producto["descuentoConIva"] * $producto["cantidad"];
                    $ventaGravadaFinal += $monto;
                }
            }  
        }
        // --- Caso: tipo_cliente "01" y DTE "01"
        elseif($cliente["tipo_cliente"] == "01" && $factura["tipoDte"] == "01"){
            foreach ($productos as $producto) {
                $productoLeido = ControladorProductos::ctrMostrarProductos("id", $producto["idProducto"], "no");
                $claveCategoriaInfo = ControladorCategorias::ctrMostrarCategorias("id", $productoLeido["categoria_id"]);
                $claveCategoriaId = $claveCategoriaInfo["id"];
                $claveCategoriaNombre = $claveCategoriaInfo["nombre"];
                $claveCategoria = str_pad($claveCategoriaId, 5, "0", STR_PAD_LEFT) . "||" . $claveCategoriaNombre;

                inicializaAgrupacion($ventasDetalladas, $nombreVendedor, $claveCategoria);
                inicializaCategoriaGlobal($ventasPorCategoriaGlobal, $claveCategoria);

                
                if($productoLeido["exento_iva"] == "si"){
                    $monto = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
                    $ventaExenta   += $monto;
                    $ventasDetalladas[$nombreVendedor][$claveCategoria]['exenta'] += $monto;
                    $ventasPorCategoriaGlobal[$claveCategoria]['exenta'] += $monto;
                    $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                    $ventaExentaFinal += $monto;
                } else {
                    $monto = (($producto["precioConIva"] - $producto["descuentoConIva"]) * $producto["cantidad"]);
                    $ventaGravada  += $monto;
                    $ventasDetalladas[$nombreVendedor][$claveCategoria]['gravada'] += $monto;
                    $ventasPorCategoriaGlobal[$claveCategoria]['gravada'] += $monto;
                    $totalDescuentos += $producto["descuentoConIva"] * $producto["cantidad"];
                    $ventaGravadaFinal += $monto;
                }
            }
        }
        // --- Caso: tipo_cliente "02" y DTE "01"
        elseif($cliente["tipo_cliente"] == "02" && $factura["tipoDte"] == "01"){
            foreach ($productos as $producto) {
                $productoLeido = ControladorProductos::ctrMostrarProductos("id", $producto["idProducto"], "no");
                $claveCategoriaInfo = ControladorCategorias::ctrMostrarCategorias("id", $productoLeido["categoria_id"]);
                $claveCategoriaId = $claveCategoriaInfo["id"];
                $claveCategoriaNombre = $claveCategoriaInfo["nombre"];
                $claveCategoria = str_pad($claveCategoriaId, 5, "0", STR_PAD_LEFT) . "||" . $claveCategoriaNombre;

                inicializaAgrupacion($ventasDetalladas, $nombreVendedor, $claveCategoria);
                inicializaCategoriaGlobal($ventasPorCategoriaGlobal, $claveCategoria);

                
                if($productoLeido["exento_iva"] == "si"){
                    $monto = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
                    $ventaExenta   += $monto;
                    $ventasDetalladas[$nombreVendedor][$claveCategoria]['exenta'] += $monto;
                    $ventasPorCategoriaGlobal[$claveCategoria]['exenta'] += $monto;
                    $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                    $ventaExentaFinal += $monto;
                } else {
                    $monto = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
                    $ventaNoSujeta += $monto;
                    $ventasDetalladas[$nombreVendedor][$claveCategoria]['no_sujeta'] += $monto;
                    $ventasPorCategoriaGlobal[$claveCategoria]['no_sujeta'] += $monto;
                    $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                }
            }
        }
        // --- Caso: tipo_cliente "03" y DTE "01"
        elseif($cliente["tipo_cliente"] == "03" && $factura["tipoDte"] == "01"){
            foreach ($productos as $producto) {
                $productoLeido = ControladorProductos::ctrMostrarProductos("id", $producto["idProducto"], "no");
                $claveCategoriaInfo = ControladorCategorias::ctrMostrarCategorias("id", $productoLeido["categoria_id"]);
                $claveCategoriaId = $claveCategoriaInfo["id"];
                $claveCategoriaNombre = $claveCategoriaInfo["nombre"];
                $claveCategoria = str_pad($claveCategoriaId, 5, "0", STR_PAD_LEFT) . "||" . $claveCategoriaNombre;

                inicializaAgrupacion($ventasDetalladas, $nombreVendedor, $claveCategoria);
                inicializaCategoriaGlobal($ventasPorCategoriaGlobal, $claveCategoria);

                
                $monto = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
                $ventaExenta   += $monto;
                $ventasDetalladas[$nombreVendedor][$claveCategoria]['exenta'] += $monto;
                $ventasPorCategoriaGlobal[$claveCategoria]['exenta'] += $monto;
                $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                $ventaExentaFinal += $monto;
            }
        }
        // --- Caso: Exportaciones, DTE "11"
        elseif($factura["tipoDte"] == "11") {
            if($cliente["tipo_cliente"] == "01"){
                foreach ($productos as $producto) {
                    $productoLeido = ControladorProductos::ctrMostrarProductos("id", $producto["idProducto"], "no");
                    $claveCategoriaInfo = ControladorCategorias::ctrMostrarCategorias("id", $productoLeido["categoria_id"]);
                $claveCategoriaId = $claveCategoriaInfo["id"];
                $claveCategoriaNombre = $claveCategoriaInfo["nombre"];
                $claveCategoria = str_pad($claveCategoriaId, 5, "0", STR_PAD_LEFT) . "||" . $claveCategoriaNombre;

                    inicializaAgrupacion($ventasDetalladas, $nombreVendedor, $claveCategoria);
                    inicializaCategoriaGlobal($ventasPorCategoriaGlobal, $claveCategoria);

                    
                    if($productoLeido["exento_iva"] == "si"){
                        $monto = ((($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"])
                                    + $factura["seguro"] + $factura["flete"]);
                        $ventaExenta   += $monto;
                        $ventasDetalladas[$nombreVendedor][$claveCategoria]['exenta'] += $monto;
                        $ventasPorCategoriaGlobal[$claveCategoria]['exenta'] += $monto;
                        $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                        $ventaExentaFinal += $monto;
                    } else {
                        $monto = ((($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"])
                                    + $factura["seguro"] + $factura["flete"]);
                        $ventaGravada  += $monto;
                        $ventasDetalladas[$nombreVendedor][$claveCategoria]['gravada'] += $monto;
                        $ventasPorCategoriaGlobal[$claveCategoria]['gravada'] += $monto;
                        $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                        $ventaGravadaFinal += $monto;
                    }
                }
            }
            elseif($cliente["tipo_cliente"] == "02"){
                foreach ($productos as $producto) {
                    $productoLeido = ControladorProductos::ctrMostrarProductos("id", $producto["idProducto"], "no");
                    $claveCategoriaInfo = ControladorCategorias::ctrMostrarCategorias("id", $productoLeido["categoria_id"]);
                $claveCategoriaId = $claveCategoriaInfo["id"];
                $claveCategoriaNombre = $claveCategoriaInfo["nombre"];
                $claveCategoria = str_pad($claveCategoriaId, 5, "0", STR_PAD_LEFT) . "||" . $claveCategoriaNombre;

                    inicializaAgrupacion($ventasDetalladas, $nombreVendedor, $claveCategoria);
                    inicializaCategoriaGlobal($ventasPorCategoriaGlobal, $claveCategoria);

                    
                    if($productoLeido["exento_iva"] == "si"){
                        $monto = ((($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"])
                                    + $factura["seguro"] + $factura["flete"]);
                        $ventaExenta   += $monto;
                        $ventasDetalladas[$nombreVendedor][$claveCategoria]['exenta'] += $monto;
                        $ventasPorCategoriaGlobal[$claveCategoria]['exenta'] += $monto;
                        $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                        $ventaExentaFinal += $monto;
                    } else {
                        $monto = ((($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"])
                                    + $factura["seguro"] + $factura["flete"]);
                        $ventaNoSujeta += $monto;
                        $ventasDetalladas[$nombreVendedor][$claveCategoria]['no_sujeta'] += $monto;
                        $ventasPorCategoriaGlobal[$claveCategoria]['no_sujeta'] += $monto;
                        $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                    }
                }
            }
            elseif($cliente["tipo_cliente"] == "03"){
                foreach ($productos as $producto) {
                    $productoLeido = ControladorProductos::ctrMostrarProductos("id", $producto["idProducto"], "no");
                    $claveCategoriaInfo = ControladorCategorias::ctrMostrarCategorias("id", $productoLeido["categoria_id"]);
                $claveCategoriaId = $claveCategoriaInfo["id"];
                $claveCategoriaNombre = $claveCategoriaInfo["nombre"];
                $claveCategoria = str_pad($claveCategoriaId, 5, "0", STR_PAD_LEFT) . "||" . $claveCategoriaNombre;

                    inicializaAgrupacion($ventasDetalladas, $nombreVendedor, $claveCategoria);
                    inicializaCategoriaGlobal($ventasPorCategoriaGlobal, $claveCategoria);

                    
                    $monto = ((($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"])
                                + $factura["seguro"] + $factura["flete"]);
                    $ventaExenta   += $monto;
                    $ventasDetalladas[$nombreVendedor][$claveCategoria]['exenta'] += $monto;
                    $ventasPorCategoriaGlobal[$claveCategoria]['exenta'] += $monto;
                    $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                    $ventaExentaFinal += $monto;
                }
            }
        }
        // --- Caso: DTE "03"
        elseif($factura["tipoDte"] == "03") {
            if($cliente["tipo_cliente"] == "01"){
                foreach ($productos as $producto) {
                    $productoLeido = ControladorProductos::ctrMostrarProductos("id", $producto["idProducto"], "no");
                    $claveCategoriaInfo = ControladorCategorias::ctrMostrarCategorias("id", $productoLeido["categoria_id"]);
                $claveCategoriaId = $claveCategoriaInfo["id"];
                $claveCategoriaNombre = $claveCategoriaInfo["nombre"];
                $claveCategoria = str_pad($claveCategoriaId, 5, "0", STR_PAD_LEFT) . "||" . $claveCategoriaNombre;

                    inicializaAgrupacion($ventasDetalladas, $nombreVendedor, $claveCategoria);
                    inicializaCategoriaGlobal($ventasPorCategoriaGlobal, $claveCategoria);

                    
                    if($productoLeido["exento_iva"] == "si"){
                        $monto = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
                        $ventaExenta   += $monto;
                        $ventasDetalladas[$nombreVendedor][$claveCategoria]['exenta'] += $monto;
                        $ventasPorCategoriaGlobal[$claveCategoria]['exenta'] += $monto;
                        $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                        $ventaExentaCredito += $monto;
                    } else {
                        $monto = (($producto["precioConIva"] - $producto["descuentoConIva"]) * $producto["cantidad"]);
                        $ventaGravada  += $monto;
                        $ventasDetalladas[$nombreVendedor][$claveCategoria]['gravada'] += $monto;
                        $ventasPorCategoriaGlobal[$claveCategoria]['gravada'] += $monto;
                        $totalDescuentos += $producto["descuentoConIva"] * $producto["cantidad"];
                        $ventaGravadaCredito += $monto;
                    }
                }
            }
            elseif($cliente["tipo_cliente"] == "02"){
                foreach ($productos as $producto) {
                    $productoLeido = ControladorProductos::ctrMostrarProductos("id", $producto["idProducto"], "no");
                    $claveCategoriaInfo = ControladorCategorias::ctrMostrarCategorias("id", $productoLeido["categoria_id"]);
                $claveCategoriaId = $claveCategoriaInfo["id"];
                $claveCategoriaNombre = $claveCategoriaInfo["nombre"];
                $claveCategoria = str_pad($claveCategoriaId, 5, "0", STR_PAD_LEFT) . "||" . $claveCategoriaNombre;

                    inicializaAgrupacion($ventasDetalladas, $nombreVendedor, $claveCategoria);
                    inicializaCategoriaGlobal($ventasPorCategoriaGlobal, $claveCategoria);

                    
                    if($productoLeido["exento_iva"] == "si"){
                        $monto = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
                        $ventaExenta   += $monto;
                        $ventasDetalladas[$nombreVendedor][$claveCategoria]['exenta'] += $monto;
                        $ventasPorCategoriaGlobal[$claveCategoria]['exenta'] += $monto;
                        $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                        $ventaExentaCredito += $monto;
                    } else {
                        $monto = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
                        $ventaNoSujeta += $monto;
                        $ventasDetalladas[$nombreVendedor][$claveCategoria]['no_sujeta'] += $monto;
                        $ventasPorCategoriaGlobal[$claveCategoria]['no_sujeta'] += $monto;
                        $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                    }
                }
            }
            elseif($cliente["tipo_cliente"] == "03"){
                foreach ($productos as $producto) {
                    $productoLeido = ControladorProductos::ctrMostrarProductos("id", $producto["idProducto"], "no");
                    $claveCategoriaInfo = ControladorCategorias::ctrMostrarCategorias("id", $productoLeido["categoria_id"]);
                $claveCategoriaId = $claveCategoriaInfo["id"];
                $claveCategoriaNombre = $claveCategoriaInfo["nombre"];
                $claveCategoria = str_pad($claveCategoriaId, 5, "0", STR_PAD_LEFT) . "||" . $claveCategoriaNombre;

                    inicializaAgrupacion($ventasDetalladas, $nombreVendedor, $claveCategoria);
                    inicializaCategoriaGlobal($ventasPorCategoriaGlobal, $claveCategoria);

                    
                    $monto = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
                    $ventaExenta   += $monto;
                    $ventasDetalladas[$nombreVendedor][$claveCategoria]['exenta'] += $monto;
                    $ventasPorCategoriaGlobal[$claveCategoria]['exenta'] += $monto;
                    $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                    $ventaExentaCredito += $monto;
                }
            }
        }
        // --- Caso: DTE "14"
        elseif($factura["tipoDte"] == "14") {
            if($cliente["tipo_cliente"] == "00"){
                foreach ($productos as $producto) {
                    $productoLeido = ControladorProductos::ctrMostrarProductos("id", $producto["idProducto"], "no");
                    $claveCategoriaInfo = ControladorCategorias::ctrMostrarCategorias("id", $productoLeido["categoria_id"]);
                $claveCategoriaId = $claveCategoriaInfo["id"];
                $claveCategoriaNombre = $claveCategoriaInfo["nombre"];
                $claveCategoria = str_pad($claveCategoriaId, 5, "0", STR_PAD_LEFT) . "||" . $claveCategoriaNombre;

                    inicializaAgrupacion($ventasDetalladas, $nombreVendedor, $claveCategoria);
                    inicializaCategoriaGlobal($ventasPorCategoriaGlobal, $claveCategoria);

                    
                    if($productoLeido["exento_iva"] == "si"){
                        $monto = ($factura["totalSinIva"] - ($factura["totalSinIva"] * 0.1));
                        $ventaExenta   -= $monto;
                        $ventasDetalladas[$nombreVendedor][$claveCategoria]['exenta'] -= $monto;
                        $ventasPorCategoriaGlobal[$claveCategoria]['exenta'] -= $monto;
                        $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                        $ventaExentaFinal -= $monto;
                    } else {
                        $monto = ($factura["totalSinIva"] - ($factura["totalSinIva"] * 0.1));
                        $ventaGravada  -= $monto;
                        $ventasDetalladas[$nombreVendedor][$claveCategoria]['gravada'] -= $monto;
                        $ventasPorCategoriaGlobal[$claveCategoria]['gravada'] -= $monto;
                        $totalDescuentos += $producto["descuento"] * $producto["cantidad"];
                        $ventaGravadaFinal -= $monto;
                    }
                }
            }
        }
        
        // Acumulamos los totales de esta factura por vendedor
        if (!isset($ventasPorVendedor[$nombreVendedor])) {
            $ventasPorVendedor[$nombreVendedor] = array(
                'ventaExenta'   => 0.00,
                'ventaNoSujeta' => 0.00,
                'ventaGravada'  => 0.00
            );
        }
        $ventasPorVendedor[$nombreVendedor]['ventaExenta']   += $ventaExenta;
        $ventasPorVendedor[$nombreVendedor]['ventaNoSujeta'] += $ventaNoSujeta;
        $ventasPorVendedor[$nombreVendedor]['ventaGravada']  += $ventaGravada;
        
        // Actualizamos los totales globales
        $ventaExentaTotal   += $ventaExenta;
        $ventaNoSujetaTotal += $ventaNoSujeta;
        $ventaGravadaTotal  += $ventaGravada;
    }
}

// Calcular total general
$totalFGeneral = $ventaExentaTotal + $ventaNoSujetaTotal + $ventaGravadaTotal;

// Generar las filas para la tabla con los totales agrupados
$filasTabla = "";
ksort($ventasDetalladas); // ordena vendedores
foreach ($ventasDetalladas as $vendedor => $claveCategorias) {
    ksort($claveCategorias); // ordena las categorías por ID (porque están al inicio de la clave)
    // Encabezado del vendedor
    $filasTabla .= "<tr style='background-color:#dcdcdc;'><td colspan='4'><strong>" . htmlspecialchars($vendedor) . "</strong></td></tr>";
    // Recorremos las categorías de ese vendedor
    foreach ($claveCategorias as $claveCategoria => $tipos) {
        list(, $nombreCategoria) = explode("||", $claveCategoria);
        $filasTabla .= '<tr>
            <td style="padding-left:20px"><em>' . htmlspecialchars($nombreCategoria) . '</em></td>
            <td style="text-align:right;">$' . number_format($tipos["exenta"], 2) . '</td>
            <td style="text-align:right;">$' . number_format($tipos["no_sujeta"], 2) . '</td>
            <td style="text-align:right;">$' . number_format($tipos["gravada"], 2) . '</td>
        </tr>';
    }
    // Opcional: fila de total por vendedor, usando el arreglo de totales generales por vendedor
    $filasTabla .= '<tr style="background-color:#f0f0f0;">
        <td style="padding-left:10px;"><strong>Total</strong></td>
        <td style="text-align:right;"><strong>$' . number_format($ventasPorVendedor[$vendedor]['ventaExenta'], 2) . '</strong></td>
        <td style="text-align:right;"><strong>$' . number_format($ventasPorVendedor[$vendedor]['ventaNoSujeta'], 2) . '</strong></td>
        <td style="text-align:right;"><strong>$' . number_format($ventasPorVendedor[$vendedor]['ventaGravada'], 2) . '</strong></td>
    </tr>';
}
unset($claveCategorias);
// -------------------------
// Configuración de TCPDF para formato ticket
// -------------------------
$pdf = new TCPDF('P', 'mm', array($ancho, $largo), true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
// Reducir los márgenes, en especial el superior
$pdf->SetMargins(0, 0, 0, true);
$pdf->AddPage();

// Definir el contenido HTML con estilo simplificado
$html = '
<div style="font-family:dejavusanscondensed, helvetica, sans-serif; font-size:8px;">
    <!-- Encabezado -->
    <div style="text-align:center; margin-bottom:5px;">
        <h5 style="font-size:10px; margin:0;">Corte de Caja</h5>
    </div>
    <div style="text-align:center; margin-bottom:5px;">
        <span><strong>Fecha:</strong> ' . $corte["fecha"] . '</span><br>
        <span><strong>Autorizada:</strong> ' . $autorizacion . ' | <strong>Cuadrada:</strong> ' . $cuadrada . '</span>
    </div>
    <br><br><br>
    <!-- Tabla de totales por facturador, categoría y tipo de venta -->
    <table cellpadding="2" cellspacing="0" style="width:100%; font-size:6px;">
        <thead>
            <tr>
                <th style="width:30%;">Categoría / Vendedor</th>
                <th style="width:23%; text-align:right;">Venta Exenta</th>
                <th style="width:23%; text-align:right;">Venta No Sujeta</th>
                <th style="width:23%; text-align:right;">Venta Gravada</th>
            </tr>
        </thead>
        <tbody>
            ' . $filasTabla . '
        </tbody>
    </table>
    <br>';
    $html .= '<hr><strong>Totales por Categoría</strong><br>';

foreach ($ventasPorCategoriaGlobal as $claveCategoria => $valores) {
    $html .= '<table cellpadding="5" cellspacing="0" style="font-size:6px; line-height:1.2em; width:100%">';
    list(, $nombreCategoria) = explode("||", $claveCategoria);
    $html .= '<tr><td colspan="2"><strong>' . $nombreCategoria . '</strong></td></tr>';


    $total = 0.00;
    if ($valores['exenta'] > 0) {
        $html .= '<tr><td>Exenta</td><td align="right">$' . number_format($valores['exenta'], 2) . '</td></tr>';
        $total += $valores['exenta'];
    }
    if ($valores['no_sujeta'] > 0) {
        $html .= '<tr><td>No Sujeta</td><td align="right">$' . number_format($valores['no_sujeta'], 2) . '</td></tr>';
        $total += $valores['no_sujeta'];
    }
    if ($valores['gravada'] > 0) {
        $html .= '<tr><td>Gravada</td><td align="right">$' . number_format($valores['gravada'], 2) . '</td></tr>';
        $total += $valores['gravada'];
    }
    $html .= '<tr><td><strong>Total '.$claveCategoria.'</strong></td><td align="right"><strong>$'.number_format($total, 2). '</strong></td></tr>';
    $html .= '</table><br>';
}


    $html .= '
    <!-- Totales globales desglosados -->
    <div style="text-align:left; margin-top:1px; font-size:6px;">
        Total Venta No Sujeta: $' . number_format($ventaNoSujetaTotal, 2) . '<br><br>

        Total Venta Exenta Consumidor Final: $' . number_format($ventaExentaFinal, 2) . '<br><br>
        Total Venta Exenta Crédito Fiscal: $' . number_format($ventaExentaCredito, 2) . '<br><br>
        <strong>Total Venta Exenta: $' . number_format($ventaExentaTotal, 2) . '</strong><br><br>

        Total Venta Gravada Consumidor Final: $' . number_format($ventaGravadaFinal, 2) . '<br><br>
        Total Venta Gravada Crédito Fiscal: $' . number_format($ventaGravadaCredito, 2) . '<br><br>
        <strong>Total Venta Gravada: $' . number_format($ventaGravadaTotal, 2) . '</strong><br><br>

        <strong>Total descuentos efectuados: $' . number_format($totalDescuentos, 2) . '</strong><br><br>

        <strong>Total Efectivo: $' . number_format($totalFGeneral, 2) . '</strong>

        
    </div>
    <!-- Pie de ticket -->
    <div style="text-align:center; margin-top:10px; font-size:9px;"></div>
</div>';

// Escribir el contenido HTML en el PDF
$pdf->writeHTML($html, true, false, true, false, '');
// Salida del PDF: visualizar en el navegador
$pdf->Output('ticket_corte.pdf', 'I');
?>
