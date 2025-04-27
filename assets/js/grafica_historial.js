// Espera a que el DOM este listo
document.addEventListener("DOMContentLoaded", function () {
    const grafica = document.getElementById('graficaCumplimiento'); // Busca el canvas que cree en seguimiento/index.php por su ID

    // Comprueba que exista el elemento y que los datos esten definidos (los que paso desde php)
    if (grafica && typeof window.graficaData !== 'undefined') {
        // Se crea la tabla, su tipo, los datos y los estilos
        new Chart(grafica, {
            type: 'bar',
            data: {
                labels: ['Cumplidos', 'Pendientes'], // Nombres de las barras
                datasets: [{
                    label: 'Hábitos',
                    data: window.graficaData, // Usa el array window.graficaData que proviene de php
                    backgroundColor: ['#198754', '#dc3545'], // Color de barra verde para cumplidos
                    borderColor: ['#145c3f', '#8a1b1b'], // Color de barra rojo para pendientes
                    borderWidth: 1
                }]
            },
            // Personalizo el comportamiento y la apariencia
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }, // Oculta la leyenda
                    tooltip: { // Muestra x cantidad de hábitos al pasar el mouse sobre la barra
                        callbacks: {
                            label: (context) => `${context.parsed.y} hábitos`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true, // Fuerza que el eje Y empiece en 0
                        ticks: {
                            stepSize: 1, // Muestra números enteros
                            precision: 0 // Sin decimales
                        }
                    }
                }
            }
        });
    }
});
