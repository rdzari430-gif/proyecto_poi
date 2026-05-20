<?php
$host = "localhost";
$user = "root"; // Usuario por defecto de XAMPP
$pass = "";     // Contraseña por defecto (vacía)
$db   = "poi";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Configurar para que acepte tildes y eñes
mysqli_set_charset($conn, "utf8mb4");
?>