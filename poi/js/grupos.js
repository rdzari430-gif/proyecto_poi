document.addEventListener("DOMContentLoaded", () => {
    let salaActual = null;
    const searchInput = document.getElementById("user-search");
    const searchResults = document.getElementById("search-results");
    const chatDisplay = document.getElementById("chat-messages");
    const chatTitle = document.getElementById("chat-title");
    const chatInput = document.getElementById("main-chat-input");
    const btnSend = document.getElementById("btn-send-msg");
    const fileUpload = document.getElementById("file-upload");

    const modal = document.getElementById("custom-modal");
    const modalInput = document.getElementById("modal-input");
    let modalCallback = null;

    function showModal(title, callback) {
        document.getElementById("modal-title").innerText = title;
        modal.style.display = "flex";
        modalInput.value = "";
        modalInput.focus();
        modalCallback = callback;
    }

    document.getElementById("modal-confirm").onclick = () => {
        if (modalInput.value.trim() !== "") {
            modal.style.display = "none";
            modalCallback(modalInput.value);
        }
    };
    
    document.getElementById("modal-cancel").onclick = () => modal.style.display = "none";

    searchInput.addEventListener("input", () => {
        let q = searchInput.value;
        if (q.length < 2) { searchResults.innerHTML = ""; return; }
        fetch(`buscarUsuarios.php?q=${q}`)
            .then(res => {
                if(!res.ok) throw new Error("No se encontró buscarUsuarios.php");
                return res.json();
            })
            .then(data => {
                searchResults.innerHTML = data.map(u => `
                    <div class="search-item" onclick="iniciarChat('${u.username}')">
                        <i class="fas fa-user"></i> <span>${u.username}</span>
                    </div>
                `).join('');
            })
            .catch(err => console.error("Error en búsqueda:", err));
    });

    window.iniciarChat = function(username) {
        crearSala(username, 'directo');
        searchInput.value = "";
        searchResults.innerHTML = "";
    };

    function crearSala(nombre, tipo) {
        const fd = new FormData();
        fd.append('nombre', nombre);
        fd.append('tipo', tipo);
        fetch('accionesSala.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    cargarSalas();
                    seleccionarSala(data.id, nombre);
                } else {
                    alert("Error al crear sala: " + data.message);
                }
            });
    }

    document.getElementById("new-group-btn").onclick = () => {
        showModal("Nombre del nuevo grupo mundialista:", (nombre) => {
            crearSala(nombre, 'grupo');
        });
    };

    function cargarSalas() {
        fetch('obtenerDatos.php?accion=listar_chats')
            .then(res => res.json())
            .then(data => {
                if(data.status === "error") return;
                const historyDiv = document.getElementById('chats-history');
                if (!historyDiv) return;
                historyDiv.innerHTML = data.map(c => `
                    <div class="chat-item ${salaActual == c.id ? 'active' : ''}" onclick="seleccionarSala(${c.id}, '${c.nombre}')">
                        <i class="${c.tipo === 'grupo' ? 'fas fa-users' : 'fas fa-user'}"></i> 
                        <span>${c.nombre || 'Chat Directo'}</span>
                    </div>
                `).join('');
            });
    }

    window.seleccionarSala = function(id, nombre) {
        salaActual = id;
        chatTitle.innerHTML = `<i class="fas fa-comments"></i> ${nombre || 'Chat Directo'}`;
        chatInput.disabled = false;
        btnSend.disabled = false;
        
        let btnVideo = document.getElementById('btn-video');
        if(btnVideo) {
            btnVideo.style.display = "block";
            let nuevoBtnVideo = btnVideo.cloneNode(true);
            btnVideo.parentNode.replaceChild(nuevoBtnVideo, btnVideo);
            
            // AQUI CAMBIAMOS LA LOGICA PARA CONECTAR AL NUEVO SISTEMA DE LLAMADAS
            nuevoBtnVideo.onclick = () => {
                if(typeof iniciarLlamada === 'function') {
                    iniciarLlamada(salaActual);
                }
            };
        }
        
        chatInput.focus();
        cargarMensajes(true);
    };

    function cargarMensajes(forzarScroll = false) {
        if (!salaActual) return;
        fetch(`obtenerDatos.php?accion=listar_mensajes&sala_id=${salaActual}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === "error" || !Array.isArray(data)) return;
                if (data.length === 0) {
                    chatDisplay.innerHTML = `<div class="welcome-msg"><h3>No hay mensajes aún</h3></div>`;
                    return;
                }
                chatDisplay.innerHTML = data.map(m => {
                    const isMine = m.is_mine; 
                    const avatarSrc = (m.foto_perfil && m.foto_perfil.trim() !== "") ? m.foto_perfil : "img/5.jpg";
                    let cuerpoMensaje = m.contenido;
                    if (m.tipo_mensaje === 'imagen') {
                        cuerpoMensaje = `<img src="${m.archivo_url}" class="img-chat-render" style="max-width:100%; max-height:220px; border-radius:8px; display:block; margin-top:5px;">`;
                    } else if (m.tipo_mensaje === 'video') {
                        cuerpoMensaje = `<video src="${m.archivo_url}" controls class="video-chat-render" style="max-width:100%; max-height:220px; border-radius:8px; display:block; margin-top:5px;"></video>`;
                    }
                    return `
                        <div class="message-container ${isMine ? 'msg-mine' : 'msg-other'}" style="display:flex; gap:10px; align-items:flex-start; margin-bottom:12px; ${isMine ? 'flex-direction:row-reverse;' : ''}">
                            <img src="${avatarSrc}" alt="Avatar" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border: 1px solid #dbb04a;">
                            <div style="max-width:70%; display:flex; flex-direction:column; ${isMine ? 'align-items:flex-end;' : 'align-items:flex-start;'}">
                                ${!isMine ? `<span class="message-sender" style="font-size:0.8rem; font-weight:bold; color:#ccc; margin-bottom:2px;">${m.username}</span>` : ''}
                                <div class="message-bubble" style="word-break:break-word;">
                                    ${cuerpoMensaje} 
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                if (forzarScroll) chatDisplay.scrollTop = chatDisplay.scrollHeight;
            });
    }

    function enviar() {
        if (!salaActual || chatInput.value.trim() === "") return;
        const fd = new FormData();
        fd.append('sala_id', salaActual);
        fd.append('contenido', chatInput.value);
        chatInput.value = ""; 

        fetch('mensajeBD.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if(data.status === "success") {
                    cargarMensajes(true);
                    if (typeof activarLogroMensaje === "function") activarLogroMensaje(); 
                }
            });
    }

    fileUpload.addEventListener("change", () => {
        if (!salaActual || fileUpload.files.length === 0) return;
        const fd = new FormData();
        fd.append('sala_id', salaActual);
        fd.append('archivo', fileUpload.files[0]);

        fetch('mensajeBD.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if(data.status === "success") {
                    cargarMensajes(true);
                } else {
                    alert("Error al subir archivo: " + data.message);
                }
                fileUpload.value = "";
            });
    });

    btnSend.onclick = enviar;
    chatInput.onkeypress = (e) => { if (e.key === "Enter") enviar(); };
    setInterval(() => { if(salaActual) cargarMensajes(); }, 2500);
    cargarSalas();
});