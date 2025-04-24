<?php

    session_start();
    require_once './includes/verificar_sesion.php';
    // Si el usuario tiene una sesión iniciada debe de cerrarla primero
    verificarSesionCerrada();

    require_once './includes/conexion.php';

    // En caso de saltarse la validación con HTML, volvemos a verificar con PHP que todos los campos obligatorios estén llenos
    if (empty($_GET['token'])) {
        $_SESSION['error_recuperacion'] = "No se proporciono un token.";
        header("Location: forgot_password.php");
        exit();
    }

    // Recibe el token con la URL o una cadena vacia si no se proporciona
    $token = $_GET['token'];

    if ($token) {
        // Se prepara la consulta para buscar el token en la base de datos
        $stmt = $pdo->prepare("SELECT id_usuario, expira_token FROM recuperacion_password WHERE token = ?");
        $stmt->execute([$token]);
        $recuperacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Si el token es valido
        if ($recuperacion) {
            $fecha_expiracion = strtotime($recuperacion['expira_token']);
            // Se verifica si el token expiro, en caso de que si se manda el error
            if ($fecha_expiracion < time()) {
                $_SESSION["error"] = "El enlace ha expirado.";
                header("Location: forgot_password.php");
                exit();
            }
        } else {
            // Si el token no se encontro en la base de datos
            $_SESSION["error"] = "Token inválido.";
            header("Location: forgot_password.php");
            exit();
        }
    } else {
        // Si no hay ningun token en la URL
        $_SESSION["error"] = "Token no proporcionado.";
        header("Location: forgot_password.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¿Olvidaste tu contraseña?</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body>

<!-- Restablecer contraseña con un card -->
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card border-0 shadow-lg" style="width: 26rem;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-dark">RESTABLECER CONTRASEÑA</h2>
                <p class="text-muted">Ingresa una contraseña nueva</p>

                <!-- Mostrar mensajes de error-->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

            </div>

            <!-- Formulario de restablecimiento de contraesña -->
            <form action="procesar_restablecer.php" method="post">

                <div class="mb-3 ">
                    <input type="hidden" class="form-control form-control-lg"  name="token" value="<?= htmlspecialchars($token) ?>">
                </div>

                <div class="mb-3 ">
                    <label for="password" class="form-label">Nueva contraseña</label>
                    <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Nueva contraseña" required>
                </div>
                
                <div class="mb-4 ">
                    <label for="password" class="form-label">Confirmar nueva contraseña</label>
                    <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" placeholder="Confirmar nueva contraseña" required>
                </div>
                
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Restablecer contraseña
                    </button>
                </div>
                
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>

</body>
</html>