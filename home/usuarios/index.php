<?php

session_start();
//Para que no puedan entrar con el enlace sin iniciar sesión
if(!isset($_SESSION['usuario_id'])){
    header("Location: ../");
    exit();
} 
//Para que no puedan entrar tampoco si no son admins
if($_SESSION['rol'] != '1'){
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso no permitido');
} 
define('INCLUIDO', true);
$seccionActual = 'usuarios';
require_once '../../includes/header.php';
?>

<main class="content container mt-4">
    <p>Bienvenido</p>
</main>
<?php
require_once '../../includes/footer.php';
?>