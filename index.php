<?php

    session_start();

    // Verifica si el usuario está logueado
    if (isset($_SESSION['usuario_id'])) {
        // Si está autenticado, redirige al home
        header("Location: ./home/");
        exit();
    } else {
        // Si no a iniciado sesión, redirige al login
        header("Location: login.php");
        exit();
    }

?>