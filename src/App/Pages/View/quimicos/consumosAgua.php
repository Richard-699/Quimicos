<?php
include '../../Handler/auth/session_init.php'; 

$id_celula = $_SESSION['administrador']->id_celula_consumo_agua ?? null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Consumos de Agua</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../../../../public/css/quimicos/quimicos.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/libs/libs.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
    <link rel="stylesheet" href="../../../../../public/css/dataTable/dataTable.css">
    <link rel="stylesheet" href="../../../../../public/css/quimicos/consumosAgua.css">

    <?php include('../../../Shared/Util/spinner.php'); ?>
</head>

<body>
    <?php include '../shared/header.php' ?>

    <div class="container-fluid px-2 py-3">
        <div class="table-container table-responsive">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-2">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-faucet-drip me-2 fs-4"></i>
                    <h5 class="m-0 fw-semibold text-dark" id="titulo_consumo">Consumos de Agua</h5>
                </div>
                <input type="hidden" id="id_celula_hidden" value="<?= htmlspecialchars($id_celula) ?>">
                <?php if ($id_celula): ?>
                <button class="btn btn-primary btn-sm" id="btnRegistrarConsumo">
                    <i class="fa fa-plus me-1"></i> Registrar Consumo
                </button>
                <?php endif; ?>
            </div>

            <table id="tabla-consumos-agua" class="table table-striped table-bordered table-sm dt-responsive nowrap">
                <thead class="table-light">
                    <tr>
                        <th class="col-w25">Última Fecha Registrada</th>
                        <th class="col-w25">Tanque</th>
                        <th class="col-w25">Consumo Inicial</th>
                        <th class="col-w25">Consumo Final</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <?php include '../shared/footer.php'; ?>

    <!-- Scripts en orden -->
    <script src="../../../../../public/js/utils/libs/jquery.js"></script>
    <script src="../../../../../public/js/utils/libs/bootstrap.js"></script>
    <script src="../../../../../public/js/utils/libs/datatables.js"></script>
    <script src="../../../../../public/js/utils/libs/fancybox.js"></script>
    <script src="../../../../../public/js/utils/libs/notification.js"></script>

    <!-- Scripts funcionalidades -->
    <script src="../../../../../public/js/utils/spinner.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/quimicos/consumosAgua.js?v=<?= time() ?>"></script>
</body>

</html>