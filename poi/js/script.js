document.addEventListener("DOMContentLoaded", () => {
    // Banner
    const slides = document.querySelectorAll(".banner-slide");
    let index = 0;
    setInterval(() => {
        slides[index].classList.remove("active");
        index = (index + 1) % slides.length;
        slides[index].classList.add("active");
    }, 4000);

    // Mapa
    const btns = document.querySelectorAll(".stadium-btn");
    const map = document.getElementById("google-map");
    btns.forEach(b => {
        b.addEventListener("click", () => {
            btns.forEach(btn => btn.classList.remove("active"));
            b.classList.add("active");
            const loc = b.getAttribute("data-stadium");
            map.src = `https://maps.google.com/maps?q=${encodeURIComponent(loc)}&output=embed`;
        });
    });

    // Partidos (Simulados)
    const container = document.getElementById("matches-container");
    const data = [
        { t: "México vs Por definir", d: "11 Junio 2026", s: "Azteca" },
        { t: "Por definir vs Por definir", d: "14 Junio 2026", s: "BBVA" }
    ];
    container.innerHTML = "";
    data.forEach(m => {
        container.innerHTML += `<div class="match-card"><h3>${m.t}</h3><p>${m.d}</p><p>${m.s}</p></div>`;
    });
});