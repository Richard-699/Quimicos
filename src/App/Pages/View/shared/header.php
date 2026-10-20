<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once '../../Handler/auth/validateSesionHandler.php';
include('../../../Shared/Util/spinner.php');

$permisos = $_SESSION['permisosAdministradores'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestión Ambiental</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link rel="stylesheet" href="../../../../../public/css/utils/libs/libs.css">
    <link rel="stylesheet" href="../../../../../public/css/shared/estilos_header.css" />
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
</head>

<body>

    <div id="sidebar" class="sidebar">
        <a class="fondo-img" href="index.php"><img src="../../../../../public/img/LogoBlanco.png" class="img-logo"></a>
        <a href="javascript:void(0);" onclick="Inicio();" class="mt-3 hov"><i class="fas fa-home"></i> Inicio</a>
        <hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />
        <?php
            foreach ($permisos as $permiso) {
                switch ($permiso['tipo_permiso']) {

                    case "Gestión Químicos":
                        echo '<a class="hov" href="quimicos.php"">
                                <i class="fa-solid fa-flask"></i> Químicos</a>
                                <hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        echo '<a class="hov" href="solicitudes.php">
                                <i class="fa-solid fa-file-circle-exclamation"></i> Solicitudes</a>
                                <hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        echo '<a class="hov" href="informe.php">
                                <i class="fa-solid fa-file"></i> Informe Químicos</a>
                                <hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        echo '<a class="hov" href="proyeccionesConsumosPrecios.php">
                                <i class="fa-solid fa-chart-line"></i> Proyecciones Consumo y Precios</a>
                                <hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        break;
                    case "Gestión Administradores":
                        echo '<a class="hov" href="administradores.php">
                                <i class="fa-solid fa-users"></i> Administradores</a>
                                <hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        break;
                    case "Registrar Consumos Agua":
                        echo '<a class="hov" href="consumosAgua.php">
                                <i class="fa-solid fa-faucet-drip"></i> Consumo de Agua</a>
                                <hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        break;
                    case "Visualizar Indicador Consumos Agua":
                        echo '<a class="hov" href="indicadorConsumosAgua.php">
                                <i class="fa-solid fa-dashboard"></i> Indicador Consumo de Agua</a>
                                <hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        break;
                }
            }
        ?>
            
        <a href="javascript:void(0);" class="hov" id="BtnCerrarSesion"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </div>

    <div class="content">
        <nav class="navbar navbar-expand-lg navbar-light px-3">
            <button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()">☰ Menú</button>
            <div class="ms-auto d-flex align-items-center">
                <span class='nombre-celula'>Administrador</span>
                <a href="javascript:void(0);" id="BtnCerrarSesionMenu" class="text-dark icon-logout"><i class="fas fa-sign-out-alt fa-lg"></i></a>
            </div>
        </nav>

        <div class="container mt-4">