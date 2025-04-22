<?php
    
    session_start(); 
    // Por seguridad solo se ejecuta cuando se esta usando POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('HTTP/1.1 403 Forbidden');
        exit('Acceso no permitido');
    }
    define('INCLUIDO', true);
    require_once './includes/conexion.php';

    // Mostrar errores (solo en entorno de desarrollo)
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    try {

        /*
        * Validación adicional de contraseñas del lado del servidor
        * Esto es importante incluso si ya se validó en el navegador con JavaScript,
        * porque un usuario puede modificar o saltarse el JS.
        */
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $_SESSION['error_sign_up'] = "Las contraseñas no coinciden.";
            header("Location: sign_up.php");
            exit();
        }

        // En caso de saltarse la validación con HTML, volvemos a verificar con PHP que todos los campos obligatorios estén llenos
        if (
            empty($_POST['nombre']) ||
            empty($_POST['usuario']) ||
            empty($_POST['password']) ||
            empty($_POST['confirm_password'])
        ) {
            $_SESSION['error_sign_up'] = "Todos los campos deben de llenarse.";
            header("Location: sign_up.php");
            exit();
        }

        // Asignamos los datos del formulario a variables PHP
        $nombre = $_POST['nombre'];
        $usuario = $_POST['usuario'];

        // Ciframos la contraseña antes de guardarla (muy importante para seguridad)        
        $password = password_hash($password, PASSWORD_DEFAULT);

        // Valor por defecto para el rol (usuario)        
        $id_rol = 2;

        // Verificamos si el correo ya está registrado en la base de datos
        $verificar = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo");
        $verificar->execute([':correo' => $usuario]);

        if ($verificar->rowCount() > 0) {
            $_SESSION['error_sign_up'] = "El correo ya esta registrado. <a href='forgot_password.php'class='text-danger'>¿Olvidaste tu contraseña?</a>";
            header("Location: sign_up.php");
            exit();
        }

        // Si el correo no existe, insertamos el nuevo usuario usando consulta preparada
        $sql = "INSERT INTO usuarios (nombre, correo, password, id_rol, fecha_registro)
                VALUES (:nombre, :correo, :password, :id_rol, NOW())"; //El now es para que se registre automaticamente la hora exacta en que se registro

        $stmt = $pdo->prepare($sql);

        // Ejecutamos la consulta pasando los datos de forma segura (evita inyección SQL)
        $stmt = $stmt->execute([
            ':nombre'    => $nombre,
            ':correo'     => $usuario,
            ':password'   => $password,
            ':id_rol'     => $id_rol,
        ]);

        $_SESSION['usuario_registrado'] = 'Te haz registrado exitosamente. Inicia sesión.';
        header("Location: login.php");
        exit();

    } catch (PDOException $e) {
        // Si ocurre un error con la base de datos, lo mostramos        
        die ("Error PDO: " . $e->getMessage());
    }
?>