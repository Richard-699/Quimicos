<?php
// *****************************************************************
// INICIALIZACIÓN DE VISTA PROTEGIDA
// Este archivo carga Composer, Inicia la Sesión, Valida la Sesión 
// y define las variables de administrador requeridas por el header.
// *****************************************************************
include '../../Handler/auth/session_init.php'; 
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haceb Whirlpool Industrial · Sistema de Gestión Ambiental</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../../../../public/css/quimicos/index.css?v=<?= time() ?>" rel="stylesheet">
</head>

<body>
    <?php include '../shared/header.php'; ?>

    <?php
    $nombreAdmin = 'Administrador';
    if (isset($_SESSION['administrador'])) {
        $admin = $_SESSION['administrador'];
        if (is_object($admin)) {
            $nombreAdmin = trim(($admin->nombre_administrador ?? '') . ' ' . ($admin->apellidos_administrador ?? '')) ?: 'Administrador';
        } elseif (is_array($admin)) {
            $nombreAdmin = trim(($admin['nombre_administrador'] ?? '') . ' ' . ($admin['apellidos_administrador'] ?? '')) ?: 'Administrador';
        }
    }

    $permisos = $_SESSION['permisosAdministradores'] ?? [];
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

    // Accesos directos basados en los módulos y permisos del sistema Químicos HWI
    $todosAccesos = [
        [
            'id' => 'quimicos',
            'titulo' => 'Químicos',
            'url' => 'quimicos.php',
            'icono' => 'fa-solid fa-flask',
            'icono_class' => 'icon-celula',
            'desc' => 'Catálogo maestro de insumos químicos, stock disponible, hojas de seguridad SDS y fichas técnicas.',
            'activo' => $tienePermiso('Gestión Químicos'),
        ],
        [
            'id' => 'solicitudes',
            'titulo' => 'Solicitudes',
            'url' => 'solicitudes.php',
            'icono' => 'fa-solid fa-file-circle-exclamation',
            'icono_class' => 'icon-horas',
            'desc' => 'Trazabilidad de requerimientos de insumos químicos por célula productiva y gestión de despachos.',
            'activo' => $tienePermiso('Gestión Químicos'),
        ],
        [
            'id' => 'informe-quimicos',
            'titulo' => 'Informe Químicos',
            'url' => 'informe.php',
            'icono' => 'fa-solid fa-file-excel',
            'icono_class' => 'icon-rotacion',
            'desc' => 'Dashboard consolidado, reporte de consumos por área y exportación de datos a Microsoft Excel.',
            'activo' => $tienePermiso('Gestión Químicos'),
        ],
        [
            'id' => 'proyecciones-ml',
            'titulo' => 'Proyecciones y ML',
            'url' => 'proyeccionesConsumosPrecios.php',
            'icono' => 'fa-solid fa-chart-line',
            'icono_class' => 'icon-ilu',
            'desc' => 'Analítica predictiva de demanda (Holt-Winters), histórico de precios y Asistente Virtual Inteligente.',
            'activo' => $tienePermiso('Gestión Químicos'),
        ],
        [
            'id' => 'registro-consumos-agua',
            'titulo' => 'Consumo de Agua',
            'url' => 'consumosAgua.php',
            'icono' => 'fa-solid fa-faucet-drip',
            'icono_class' => 'icon-visualizar',
            'desc' => 'Registro diario de aforos de agua con control anti-duplicados por fecha, célula y tanque.',
            'activo' => $tienePermiso('Registrar Consumos Agua'),
        ],
        [
            'id' => 'indicador-agua',
            'titulo' => 'Indicador de Agua',
            'url' => 'indicadorConsumosAgua.php',
            'icono' => 'fa-solid fa-chart-pie',
            'icono_class' => 'icon-formacion',
            'desc' => 'Dashboard gerencial con KPIs de consumo hídrico frente a metas corporativas mensuales.',
            'activo' => $tienePermiso('Visualizar Indicador Consumos Agua'),
        ],
        [
            'id' => 'administradores',
            'titulo' => 'Administradores',
            'url' => 'administradores.php',
            'icono' => 'fa-solid fa-users',
            'icono_class' => 'icon-personal',
            'desc' => 'Administración de usuarios, activación de cuentas y asignación de la matriz de permisos.',
            'activo' => $tienePermiso('Gestión Administradores'),
        ],
    ];

    $accesosPermitidos = array_values(array_filter($todosAccesos, fn($a) => !empty($a['activo'])));
    ?>

    <main class="hwi-home-clean">
        <div class="hwi-clean-inner">

            <!-- Banner de Bienvenida -->
            <section class="hwi-welcome-card">
                <div class="hwi-welcome-badge">
                    <i class="fa-solid fa-leaf text-success"></i>
                    <span>Haceb Whirlpool Industrial · Sistema de Gestión Ambiental</span>
                </div>
                <h1 class="hwi-welcome-title">
                    Bienvenido, <?= htmlspecialchars($nombreAdmin) ?>
                </h1>
                <p class="hwi-welcome-desc">
                    Selecciona un módulo para comenzar la gestión de productos químicos y recursos hídricos según los permisos asignados a tu perfil.
                </p>
            </section>

            <!-- Cuadrícula con los Accesos Permitidos -->
            <section class="hwi-modules-grid">
                <?php if (!empty($accesosPermitidos)): ?>
                    <?php foreach ($accesosPermitidos as $modulo): ?>
                        <a href="<?= htmlspecialchars($modulo['url']) ?>" class="hwi-module-card">
                            <div>
                                <div class="hwi-module-header">
                                    <div class="hwi-module-icon <?= htmlspecialchars($modulo['icono_class']) ?>">
                                        <i class="<?= htmlspecialchars($modulo['icono']) ?>"></i>
                                    </div>
                                    <div>
                                        <h2 class="hwi-module-title"><?= htmlspecialchars($modulo['titulo']) ?></h2>
                                    </div>
                                </div>
                                <p class="hwi-module-desc">
                                    <?= htmlspecialchars($modulo['desc']) ?>
                                </p>
                            </div>
                            <div class="hwi-module-footer">
                                <span>Ingresar</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-light border text-center py-4">
                            <i class="fa-solid fa-lock text-muted fs-3 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark">Sin accesos directos disponibles</h6>
                            <p class="text-muted small mb-0">No cuentas con permisos para los módulos principales. Contacta a un administrador para más información.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

        </div>
    </main>

    <?php include '../shared/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
