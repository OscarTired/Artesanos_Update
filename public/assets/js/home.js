function mostrarSigForm() {
  document.getElementById('formParteUno').classList.add('d-none');
  document.getElementById('formParteDos').classList.remove('d-none');
}

let modalCrearAlbum = document.getElementById('modalCrearAlbum');
modalCrearAlbum.addEventListener('hidden.bs.modal', function () { //resetea el form si se cierra
  
  document.getElementById('formParteUno').classList.remove('d-none');
  document.getElementById('formParteDos').classList.add('d-none');

  const formulario = modalCrearAlbum.querySelector('form');
  formulario.reset();

  document.getElementById('previoPortada').classList.add('d-none');
  document.getElementById('previoImagen').classList.add('d-none');
});
