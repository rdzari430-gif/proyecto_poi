<?php
include('db.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$mi_id = $_SESSION['user_id'];
$accion = $_GET['accion'] ?? '';

if ($accion === 'listar_chats') {
    $query = "
        (SELECT s.id, 
                (SELECT u.username FROM usuarios_salas us2 
                 JOIN usuarios u ON us2.usuario_id = u.id 
                 WHERE us2.sala_id = s.id AND us2.usuario_id != $mi_id LIMIT 1) as nombre, 
                s.tipo 
         FROM salas_chat s
         JOIN usuarios_salas us ON s.id = us.sala_id
         WHERE s.tipo = 'directo' AND us.usuario_id = $mi_id)
        UNION
        (SELECT id, nombre, tipo 
         FROM salas_chat 
         WHERE tipo = 'grupo')
        ORDER BY id DESC
    ";
    
    $res = mysqli_query($conn, $query);
    if (!$res) {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        exit;
    }
    
    $chats = mysqli_fetch_all($res, MYSQLI_ASSOC);
    echo json_encode($chats);
    exit;
}

if ($accion === 'listar_mensajes') {
    $sala_id = intval($_GET['sala_id'] ?? 0);
    if ($sala_id <= 0) {
        echo json_encode(["status" => "error", "message" => "Sala no válida"]);
        exit;
    }
    
    // Consulta limpia apuntando a la tabla 'mensaje' (singular)
    $query = "SELECT m.*, u.username, u.foto_perfil 
              FROM mensaje m
              JOIN usuarios u ON m.usuario_id = u.id
              WHERE m.sala_id = $sala_id
              ORDER BY m.id ASC";
              
    $res = mysqli_query($conn, $query);
    if (!$res) {
        echo json_encode(["status" => "error", "message" => "Error al traer mensajes: " . mysqli_error($conn)]);
        exit;
    }
    
    $mensajes = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $row['is_mine'] = ($row['usuario_id'] == $mi_id);
        $mensajes[] = $row;
    }
    
    echo json_encode($mensajes);
    exit;
}
?>