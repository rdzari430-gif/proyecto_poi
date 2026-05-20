<?php
include('db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'register') {
        $user = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass_plain = $_POST['password'];
        
        // Cifrado de contraseña
        $pass_hashed = password_hash($pass_plain, PASSWORD_BCRYPT);

        // Verificar si el correo ya existe
        $checkEmail = mysqli_query($conn, "SELECT id FROM usuarios WHERE email = '$email'");
        if (mysqli_num_rows($checkEmail) > 0) {
            $_SESSION['error'] = "El correo ya está registrado.";
            header("Location: registro.php");
            exit();
        }

        $sql = "INSERT INTO usuarios (username, email, password_hash) VALUES ('$user', '$email', '$pass_hashed')";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Error en el servidor al registrar.";
            header("Location: registro.php");
            exit();
        }
    } 
    
    elseif ($action == 'login') {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass_input = $_POST['password'];

        $res = mysqli_query($conn, "SELECT * FROM usuarios WHERE email = '$email'");
        
        if ($user_data = mysqli_fetch_assoc($res)) {
            // Verificación correcta del hash
            if (password_verify($pass_input, $user_data['password_hash'])) {
                $_SESSION['user_id'] = $user_data['id'];
                $_SESSION['username'] = $user_data['username'];
                
                // Guardamos la foto en la memoria de la sesión (o la default si no tiene)
                $_SESSION['foto_perfil'] = !empty($user_data['foto_perfil']) ? $user_data['foto_perfil'] : 'img/5.jpg';

                header("Location: index.php");
                exit();
            } else {
                $_SESSION['error'] = "Contraseña incorrecta.";
                header("Location: registro.php");
                exit();
            }
        } else {
            // Faltaba cerrar este bloque correctamente
            $_SESSION['error'] = "El usuario no existe.";
            header("Location: registro.php");
            exit();
        }
    }
}
?>