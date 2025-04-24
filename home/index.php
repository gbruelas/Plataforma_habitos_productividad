<?php

    session_start();

    require_once '../includes/verificar_sesion.php';
    //Para que no puedan entrar con el enlace sin iniciar sesión
    verificarSesion();

    define('INCLUIDO', true);
    $pageTitle = "Inicio"; 
    $seccionActual = 'inicio';
    require_once '../includes/header.php';
?>

<main class="content container mt-4">
    <p>Bienvenido</p>
</main>

<?php
    require_once '../includes/footer.php';
?>