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

    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validación de campos
            if (empty($_POST['id']) || 
            empty($_POST['id_habito']) || 
            empty($_POST['cantidad_objetivo']) || 
            empty($_POST['id_periodo'])) {
                $_SESSION['error'] = "Todos los campos son obligatorios.";
                header("Location: editar_meta.php?id=" . $_POST['id']);
                exit();
            }

            if (!is_numeric($_POST['cantidad_objetivo']) || $_POST['cantidad_objetivo'] <= 0) {
                $_SESSION['error'] = "La cantidad objetivo debe ser un número positivo.";
                header("Location: editar_meta.php?id=" . $_POST['id']);
                exit();
            }

            // Verificar que la meta pertenece al usuario
            $verificar = $pdo->prepare("SELECT m.id FROM metas m 
                                        JOIN habitos h ON m.id_habito = h.id 
                                        WHERE m.id = :id AND h.id_usuario = :id_usuario");
            $verificar->execute([
                ':id' => $_POST['id'],
                ':id_usuario' => $id_usuario
            ]);

            if ($verificar->rowCount() === 0) {
                $_SESSION['error'] = "No tienes permiso para editar esta meta.";
                header("Location: index.php");
                exit();
            }

            // Actualizar meta
            $sql = "UPDATE metas SET 
                    id_habito = :id_habito,
                    cantidad_objetivo = :cantidad_objetivo,
                    id_periodo = :id_periodo
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_habito' => $_POST['id_habito'],
                ':cantidad_objetivo' => $_POST['cantidad_objetivo'],
                ':id_periodo' => $_POST['id_periodo'],
                ':id' => $_POST['id']
            ]);

            $_SESSION['exito'] = 'Meta actualizada exitosamente';
            header("Location: index.php");
            exit();
        }

        // Obtener meta para edición
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("SELECT m.* FROM metas m
                                JOIN habitos h ON m.id_habito = h.id
                                WHERE m.id = :id AND h.id_usuario = :id_usuario");
            $stmt->execute([':id' => $id, ':id_usuario' => $id_usuario]);
            $meta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$meta) {
                $_SESSION['error'] = "Meta no encontrada o no tienes permiso para editarla.";
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "ID de meta inválido.";
            header("Location: index.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: index.php");
        exit();
    }

    $pageTitle = "Editar meta";
    $seccionActual = 'metas';
    require_once '../../includes/header.php';
?>

<main class="content container mt-4">
    <h1 class="text-center">Editar meta</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" class="col-md-6 mx-auto">
        <input type="hidden" name="id" value="<?= htmlspecialchars($meta['id']) ?>">
        
        <div class="mb-3">
            <label class="form-label">Hábito:</label>
            <select name="id_habito" class="form-select" required>
                <option value="">Seleccione un hábito</option>
                <?php foreach ($habitos as $habito): ?>
                    <option value="<?= htmlspecialchars($habito['id']) ?>" 
                        <?= ($habito['id'] == $meta['id_habito']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($habito['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Cantidad objetivo:</label>
            <input type="number" name="cantidad_objetivo" class="form-control" 
                   value="<?= htmlspecialchars($meta['cantidad_objetivo']) ?>" min="1" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Periodo:</label>
            <select name="id_periodo" class="form-select" required>
                <option value="">Seleccione un periodo</option>
                <?php foreach ($periodos as $periodo): ?>
                    <option value="<?= htmlspecialchars($periodo['id']) ?>" 
                        <?= ($periodo['id'] == $meta['id_periodo']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($periodo['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="index.php" class="btn btn-secondary me-md-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</main>

<?php 
    require_once '../../includes/footer.php'; 
?>