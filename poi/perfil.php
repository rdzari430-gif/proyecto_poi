<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT username, bio, foto_perfil FROM usuarios WHERE id = $user_id";
$query = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($query);

$avatar_actual = !empty($user['foto_perfil']) ? $user['foto_perfil'] : 'img/5.jpg';
$bio_actual = $user['bio'] ?? '';
$username_actual = $user['username'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Mundial 2026</title>
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Estilos Profesionales Transparentes (Estilo Glassmorphic) */
        body {
            background: linear-gradient(135deg, #0f0f1a 0%, #1e1e2f 100%) !important;
            color: #fff !important;
        }
        
        .profile-container {
            background: transparent !important;
            padding: 40px 20px;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.05) !important;
            backdrop-filter: blur(16px) saturate(120%);
            -webkit-backdrop-filter: blur(16px) saturate(120%);
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 20px !important;
            box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.5) !important;
            color: #fff !important;
            overflow: hidden;
        }

        .username {
            color: #ffffff !important;
            text-shadow: 0 2px 10px rgba(255,255,255,0.2);
        }

        .user-tag {
            color: #ffd700 !important;
            font-weight: bold;
        }

        .info-section label {
            color: #ffd700 !important;
            font-weight: 600 !important;
            letter-spacing: 1px;
            font-size: 13px;
        }

        .info-section textarea {
            background: rgba(0, 0, 0, 0.3) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #fff !important;
            border-radius: 10px !important;
            padding: 14px !important;
            transition: border 0.3s ease;
        }

        .info-section textarea:focus {
            border-color: #ffd700 !important;
            outline: none;
        }

        /* Contenedores de Estandartes Rediseñados */
        .logros-section {
            margin: 30px 0;
            text-align: center;
        }

        .logros-section h3 {
            color: #fff;
            margin-bottom: 15px;
            font-size: 18px;
            letter-spacing: 0.5px;
        }

        .logros-caja { 
            display: flex; 
            gap: 25px; 
            justify-content: center; 
            margin-top: 15px; 
        }

        .ranura-logro { 
            width: 110px; 
            height: 135px; 
            background: rgba(0, 0, 0, 0.4); 
            border-radius: 14px; 
            border: 2px dashed rgba(255, 255, 255, 0.15); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .ranura-logro:hover {
            border-color: #ffd700;
            background: rgba(255, 255, 255, 0.02);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.1);
        }

        .img-estandarte { 
            width: 85%; 
            height: 85%; 
            object-fit: contain; 
            filter: drop-shadow(0px 5px 10px rgba(0,0,0,0.6));
        }

        /* Botones de acción mejorados */
        .btn-save {
            background: linear-gradient(135deg, #8b0000 0%, #5a0000 100%) !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: bold;
            transition: transform 0.2s ease !important;
        }

        .btn-save:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 15px rgba(139, 0, 0, 0.4);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 8px !important;
            transition: background 0.2s ease !important;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.18) !important;
        }
    </style>
</head>
<body>

    <div class="profile-container">
        <form action="perfilBD.php" method="POST" enctype="multipart/form-data" class="profile-card">
            
            <div class="profile-banner"></div>

            <div class="profile-header">
                <div class="avatar-container">
                    <img src="<?php echo $avatar_actual; ?>" id="preview-avatar" alt="Mi Foto de Perfil" class="current-avatar">
                    <div class="avatar-overlay">
                        <i class="fas fa-camera"></i>
                        <span>CAMBIAR FOTO</span>
                        <input type="file" name="foto_perfil" id="upload-photo" accept="image/*" onchange="previewImage(event)">
                    </div>
                </div>
                <div class="user-info">
                    <h1 class="username"><?php echo htmlspecialchars($username_actual); ?></h1>
                    <p class="user-tag">#2026</p>
                </div>
            </div>

            <div class="profile-body">
                
                <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                    <div style="background: #28a745; color: white; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px;">
                        ¡Perfil actualizado correctamente!
                    </div>
                <?php endif; ?>

                <div class="info-section">
                    <label>SOBRE MÍ</label>
                    <textarea name="bio" placeholder="¡Hola! Estoy listo para el Mundial en Monterrey..."><?php echo htmlspecialchars($bio_actual); ?></textarea>
                </div>

                <div class="logros-section">
                    <h3>Mis Estandartes</h3>
                    <div class="logros-caja">
                        <div id="render-bronce" class="ranura-logro" title="Explorador Regio"></div>
                        <div id="render-plata" class="ranura-logro" title="Comunicador"></div>
                        <div id="render-oro" class="ranura-logro" title="Experto Mundialista"></div>
                    </div>
                </div>

                <div class="profile-actions">
                    <button type="submit" class="btn-save">Guardar Cambios</button>
                    <a href="index.php" class="btn-back">Volver al Inicio</a>
                </div>
            </div>
        </form>
    </div>

    <script> const MI_USER_ID = <?php echo $_SESSION['user_id']; ?>; </script>
    <script src="js/logros.js"></script>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('preview-avatar');
                output.src = reader.result;
            };
            if(event.target.files[0]){
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
</body>
</html>