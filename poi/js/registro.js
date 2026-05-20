// Esperamos a que el HTML cargue por completo
document.addEventListener("DOMContentLoaded", () => {
    
    // Seleccionamos la tarjeta completa y los botones de intercambio
    const card = document.getElementById("auth-card");
    const btnGoRegister = document.getElementById("btn-go-register");
    const btnGoLogin = document.getElementById("btn-go-login");

    // Cuando hacemos clic en "¿No tienes cuenta? Regístrate"
    btnGoRegister.addEventListener("click", () => {
        // Agregamos la clase que hace el giro de 180 grados
        card.classList.add("is-flipped");
    });

    // Cuando hacemos clic en "Volver a Iniciar Sesión"
    btnGoLogin.addEventListener("click", () => {
        // Quitamos la clase para que regrese a su posición original (0 grados)
        card.classList.remove("is-flipped");
    });

    // Manejo de alerta temporal
    if (alertMsg) {
    setTimeout(() => {
        alertMsg.style.transition = "opacity 0.5s ease";
        alertMsg.style.opacity = "0";
        setTimeout(() => {
            alertMsg.remove();
        }, 500);
    }, 4000); // Se quita después de 4 segundos
    }

});