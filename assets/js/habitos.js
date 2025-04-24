// Espera a que el dom este listo
document.addEventListener('DOMContentLoaded', function() {
    // Seleccion de elementos
    const frecuenciaSelect = document.getElementById('id_frecuencia'); // El dropdown para seleccionar la frecuencia
    const opcionesPersonalizada = document.getElementById('opcionesPersonalizada'); // Contenedor de opciones para frecuencia personalizada
    const opcionDias = document.getElementById('opcionDias'); // Radio buttoms
    const opcionCadaXDias = document.getElementById('opcionCadaXDias');
    const diasContainer = document.getElementById('diasSemanaContainer'); // Contenedores de los dias de la semana y cada cuantos dias
    const cadaCuantosContainer = document.getElementById('cadaCuantosDiasContainer');

    /*
    * Obtiene el valor seleccionado de frecuencia (convertido a número), 
    * tambien oculta todos los contenedores
    */
    function actualizarCampos() {
        const frecuenciaId = parseInt(frecuenciaSelect.value);
        opcionesPersonalizada.style.display = 'none';
        diasContainer.style.display = 'none';
        cadaCuantosContainer.style.display = 'none';

        /* 
        * Si el id es 4 (personalizada), entonces muestra el contenedor de opciones
        * y muestra el campo correspondiente a la opción seleccionada
        */
        if (frecuenciaId === 4) {
            opcionesPersonalizada.style.display = 'block';
            if (opcionDias.checked) {
                diasContainer.style.display = 'block';
            } else if (opcionCadaXDias.checked) {
                cadaCuantosContainer.style.display = 'block';
            }
        }
    }

    // Escuchan cambios en el dropdown de frecuencia y de los radio buttoms
    frecuenciaSelect.addEventListener('change', actualizarCampos);

    if (opcionDias && opcionCadaXDias) {
        opcionDias.addEventListener('change', actualizarCampos);
        opcionCadaXDias.addEventListener('change', actualizarCampos);
    }

    // Ejecuta la función al cargar la página para mostrar el estado inicial correcto
    actualizarCampos();
});
