<?php
include '../../Handler/auth/session_init.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyecciones de Consumo y Precios</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../public/css/utils/libs/libs.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
    <link rel="stylesheet" href="../../../../../public/css/dataTable/dataTable.css"> 
    <link rel="stylesheet" href="../../../../../public/css/quimicos/proyeccionesConsumosPrecios.css">
    <!-- CSS Choices -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <?php include('../../../Shared/Util/spinner.php'); ?>
</head>
<body>
    <?php include '../shared/header.php' ?>

    <div class="container-fluid px-2 py-1">
        <div class="dashboard-embed-container">
            <!-- Carga de la aplicación Streamlit embebida -->
            <iframe 
                src="http://localhost:8501/?embed=true" 
                class="dashboard-iframe"
                title="Proyecciones de Consumo y Precios - HWI">
            </iframe>
        </div>
    </div>

    <?php include '../shared/footer.php'; ?>

    <!-- Scripts en orden -->
    <script src="../../../../../public/js/utils/libs/jquery.js"></script>
    <script src="../../../../../public/js/utils/libs/bootstrap.js"></script>
    <script src="../../../../../public/js/utils/libs/datatables.js"></script>
    <script src="../../../../../public/js/utils/libs/fancybox.js"></script>
    <script src="../../../../../public/js/utils/libs/notification.js"></script>
    <!-- JS Choices -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <!-- Scripts funcionalidades -->
    <script src="../../../../../public/js/utils/spinner.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script>
        window.addEventListener('message', function(event) {
            if (event.data && event.data.action === 'mostrarCarga') {
                if (typeof mostrarCarga === 'function') mostrarCarga();
            }
            if (event.data && event.data.action === 'ocultarCarga') {
                if (typeof ocultarCarga === 'function') ocultarCarga();
            }
        });
    </script>
</body>
</html>