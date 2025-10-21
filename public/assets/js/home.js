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
  fetch("/artesanos/app/controllers/guardarAlbum.php", {
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
