<?php

    session_start(); 
    // Por seguridad solo se ejecuta cuando se esta usando POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('HTTP/1.1 403 Forbidden');
        exit('Acceso no permitido');
    }

    require_once './includes/conexion.php';

    // En caso de saltarse la validación con HTML, volvemos a verificar con PHP que todos los campos obligatorios estén llenos
    if (
        empty($_POST['token']) ||
        empty($_POST['password']) ||
        empty($_POST['confirm_password'])
    ) {
        $_SESSION['error'] = "Todos los campos deben de llenarse.";
        header("Location: restablecer_password.php?token=" . urlencode($token));
        exit();
    }

    $token = $_POST["token"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Verificar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        $_SESSION["error"] = "Las contraseñas no coinciden.";
        header("Location: restablecer_password.php?token=" . urlencode($token));
        exit();
    }

    // Validar la longitud de la contraseña
    if (strlen($password) < 8 || strlen($password) > 20) {
        $_SESSION['error'] = "La contraseña debe de ser de entre 8 y 20 caracteres.";
        header("Location: restablecer_password.php?token=" . urlencode($token));
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
            header("Location: restablecer_password.php?token=" . urlencode($token));
            exit();
        }
    }

    // Verificar si el token existe y no ha expirado
    $stmt = $pdo->prepare("SELECT id_usuario FROM recuperacion_password WHERE token = ? AND expira_token > NOW()");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $id_usuario = $usuario["id_usuario"];
        $nueva_password = password_hash($password, PASSWORD_DEFAULT);

        // Actualizar contraseña del usuario
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt->execute([$nueva_password, $id_usuario]);

        // Eliminar el token utilizado
        $stmt = $pdo->prepare("DELETE FROM recuperacion_password WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);

        $_SESSION["exito"] = "¡Contraseña actualizada correctamente!";
        header("Location: login.php");
    } else {
        $_SESSION["exito"] = "El enlace es inválido o ha expirado.";
        header("Location: forgot_password.php");
    }
    exit();
?>