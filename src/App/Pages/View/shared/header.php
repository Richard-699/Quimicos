<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once '../../Handler/auth/validateSesionHandler.php';
include('../../../Shared/Util/spinner.php');

if (isset($_SESSION['administrador'])) {
    $administrador = $_SESSION['administrador'];
    $permisosAdministradores = $administrador->permisosAdministradoresDTO;
    $permisos = $administrador->permisosDTO;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inventario</title>
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

        foreach ($permisosAdministradores as $permisoAdministrador) {
            $id_permiso_permisos = $permisoAdministrador->id_permiso_permisos;
            foreach ($permisos as $permiso) {
                if ($permiso->id_permiso == $id_permiso_permisos) {
                    if ($permiso->tipo_permiso == 'Gestión Interna Inventario') {
                        echo '<a class="hov" href="javascript:void(0);" onclick="Almacen();"><i class="fa-solid fa-warehouse"></i> Almacenes</a>';
                        echo '<hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        echo '<a class="hov" href="javascript:void(0);" onclick="Localizaciones();"><i class="fa-solid fa-location-dot"></i> Localizaciones</a>';
                        echo '<hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        echo '<a class="hov" href="javascript:void(0);" onclick="Grupos();"><i class="fa-solid fa-layer-group"></i> Grupos</a>';
                        echo '<hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        echo '<a class="hov" href="javascript:void(0);" onclick="PartNumbers();"><i class="fa-solid fa-dolly"></i> Part Numbers</a>';
                        echo '<hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        echo '<a class="hov" href="javascript:void(0);" onclick="Cronograma();"><i class="fa-solid fa-calendar-check"></i> Cronograma</a>';
                        echo '<hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        echo '<a class="hov" href="javascript:void(0);" onclick="Exactitud();"><i class="fa-solid fa-crosshairs"></i> Exactitud</a>';
                        echo '<hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        break;
                    }
                    if ($permiso->tipo_permiso == 'Gestionar Administradores') {
                        echo '<a class="hov" href="javascript:void(0);" onclick="GestionarAdministradores();"><i class="fa-solid fa-users-gear"></i> Gestionar Administradores</a>';
                        echo '<hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        break;
                    }
                    if ($permiso->tipo_permiso == 'Aprobación y Ajuste Inventario') {
                        echo '<a class="hov" href="javascript:void(0);" onclick="Aprobacion();"><i class="fa-regular fa-circle-check"></i> Aprobación Inventario</a>';
                        echo '<hr style="width: 93%; margin-left: 4%; color: white; margin-top: -1px; margin-bottom: -1px" />';
                        break;
                    }
                }
            }
        }
        ?>

        <a href="javascript:void(0);" class="hov" id="BtnCerrarSesion"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </div>


    <div class="content">
        <nav class="navbar navbar-expand-lg navbar-light px-3">
            <button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()">☰ Menú</button>
            <div class="ms-auto d-flex align-items-center">
                <span class='nombre-celula'>Administrador Master</span>
                <a href="javascript:void(0);" id="BtnCerrarSesionMenu" class="text-dark icon-logout"><i class="fas fa-sign-out-alt fa-lg"></i></a>
            </div>
        </nav>

        <div class="container mt-4">