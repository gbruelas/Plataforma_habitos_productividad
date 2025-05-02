<?php

    session_start();
    require_once '../../includes/verificar_sesion.php';

    // Verificar que el usuario haya iniciado sesión
    verificarSesion();

    require_once '../../includes/conexion.php';

    $pageTitle = "Seguimiento";
    $seccionActual = 'seguimiento';
    require_once '../../includes/header.php';

    try {
        $usuario_id = $_SESSION['usuario_id'];

        // Filtro (día, semana, mes)
        $filtro = $_GET['filtro'] ?? 'semana';
        $hoy = date('Y-m-d');

        // Para poder filtrar por fechas
        switch ($filtro) {
            case 'dia':
                $fecha_inicio = $hoy;
                $fecha_fin = $hoy;
                break;
            case 'mes':
                $fecha_inicio = date('Y-m-01');
                $fecha_fin = date('Y-m-t');
                break;
            case 'semana':
            default:
                $fecha_inicio = date('Y-m-d', strtotime('monday this week'));
                $fecha_fin = date('Y-m-d', strtotime('sunday this week'));
                break;
        }

        // Consulta de seguimientos
        $sql = "SELECT h.nombre AS habito, s.fecha, s.cumplido
                    FROM seguimiento_habito s
                    JOIN habitos h ON s.id_habito = h.id
                    WHERE h.id_usuario = :usuario_id
                    AND s.fecha BETWEEN :inicio AND :fin
                    ORDER BY s.fecha DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':inicio' => $fecha_inicio,
            ':fin' => $fecha_fin
        ]);

        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcular resumen
        $total = count($registros);
        $completados = count(array_filter($registros, fn($r) => $r['cumplido']));
        $porcentaje = $total > 0 ? round(($completados / $total) * 100) : 0;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        $registros = [];
        $total = 0;
        $completados = 0;
        $porcentaje = 0;
    }
    
?>

<main class="content container mt-4">

    <h1 class="text-center">Seguimiento</h1>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Historial de hábitos</h2>
        <form method="GET" class="d-flex gap-2">
            <select name="filtro" class="form-select" onchange="this.form.submit()">
                <option value="dia" <?= $filtro == 'dia' ? 'selected' : '' ?>>Hoy</option>
                <option value="semana" <?= $filtro == 'semana' ? 'selected' : '' ?>>Esta semana</option>
                <option value="mes" <?= $filtro == 'mes' ? 'selected' : '' ?>>Este mes</option>
            </select>
        </form>
    </div>

    <!-- Resumen -->
    <div class="alert alert-info">
        <?php if ($total > 0): ?>
            <strong><?= $completados ?></strong> de <strong><?= $total ?></strong> hábitos completados (<?= $porcentaje ?>%).
        <?php else: ?>
            No hay registros para este periodo.
        <?php endif; ?>
    </div>

    <!-- Mostrar mensajes de error-->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Tabla -->
    <?php if ($total > 0): ?>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th scope="col" class="text-center col-1">Fecha</th>
                    <th scope="col" class="col-5">Hábito</th>
                    <th scope="col" class="text-center col-1">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $registro): ?>
                    <tr>
                        <td class="text-center"><?= htmlspecialchars(date('d/m/Y', strtotime($registro['fecha'])), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($registro['habito'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="text-center">
                            <?php if ($registro['cumplido']): ?>
                                <span class="badge bg-success">Cumplido</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Pendiente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Gráfica -->
    <?php if ($total > 0): ?>
    <div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Resumen visual</h2>
        <form method="GET" class="d-flex gap-2">
            <select name="filtro" class="form-select" onchange="this.form.submit()">
                <option value="dia" <?= $filtro == 'dia' ? 'selected' : '' ?>>Hoy</option>
                <option value="semana" <?= $filtro == 'semana' ? 'selected' : '' ?>>Esta semana</option>
                <option value="mes" <?= $filtro == 'mes' ? 'selected' : '' ?>>Este mes</option>
            </select>
        </form>
    </div>
        <canvas class="mb-4" id="graficaCumplimiento" height="100"></canvas>
    </div>
    <?php endif; ?>

</main>

<?php if ($total > 0): ?>
    <script>
        // Le paso los datos al js de forma segura, aún que no es necesario ya que en ningun momento el usuario ingresa estos datos
        window.graficaData = <?= json_encode([$completados, $total - $completados]) ?>;
    </script>
<?php endif; ?>

<?php 
    require_once '../../includes/footer.php'; 
?>
