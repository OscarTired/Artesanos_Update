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

//carga el detalle del album en el modal
document.querySelectorAll(".abrir-modal-album").forEach((el) => {
  el.addEventListener("click", function () {
    let idAlbum = this.getAttribute("data-id");

    fetch(`../controllers/detalleAlbum.php?id=${idAlbum}`)
      .then((res) => res.json())
      .then((data) => {
        let fechaRelativa = tiempoRelativo(data.fecha);
        console.log(data);
        //datos del usuario
        document.getElementById("modalDetalleAlbumLabel").innerHTML = `
        <div class="d-flex flex-column">
          <div class="d-flex align-items-center gap-2">
            <h4 class="mb-0"><strong>${data.apodo}</strong></h4>
            <div class="text-muted fw-light"><small> - @${data.usuario}</small></div>
          </div>
          <div class="text-muted mt-1 fw-light" style="font-size: 0.9rem;">${fechaRelativa}</div>
        </div>
        `;

        document.getElementById("modalFotoPerfil").src = data.fotoPerfil;
        document
          .getElementById("btnSeguir")
          .setAttribute("data-id", data.idUsuario);

        document.getElementById("detalleAlbumIzquierda").innerHTML =
          data.izquierda;
        document.getElementById("detalleAlbumDerecha").innerHTML = data.derecha;
        setTimeout(() => {
          const btnEnviar = document.getElementById("btnEnviarComentario");
          const inputComentario = document.getElementById("inputComentario");

          if (btnEnviar && inputComentario) {
            btnEnviar.addEventListener("click", function () {
              let idImagen = this.getAttribute("data-idimagen");
              let mensaje = inputComentario.value.trim();
              if (!mensaje) return;

              fetch("../controllers/agregarComentario.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ idImagen, mensaje }),
              })
                .then((res) => res.json())
                .then((res) => {
                  if (res.ok) {
                    let nuevo = `
            <div class="d-flex gap-2 mb-3">
              <img src="${res.avatar}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
              <div>
                <strong>${res.apodo}</strong><br>
                <p class="mb-0">${res.mensaje}</p>
              </div>
            </div>
          `;
                    document
                      .getElementById("listaComentarios")
                      .insertAdjacentHTML("beforeend", nuevo);
                    inputComentario.value = "";

                    if (!data.comentarios[idImagen]) {
                      data.comentarios[idImagen] = [];
                    }
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
        }, 0);

        //cambia la info de la imagen al cambiar de slide y los comentarios
        let carrusel = document.getElementById("carouselAlbum");
        function actualizarInfoImagen() {
          let activo = carrusel.querySelector(".carousel-item.active");
          let titulo = activo.getAttribute("data-titulo") || "";
          let descripcion = activo.getAttribute("data-descripcion") || "";
          let idImagen = activo.getAttribute("data-idimagen");

          document.getElementById("tituloImagen").textContent = titulo;
          document.getElementById("descripcionImagen").textContent =
            descripcion;

          let comentarios = data.comentarios[idImagen] || [];
          let htmlComentarios = comentarios
            .map(
              (c) => `
    <div class="d-flex gap-2 mb-3" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 8px;">
      <img src="${c.avatar}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
      <div>
        <strong>${c.apodo}</strong><br>
        <p class="mb-0">${c.mensaje}</p>
      </div>
    </div>
  `
            )
            .join("");

          document.getElementById("listaComentarios").innerHTML =
            htmlComentarios;

          document
            .getElementById("btnEnviarComentario")
            .setAttribute("data-idimagen", idImagen);
        }

        actualizarInfoImagen();

        carrusel.addEventListener("slid.bs.carousel", actualizarInfoImagen);
      });
  });
});

//funcion para el boton de cargar mas albumes
document.addEventListener("DOMContentLoaded", function () {
  let albums = document.querySelectorAll(".album-item");
  let loadMoreBtn = document.getElementById("loadMore");

  let itemsPerPage = 20;
  let currentItems = itemsPerPage;

  // ocultar todos menos los primeros 20
  albums.forEach((album, index) => {
    if (index >= currentItems) album.style.display = "none";
  });

  loadMoreBtn.addEventListener("click", () => {
    for (let i = currentItems; i < currentItems + itemsPerPage; i++) {
      if (albums[i]) albums[i].style.display = "block";
    }
    currentItems += itemsPerPage;

    if (currentItems >= albums.length) {
      loadMoreBtn.style.display = "none";
    }
  });

  if (albums.length <= itemsPerPage) {
    loadMoreBtn.style.display = "none";
  }
});

//cambia de "pagina" dentro del formulario
function mostrarSigForm() {
  //primero valida que los datos del album esten bien
  const form = document.getElementById("formCrearAlbum");
  form.classList.add("was-validated");

  document.getElementById("inputPortada").classList.remove("is-invalid");
  document.getElementById("tituloAlb").classList.remove("is-invalid");
  document.getElementById("etiquetaAlb").classList.remove("is-invalid");

  let valido = true;
  let portada = document.getElementById("inputPortada").files[0];
  let tituloAlb = document.getElementById("tituloAlb").value.trim();
  let etiquetaAlb = document.getElementById("etiquetaAlb").value.trim();

  if (!portada || !portada.type.startsWith("image/")) {
    document.getElementById("inputPortada").classList.add("is-invalid");
    document.getElementById("inputPortada").nextElementSibling.textContent =
      "Sube una imagen de portada.";
    valido = false;
  }
  if (tituloAlb.length < 3) {
    document.getElementById("tituloAlb").classList.add("is-invalid");
    document.getElementById("tituloAlb").nextElementSibling.textContent =
      "Completa el título del álbum. Debe tener al menos 3 letras.";
    valido = false;
  }
  if (etiquetaAlb != "" && etiquetaAlb.length < 3) {
    document.getElementById("etiquetaAlb").classList.add("is-invalid");
    document.getElementById("etiquetaAlb").nextElementSibling.textContent =
      "Si agregas alguna etiqueta, debe tener al menos 3 letras.";
    valido = false;
  }

  //si hay errores queda en la pagina esa y sino avanza
  if (!valido) return;

  document.getElementById("formParteUno").classList.add("d-none");
  document.getElementById("formParteDos").classList.remove("d-none");
  document.getElementById("formParteTres").classList.add("d-none");
}

//muestra la vista previa de la portada
document.getElementById("inputPortada").addEventListener("change", function () {
  let archivo = this.files[0];
  let preview = document.getElementById("previoPortada");

  if (archivo) {
    let lector = new FileReader();

    lector.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = "block";
      document.getElementById("portada").style.display = "none";
    };

    lector.readAsDataURL(archivo);
  }
});

document
  .getElementById("inputImagenes")
  .addEventListener("change", function () {
    let cantidad = this.files.length;

    if (cantidad > 0) {
      mostrarSigForm2();
    }
  });

let imagenes = [];
let indiceActual = 0;

function mostrarSigForm2() {
  let input = document.getElementById("inputImagenes");
  imagenes = Array.from(input.files);
  indiceActual = 0;

  if (imagenes.length === 0) return;

  document.getElementById("formParteDos").classList.add("d-none");
  document.getElementById("formParteTres").classList.remove("d-none");

  mostrarImagenActual();
}

//muestra las imagenes q cargo el usuario y el form para que le agregue detalles
function mostrarImagenActual() {
  let archivo = imagenes[indiceActual];
  let lector = new FileReader();

  lector.onload = function (e) {
    let bloque = `<p><small>Imagen ${indiceActual + 1} de ${
      imagenes.length
    }</small></p>
      <div class="row mb-4 align-items-center">
        <div class="col-lg-6 col-12">
          <img src="${
            e.target.result
          }" class="img-fluid rounded shadow-sm imagenPreview">
        </div>
        <div class="col-lg-6 col-12">
          <input type="text" id="tituloImagen${indiceActual}" name="tituloImagen${indiceActual}" class="form-control mb-3" placeholder="Título de la imagen">
            <div class="invalid-feedback">Si completás este campo, que tenga al menos 1 carácter visible.</div>

          <textarea name="descripcionImagen${indiceActual}" id="descripcionImagen${indiceActual}" class="form-control mb-3" rows="4" placeholder="Descripción" style="resize: none;"></textarea>
            <div class="invalid-feedback">Si completás este campo, que tenga al menos 1 carácter visible.</div>

          <input type="text" name="etiquetaImagen${indiceActual}" id="etiquetaImagen${indiceActual}" class="form-control mb-3" placeholder="#etiqueta">
            <div class="invalid-feedback">Si agregas alguna etiqueta, debe tener al menos 3 letras.</div>

        </div>
      </div>
    `;
    document.getElementById("bloqueImagenActual").innerHTML = bloque;

    document
      .querySelector(`input[name="etiquetaImagen${indiceActual}"]`)
      .addEventListener("keydown", function (e) {
        if (e.key === " ") e.preventDefault();
      });

    //mostrar o no los botones segun la posicion
    let btnCrear = document.getElementById("btnCrear");
    let btnSiguiente = document.getElementById("btnSiguienteImagen");
    let btnAnterior = document.getElementById("btnAnteriorImagen");

    btnAnterior.classList.toggle("d-none", indiceActual === 0);
    btnSiguiente.classList.toggle(
      "d-none",
      indiceActual === imagenes.length - 1
    );
    btnCrear.classList.toggle("d-none", indiceActual !== imagenes.length - 1);

    document.getElementById("btnSiguienteImagen").onclick = () => {
      if (!validarImagenActual()) return;
      guardarDatosImagenActual();
      if (indiceActual < imagenes.length - 1) {
        indiceActual++;
        mostrarImagenActual();
      }
    };
  };

  lector.readAsDataURL(archivo);
}
let datosImagenes = [];
function guardarDatosImagenActual() {
  datosImagenes[indiceActual] = {
    archivo: imagenes[indiceActual],
    titulo: document.getElementById(`tituloImagen${indiceActual}`).value,
    descripcion: document.getElementById(`descripcionImagen${indiceActual}`)
      .value,
    etiqueta: document.getElementById(`etiquetaImagen${indiceActual}`).value,
  };
}
function validarImagenActual() {
  let valido = true;

  const form = document.getElementById("formCrearAlbum");

  let titulo = document.getElementById(`tituloImagen${indiceActual}`);
  let descripcion = document.getElementById(`descripcionImagen${indiceActual}`);
  let etiqueta = document.getElementById(`etiquetaImagen${indiceActual}`);

  titulo.classList.remove("is-invalid");
  descripcion.classList.remove("is-invalid");
  etiqueta.classList.remove("is-invalid");

  if (titulo.value.trim() < 3) {
    titulo.classList.add("is-invalid");
    titulo.nextElementSibling.textContent =
      "Completa el título de la imagen. Debe tener al menos 3 letras.";
    valido = false;
  }

  if (descripcion.value.trim() !== "" && descripcion.value.trim().length < 1) {
    descripcion.classList.add("is-invalid");
    descripcion.nextElementSibling.textContent =
      "Si completás este campo, que tenga al menos 1 carácter visible.";
    valido = false;
  }

  if (etiqueta.value.trim() !== "" && etiqueta.value.trim().length < 3) {
    etiqueta.classList.add("is-invalid");
    etiqueta.nextElementSibling.textContent =
      "Si agregas alguna etiqueta, debe tener al menos 3 letras.";
    valido = false;
  }

  return valido;
}

document.getElementById("btnAnteriorImagen").addEventListener("click", () => {
  guardarDatosImagenActual();
  if (indiceActual > 0) {
    indiceActual--;
    mostrarImagenActual();
  }
});

let modalCrearAlbum = document.getElementById("modalCrearAlbum");
modalCrearAlbum.addEventListener("hidden.bs.modal", function () {
  //resetea el form si se cierra
  const form = document.getElementById("formCrearAlbum");
  form.reset();
  form.classList.remove("was-validated");

  form.querySelectorAll(".form-control, .form-select").forEach((campo) => {
    campo.classList.remove("is-invalid", "is-valid");
  });

  form.querySelectorAll(".invalid-feedback").forEach((msg) => {
    msg.textContent = "";
  });

  document.getElementById("formParteUno").classList.remove("d-none");
  document.getElementById("formParteDos").classList.add("d-none");
  document.getElementById("formParteTres").classList.add("d-none");

  document.getElementById("portada").style.display = "block";
  document.getElementById("previoPortada").style.display = "none";
  document.getElementById("previoImagen").classList.add("d-none");
});

//permite una sola palabra en la etiqueta
document
  .querySelector('input[name="etiquetaAlb"]')
  .addEventListener("keydown", function (e) {
    if (e.key === " ") e.preventDefault();
  });

function actualizarVistaPrincipal() {}

//envio del formulario
document.getElementById("btnCrear").addEventListener("click", function (e) {
  if (!validarImagenActual()) return;
  guardarDatosImagenActual();
  e.preventDefault();
  const formData = new FormData();

  //album
  formData.append("tituloAlbum", document.getElementById("tituloAlb").value);
  formData.append(
    "etiquetaAlbum",
    document.getElementById("etiquetaAlb").value
  );
  formData.append("portada", document.getElementById("inputPortada").files[0]);

  //imagenes
  datosImagenes.forEach((img, i) => {
    formData.append(`imagen${i}`, img.archivo);
    formData.append(`tituloImagen${i}`, img.titulo);
    formData.append(`descripcionImagen${i}`, img.descripcion);
    formData.append(`etiquetaImagen${i}`, img.etiqueta);
  });

  formData.append("cantidadImagenes", datosImagenes.length);
  for (let [key, value] of formData.entries()) {
    console.log(key, value);
  }
  //envio los datos al controlador
  // ajusta segun la estructura de carpetas
  const base = window.location.origin;
  const rutaRaiz = window.location.pathname.split("/app/views")[0]; // todo antes de /app/views
  const ruta = `${base}${rutaRaiz}/app/controllers/guardarAlbum.php`;

  fetch(ruta, {
    method: "POST",
    body: formData,
  })
    .then(async (res) => {
      const text = await res.text();
      try {
        const data = JSON.parse(text);
        if (data.exito) {
          Swal.fire({
            title: "¡Álbum creado con éxito!",
            text: "Redirigiendo a la pagina principal...",
            icon: "success",
            timer: 3000,
            showConfirmButton: false,
            willClose: () => {
              location.reload();
            },
          });
        } else {
          alert("Error: " + data.mensaje);
        }
      } catch (err) {
        console.error("Respuesta no válida:", text);
        alert("Error inesperado del servidor.");
      }
    })

    .catch((err) => {
      console.error("Error:", err);
      Swal.fire({
        icon: "error",
        title: "Error de conexión",
        text: "No se pudo conectar con el servidor.",
      });
    });
});
