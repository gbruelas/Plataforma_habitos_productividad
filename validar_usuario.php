<?php
    session_start(); 
    // Por seguridad solo se ejecuta cuando se esta usando POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('HTTP/1.1 403 Forbidden');
        exit('Acceso no permitido');
    }

    require_once './includes/conexion.php';

    //Desde el formulario
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    //Conexión a la base de datos
    $sql = "SELECT * FROM usuarios WHERE correo = :correo";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['correo' => $usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        if (password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['rol'] = $usuario['id_rol']; // Guardo el rol para usarlo en el menu
            header("Location: ./home/");
            exit();
        } else {
            // Contraseña incorrecta, se redirige al login de nuevo con el error
            $_SESSION['error'] = 'Contraseña incorrecta';
            header("Location: login.php");
            exit();
        }
    } else {
        // Usuario no existe, se redirige al login de nuevo con el error
        $_SESSION['error'] = 'Credenciales incorrectas';
        header("Location: login.php");
        exit();
        }

    ?>