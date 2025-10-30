document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("modalDetalleAlbum");
  const modalLabel = document.getElementById("modalDetalleAlbumLabel");
  const modalBodyIzq = document.getElementById("detalleAlbumIzquierda");
  const modalBodyDer = document.getElementById("detalleAlbumDerecha");
  const fotoPerfil = document.getElementById("modalFotoPerfil");

  function tiempoRelativo(fech) {
    let fecha = new Date(fech);
    let ahora = new Date();
    let diffMs = ahora - fecha;
    let diffSeg = Math.floor(diffMs / 1000);
    let diffMin = Math.floor(diffSeg / 60);
    let diffHoras = Math.floor(diffMin / 60);
    let diffDias = Math.floor(diffHoras / 24);

    if (diffSeg < 60) return `hace ${diffSeg} segundos`;
    if (diffMin < 60) return `hace ${diffMin} minutos`;
    if (diffHoras < 24) return `hace ${diffHoras} horas`;
    if (diffDias === 1) return `ayer`;
    return `hace ${diffDias} días`;
  }

  document.querySelectorAll(".album-card").forEach((card) => {
    card.addEventListener("click", async () => {
      const albumId = card.dataset.id;

      modalLabel.innerHTML = `<img src='../../public/assets/images/logo.png' width='28' class='me-2'> Cargando álbum...`;
      modalBodyIzq.innerHTML = `<p class='text-center py-5'>Cargando imágenes...</p>`;
      modalBodyDer.innerHTML = `<p class='text-center py-5'>Cargando datos...</p>`;
      fotoPerfil.src = "";

      try {
        const res = await fetch(`../../app/controllers/detalleAlbum.php?id=${albumId}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();

        if (data.error) {
          modalBodyIzq.innerHTML = `<p class='text-danger text-center py-5'>${data.error}</p>`;
          modalBodyDer.innerHTML = "";
          modalLabel.textContent = "Error";
          return;
        }

        // Mostrar datos
        let fechaRelativa = tiempoRelativo(data.fecha);
        modalLabel.innerHTML = `
          <div class="d-flex flex-column">
            <div class="d-flex align-items-center gap-2">
              <h4 class="mb-0"><strong>${data.apodo}</strong></h4>
              <div class="text-muted fw-light"><small> - @${data.usuario}</small></div>
            </div>
            <div class="text-muted mt-1 fw-light" style="font-size: 0.9rem;">${fechaRelativa}</div>
          </div>`;

        fotoPerfil.src = data.fotoPerfil;
        document.getElementById("btnSeguir").setAttribute("data-id", data.idUsuario);
        modalBodyIzq.innerHTML = data.izquierda;
        modalBodyDer.innerHTML = data.derecha;

        // Comentarios iniciales
        setTimeout(() => {
          const btnEnviar = document.getElementById("btnEnviarComentario");
          const inputComentario = document.getElementById("inputComentario");
          const carrusel = document.getElementById("carouselAlbum");
          const listaComentarios = document.getElementById("listaComentarios");

          function mostrarComentarios(idImg) {
            const comentarios = data.comentarios[idImg] || [];
            listaComentarios.innerHTML = comentarios
              .map(
                (c) => `
              <div class="d-flex gap-2 mb-3" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 8px;">
                <img src="${c.avatar}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                <div>
                  <strong>${c.apodo}</strong><br>
                  <p class="mb-0">${c.mensaje}</p>
                </div>
              </div>`
              )
              .join("");
          }

          function actualizarInfoImagen() {
            const activo = carrusel.querySelector(".carousel-item.active");
            const titulo = activo?.dataset.titulo || "";
            const descripcion = activo?.dataset.descripcion || "";
            const idImagen = activo?.dataset.idimagen;
            document.getElementById("tituloImagen").textContent = titulo;
            document.getElementById("descripcionImagen").textContent = descripcion;
            btnEnviar.setAttribute("data-idimagen", idImagen);
            mostrarComentarios(idImagen);
          }

          if (btnEnviar && inputComentario) {
            btnEnviar.addEventListener("click", () => {
              const idImagen = btnEnviar.getAttribute("data-idimagen");
              const mensaje = inputComentario.value.trim();
              if (!mensaje) return;

              fetch("../../app/controllers/agregarComentario.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ idImagen, mensaje }),
              })
                .then((res) => res.json())
                .then((res) => {
                  if (res.ok) {
                    const nuevo = `
                      <div class="d-flex gap-2 mb-3">
                        <img src="${res.avatar}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                          <strong>${res.apodo}</strong><br>
                          <p class="mb-0">${res.mensaje}</p>
                        </div>
                      </div>`;
                    listaComentarios.insertAdjacentHTML("beforeend", nuevo);
                    inputComentario.value = "";

                    if (!data.comentarios[idImagen]) data.comentarios[idImagen] = [];
                    data.comentarios[idImagen].push({
                      apodo: res.apodo,
                      avatar: res.avatar,
                      mensaje: res.mensaje,
                      fecha: new Date().toISOString(),
                    });
                  }
                });
            });
          }

          actualizarInfoImagen();
          carrusel.addEventListener("slid.bs.carousel", actualizarInfoImagen);
        }, 150);
      } catch (err) {
        console.error("Error al cargar álbum:", err);
        modalLabel.textContent = "Error de carga";
        modalBodyIzq.innerHTML = `<p class='text-danger text-center py-5'>No se pudo cargar el álbum.</p>`;
        modalBodyDer.innerHTML = "";
      }
    });
  });
});
