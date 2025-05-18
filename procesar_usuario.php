<?php
    
    session_start(); 
    // Por seguridad solo se ejecuta cuando se esta usando POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('HTTP/1.1 403 Forbidden');
        exit('Acceso no permitido');
    }

    require_once './includes/conexion.php';

    // Mostrar errores (solo en entorno de desarrollo)
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    try {

        // En caso de saltarse la validación con HTML, volvemos a verificar con PHP que todos los campos obligatorios estén llenos
        if (
            empty($_POST['nombre']) ||
            empty($_POST['usuario']) ||
            empty($_POST['password']) ||
            empty($_POST['confirm_password'])
        ) {
            $_SESSION['error'] = "Todos los campos deben de llenarse.";
            header("Location: sign_up.php");
            exit();
        }

        // Validar la longitud del nombre de usuario
        if (strlen($_POST['nombre']) > 20) {
            $_SESSION['error'] = "El nombre de usuario no debe de ser mayor a 20 caracteres.";
            header("Location: sign_up.php");
            exit();
        }

        /*
        * Validación adicional de contraseñas del lado del servidor
        * Esto es importante incluso si ya se validó en el navegador con JavaScript,
        * porque un usuario puede modificar o saltarse el JS.
        */
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Las contraseñas no coinciden.";
            header("Location: sign_up.php");
            exit();
        }

        // Validar la longitud de la contraseña
        if (strlen($password) < 8 || strlen($password) > 20) {
            $_SESSION['error'] = "La contraseña debe de ser de entre 8 y 20 caracteres.";
            header("Location: sign_up.php");
            exit();
        }

        // Validar la complejidad de la contraseña usando expresiones regulares
        $patrones = [
            '/[A-Z]/' => "al menos una letra mayúscula",
            '/[a-z]/' => "al menos una letra minúscula",
            '/[0-9]/' => "al menos un número",
            '/[\W_]/' => "al menos un carácter especial",
        ];

        foreach ($patrones as $patron => $falta_complejidad) {
            if (!preg_match($patron, $password)) {
                $_SESSION['error'] = "La contraseña debe contener $falta_complejidad";
                header("Location: sign_up.php");
                exit();
            }
        }

        // Asignamos los datos del formulario a variables PHP
        $nombre = $_POST['nombre'];
        $usuario = $_POST['usuario'];

        // Ciframos la contraseña antes de guardarla (muy importante para seguridad)        
        $password = password_hash($password, PASSWORD_DEFAULT);

        // Valor por defecto para el rol (usuario)        
        $id_rol = 2;

        // Validar la longitud del correo
        if (strlen($usuario) > 100) {
            $_SESSION['error'] = "El correo no debe de tener mas de 100 caracteres.";
            header("Location: sign_up.php");
            exit();
        }

        // Verificamos si el correo cumple con el formato de un correo
        if (!filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Ingresa un correo válido.";
            header("Location: sign_up.php");
            exit();
        }

        // Verificamos si el correo ya está registrado en la base de datos
        $verificar = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo");
        $verificar->execute([':correo' => $usuario]);

        if ($verificar->rowCount() > 0) {
            $_SESSION['error'] = "El correo ya esta registrado."; // Se quito el olvidaste tu contraseña por el htmlspecialchars ya que lo pasa como texto plano
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

        $_SESSION['exito'] = 'Te haz registrado exitosamente. Inicia sesión.';
        header("Location: login.php");
        exit();

    } catch (PDOException $e) {
        // Si ocurre un error con la base de datos, lo mostramos  
        $_SESSION['error'] = "Error con la base de datos. Intentalo más tarde.";
        header("Location: sign_up.php");
        exit();
    }
?>