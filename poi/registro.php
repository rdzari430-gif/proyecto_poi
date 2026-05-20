<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión / Registro - POI</title>
    <link rel="stylesheet" href="css/registro.css">
</head>
<body>
    <div class="login-wrapper">
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert-container" id="alert-msg">
                <div class="alert-box">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="scene">
            <div class="card" id="auth-card">
                <div class="card-face card-front">
                    <h2 class="form-title">Iniciar Sesión</h2>
                    <form class="auth-form" action="auth.php" method="POST">
                        <input type="hidden" name="action" value="login">
                        <div class="input-group">
                            <label>Correo Electrónico</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="input-group">
                            <label>Contraseña</label>
                            <input type="password" name="password" required>
                        </div>
                        <button type="submit" class="btn-submit">Entrar al Servidor</button>
                    </form>
                    <div class="form-footer">
                        <button class="btn-switch" id="btn-go-register">¿No tienes cuenta? Regístrate</button>
                    </div>
                </div>

                <div class="card-face card-back">
                    <h2 class="form-title">Crear Cuenta</h2>
                    <form class="auth-form" action="auth.php" method="POST">
                        <input type="hidden" name="action" value="register">
                        <div class="input-group">
                            <label>Nombre de Usuario</label>
                            <input type="text" name="username" required>
                        </div>
                        <div class="input-group">
                            <label>Correo Electrónico</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="input-group">
                            <label>Contraseña</label>
                            <input type="password" name="password" required>
                        </div>
                        <button type="submit" class="btn-submit">Registrarme</button>
                    </form>
                    <div class="form-footer">
                        <button class="btn-switch" id="btn-go-login">Volver a Iniciar Sesión</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/registro.js"></script>
</body>
</html>