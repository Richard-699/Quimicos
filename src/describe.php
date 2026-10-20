<?php
require 'App/Infrastructure/Database/Connection.php';
$db = (new App\Infrastructure\Database\Connection())->dbQuimicosHwi;
$stmt1 = $db->query("DESCRIBE gestion_ambiental_hwi_consumo_agua");
echo "consumo_agua:\n";
print_r($stmt1->fetchAll(PDO::FETCH_ASSOC));

$stmt2 = $db->query("DESCRIBE gestion_ambiental_hwi_tanques_abastecimiento_agua");
echo "tanques:\n";
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
