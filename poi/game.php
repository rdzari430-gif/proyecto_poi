<?php
session_start();
if(!isset($_SESSION['user_id'])) { 
    header("Location: registro.php"); 
    exit; 
}
$mi_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Trivia Mundialista</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; text-align: center; padding: 50px; }
        .quiz-container { background: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .pregunta { margin-bottom: 20px; text-align: left; }
        button { background-color: #8b0000; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #600000; }
    </style>
    <script> const MI_USER_ID = <?php echo $mi_id; ?>; </script>
</head>
<body>

    <div class="quiz-container">
        <h2>⚽ Trivia Mundial 2026 ⚽</h2>
        <p>Responde estas 5 preguntas para ganar tu Estandarte de Oro.</p>
        
        <form onsubmit="completarCuestionario(event)">
            <div class="pregunta">
                <label>1. ¿Cuántos jugadores por equipo hay en la cancha?</label><br>
                <input type="radio" name="p1" required> 9 <br>
                <input type="radio" name="p1"> 11 <br>
                <input type="radio" name="p1"> 10
            </div>

            <div class="pregunta">
                <label>2. ¿Cuánto dura el tiempo reglamentario de un partido?</label><br>
                <input type="radio" name="p2" required> 90 minutos <br>
                <input type="radio" name="p2"> 120 minutos <br>
                <input type="radio" name="p2"> 60 minutos
            </div>

            <div class="pregunta">
                <label>3. ¿Qué país ha ganado más Mundiales de la FIFA?</label><br>
                <input type="radio" name="p3" required> Alemania <br>
                <input type="radio" name="p3"> Brasil <br>
                <input type="radio" name="p3"> Argentina
            </div>

            <div class="pregunta">
                <label>4. ¿Cada cuántos años se celebra el Mundial?</label><br>
                <input type="radio" name="p4" required> 2 años <br>
                <input type="radio" name="p4"> 4 años <br>
                <input type="radio" name="p4"> 5 años
            </div>

            <div class="pregunta">
                <label>5. ¿Dónde se jugará la final del Mundial 2026?</label><br>
                <input type="radio" name="p5" required> México <br>
                <input type="radio" name="p5"> Canadá <br>
                <input type="radio" name="p5"> Estados Unidos
            </div>

            <button type="submit">Enviar Respuestas</button>
        </form>
    </div>

    <script src="js/logros.js"></script>
</body>
</html>