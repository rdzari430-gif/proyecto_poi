<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: registro.php");
    exit;
}

$logeado = true; 
$nombre_usuario = $_SESSION['username'] ?? 'Usuario';
$mi_foto = $_SESSION['foto_perfil'] ?? 'img/5.jpg';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mundial 2026 - México</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        
        <header class="topbar">
            <div class="topbar-left">
                <img src="img/6.jpg" alt="Logo Mundial" class="logo">
            </div>
            <div class="topbar-right">
                <?php if(!$logeado): ?>
                    <a href="registro.php" class="btn-login">Iniciar Sesión</a>
                <?php else: ?>
                    <span class="user-display">Hola, <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong></span>
                    <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
                <?php endif; ?>
                <img src="<?php echo htmlspecialchars($mi_foto); ?>" alt="Perfil" class="profile-pic">
            </div>
        </header>

        <div class="body-wrapper">
            <aside class="sidebar">
                <div class="sidebar-top">
                    <a href="game.php" class="side-link" title="Minijuegos">
                        <i class="fas fa-gamepad"></i>
                    </a>
                </div>

                <div class="sidebar-bottom">
                    <a href="grupos.php" class="side-link" title="Chat Grupal">
                        <i class="fas fa-comments"></i>
                    </a>
                    <a href="grupos.php" class="side-link" title="Chat de Voz">
                        <i class="fas fa-microphone"></i>
                    </a>
                    <a href="perfil.php" class="side-link" title="Mi Perfil">
                        <i class="fas fa-user-circle"></i>
                    </a>
                </div>
            </aside>

            <main class="content">
                <section class="banner-container">
                    <div class="banner-overlay"></div>
                    <div class="banner-content">
                        <h1 class="mundial-title">MUNDIAL</h1>
                    </div>
                    <img src="img/3.jpg" class="banner-slide active" alt="Mundial 1">
                    <img src="img/1.jpg" class="banner-slide" alt="Mundial 2">
                    <img src="img/2.jpg" class="banner-slide" alt="Mundial 3">
                </section>

                <section class="map-section">
                    <h2>Estadios en Nuevo León</h2>
                    <div class="stadium-buttons">
                        <button class="stadium-btn active" data-stadium="Estadio BBVA, Guadalupe, Nuevo Leon" onclick="visitarEstadio('bbva')">Estadio BBVA</button>
                        <button class="stadium-btn" data-stadium="Estadio Universitario, San Nicolas, Nuevo Leon" onclick="visitarEstadio('uni')">Estadio Universitario</button>
                        <button class="stadium-btn" data-stadium="Estadio Banorte, Monterrey, Nuevo Leon" onclick="visitarEstadio('banorte')">Estadio Banorte</button>
                    </div>
                    <div class="map-container">
                        <iframe id="google-map" width="100%" height="400" frameborder="0" style="border:0" src="https://maps.google.com/maps?q=Estadio%20BBVA,%20Guadalupe,%20Nuevo%20Leon&t=&z=13&ie=UTF8&iwloc=&output=embed" allowfullscreen></iframe>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script> const MI_USER_ID = <?php echo $_SESSION['user_id']; ?>; </script>
    <script src="js/logros.js"></script>
    <script src="js/script.js"></script>
</body>
</html>