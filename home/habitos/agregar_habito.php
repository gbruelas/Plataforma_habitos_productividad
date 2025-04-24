<?php
    session_start();
    require_once '../../includes/verificar_sesion.php';
    verificarSesion();

    require_once '../../includes/conexion.php';

    // Obtener frecuencias para el select
    $frecuencias = $pdo->query("SELECT id, nombre FROM frecuencias")->fetchAll(PDO::FETCH_ASSOC);

    // Obtener días de la semana para checkboxes
    $dias = $pdo->query("SELECT id, nombre FROM dias")->fetchAll(PDO::FETCH_ASSOC);

    // Procesar formulario POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Validar campos obligatorios
            if (empty($_POST['nombre']) || empty($_POST['id_frecuencia'])) {
                $_SESSION['error'] = "El nombre y la frecuencia son campos obligatorios.";
                header("Location: agregar_habito.php");
                exit();
            }

            $id_frecuencia = (int)$_POST['id_frecuencia'];
            $cada_cuantos_dias = null;
            $dias_seleccionados = [];

            // Manejar frecuencia personalizada (ID 4)
            if ($id_frecuencia === 4) {
                // Opción 1: Por dias de la semana
                if (!empty($_POST['dias'])) {
                    $dias_seleccionados = $_POST['dias'];
                    // Validar que sean dias válidos
                    foreach ($dias_seleccionados as $dia) {
                        /*
                        * array_column lo que hace es extraer todos los valores de la clave id del array dias
                        * (por ejemplo, [1, 3, 4], que seria lunes miercoles y jueves)
                        * y el in_array verifica buscando el valor de dia en el array de ids generados
                        * en esta condicion lo que hace es generar el error si un id no existe
                        */
                        if (!in_array($dia, array_column($dias, 'id'))) {
                            $_SESSION['error'] = "Día seleccionado no valido.";
                            header("Location: agregar_habito.php");
                            exit;
                        }
                    }
                } 
                // Opción 2: Cada x días
                elseif (!empty($_POST['cada_cuantos_dias'])) {
                    $cada_cuantos_dias = (int)$_POST['cada_cuantos_dias'];
                    if ($cada_cuantos_dias < 1 || $cada_cuantos_dias > 365) {
                        $_SESSION['error'] = "El numero de días debe de ser entre entre 1 y 365 días";
                        header("Location: agregar_habito.php");
                        exit;
                    }
                } else {
                    $_SESSION['error'] = "Para frecuencia personalizada debes seleccionar días específicos o indicar cada cuántos días.";
                    header("Location: agregar_habito.php");
                    exit;
                }
            }

            // Insertar nuevo hábito
            $sql = "INSERT INTO habitos (id_usuario, nombre, descripcion, id_frecuencia, cada_cuantos_dias, fecha_creacion)
                    VALUES (:id_usuario, :nombre, :descripcion, :id_frecuencia, :cada_cuantos_dias, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $_SESSION['usuario_id'],
                ':nombre' => $_POST['nombre'],
                ':descripcion' => $_POST['descripcion'] ?? null,
                ':id_frecuencia' => $id_frecuencia,
                ':cada_cuantos_dias' => $cada_cuantos_dias ?: null // Asegurar NULL si es 0 o vacío
            ]);

            // Se necesita saber el ultimo id para relacionar los dias seleccionados en dias_habito
            $id_habito = $pdo->lastInsertId();

            // Si se seleccionaron días específicos, insertarlos
            if (!empty($dias_seleccionados)) {
                $sqlDias = "INSERT INTO dias_habito (id_habito, id_dia) VALUES (:id_habito, :id_dia)";
                $stmtDias = $pdo->prepare($sqlDias);
                
                foreach ($dias_seleccionados as $id_dia) {
                    $stmtDias->execute([
                        ':id_habito' => $id_habito,
                        ':id_dia' => $id_dia
                    ]);
                }
            }

            $_SESSION['exito'] = 'Hábito registrado exitosamente';
            header("Location: index.php");
            exit();

        } catch (Exception $e) {
            $_SESSION['error'] = "Error al registrar hábito: " . $e->getMessage();
            header("Location: agregar_habito.php");
            exit();
        }
    }

    $seccionActual = 'habitos';
    $pageTitle = "Agregar hábito"; 
    require_once '../../includes/header.php';
?>

<main class="content container mt-4">
    <h1 class="text-center">Agregar nuevo hábito</h1>
    
    <!-- Mostrar mensajes de error-->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="agregar_habito.php" class="col-md-6 mx-auto">
        <div class="mb-3">
            <label class="form-label">Nombre del hábito:</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
                
        <div class="mb-3">
            <label class="form-label">Descripción (opcional):</label>
            <textarea name="descripcion" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Frecuencia:</label>
            <select name="id_frecuencia" id="id_frecuencia" class="form-select" required>
                <?php foreach ($frecuencias as $frecuencia): ?>
                    <option value="<?= $frecuencia['id'] ?>">
                        <?= htmlspecialchars($frecuencia['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Opciones para frecuencia personalizada -->
        <div id="opcionesPersonalizada" style="display: none;">
            <div class="mb-3">
                <label class="form-label">Opciones para frecuencia personalizada:</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="opcion_personalizada" id="opcionDias" value="dias" checked>
                    <label class="form-check-label" for="opcionDias">
                        Días específicos de la semana
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="opcion_personalizada" id="opcionCadaXDias" value="cada_x_dias">
                    <label class="form-check-label" for="opcionCadaXDias">
                        Cada X días
                    </label>
                </div>
            </div>

            <!-- Días de la semana (visible por defecto) -->
            <div class="mb-3" id="diasSemanaContainer">
                <label class="form-label">Selecciona los días:</label>
                <div class="border p-3 rounded">
                    <?php foreach ($dias as $dia): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="dias[]" value="<?= $dia['id'] ?>" id="dia<?= $dia['id'] ?>">
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
                    <input type="number" name="cada_cuantos_dias" class="form-control" min="1">
                </div>
            </div>
                
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="index.php" class="btn btn-secondary me-md-2 mb-3">Cancelar</a>
            <button type="submit" class="btn btn-primary mb-3">Guardar</button>
        </div>
    </form>
</main>

<?php
require_once '../../includes/footer.php';
?>