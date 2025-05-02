<?php

    session_start();
    require_once '../includes/verificar_sesion.php';

    // Verificar que el usuario haya iniciado sesión
    verificarSesion();

    require_once '../includes/conexion.php';

    $pageTitle = "Inicio";
    $seccionActual = 'inicio';
    require_once '../includes/header.php';

    try {
        $usuario_id = $_SESSION['usuario_id'];
        $hoy = date('Y-m-d'); // Devuelve la fecha actual
        $dia_nombre = date('l', strtotime($hoy)); // Devuelve el nombre del día en ingles

        // Días en español
        $dias = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miercoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sabado',
            'Sunday' => 'Domingo'
        ];
        $dia_actual = $dias[$dia_nombre];
        $anio_mes = date('Y-m');
        $anio_semana = date('o-W');

        // Obtener todos los hábitos del usuario
        $sqlHabitos = "SELECT id, id_frecuencia 
                        FROM habitos 
                        WHERE id_usuario = :usuario_id";
        $stmtHabitos = $pdo->prepare($sqlHabitos);
        $stmtHabitos->execute([':usuario_id' => $usuario_id]);
        $habitos = $stmtHabitos->fetchAll(PDO::FETCH_ASSOC);

        // Insertar seguimientos según frecuencia
        foreach ($habitos as $habito) {
            $id = $habito['id'];
            $frecuencia = $habito['id_frecuencia'];
            $insertar = false;

            if ($frecuencia == 1) { // Diaria
                $insertar = true;
            } elseif ($frecuencia == 2) { // Semanal
                $check = $pdo->prepare("SELECT COUNT(*) 
                                        FROM seguimiento_habito 
                                        WHERE id_habito = :id AND YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)");
                $check->execute([':id' => $id]);
                if ($check->fetchColumn() == 0) $insertar = true;
            } elseif ($frecuencia == 3) { // Mensual
                $check = $pdo->prepare("SELECT COUNT(*) 
                                        FROM seguimiento_habito 
                                        WHERE id_habito = :id AND DATE_FORMAT(fecha, '%Y-%m') = :mes");
                $check->execute([':id' => $id, ':mes' => $anio_mes]);
                if ($check->fetchColumn() == 0) $insertar = true;
            } elseif ($frecuencia == 4) {
                // Tiene días definidos?
                $checkDias = $pdo->prepare("SELECT COUNT(*) 
                                            FROM dias_habito 
                                            WHERE id_habito = :id");
                $checkDias->execute([':id' => $id]);
                $usa_dias = $checkDias->fetchColumn() > 0;

                if ($usa_dias) {
                    // Verificar si el día actual esta entre los asignados
                    $checkHoy = $pdo->prepare("SELECT COUNT(*) 
                                                FROM dias_habito dh
                                                JOIN dias d ON dh.id_dia = d.id
                                                WHERE dh.id_habito = :id AND d.nombre = :dia");
                    $checkHoy->execute([':id' => $id, ':dia' => $dia_actual]);
                    if ($checkHoy->fetchColumn() > 0) $insertar = true;
                } else {
                    // Verificar cada_cuantos_dias
                    $checkIntervalo = $pdo->prepare("SELECT cada_cuantos_dias 
                                                    FROM habitos 
                                                    WHERE id = :id AND cada_cuantos_dias IS NOT NULL");
                    $checkIntervalo->execute([':id' => $id]);
                    $info = $checkIntervalo->fetch(PDO::FETCH_ASSOC);

                    if ($info && $info['cada_cuantos_dias'] > 0) {
                        $dias_intervalo = (int)$info['cada_cuantos_dias'];

                        // Buscar ultima fecha registrada
                        $ultimaFecha = $pdo->prepare("SELECT MAX(fecha) 
                                                        FROM seguimiento_habito 
                                                        WHERE id_habito = :id");
                        $ultimaFecha->execute([':id' => $id]);
                        $ultimo = $ultimaFecha->fetchColumn();

                        if ($ultimo) {
                            $dias_transcurridos = (new DateTime($ultimo))->diff(new DateTime($hoy))->days;
                            if ($dias_transcurridos >= $dias_intervalo) $insertar = true;
                        } else {
                            $insertar = true; // nunca ha tenido seguimiento
                        }
                    }
                }
            }

            // Insertar si aplica y no existe para hoy
            if ($insertar) {
                $checkHoy = $pdo->prepare("SELECT COUNT(*) FROM seguimiento_habito WHERE id_habito = :id AND fecha = :fecha");
                $checkHoy->execute([':id' => $id, ':fecha' => $hoy]);

                if ($checkHoy->fetchColumn() == 0) {
                    $insert = $pdo->prepare("INSERT INTO seguimiento_habito (id_habito, fecha, cumplido) VALUES (:id, :fecha, 0)");
                    $insert->execute([':id' => $id, ':fecha' => $hoy]);
                }
            }
        }

        // Obtener hábitos de hoy
        $sqlHabitosHoy = "SELECT h.id, h.nombre, h.descripcion, s.cumplido
                            FROM habitos h
                            JOIN seguimiento_habito s ON s.id_habito = h.id
                            WHERE h.id_usuario = :usuario_id AND s.fecha = :hoy
                            ORDER BY h.nombre";

        $stmt = $pdo->prepare($sqlHabitosHoy);
        $stmt->execute([':usuario_id' => $usuario_id, ':hoy' => $hoy]);
        $habitos_del_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcular el progreso
        $total = count($habitos_del_dia);
        $completados = count(array_filter($habitos_del_dia, fn($h) => $h['cumplido']));
        $porcentaje = $total > 0 ? round(($completados / $total) * 100) : 0;

    } catch (PDOException $e) {
        // Mostrar el error en caso de haber
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: index.php");
        exit;
    }

?>

<main class="content container mt-4">

    <h1 class="mb-4 text-center">Hábitos del día - <?= $dia_actual ?></h1>

        <!-- Mostrar mensajes de error-->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Mostrar mensajes de exito -->
    <?php if (isset($_SESSION['exito'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['exito'], ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['exito']); ?>
    <?php endif; ?>

    <!-- ¿Hay habitos para hoy? Si no hay: -->
    <?php if (empty($habitos_del_dia)): ?>
        <div class="alert alert-info">
            ¡No hay hábitos por hoy!
        </div>
        <div id="carouselExampleControls" class="carousel slide mb-3" data-bs-ride="carousel">
        <div class="carousel-inner ratio ratio-16x9">
            <div class="carousel-item active">
            <img src="../assets/imgs/focus.jpg" class="d-block w-100" alt="Imagen de enfoque personal">
            </div>
            <div class="carousel-item">
            <img src="../assets/imgs/productivity.webp" class="d-block w-100" alt="Imagen de la productividad">
            </div>
            <div class="carousel-item">
            <img src="../assets/imgs/time.webp" class="d-block w-100" alt="Imagen del valor del tiempo">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
        </div>
    <?php else: ?>
        <!-- Si si hay habitos para hoy: -->
        <!-- Barra de progreso -->
        <div class="progress mb-4">
            <div class="progress-bar bg-success" style="width: <?= $porcentaje ?>%;">
                <?= $porcentaje ?>% completado
            </div>
        </div>
        <!-- Lista de hábitos -->
        <?php foreach ($habitos_del_dia as $habito_del_dia): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= htmlspecialchars($habito_del_dia['nombre'], ENT_QUOTES, 'UTF-8'); ?></h5>
                        <p class="card-text"><?= htmlspecialchars($habito_del_dia['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <?php if ($habito_del_dia['cumplido']): ?>
                        <span class="badge bg-success">Completado</span>
                    <?php else: ?>
                        <form action="completar.php" method="POST">
                            <input type="hidden" name="id_habito" value="<?= htmlspecialchars($habito_del_dia['id']) ?>">
                            <button class="btn btn-outline-primary btn-sm">Marcar como hecho</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</main>

<?php 
    require_once '../includes/footer.php'; 
?>