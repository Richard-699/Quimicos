<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once '../../Handler/auth/validateSesionHandler.php';
include('../../../Shared/Util/spinner.php');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Químicos</title>
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
            <a class="hov" href="javascript:void(0);" onclick="Quimicos();"><i class="fa-solid fa-flask"></i> Químicos</a>
            <hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />
            <a class="hov" href="javascript:void(0);" onclick="Solicitudes();"><i class="fa-solid fa-file-circle-exclamation"></i> Solicitudes</a>
            <hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />
            <a class="hov" href="javascript:void(0);" onclick="Inventario();"><i class="fa-solid fa-warehouse"></i> Inventario</a>
            <hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />
            <a class="hov" href="javascript:void(0);" onclick="Administradores();"><i class="fa-solid fa-users"></i> Administradores</a>
            <hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />
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