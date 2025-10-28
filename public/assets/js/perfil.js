document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("modalDetalleAlbum");
  const modalLabel = document.getElementById("modalDetalleAlbumLabel");
  const modalBodyIzq = document.getElementById("detalleAlbumIzquierda");
  const modalBodyDer = document.getElementById("detalleAlbumDerecha");
  const fotoPerfil = document.getElementById("modalFotoPerfil");

  // Cuando se hace clic en un álbum
  document.querySelectorAll(".album-card").forEach(card => {
    card.addEventListener("click", async () => {
      const albumId = card.dataset.id;

      // Mostrar mensaje de carga
      modalLabel.innerHTML = `<img src='../../public/assets/images/logo.png' width='28' class='me-2'> Cargando álbum...`;
      modalBodyIzq.innerHTML = `<p class='text-center py-5'>Cargando imágenes del álbum ${albumId}...</p>`;
      modalBodyDer.innerHTML = `<p class='text-center py-5'>Cargando datos del autor...</p>`;
      fotoPerfil.src = "";

      try {
        // 📦 Llamada AJAX al controlador correcto
        const res = await fetch(`../../app/controllers/detalleAlbum.php?id=${albumId}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();

        // Validar error
        if (data.error) {
          modalBodyIzq.innerHTML = `<p class='text-danger text-center py-5'>${data.error}</p>`;
          modalBodyDer.innerHTML = "";
          modalLabel.textContent = "Error";
          return;
        }

        // 🖼️ Mostrar contenido HTML generado por el backend
        modalBodyIzq.innerHTML = data.izquierda || "<p class='text-muted text-center'>Sin contenido.</p>";
        modalBodyDer.innerHTML = data.derecha || "<p class='text-muted text-center'>Sin información.</p>";

        // 🧑 Datos de cabecera
        modalLabel.textContent = data.tituloAlbum || "Álbum";
        fotoPerfil.src = data.fotoPerfil || "../../public/uploads/perfiles/default.png";

        // 🔁 Sincronizar título y descripción al cambiar imagen
        const carousel = document.querySelector("#carouselAlbum");
        if (carousel) {
          carousel.addEventListener("slide.bs.carousel", event => {
            const nextItem = event.relatedTarget;
            const titulo = nextItem.getAttribute("data-titulo") || "";
            const desc = nextItem.getAttribute("data-descripcion") || "";
            document.getElementById("tituloImagen").textContent = titulo;
            document.getElementById("descripcionImagen").textContent = desc;
          });
        }

      } catch (err) {
        console.error("Error al cargar álbum:", err);
        modalLabel.textContent = "Error de carga";
        modalBodyIzq.innerHTML = `<p class='text-danger text-center py-5'>No se pudo cargar el álbum.</p>`;
        modalBodyDer.innerHTML = "";
      }
    });
  });
});
