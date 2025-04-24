<?php

    session_start();
    require_once '../../includes/verificar_sesion.php';

    //Para que no puedan entrar con el enlace sin iniciar sesión o sin ser admins
    verificarSesion();
    verificarSesionAdmin();

    // Variables iniciales
    $usuario = [];
    $roles = [];

    try {
        require_once '../../includes/conexion.php';

        /*
        * Cargamos los datos necesarios para los menús desplegables (roles y estatus).
        * Esto permite que el usuario edite el rol y el estatus en el formulario.
        */
        $roles = $pdo->query("SELECT id, nombre FROM roles")->fetchAll(PDO::FETCH_ASSOC);

        /*
        * Si se ha enviado el formulario con método POST, procesamos la edición.
        */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtenemos los datos del formulario        
            $id         = $_POST['id'];
            $nombre    = $_POST['nombre'];
            $correo     = $_POST['correo'];
            $id_rol     = $_POST['id_rol'];

            // Validar campos
            if (empty($_POST['id']) || 
                empty($_POST['nombre']) || 
                empty($_POST['correo']) || 
                empty($_POST['id_rol'])) {
                $_SESSION['error'] = "Todos los campos deben llenarse.";
                header("Location: editar_usuario.php?id=$id");
                exit();
            }

            /*
            * Verificamos que el correo electrónico no esté en uso por otro usuario
            * con un ID distinto al actual.
            */
            $check = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo AND id != :id");
            $check->execute([':correo' => $correo, ':id' => $id]);

            if ($check->rowCount() > 0) {
                $_SESSION['error'] = "El correo ya esta en uso por otro usuario.";
                header("Location: editar_usuario.php?id=$id"); 
                exit();
            } else {
                // Si no hay conflicto, actualizamos los datos del usuario         
                $sql = "UPDATE usuarios SET 
                            nombre = :nombre,
                            correo = :correo,
                            id_rol = :id_rol
                        WHERE id = :id";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nombre'    => $nombre,
                    ':correo'     => $correo,
                    ':id_rol'     => $id_rol,
                    ':id'         => $id
                ]);

                // Redirigimos para evitar reenvío del formulario al refrescar (patrón PRG)
                header("Location: editar_usuario.php?id=$id&actualizado=1");
                exit;
            }

            // Si hubo error, recargamos los datos del usuario para mostrarlos
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Si es una petición GET con un ID válido, cargamos los datos del usuario
        elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si no se encuentra el usuario, mostramos un mensaje
            if (!$usuario) {
                $_SESSION["error"] = "Usuario no encontrado.";
                header("Location: index.php");
                exit();
            }
        } else {
            // Si no se envió un ID válido, mostramos mensaje de error   
            $_SESSION["error"] = "ID inválido."; 
            header("Location: index.php");
            exit(); 
        }

        // Mensaje de éxito si venimos de la redirección después de guardar cambios
        if (isset($_GET['actualizado']) && $_GET['actualizado'] == 1) {
            $_SESSION["exito"] = "¡Usuario actualizado correctamente!";
            header("Location: index.php");
            exit();
        }

    } catch (PDOException $e) {
        // Capturamos errores de PDO y los mostramos  
        $_SESSION["error"] = "Error: " . $e->getMessage();
        header("Location: index.php");
        exit();  
    }

    $pageTitle = "Editar usuario"; 
    $seccionActual = 'usuarios';
    require_once '../../includes/header.php';
?>

<main class="content container mt-4">
    <h1 class="text-center">Editar Usuario</h1>

    <!-- Mostrar mensajes de error-->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($usuario)): ?>
        <form method="POST" action="editar_usuario.php" class="col-md-6 mx-auto">
            <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">
            
            <div class="mb-3">
                <label class="form-label">Nombre:</label>
                <input type="text" name="nombre" class="form-control" 
                       value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Correo:</label>
                <input type="email" name="correo" class="form-control" 
                       value="<?= htmlspecialchars($usuario['correo']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Rol:</label>
                <select name="id_rol" class="form-select" required>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= $rol['id'] ?>" 
                            <?= $rol['id'] == $usuario['id_rol'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rol['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="index.php" class="btn btn-secondary me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    <?php endif; ?>
</main>

<?php
require_once '../../includes/footer.php';
?>