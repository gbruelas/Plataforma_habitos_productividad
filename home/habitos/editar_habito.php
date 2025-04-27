<?php

    session_start();
    require_once '../../includes/verificar_sesion.php';

    // Para que no puedan entrar con el enlace sin iniciar sesión
    verificarSesion();

    $habito = [];
    $frecuencias = [];
    $dias = [];
    $id_usuario = $_SESSION['usuario_id'];

    try {
        require_once '../../includes/conexion.php';

        // Obtener los datos para el formulario
        $frecuencias = $pdo->query("SELECT id, nombre FROM frecuencias")->fetchAll(PDO::FETCH_ASSOC);
        $dias = $pdo->query("SELECT id, nombre FROM dias")->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar que el hábito pertenece al usuario
            $id = $_POST['id'];
            $check = $pdo->prepare("SELECT id FROM habitos WHERE id = ? AND id_usuario = ?");
            $check->execute([$id, $id_usuario]);
            
            if ($check->rowCount() === 0) {
                $_SESSION['error'] = "No tienes permiso para editar este hábito";
                header("Location: index.php");
                exit();
            }

            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'];
            $id_frecuencia = $_POST['id_frecuencia'];
            $opcion_personalizada = $_POST['opcion_personalizada'] ?? '';
            $cada_cuantos_dias = null;
            $dias_seleccionados = [];
            
            // Determinar qué opción de personalizada se selecciono
            if ($id_frecuencia == 4) {
                if ($opcion_personalizada == 'dias') {
                    $dias_seleccionados = $_POST['dias'] ?? [];
                    if (empty($dias_seleccionados)) {
                        $_SESSION['error'] = "Debes seleccionar al menos un día de la semana";
                        header("Location: editar_habito.php?id=$id");
                        exit;
                    }
                } elseif ($opcion_personalizada == 'cada_x_dias') {
                    $cada_cuantos_dias = !empty($_POST['cada_cuantos_dias']) ? (int)$_POST['cada_cuantos_dias'] : null;
                    if ($cada_cuantos_dias < 1 || $cada_cuantos_dias > 365) {
                        $_SESSION['error'] = "El número de días debe ser entre 1 y 365 días";
                        header("Location: editar_habito.php?id=$id");
                        exit;
                    }
                } else {
                    $_SESSION['error'] = "Debes seleccionar una opción para la frecuencia personalizada";
                    header("Location: editar_habito.php?id=$id");
                    exit;
                }
            }

            try {
                // Actualizar hábito
                $sql = "UPDATE habitos SET 
                        nombre = ?,
                        descripcion = ?,
                        id_frecuencia = ?,
                        cada_cuantos_dias = ?
                    WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $nombre, 
                    $descripcion, 
                    $id_frecuencia,
                    ($id_frecuencia == 4 && $opcion_personalizada == 'cada_x_dias') ? $cada_cuantos_dias : null,
                    $id
                ]);

                // Eliminar días existentes (si los hay) para evitar errores
                $pdo->prepare("DELETE FROM dias_habito WHERE id_habito = ?")->execute([$id]);
                
                // Si es frecuencia personalizada por días, insertar los nuevos
                if ($id_frecuencia == 4 && $opcion_personalizada == 'dias' && !empty($dias_seleccionados)) {
                    $insertDias = $pdo->prepare("INSERT INTO dias_habito (id_habito, id_dia) VALUES (?, ?)");
                    foreach ($dias_seleccionados as $id_dia) {
                        $insertDias->execute([$id, $id_dia]);
                    }
                }

                $_SESSION["exito"] = "¡Hábito actualizado correctamente!";
                header("Location: index.php");
                exit;

            } catch (Exception $e) {
                $_SESSION["error"] = "Error: " . $e->getMessage();
                header("Location: index.php");
                exit();
            }
        }
        elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = $_GET['id'];

            // Verificar pertenencia del hábito
            $stmt = $pdo->prepare("SELECT * FROM habitos WHERE id = ? AND id_usuario = ?");
            $stmt->execute([$id, $id_usuario]);
            $habito = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$habito) {
                $_SESSION['error'] = "No tienes permiso para editar este hábito o no existe";
                header("Location: index.php"); 
                exit();
            }
            
            // Si es frecuencia personalizada, obtenemos los días seleccionados
            if ($habito['id_frecuencia'] == 4) {
                $stmtDias = $pdo->prepare("SELECT id_dia FROM dias_habito WHERE id_habito = ?");
                $stmtDias->execute([$id]);
                $habito['dias_seleccionados'] = $stmtDias->fetchAll(PDO::FETCH_COLUMN);
                
                // Determinar qué opción está seleccionada
                $habito['opcion_personalizada'] = (!empty($habito['dias_seleccionados'])) ? 'dias' : 'cada_x_dias';
            }
        }
    } catch (Exception $e) {
        $_SESSION["error"] = "Error: " . $e->getMessage();
        header("Location: index.php");
        exit();  
    }

    $pageTitle = "Editar hábito"; 
    $seccionActual = 'habitos';
    require_once '../../includes/header.php';
    
?>

<main class="content container mt-4">
    <h1 class="text-center">Editar hábito</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($habito)): ?>
        <form method="POST" action="editar_habito.php" class="col-md-6 mx-auto">
            <input type="hidden" name="id" value="<?= htmlspecialchars($habito['id']) ?>">
            
            <div class="mb-3">
                <label class="form-label">Nombre del hábito:</label>
                <input type="text" name="nombre" class="form-control" 
                       value="<?= htmlspecialchars($habito['nombre']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Descripción:</label>
                <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($habito['descripcion']) ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Frecuencia:</label>
                <select name="id_frecuencia" id="id_frecuencia" class="form-select" required>
                    <?php foreach ($frecuencias as $frecuencia): ?>
                        <option value="<?= $frecuencia['id'] ?>" 
                            <?= $frecuencia['id'] == $habito['id_frecuencia'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($frecuencia['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Opciones para frecuencia personalizada -->
            <div id="opcionesPersonalizada" style="display: <?= $habito['id_frecuencia'] == 4 ? 'block' : 'none' ?>;">
                <div class="mb-3">
                    <label class="form-label">Opciones:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="opcion_personalizada" id="opcionDias" value="dias" 
                            <?= $habito['id_frecuencia'] == 4 && !empty($habito['dias_seleccionados']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="opcionDias">
                            Días específicos de la semana
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="opcion_personalizada" id="opcionCadaXDias" value="cada_x_dias"
                            <?= $habito['id_frecuencia'] == 4 && empty($habito['dias_seleccionados']) && $habito['cada_cuantos_dias'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="opcionCadaXDias">
                            Cada X días
                        </label>
                    </div>
                </div>

                <!-- Días de la semana -->
                <div class="mb-3" id="diasSemanaContainer" style="display: <?= ($habito['id_frecuencia'] == 4 && !empty($habito['dias_seleccionados'])) ? 'block' : 'none' ?>;">
                    <label class="form-label">Selecciona los días:</label>
                    <div class="border p-3 rounded">
                        <?php foreach ($dias as $dia): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="dias[]" 
                                       value="<?= $dia['id'] ?>" id="dia<?= $dia['id'] ?>"
                                       <?= (isset($habito['dias_seleccionados']) && in_array($dia['id'], $habito['dias_seleccionados'])) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="dia<?= $dia['id'] ?>">
                                    <?= htmlspecialchars($dia['nombre']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Cada X días -->
                <div class="mb-3" id="cadaCuantosDiasContainer" style="display: <?= (empty($habito['dias_seleccionados']) && !empty($habito['cada_cuantos_dias'])) ? 'block' : 'none' ?>;">
                    <label class="form-label">Repetir cada cuántos días:</label>
                    <input type="number" name="cada_cuantos_dias" class="form-control" 
                        value="<?= htmlspecialchars($habito['cada_cuantos_dias'] ?? '') ?>" min="1">
                    </div>
                </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="index.php" class="btn btn-secondary me-md-2 mb-3">Cancelar</a>
                <button type="submit" class="btn btn-primary mb-3">Guardar cambios</button>
            </div>
        </form>
    <?php endif; ?>
</main>

<?php
require_once '../../includes/footer.php';