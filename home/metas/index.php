<?php

session_start();
//Para que no puedan entrar con el enlace sin iniciar sesión
if(!isset($_SESSION['usuario_id'])){
    header("Location: ../");
    exit();
} 
define('INCLUIDO', true);
$seccionActual = 'metas';
require_once '../../includes/header.php';
?>

<main class="content container mt-4">
    <p>Bienvenido</p>
</main>
<?php
require_once '../../includes/footer.php';
?>