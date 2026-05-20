<?php
session_start();
include('db.php');

// Verificamos que el usuario haya iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$biografia = mysqli_real_escape_string($conn, $_POST['bio'] ?? '');

// Consulta SQL base 
$sql_update = "UPDATE usuarios SET bio = '$biografia'";

// Verificamos si el usuario subió una imagen nueva y no hubo errores en la subida
if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
    
    // VERIFICACIÓN CLAVE: Nos aseguramos de que la carpeta uploads exista
    $directorio_destino = 'uploads/';
    if (!is_dir($directorio_destino)) {
        // Si no existe, la creamos automáticamente con permisos de escritura
        mkdir($directorio_destino, 0777, true);
    }

    $file_tmp = $_FILES['foto_perfil']['tmp_name'];
    $file_name = $_FILES['foto_perfil']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Extensiones permitidas
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($file_ext, $allowed)) {
        // Creamos un nombre único
        $nuevo_nombre = "perfil_" . $user_id . "_" . time() . "." . $file_ext;
        $destino = $directorio_destino . $nuevo_nombre;
        
        // Movemos el archivo a la carpeta uploads
        if (move_uploaded_file($file_tmp, $destino)) {
            // Solo si se movió con éxito, agregamos la foto a la actualización SQL
            $sql_update .= ", foto_perfil = '$destino'";
            
            // Actualizamos la sesión
            $_SESSION['foto_perfil'] = $destino;
        }
    }
}

// Cerramos la condición del usuario actual
$sql_update .= " WHERE id = $user_id";

// Ejecutamos la consulta
if (mysqli_query($conn, $sql_update)) {
    header("Location: perfil.php?status=success");
} else {
    header("Location: perfil.php?status=error");
}
exit;
?>