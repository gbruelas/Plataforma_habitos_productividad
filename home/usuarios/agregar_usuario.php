<?php

    session_start();
    require_once '../../includes/verificar_sesion.php';

    //Para que no puedan entrar con el enlace sin iniciar sesión o sin ser admins
    verificarSesion();
    verificarSesionAdmin();

    require_once '../../includes/conexion.php';

    // Obtener roles para el select
    $roles = $pdo->query("SELECT id, nombre FROM roles")->fetchAll(PDO::FETCH_ASSOC);

    // Procesar formulario POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Validar campos
            if (empty($_POST['nombre']) || 
                empty($_POST['correo']) || 
                empty($_POST['password']) || 
                empty($_POST['confirm_password']) || 
                empty($_POST['id_rol'])) {
                $_SESSION['error'] = "Todos los campos deben llenarse.";
                header("Location: agregar_usuario.php");
                exit();
            }

            // Verificar coincidencia de contraseñas
            if ($_POST['password'] !== $_POST['confirm_password']) {
                $_SESSION['error'] = "Las contraseñas no coinciden.";
                header("Location: agregar_usuario.php");
                exit();
            }

            // Validar la longitud de la contraseña
            if (strlen($_POST['password']) < 8 || strlen($_POST['password']) > 20) {
                $_SESSION['error'] = "La contraseña debe de ser de entre 8 y 20 caracteres.";
                header("Location: agregar_usuario.php");
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
                if (!preg_match($patron, $_POST['password'])) {
                    $_SESSION['error'] = "La contraseña debe contener $falta_complejidad";
                    header("Location: agregar_usuario.php");
                    exit();
                }
            }

            // Verificar si el correo ya existe
            $verificar = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo");
            $verificar->execute([':correo' => $_POST['correo']]);

            if ($verificar->rowCount() > 0) {
                $_SESSION['error'] = "El correo ya está registrado.";
                header("Location: agregar_usuario.php");
                exit();
            }

            // Insertar nuevo usuario
            $sql = "INSERT INTO usuarios (nombre, correo, password, id_rol, fecha_registro)
                    VALUES (:nombre, :correo, :password, :id_rol, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $_POST['nombre'],
                ':correo' => $_POST['correo'],
                ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                ':id_rol' => $_POST['id_rol']
            ]);

            $_SESSION['exito'] = 'Usuario registrado exitosamente';
            header("Location: index.php");
            exit();

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al registrar usuario: " . $e->getMessage();
            header("Location: agregar_usuario.php");
            exit();
        }
    }

    $seccionActual = 'usuarios';
    $pageTitle = "Agregar usuario"; 
    require_once '../../includes/header.php';
?>

<main class="content container mt-4">
    <h1 class="text-center">Agregar nuevo usuario</h1>
    
    <!-- Mostrar mensajes de error-->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="agregar_usuario.php" class="col-md-6 mx-auto">
        <div class="mb-3">
            <label class="form-label">Nombre:</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
                
        <div class="mb-3">
            <label class="form-label">Correo:</label>
            <input type="email" name="correo" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña:</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirmar contraseña:</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
                
        <div class="mb-3">
            <label class="form-label">Rol:</label>
            <select name="id_rol" class="form-select" required>
                <?php foreach ($roles as $rol): ?>
                    <option value="<?= $rol['id'] ?>">
                        <?= htmlspecialchars($rol['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
                
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="index.php" class="btn btn-secondary me-md-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</main>

<?php
require_once '../../includes/footer.php';
?>