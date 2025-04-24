<?php

    require_once 'secret.php';

    $host = 'localhost';
    $dbname = 'habitos_db';
    $usuario = 'root';
    $password = $dbpassword; //Contraseña protegida

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {  
        die("Error de conexión: " . $e->getMessage());
    }

?>