//cambia de "pagina" dentro del formulario
function mostrarSigForm() {
  document.getElementById("formParteUno").classList.add("d-none");
  document.getElementById("formParteDos").classList.remove("d-none");
  document.getElementById("formParteTres").classList.add("d-none");
}

//muestra la vista previa de la portada
document
  .getElementById("inputPortada")
  .addEventListener("change", function () {
    let archivo = this.files[0];
    let preview = document.getElementById('previoPortada');

  if (archivo) {
    let lector = new FileReader();

    lector.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
      document.getElementById('portada').style.display = 'none';
    };

    lector.readAsDataURL(archivo);
  }

  });

document
  .getElementById("inputImagenes")
  .addEventListener("change", function () {
    let cantidad = this.files.length;

    if (cantidad > 0) {
      Swal.fire({
        title: "¡Imágenes cargadas!",
        text: `${cantidad} imagen(es) lista(s) para editar.`,
        icon: "success",
        timer: 1700,
        showConfirmButton: false,
      });

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
          <input type="text" name="tituloImagen${indiceActual}" class="form-control mb-3" placeholder="Título de la imagen">
          <textarea name="descripcionImagen${indiceActual}" class="form-control mb-3" rows="4" placeholder="Descripción" style="resize: none;"></textarea>
          <input type="text" name="etiquetaImagen${indiceActual}" class="form-control mb-3" placeholder="#etiqueta">
        </div>
      </div>
    `;
    document.getElementById("bloqueImagenActual").innerHTML = bloque;

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
  };

  lector.readAsDataURL(archivo);
}

document.getElementById("btnSiguienteImagen").addEventListener("click", () => {
  if (indiceActual < imagenes.length - 1) {
    indiceActual++;
    mostrarImagenActual();
  }
});
document.getElementById("btnAnteriorImagen").addEventListener("click", () => {
  if (indiceActual > 0) {
    indiceActual--;
    mostrarImagenActual();
  }
});

let modalCrearAlbum = document.getElementById("modalCrearAlbum");
modalCrearAlbum.addEventListener("hidden.bs.modal", function () {
  //resetea el form si se cierra

  document.getElementById("formParteUno").classList.remove("d-none");
  document.getElementById("formParteDos").classList.add("d-none");
  document.getElementById("formParteTres").classList.add("d-none");

  let formulario = modalCrearAlbum.querySelector("form");
  formulario.reset();

  document.getElementById('portada').style.display = 'block';
  document.getElementById("previoPortada").style.display = 'none';
  document.getElementById("previoImagen").classList.add("d-none");
});

//validacion del formulario y envio
