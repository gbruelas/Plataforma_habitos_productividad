<?php

    session_start();
    require_once '../../includes/verificar_sesion.php';
    
    // Para que no puedan entrar con el enlace sin iniciar sesión
    verificarSesion();

    require_once '../../includes/conexion.php';

    $id_usuario = $_SESSION['usuario_id'];

    // Obtener hábitos del usuario
    $habitos = $pdo->prepare("SELECT id, nombre FROM habitos WHERE id_usuario = :id_usuario");
    $habitos->execute([':id_usuario' => $id_usuario]);
    $habitos = $habitos->fetchAll(PDO::FETCH_ASSOC);

    // Obtener periodos
    $periodos = $pdo->query("SELECT id, nombre FROM periodos ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Validación de campos
            if (empty($_POST['id_habito']) || 
            empty($_POST['cantidad_objetivo']) || 
            empty($_POST['id_periodo'])) {
                $_SESSION['error'] = "Todos los campos son obligatorios.";
                header("Location: agregar_meta.php");
                exit();
            }

            // Validación de número y número positivo
            if (!is_numeric($_POST['cantidad_objetivo']) || $_POST['cantidad_objetivo'] <= 0) {
                $_SESSION['error'] = "La cantidad objetivo debe ser un número positivo.";
                header("Location: agregar_meta.php");
                exit();
            }

            // Verificar que el hábito pertenece al usuario
            $verificar = $pdo->prepare("SELECT id FROM habitos WHERE id = :id_habito AND id_usuario = :id_usuario");
            $verificar->execute([
                ':id_habito' => $_POST['id_habito'],
                ':id_usuario' => $id_usuario
            ]);

            if ($verificar->rowCount() === 0) {
                $_SESSION['error'] = "El hábito seleccionado no es válido.";
                header("Location: agregar_meta.php");
                exit();
            }

            // Insertar nueva meta
            $sql = "INSERT INTO metas (id_habito, cantidad_objetivo, id_periodo) 
                    VALUES (:id_habito, :cantidad_objetivo, :id_periodo)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_habito' => $_POST['id_habito'],
                ':cantidad_objetivo' => $_POST['cantidad_objetivo'],
                ':id_periodo' => $_POST['id_periodo']
            ]);

            $_SESSION['exito'] = 'Meta creada exitosamente';
            header("Location: index.php");
            exit();

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error con la base de datos. Intentalo más tarde.";
            header("Location: agregar_meta.php");
            exit();
        }
    }

    $pageTitle = "Agregar meta";
    $seccionActual = 'metas';
    require_once '../../includes/header.php';

?>

<main class="content container mt-4">
    <h1 class="text-center">Crear nueva meta</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" class="col-md-6 mx-auto">
        <div class="mb-3">
            <label class="form-label">Hábito:</label>
            <select name="id_habito" class="form-select" required>
                <option value="">Seleccione un hábito</option>
                <?php foreach ($habitos as $habito): ?>
                    <option value="<?= htmlspecialchars($habito['id']) ?>">
                        <?= htmlspecialchars($habito['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Cantidad objetivo:</label>
            <input type="number" name="cantidad_objetivo" class="form-control" min="1" required>
            <small class="text-muted">Número de veces que deseas completar el hábito</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Periodo:</label>
            <select name="id_periodo" class="form-select" required>
                <option value="">Seleccione un periodo</option>
                <?php foreach ($periodos as $periodo): ?>
                    <option value="<?= htmlspecialchars($periodo['id']) ?>">
                        <?= htmlspecialchars($periodo['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-3">
            <a href="index.php" class="btn btn-secondary me-md-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</main>

<?php 
    require_once '../../includes/footer.php'; 
?>