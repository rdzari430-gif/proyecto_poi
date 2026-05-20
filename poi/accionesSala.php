<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$mi_id = $_SESSION['user_id'];
$tipo = $_POST['tipo'] ?? 'directo';
$nombre_input = $_POST['nombre'] ?? '';

if (empty($nombre_input)) {
    echo json_encode(["status" => "error"]);
    exit;
}

if ($tipo === 'directo') {
    $target_username = mysqli_real_escape_string($conn, $nombre_input);
    $query = mysqli_query($conn, "SELECT id FROM usuarios WHERE username = '$target_username'");
    if ($row = mysqli_fetch_assoc($query)) {
        $target_id = $row['id'];
        
        mysqli_query($conn, "INSERT INTO salas_chat (nombre, tipo) VALUES ('$target_username', 'directo')");
        $sala_id = mysqli_insert_id($conn);

        mysqli_query($conn, "INSERT INTO usuarios_salas (sala_id, usuario_id) VALUES ($sala_id, $mi_id)");
        mysqli_query($conn, "INSERT INTO usuarios_salas (sala_id, usuario_id) VALUES ($sala_id, $target_id)");

        echo json_encode(["status" => "success", "id" => $sala_id]);
    } else {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    }
} else {
    $nombre_grupo = mysqli_real_escape_string($conn, $nombre_input);
    mysqli_query($conn, "INSERT INTO salas_chat (nombre, tipo) VALUES ('$nombre_grupo', 'grupo')");
    $sala_id = mysqli_insert_id($conn);

    mysqli_query($conn, "INSERT INTO usuarios_salas (sala_id, usuario_id) VALUES ($sala_id, $mi_id)");

    echo json_encode(["status" => "success", "id" => $sala_id]);
}
?>