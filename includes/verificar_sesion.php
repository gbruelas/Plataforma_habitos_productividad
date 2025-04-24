<?php

    function verificarSesion(){
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            header('Location: /proyecto_integrador/login.php');
            exit();
        }
    }

    function verificarSesionAdmin(){
        if($_SESSION['rol'] != '1'){
            header('HTTP/1.0 403 Forbidden');
            exit('Acceso no permitido');
        } 
    }

    function verificarSesionCerrada(){
        if(isset($_SESSION['usuario_id'])){
            header("Location: ./home/");
            exit();
        } 
    }

?>