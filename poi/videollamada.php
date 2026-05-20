<?php
session_start();
include('db.php');

if(!isset($_SESSION['user_id'])) { 
    echo json_encode(["status" => "error", "msg" => "No auth"]);
    exit; 
}

$mi_id = $_SESSION['user_id'];

// 1. AUTO-CREAR TABLA DE SEÑALIZACIÓN WEBRTC (Para no tener que entrar a MySQL a mano)
$crear_tabla = "CREATE TABLE IF NOT EXISTS webrtc_llamadas (
    sala_id INT PRIMARY KEY,
    emisor_id INT,
    estado VARCHAR(20),
    sdp_offer TEXT,
    sdp_answer TEXT,
    ice_emisor TEXT,
    ice_receptor TEXT,
    ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
mysqli_query($conn, $crear_tabla);

// =========================================================
// API DE SEÑALIZACIÓN (PUENTE AJAX PARA PHP)
// =========================================================
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $accion = $_GET['ajax'];
    $sala = isset($_GET['sala']) ? (int)$_GET['sala'] : 0;

    if ($accion === 'timbrar') {
        // El emisor registra la intención de llamar
        $query = "INSERT INTO webrtc_llamadas (sala_id, emisor_id, estado, ice_emisor, ice_receptor) 
                  VALUES ($sala, $mi_id, 'timbrando', '[]', '[]') 
                  ON DUPLICATE KEY UPDATE emisor_id=$mi_id, estado='timbrando', sdp_offer=NULL, sdp_answer=NULL, ice_emisor='[]', ice_receptor='[]'";
        mysqli_query($conn, $query);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    if ($accion === 'revisar_entrantes') {
        // Revisar si alguien nos llama (estamos en esa sala y no somos el emisor)
        // Por practicidad del proyecto, escaneamos las llamadas en status 'timbrando'
        $query = "SELECT l.sala_id FROM webrtc_llamadas l 
                  JOIN usuarios_salas u ON u.sala_id = l.sala_id 
                  WHERE u.usuario_id = $mi_id AND l.emisor_id != $mi_id AND l.estado = 'timbrando'";
        $res = mysqli_query($conn, $query);
        if ($row = mysqli_fetch_assoc($res)) {
            echo json_encode(['hay_llamada' => true, 'sala_id' => $row['sala_id']]);
        } else {
            echo json_encode(['hay_llamada' => false]);
        }
        exit;
    }

    if ($accion === 'responder') {
        $respuesta = $_GET['respuesta'] === 'aceptada' ? 'aceptada' : 'rechazada';
        mysqli_query($conn, "UPDATE webrtc_llamadas SET estado='$respuesta' WHERE sala_id=$sala");
        echo json_encode(['status' => 'ok']);
        exit;
    }

    if ($accion === 'check_estado') {
        $res = mysqli_query($conn, "SELECT estado, sdp_offer, sdp_answer, ice_emisor, ice_receptor FROM webrtc_llamadas WHERE sala_id=$sala");
        $data = mysqli_fetch_assoc($res);
        echo json_encode($data ? $data : ['estado' => 'terminada']);
        exit;
    }

    // Guardar SDP y ICE candidates
    $post_data = json_decode(file_get_contents('php://input'), true);
    if ($accion === 'set_offer') {
        $offer = mysqli_real_escape_string($conn, $post_data['sdp']);
        mysqli_query($conn, "UPDATE webrtc_llamadas SET sdp_offer='$offer' WHERE sala_id=$sala");
        echo json_encode(['status' => 'ok']);
        exit;
    }
    if ($accion === 'set_answer') {
        $answer = mysqli_real_escape_string($conn, $post_data['sdp']);
        mysqli_query($conn, "UPDATE webrtc_llamadas SET sdp_answer='$answer' WHERE sala_id=$sala");
        echo json_encode(['status' => 'ok']);
        exit;
    }
    if ($accion === 'add_ice') {
        $rol = $_GET['rol'] === 'emisor' ? 'ice_emisor' : 'ice_receptor';
        $res = mysqli_query($conn, "SELECT $rol FROM webrtc_llamadas WHERE sala_id=$sala");
        $row = mysqli_fetch_assoc($res);
        $ice_array = json_decode($row[$rol], true) ?: [];
        $ice_array[] = $post_data['candidate'];
        $nuevo_ice = mysqli_real_escape_string($conn, json_encode($ice_array));
        mysqli_query($conn, "UPDATE webrtc_llamadas SET $rol='$nuevo_ice' WHERE sala_id=$sala");
        echo json_encode(['status' => 'ok']);
        exit;
    }

    if ($accion === 'colgar') {
        mysqli_query($conn, "UPDATE webrtc_llamadas SET estado='terminada' WHERE sala_id=$sala");
        echo json_encode(['status' => 'ok']);
        exit;
    }
}

// =========================================================
// INTERFAZ DE VIDEO WEB (HTML + WEBRTC NATIVO)
// =========================================================
$sala_id = isset($_GET['sala']) ? (int)$_GET['sala'] : 0;
$rol = isset($_GET['rol']) ? $_GET['rol'] : 'emisor';

if ($sala_id === 0) die("Sala no válida");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Videollamada Nativa</title>
    <style>
        body { margin: 0; background: #1a0a0f; color: white; font-family: sans-serif; display: flex; flex-direction: column; height: 100vh; }
        .top-bar { padding: 15px; background: #3b0018; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #dbb04a; }
        .btn-close { background: #e74c3c; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; font-weight: bold; }
        .video-grid { flex: 1; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 10px; padding: 10px; position: relative; }
        video { background: #000; border-radius: 8px; border: 2px solid #555; max-height: 100%; object-fit: cover; }
        #remoteVideo { width: 100%; height: 100%; }
        #localVideo { width: 150px; height: 150px; position: absolute; bottom: 20px; right: 20px; border: 2px solid #dbb04a; z-index: 10; }
        .status { text-align: center; padding: 10px; background: #222; font-size: 0.9em; color: #dbb04a; }
    </style>
</head>
<body>
    <div class="top-bar">
        <span>Videollamada - Sala <?php echo $sala_id; ?> (<?php echo strtoupper($rol); ?>)</span>
        <button class="btn-close" onclick="colgar()">Finalizar</button>
    </div>
    
    <div class="video-grid">
        <video id="remoteVideo" autoplay playsinline></video>
        <video id="localVideo" autoplay playsinline muted></video>
    </div>
    
    <div id="status-text" class="status">Conectando cámara...</div>

    <script>
        const salaId = <?php echo $sala_id; ?>;
        const miRol = "<?php echo $rol; ?>";
        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');
        const statusText = document.getElementById('status-text');
        
        let localStream;
        let peerConnection;
        let pollingInterval;
        let currentIceCandidates = 0; // Para saber en qué ICE candidate nos quedamos

        // Configuración gratuita de Google STUN Server para encontrar las IPs públicas
        const rtcConfig = {
            iceServers: [{ urls: "stun:stun.l.google.com:19302" }]
        };

       async function init() {
    try {
        // Solicitar cámara y micrófono
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        localVideo.srcObject = localStream;
        
        // CORRECCIÓN 1: Forzar al reproductor de video a arrancar el flujo físicamente
        await localVideo.play(); 
        
        statusText.innerText = "Cámara activa. Conectando par...";

        peerConnection = new RTCPeerConnection(rtcConfig);
        
        // Añadir nuestros tracks al peer
        localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

        // Recibir video remoto
        peerConnection.ontrack = event => {
            remoteVideo.srcObject = event.streams[0];
            remoteVideo.play().catch(e => console.log("Espera de interacción para video remoto"));
            statusText.innerText = "Conexión establecida.";
        };

        // Cuando nuestro navegador genera un paquete ICE, lo mandamos a PHP
        peerConnection.onicecandidate = event => {
            if (event.candidate) {
                fetch(`videollamada.php?ajax=add_ice&sala=${salaId}&rol=${miRol}`, {
                    method: 'POST',
                    body: JSON.stringify({candidate: event.candidate})
                });
            }
        };

        if (miRol === 'emisor') {
            // El que llama crea la oferta
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            await fetch(`videollamada.php?ajax=set_offer&sala=${salaId}`, {
                method: 'POST', body: JSON.stringify({sdp: offer.sdp})
            });
            statusText.innerText = "Esperando que el receptor conteste...";
        }

        // CORRECCIÓN 2: Pequeño retraso de 500ms para asegurar que el video se renderice 
        // antes de iniciar el bombardeo de peticiones por polling a MySQL
        setTimeout(() => {
            pollingInterval = setInterval(checkSignalingDatabase, 2000);
        }, 500);

    } catch (err) {
        console.error("Error media:", err);
        statusText.innerText = "Error accediendo a cámara/micrófono. Revisa permisos HTTPS.";
    }
}

        async function checkSignalingDatabase() {
            const res = await fetch(`videollamada.php?ajax=check_estado&sala=${salaId}`);
            const data = await res.json();

            if (data.estado === 'terminada' || data.estado === 'rechazada') {
                alert("Llamada finalizada.");
                window.close();
                return;
            }

            // Si soy receptor y apenas llega la oferta, crear respuesta
            if (miRol === 'receptor' && data.sdp_offer && peerConnection.signalingState === "stable") {
                await peerConnection.setRemoteDescription(new RTCSessionDescription({type: 'offer', sdp: data.sdp_offer}));
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                await fetch(`videollamada.php?ajax=set_answer&sala=${salaId}`, {
                    method: 'POST', body: JSON.stringify({sdp: answer.sdp})
                });
            }

            // Si soy emisor y apenas llega la respuesta
            if (miRol === 'emisor' && data.sdp_answer && peerConnection.signalingState === "have-local-offer") {
                await peerConnection.setRemoteDescription(new RTCSessionDescription({type: 'answer', sdp: data.sdp_answer}));
            }

            // Descargar e inyectar ICE candidates del otro lado
            const iceKey = miRol === 'emisor' ? 'ice_receptor' : 'ice_emisor';
            if (data[iceKey]) {
                const candidatesArray = JSON.parse(data[iceKey]);
                if (candidatesArray.length > currentIceCandidates) {
                    for (let i = currentIceCandidates; i < candidatesArray.length; i++) {
                        peerConnection.addIceCandidate(new RTCIceCandidate(candidatesArray[i]));
                    }
                    currentIceCandidates = candidatesArray.length;
                }
            }
        }

        window.colgar = function() {
            fetch(`videollamada.php?ajax=colgar&sala=${salaId}`).then(() => {
                window.close();
            });
        };

        init();
    </script>
</body>
</html>