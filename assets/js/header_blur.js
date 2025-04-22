// Espera a que el documento HTML esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Selecciona el header usando su ID
    const header = document.getElementById('header-blur');
    // Añade un event listener para el evento scroll en la ventana
    window.addEventListener('scroll', function() {
        // Comprueba si el desplazamiento vertical es mayor a 10 píxeles
        if (window.scrollY > 10) {
            // Si es verdadero, añade la clase 'header-scroll-blur'
            header.classList.add('header-scroll-blur');
        } else {
            // Si es falso, remueve la clase 'header-scroll-blur'
            header.classList.remove('header-scroll-blur');
        }
    });
});