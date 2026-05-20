<?php
include('db.php');
session_start();

$busqueda = mysqli_real_escape_string($conn, $_GET['q']);
$mi_id = $_SESSION['user_id'];

// Buscamos usuarios que coincidan con el nombre, excluyéndome a mí mismo
$query = "SELECT id, username, foto_perfil FROM usuarios 
          WHERE username LIKE '%$busqueda%' AND id != '$mi_id' LIMIT 5";

$res = mysqli_query($conn, $query);
$usuarios = mysqli_fetch_all($res, MYSQLI_ASSOC);

echo json_encode($usuarios);
?>