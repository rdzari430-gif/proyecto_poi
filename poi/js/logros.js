// Constantes globales para identificar al usuario de forma segura
const getUserId = () => typeof MI_USER_ID !== 'undefined' ? MI_USER_ID : 'invitado';
const getStorageKey = () => `logros_mundial_${getUserId()}`;

// Obtener el progreso guardado
function obtenerLogros() {
    return JSON.parse(localStorage.getItem(getStorageKey())) || {
        bronce: false,
        plata: false,
        oro: false,
        estadiosVisitados: []
    };
}

function guardarLogros(data) {
    localStorage.setItem(getStorageKey(), JSON.stringify(data));
}

// Alerta visual de Logro Desbloqueado
function mostrarNotificacion(mensaje, imagen) {
    const toast = document.createElement('div');
    toast.innerHTML = `
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="img/${imagen}" style="width: 40px; height: auto;">
            <div>
                <h4 style="margin: 0; color: #ffd700; font-family: sans-serif;">¡Logro Desbloqueado!</h4>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #fff; font-family: sans-serif;">${mensaje}</p>
            </div>
        </div>
    `;
    Object.assign(toast.style, {
        position: 'fixed', bottom: '20px', right: '20px',
        backgroundColor: 'rgba(30, 30, 45, 0.95)', color: '#fff',
        padding: '15px', borderRadius: '12px',
        boxShadow: '0 8px 24px rgba(0,0,0,0.6)', zIndex: '99999',
        borderLeft: '5px solid #ffd700', backdropFilter: 'blur(10px)',
        transition: 'opacity 0.5s'
    });

    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 500); }, 4000);
}

// Función global para desbloquear
window.desbloquearLogro = function(tipo, descripcion, imagen) {
    let logros = obtenerLogros();
    if (!logros[tipo]) {
        logros[tipo] = true;
        guardarLogros(logros);
        mostrarNotificacion(descripcion, imagen);
    }
};

// LOGRO BRONCE: Al hacer clic en los botones de estadios
window.visitarEstadio = function(idEstadio) {
    let logros = obtenerLogros();
    if (!logros.estadiosVisitados.includes(idEstadio)) {
        logros.estadiosVisitados.push(idEstadio);
        guardarLogros(logros);
        
        if (logros.estadiosVisitados.length >= 2 && !logros.bronce) {
            window.desbloquearLogro('bronce', 'Visitaste 2 sedes en Monterrey', 'P1.png');
        }
    }
};

// LOGRO PLATA: Ejecutado desde grupos.js al enviar un mensaje exitoso
window.activarLogroMensaje = function() {
    window.desbloquearLogro('plata', 'Enviaste tu primer mensaje', 'P2.png');
};

// LOGRO ORO: Ejecutado al terminar el cuestionario de game.php
window.completarCuestionario = function(e) {
    e.preventDefault();
    let logros = obtenerLogros();
    if (!logros.oro) {
        logros.oro = true;
        guardarLogros(logros);
        window.location.href = 'index.php?logro_oro=true';
    } else {
        window.location.href = 'index.php';
    }
};

// Renderizado e inicialización de elementos del DOM
document.addEventListener("DOMContentLoaded", () => {
    // Detectar si el usuario viene redirigido desde el cuestionario de Oro
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('logro_oro') === 'true') {
        window.history.replaceState({}, document.title, window.location.pathname); // Limpia la URL
        mostrarNotificacion('Completaste el cuestionario mundialista', 'P3.png');
    }

    // Dibujar los estandartes en perfil.php si ya están desbloqueados
    let logros = obtenerLogros();
    const cajaBronce = document.getElementById('render-bronce');
    const cajaPlata = document.getElementById('render-plata');
    const cajaOro = document.getElementById('render-oro');

    if (cajaBronce && logros.bronce) cajaBronce.innerHTML = '<img src="img/P1.png" class="img-estandarte" alt="Bronce">';
    if (cajaPlata && logros.plata) cajaPlata.innerHTML = '<img src="img/P2.png" class="img-estandarte" alt="Plata">';
    if (cajaOro && logros.oro) cajaOro.innerHTML = '<img src="img/P3.png" class="img-estandarte" alt="Oro">';
});