<?php

    session_start(); 
    // Por seguridad solo se ejecuta cuando se esta usando POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('HTTP/1.1 403 Forbidden');
        exit('Acceso no permitido');
    }
    define('INCLUIDO', true);
    require_once './includes/conexion.php';
    require_once './includes/secret.php';

    // Uso phpmailer como servicio para enviar los correos
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require './PHPMailer/Exception.php';
    require './PHPMailer/PHPMailer.php';
    require './PHPMailer/SMTP.php';

    // En caso de saltarse la validación con HTML, volvemos a verificar con PHP que todos los campos obligatorios estén llenos
    if (empty($_POST['usuario'])) {
        $_SESSION['error_recuperacion'] = "Debes de ingresar un correo.";
        header("Location: forgot_password.php");
        exit();
    }

    $correo = $_POST["usuario"];
    
    // Se busca el correo recibido por el formulario en la base de datos para ver si si existe, con una consulta preparada
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        // Si se encontro el usuario, se guarda su ID, se genera el token y se define la fecha de expiración de este (1 hora despues de ser enviado)
        $id_usuario = $usuario["id"];
        $token = bin2hex(random_bytes(32));
        $expiracion = date("Y-m-d H:i:s", strtotime("+1 hour"));
    
        // Elimina tokens anteriores
        $del = $pdo->prepare("DELETE FROM recuperacion_password WHERE id_usuario = ?");
        $del->execute([$id_usuario]);
    
        // Inserta nuevo token y su fecha de expiracion
        $stmt = $pdo->prepare("INSERT INTO recuperacion_password (id_usuario, token, expira_token) VALUES (?, ?, ?)");
        $stmt->execute([$id_usuario, $token, $expiracion]);
    
        // En esta variable se guarda lo que sera el enlace de recuperación, con la variable token
        $link = "http://" . $_SERVER['HTTP_HOST'] . "/proyecto_integrador/restablecer_password.php?token=$token";
    
        // Instancio phpmailer y configuro el servicio
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'no.reply.phyp@gmail.com';
            $mail->Password = $mailpassword; //Contraseña protegida
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
    
            $mail->setFrom('no.reply.phyp@gmail.com', 'Plataforma de hábitos y productividad');
            $mail->addAddress($correo); // Este es el destinatario, en este caso, el correo que ingreso el usuario medianet el formulario (en caso de estar en la base de datos)
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de contraseña';
            $mail->Body = "Haz clic <a href='$link'>aquí</a> para restablecer tu contraseña.<br>Si tú no solicitaste esto, ignora este correo.";
    
            // Se envía el correo y se muestra un mensaje según sea el caso
            $mail->send();
            $_SESSION["exito_recuperacion"] = "Se ha enviado un enlace de recuperación a tu correo.";
        } catch (Exception $e) {
            $_SESSION["error_recuperacion"] = "Error al enviar el correo: {$mail->ErrorInfo}.";
        }
    } else {
        $_SESSION["error_recuperacion"] = "El correo no está registrado.";
    }
    // Regresamos a la pagina de olvidaste tu contraseña
    header("Location: forgot_password.php");
    exit();
?>