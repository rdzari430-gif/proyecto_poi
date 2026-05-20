<?php
// Asegúrate de que este archivo se llame: mensajeBD.php
include('db.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$mi_id = $_SESSION['user_id'];
$sala_id = isset($_POST['sala_id']) ? intval($_POST['sala_id']) : 0;
$contenido = isset($_POST['contenido']) ? mysqli_real_escape_string($conn, $_POST['contenido']) : '';

if ($sala_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Sala no válida"]);
    exit;
}

// 1. Verificar sala
$sala_q = mysqli_query($conn, "SELECT tipo FROM salas_chat WHERE id = $sala_id");
if (!$sala_q || mysqli_num_rows($sala_q) == 0) {
    echo json_encode(["status" => "error", "message" => "La sala no existe"]);
    exit;
}
$sala_info = mysqli_fetch_assoc($sala_q);

// 2. Gestión de membresía grupal (Ajustado para no usar la columna 'id')
if ($sala_info['tipo'] === 'grupo') {
    // Usamos SELECT * para contar filas, ya que tu tabla no tiene un campo 'id'
    $check_membresia = mysqli_query($conn, "SELECT * FROM usuarios_salas WHERE sala_id = $sala_id AND usuario_id = $mi_id");
    
    if (mysqli_num_rows($check_membresia) == 0) {
        $insert_membresia = mysqli_query($conn, "INSERT INTO usuarios_salas (sala_id, usuario_id) VALUES ($sala_id, $mi_id)");
        if (!$insert_membresia) {
            echo json_encode(["status" => "error", "message" => "Error al unirse al grupo: " . mysqli_error($conn)]);
            exit;
        }
    }
}

// 3. Procesar Multimedia
$tipo_mensaje = 'texto';
$archivo_url = "NULL"; 

if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $file_tmp  = $_FILES['archivo']['tmp_name'];
    $file_name = $_FILES['archivo']['name'];
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    $extensiones_img = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extensiones_vid = ['mp4', 'webm', 'ogg', 'mov'];
    
    if (in_array($file_ext, $extensiones_img)) {
        $tipo_mensaje = 'imagen';
    } elseif (in_array($file_ext, $extensiones_vid)) {
        $tipo_mensaje = 'video';
    } else {
        echo json_encode(["status" => "error", "message" => "Formato no soportado"]);
        exit;
    }
    
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }
    
    $nuevo_nombre = uniqid('media_', true) . '.' . $file_ext;
    $destino = 'uploads/' . $nuevo_nombre;
    
    if (move_uploaded_file($file_tmp, $destino)) {
        $archivo_url = "'$destino'";
        if (empty($contenido)) {
            $contenido = ($tipo_mensaje === 'imagen') ? '[Imagen]' : '[Video]';
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Fallo al mover el archivo"]);
        exit;
    }
}

// 4. Inserción
$query = "INSERT INTO mensaje (sala_id, usuario_id, contenido, tipo_mensaje, archivo_url) 
          VALUES ($sala_id, $mi_id, '$contenido', '$tipo_mensaje', $archivo_url)";

if (mysqli_query($conn, $query)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error DB: " . mysqli_error($conn)]);
}
?>