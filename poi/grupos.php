<?php
session_start();
if(!isset($_SESSION['user_id'])) { 
    header("Location: registro.php"); 
    exit; 
}
include('db.php');

$nombre_sesion = isset($_SESSION['username']) ? $_SESSION['username'] : 'Usuario';
$mi_id = $_SESSION['user_id'];

// Obtener la foto de perfil real del usuario actual
$foto_perfil_sesion = 'img/5.jpg'; // Imagen por defecto
$user_q = mysqli_query($conn, "SELECT foto_perfil FROM usuarios WHERE id = $mi_id");
if ($user_row = mysqli_fetch_assoc($user_q)) {
    if (!empty($user_row['foto_perfil'])) {
        $foto_perfil_sesion = $user_row['foto_perfil'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mundial 2026 - Chat Social</title>
    <link rel="stylesheet" href="css/grupos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script> const MI_USER_ID = <?php echo $mi_id; ?>; </script>
</head>
<body>
    <div id="custom-modal" class="modal-overlay">
        <div class="modal-content">
            <h3 id="modal-title">Título</h3>
            <input type="text" id="modal-input" placeholder="...">
            <div class="modal-buttons">
                <button id="modal-cancel">Cancelar</button>
                <button id="modal-confirm">Aceptar</button>
            </div>
        </div>
    </div>

    <div id="notificacion-llamada" style="display:none; position:fixed; top:20px; right:20px; background:#3b0018; color:white; padding:20px; border-radius:8px; z-index:9999; box-shadow: 0 4px 10px rgba(0,0,0,0.5); border: 2px solid #dbb04a;">
        <h4 style="margin-top:0; color:#dbb04a;"><i class="fas fa-phone-volume"></i> Llamada Entrante</h4>
        <p id="caller-name" style="margin-bottom:15px; font-weight:bold;">Alguien te está llamando...</p>
        <button id="btn-accept-call" style="background:#27ae60; color:white; border:none; padding:10px 15px; cursor:pointer; border-radius:4px; font-weight:bold; margin-right:10px;"><i class="fas fa-phone"></i> Aceptar</button>
        <button id="btn-reject-call" style="background:#c0392b; color:white; border:none; padding:10px 15px; cursor:pointer; border-radius:4px; font-weight:bold;"><i class="fas fa-phone-slash"></i> Rechazar</button>
    </div>

    <div class="app-container">
        <header class="search-header">
            <div class="header-left">
                <img src="img/6.jpg" alt="Logo" class="mini-logo">
                <span class="brand-name">Mundial 2026</span>
            </div>
            <div class="search-bar-container">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar usuarios..." id="user-search" autocomplete="off">
                <div id="search-results" class="search-results"></div>
            </div>
            <div class="header-right">
                <span class="user-name"><?php echo htmlspecialchars($nombre_sesion); ?></span>
                <img src="<?php echo htmlspecialchars($foto_perfil_sesion); ?>" alt="Perfil" class="profile-pic">
            </div>
        </header>

        <div class="main-layout">
            <aside class="chats-sidebar">
                <div class="sidebar-header">
                    <h3>Mis Chats</h3>
                    <button class="btn-create-group" id="new-group-btn" title="Crear Grupo"><i class="fas fa-plus"></i></button>
                </div>
                <div class="chats-history" id="chats-history"></div>
            </aside>

            <main class="chat-area">
                <div class="chat-header-bar">
                    <span class="chat-title" id="chat-title">Selecciona un chat</span>
                    <button class="btn-video" id="btn-video" style="display:none;" title="Videollamada">
                        <i class="fas fa-video"></i>
                    </button>
                </div>

                <div class="chat-display" id="chat-messages">
                    <div class="welcome-msg" id="welcome-view">
                        <img src="img/6.jpg" alt="Logo" class="watermark">
                        <h2>¡Bienvenido al chat!</h2>
                        <p>Busca a alguien para empezar a hablar o selecciona un grupo.</p>
                    </div>
                </div>

                <footer class="chat-input-area">
                    <label for="file-upload" class="btn-upload">
                        <i class="fas fa-plus-circle"></i>
                    </label>
                    <input type="file" id="file-upload" style="display:none;" accept="image/*,video/*">
                    
                    <div class="input-wrapper">
                        <input type="text" placeholder="Escribe un mensaje..." id="main-chat-input">
                    </div>
                    <button class="btn-send" id="btn-send-msg">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </footer>
            </main>
        </div>
    </div>
    
    <script src="js/logros.js"></script>
    <script src="js/grupos.js"></script>
    <script src="js/llamada.js"></script>
</body>
</html>