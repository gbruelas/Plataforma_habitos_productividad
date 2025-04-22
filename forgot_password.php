<?php 
    session_start(); 
    //Por si tiene iniciada una sesión obligarlo a que la cierre primero
    if(isset($_SESSION['usuario_id'])){
        header("Location: ./home/");
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

<!-- Login con un card -->
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card border-0 shadow-lg" style="width: 26rem;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-dark">¿OLVIDASTE TU CONTRASEÑA?</h2>
                <p class="text-muted">Ingresa el correo de tu cuenta</p>

                <!-- Mostrar mensajes de error de la recuperación-->
                <?php if (isset($_SESSION['error_recuperacion'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_recuperacion']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_recuperacion']); ?>
                <?php endif; ?>

                <!-- Mostrar mensajes de exito de la recuperación-->
                <?php if (isset($_SESSION['exito_recuperacion'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['exito_recuperacion']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['exito_recuperacion']); ?>
                <?php endif; ?>

            </div>

            <!-- Formulario de forgot password -->
            <form action="procesar_recuperacion.php" method="post">
                
                <div class="mb-4 ">
                    <label for="usuario" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control form-control-lg" id="usuario" name="usuario" placeholder="Correo" required>
                </div>
                
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Enviar correo
                    </button>
                </div>
                
            </form>
        </div>
        
        <div class="card-footer bg-gray border-0 text-center py-3">
            <p class="text-muted mb-0">¿Recuerdas tu contraseña? <a href="login.php" class="text-primary">Inicia sesión</a></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>

</body>
</html>