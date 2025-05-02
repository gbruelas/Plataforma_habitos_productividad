<?php 

    session_start(); 
    require_once './includes/verificar_sesion.php';
    // Si el usuario tiene una sesión iniciada debe de cerrarla primero
    verificarSesionCerrada();
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="./assets/css/styles.css" rel="stylesheet">
</head>
<body>

<!-- Sign up con un card -->
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card border-0 shadow-lg" style="width: 26rem;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-dark">REGISTRARSE</h2>
                <p class="text-muted">Ingresa tus datos para continuar</p>

                <!-- Mostrar mensajes de error-->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

            </div>

            <!-- Formulario de sign up -->
            <form action="procesar_usuario.php" method="post">
                
                <div class="mb-3 ">
                    <label for="nombre" class="form-label">Nombre de usuario</label>
                    <input type="text" class="form-control form-control-lg" id="nombre" name="nombre" placeholder="Nombre de usuario" required>
                </div>

                <div class="mb-3 ">
                    <label for="usuario" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control form-control-lg" id="usuario" name="usuario" placeholder="Correo" required>
                </div>
                
                <div class="mb-3 ">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Contraseña" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Confirmar contraseña</label>
                    <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" placeholder="Contraseña" required>
                </div>
                
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Registrarse
                    </button>
                </div>
                
            </form>
        </div>
        
        <div class="card-footer bg-gray border-0 text-center py-3">
            <p class="text-muted mb-0">¿Ya tienes una cuenta? <a href="login.php" class="text-primary">Inicia sesión</a></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>

</body>
</html>