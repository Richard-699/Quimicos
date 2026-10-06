<?php
$permisos = json_decode($_GET['permisos'], true);
$action = $_GET['action'] ?? '';
$id_administrador = $_GET['id_administrador'] ?? null;
$celulas = isset($_GET['celulas']) ? json_decode($_GET['celulas'], true) : [];
$id_celula_consumo_agua = $_GET['id_celula_consumo_agua'] ?? '';
$idsPermisosSeleccionados = [];

if ($action == 'update') {
    $permisosSelected = [];

    if (isset($_GET['permisosSelected'])) {
        $permisosSelected = json_decode($_GET['permisosSelected'], true);

        foreach ($permisosSelected as $permiso) {
            if (isset($permiso['id_permiso_permisos'])) {
                $idsPermisosSeleccionados[] = $permiso['id_permiso_permisos'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../img/LogoBlanco.png">
    <link href="../../../../../public/css/quimicos/administradorPermisos.css" rel="stylesheet">
    <link rel="shortcut icon" href="../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
</head>

<body class="p-4">

    <div class="contenido-aprobacion-admin">
        <h5 class="mb-4"><i class="fas fa-users-gear"></i>
            <?= ($action == 'update') ? ' Editar Administrador' : ' Aprobar Administrador' ?>
        </h5>
        <form id="formUpdateAdministrador">
            <input type="hidden" name="action" id="action" value="<?= htmlspecialchars($action) ?>">
            <input type="hidden" name="id_administrador" id="id_administrador" value="<?= htmlspecialchars($id_administrador) ?>">
            <div class="col-md-12 mt-4">
                <p>Seleccione los permisos que tendrá este administrador: *</p>
                <?php if ($action == 'approve') { ?>

                    <select id="permisos_administradores" class="form-select" multiple style="width: 100%" name="permisos_administradores[]">
                        <?php foreach ($permisos as $p): ?>
                            <option value="<?= $p['id_permiso'] ?>"><?= $p['tipo_permiso'] ?></option>
                        <?php endforeach; ?>
                    </select>

                <?php } else { ?>

                    <select id="permisos_administradores" class="form-select" multiple style="width: 100%" name="permisos_administradores[]">
                        <?php foreach ($permisos as $p): ?>
                            <option value="<?= $p['id_permiso'] ?>" <?= in_array($p['id_permiso'], $idsPermisosSeleccionados) ? 'selected' : '' ?>>
                                <?= $p['tipo_permiso'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                <?php } ?>
            </div>
            
            <div class="col-md-12 mt-4" id="contenedor_celula_agua" style="display: none;">
                <p>Seleccione la célula para Consumo de Agua: *</p>
                <select id="id_celula_consumo_agua" class="form-select" name="id_celula_consumo_agua" style="width: 100%">
                    <option value="">Seleccione una célula</option>
                    <?php foreach ($celulas as $c): ?>
                        <option value="<?= $c['id_celulas_areas'] ?>" <?= ($id_celula_consumo_agua == $c['id_celulas_areas']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre_celula']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex justify-content-end mb-4 mt-4">
                <button type="submit" class="btn btn-success" id="btn-aprobar-editar">
                    <?= ($action == 'update') ? 'Actualizar' : 'Aprobar' ?>
                </button>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/quimicos/administradorPermisos.js"></script>
</body>

</html>