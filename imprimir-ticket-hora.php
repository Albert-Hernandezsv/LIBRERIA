<?php

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";
// Asegúrate de incluir la librería TCPDF
require_once('tcpdf_include.php');

// Configurar la zona horaria a El Salvador
date_default_timezone_set('America/El_Salvador');

$item = "id";
$valor = $_GET["idUsuario"];

$usuario = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

// Variables dinámicas
$userName = $usuario["nombre"];  // Reemplaza por la variable que contenga el nombre del usuario
$currentDateTime = date('d/m/Y H:i:s');  // Fecha y hora actual en El Salvador

// Crea una nueva instancia de TCPDF
$ancho   = "70";
$largo   = "350";
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array($ancho, $largo), true, 'UTF-8', false);

// Configuración del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Tu Empresa');
$pdf->SetTitle('Ticket de Atención');

// Opcional: Desactivar encabezado y pie de página predeterminados
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Establecer márgenes
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);

// Agregar una página
$pdf->AddPage();

// Contenido HTML del ticket
$html = '
<style>
    .ticket {
        text-align: center;
        font-family: sans-serif;
    }
    .ticket h2 {
        margin-bottom: 20px;
    }
    .ticket p {
        font-size: 14px;
        margin: 5px;
    }
    .firma {
        margin-top: 50px;
        text-align: left;
        font-size: 14px;
    }
</style>
<div class="ticket">
    <h2>Ticket de Atención</h2>
    <p><strong>Nombre:</strong> ' . $userName. '</p>
    <p><strong>Fecha y Hora:</strong> ' . $currentDateTime . '</p>
    <br><br>
    <div class="firma">
        <p>____________________________</p>
        <p>Firma</p>
    </div>
</div>
';

// Escribir el contenido HTML en el PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Salida del PDF al navegador
$pdf->Output('ticket.pdf', 'I');
?>
