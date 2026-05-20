document.addEventListener("DOMContentLoaded", () => {
    const notificacionUI = document.getElementById('notificacion-llamada');
    const btnAceptar = document.getElementById('btn-accept-call');
    const btnRechazar = document.getElementById('btn-reject-call');
    const callerName = document.getElementById('caller-name');
    let intervaloPolling = null;

    // Iniciar llamada (Función para el que llama)
    window.iniciarLlamada = function(salaId) {
        fetch(`videollamada.php?ajax=timbrar&sala=${salaId}`)
            .then(res => res.json())
            .then(data => {
                if(data.status === 'ok') {
                    // Abrimos la ventana como 'emisor'
                    window.open(`videollamada.php?sala=${salaId}&rol=emisor`, 'Videollamada', 'width=900,height=600');
                } else {
                    alert("No se pudo iniciar la llamada.");
                }
            });
    };

    // Polling constante para revisar si hay llamadas entrantes (El que recibe)
    function revisarLlamadas() {
        if (typeof MI_USER_ID === 'undefined') return;

        fetch('videollamada.php?ajax=revisar_entrantes')
            .then(res => res.json())
            .then(data => {
                if (data.hay_llamada) {
                    callerName.innerText = `¡Te están llamando para la sala ${data.sala_id}!`;
                    notificacionUI.style.display = 'block';

                    btnAceptar.onclick = () => {
                        fetch(`videollamada.php?ajax=responder&sala=${data.sala_id}&respuesta=aceptada`)
                            .then(() => {
                                notificacionUI.style.display = 'none';
                                // Abrimos la ventana como 'receptor'
                                window.open(`videollamada.php?sala=${data.sala_id}&rol=receptor`, 'Videollamada', 'width=900,height=600');
                            });
                    };

                    btnRechazar.onclick = () => {
                        fetch(`videollamada.php?ajax=responder&sala=${data.sala_id}&respuesta=rechazada`)
                            .then(() => {
                                notificacionUI.style.display = 'none';
                            });
                    };
                } else {
                    notificacionUI.style.display = 'none';
                }
            })
            .catch(err => console.error("Error buscando llamadas:", err));
    }

    // Revisar llamadas cada 3 segundos
    intervaloPolling = setInterval(revisarLlamadas, 3000);
});