<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once '../../Handler/auth/validateSesionHandler.php';

$permisos = $_SESSION['permisosAdministradores'] ?? [];

$nombreCompletoAdministrador = 'Administrador';
if (isset($_SESSION['administrador'])) {
    $adminObj = $_SESSION['administrador'];
    if (is_object($adminObj)) {
        $nombreCompletoAdministrador = trim(($adminObj->nombre_administrador ?? '') . ' ' . ($adminObj->apellidos_administrador ?? '')) ?: 'Administrador';
    } elseif (is_array($adminObj)) {
        $nombreCompletoAdministrador = trim(($adminObj['nombre_administrador'] ?? '') . ' ' . ($adminObj['apellidos_administrador'] ?? '')) ?: 'Administrador';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestión Ambiental HWI</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link rel="stylesheet" href="../../../../../public/css/utils/libs/libs.css">
    <link rel="stylesheet" href="../../../../../public/css/shared/estilos_header.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
</head>

<body>
    <?php include __DIR__ . '/../../../Shared/Util/spinner.php'; ?>

    <div id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <a class="fondo-img" href="index.php" title="Haceb Whirlpool Industrial">
                <img src="../../../../../public/img/LogoBlanco.png" class="img-logo" alt="HWI Logo">
            </a>
        </div>

        <div class="sidebar-nav">
            <?php
            $paginaActual = basename($_SERVER['PHP_SELF']);
            $queryString = $_SERVER['QUERY_STRING'] ?? '';
            $urlActual = $paginaActual . ($queryString ? ('?' . $queryString) : '');

            $permisosTipos = !empty($permisos) ? array_column($permisos, 'tipo_permiso') : [];
            $tienePermiso = function ($permiso) use ($permisosTipos) {
                if (is_array($permiso)) {
                    foreach ($permiso as $p) {
                        if (in_array($p, $permisosTipos, true)) return true;
                    }
                    return false;
                }
                return in_array($permiso, $permisosTipos, true);
            };

            // Menú estructurado por categorías con diseño acordeón
            $menuGrupos = [
                [
                    'id' => 'grp-quimicos',
                    'titulo' => 'Químicos',
                    'icono' => 'fa-solid fa-flask-vial',
                    'items' => array_values(array_filter([
                        $tienePermiso('Gestión Químicos') ? [
                            'titulo' => 'Químicos',
                            'url' => 'quimicos.php',
                            'icono' => 'fa-solid fa-flask'
                        ] : null,
                        $tienePermiso('Gestión Químicos') ? [
                            'titulo' => 'Solicitudes',
                            'url' => 'solicitudes.php',
                            'icono' => 'fa-solid fa-file-circle-exclamation'
                        ] : null,
                        $tienePermiso('Gestión Químicos') ? [
                            'titulo' => 'Informe Químicos',
                            'url' => 'informe.php',
                            'icono' => 'fa-solid fa-file'
                        ] : null,
                        $tienePermiso('Gestión Químicos') ? [
                            'titulo' => 'Proyecciones y ML',
                            'url' => 'proyeccionesConsumosPrecios.php',
                            'icono' => 'fa-solid fa-chart-line'
                        ] : null,
                    ]))
                ],
                [
                    'id' => 'grp-agua',
                    'titulo' => 'Consumo de Agua',
                    'icono' => 'fa-solid fa-droplet',
                    'items' => array_values(array_filter([
                        $tienePermiso('Registrar Consumos Agua') ? [
                            'titulo' => 'Registro Consumo',
                            'url' => 'consumosAgua.php',
                            'icono' => 'fa-solid fa-faucet-drip'
                        ] : null,
                        $tienePermiso('Visualizar Indicador Consumos Agua') ? [
                            'titulo' => 'Indicador de Agua',
                            'url' => 'indicadorConsumosAgua.php',
                            'icono' => 'fa-solid fa-chart-pie'
                        ] : null,
                    ]))
                ],
                [
                    'id' => 'grp-admin',
                    'titulo' => 'Administración',
                    'icono' => 'fa-solid fa-user-shield',
                    'items' => array_values(array_filter([
                        $tienePermiso('Gestión Administradores') ? [
                            'titulo' => 'Administradores',
                            'url' => 'administradores.php',
                            'icono' => 'fa-solid fa-users'
                        ] : null,
                    ]))
                ]
            ];
            ?>

            <!-- Inicio -->
            <a href="index.php" class="nav-single-link hov nav-link <?= ($paginaActual === 'index.php') ? 'active' : '' ?>">
                <i class="fas fa-home"></i> <span>Inicio</span>
            </a>

            <hr class="sidebar-divider">

            <!-- Módulos Colapsables -->
            <?php foreach ($menuGrupos as $grupo): ?>
                <?php if (!empty($grupo['items'])): ?>
                    <?php
                    $isGroupActive = false;
                    foreach ($grupo['items'] as $item) {
                        $itemUrl = $item['url'];
                        if (strpos($itemUrl, '?') !== false) {
                            if ($urlActual === $itemUrl) {
                                $isGroupActive = true;
                                break;
                            }
                        } else {
                            if ($paginaActual === $itemUrl) {
                                $isGroupActive = true;
                                break;
                            }
                        }
                    }
                    ?>
                    <div class="nav-group <?= $isGroupActive ? 'open' : '' ?>" id="<?= $grupo['id'] ?>">
                        <button type="button" class="nav-group-toggle" title="<?= htmlspecialchars($grupo['titulo']) ?>">
                            <span class="nav-group-title">
                                <i class="<?= $grupo['icono'] ?>"></i>
                                <span><?= htmlspecialchars($grupo['titulo']) ?></span>
                            </span>
                            <i class="fa-solid fa-chevron-right menu-arrow"></i>
                        </button>
                        <div class="nav-group-content">
                            <?php foreach ($grupo['items'] as $item): ?>
                                <?php
                                $itemUrl = $item['url'];
                                if (strpos($itemUrl, '?') !== false) {
                                    $isActive = ($urlActual === $itemUrl);
                                } else {
                                    $isActive = ($paginaActual === $itemUrl);
                                }
                                ?>
                                <a href="<?= $itemUrl ?>" class="hov nav-link nav-sublink <?= $isActive ? 'active' : '' ?>">
                                    <i class="<?= $item['icono'] ?>"></i>
                                    <span><?= htmlspecialchars($item['titulo']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="sidebar-footer">
            <a href="#" class="hov" id="BtnCerrarSesion"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
        </div>
    </div>

    <div class="content">
        <nav class="navbar navbar-expand-lg navbar-light px-3">
            <button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()" type="button">
                <i class="fa-solid fa-bars"></i>
                <span>Menú</span>
            </button>
            <div class="ms-auto d-flex align-items-center">
                <span class='nombre-celula'><?= htmlspecialchars($nombreCompletoAdministrador) ?></span>
                <a href="#" id="BtnCerrarSesionMenu" class="text-dark icon-logout"><i class="fas fa-sign-out-alt fa-lg"></i></a>
            </div>
        </nav>

        <div class="container mt-4">
