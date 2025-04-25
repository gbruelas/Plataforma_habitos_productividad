<?php
    session_start();
    require_once '../../includes/verificar_sesion.php';

    // Verificar que el usuario haya iniciado sesión
    verificarSesion();

    $seccionActual = 'habitos';
    $pageTitle = "Mis hábitos"; 

    require_once '../../includes/header.php';
    require_once '../../includes/conexion.php';

    // Obtener el ID del usuario actual
    $id_usuario = $_SESSION['usuario_id'];

    // Verificamos si hay una búsqueda (GET), si no, se deja como cadena vacía
    $busqueda = $_GET['busqueda'] ?? '';

    if ($busqueda !== '') {
        // Si hay un valor de búsqueda, preparamos una consulta que lo utilice
        $sql = "SELECT 
                    h.id,
                    h.nombre AS nombre_habito,
                    h.descripcion,
                    f.nombre AS frecuencia,
                    h.cada_cuantos_dias,
                    GROUP_CONCAT(d.nombre SEPARATOR ', ') AS dias_personalizados,
                    h.fecha_creacion
                FROM habitos h
                JOIN frecuencias f ON h.id_frecuencia = f.id
                LEFT JOIN dias_habito dh ON h.id = dh.id_habito
                LEFT JOIN dias d ON dh.id_dia = d.id
                WHERE h.id_usuario = :id_usuario 
                AND (h.nombre LIKE :busqueda OR h.descripcion LIKE :busqueda)
                GROUP BY h.id";
    
        // Preparamos y ejecutamos la consulta con un valor escapado de forma segura    
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':busqueda' => "%$busqueda%"
        ]);
    } else {
        // Si no hay búsqueda, seleccionamos todos los hábitos del usuario
        $sql = "SELECT 
                    h.id,
                    h.nombre AS nombre_habito,
                    h.descripcion,
                    f.nombre AS frecuencia,
                    h.cada_cuantos_dias,
                    GROUP_CONCAT(d.nombre SEPARATOR ', ') AS dias_personalizados,
                    h.fecha_creacion
                FROM habitos h
                JOIN frecuencias f ON h.id_frecuencia = f.id
                LEFT JOIN dias_habito dh ON h.id = dh.id_habito
                LEFT JOIN dias d ON dh.id_dia = d.id
                WHERE h.id_usuario = :id_usuario
                GROUP BY h.id";
    
        // Preparamos y ejecutamos la consulta
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
    }

    // Obtenemos todos los resultados en un arreglo asociativo    
    $habitos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="content container mt-4">

    <h1 class="text-center">Mis hábitos</h1>

    <nav class="navbar navbar-whitw bg-white">
    <div class="container-fluid">
        <form class="row g-2 align-items-center w-100" method="GET" action="index.php">
            <div class="col-auto">
                <label class="col-form-label">Buscar hábitos:</label>
            </div>
            <div class="col">
                <input class="form-control" type="search" placeholder="Buscar" aria-label="Buscar" name="busqueda" value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-success" type="submit">Buscar</button>
            </div>
        </form>
    </div>
    </nav>

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

    <!-- Alert cuando no hay hábitos -->
    <?php if (empty($habitos)): ?>
        <div class="alert alert-info">
            No tienes hábitos registrados aún. ¡Crea tu primer hábito!
        </div>
    <?php else: ?>

        <table class="table">
        <caption>Lista de mis hábitos</caption>
        <thead class="table-dark">
            <tr>
            <th scope="col">ID</th>
            <th scope="col">Nombre</th>
            <th scope="col">Descripción</th>
            <th scope="col">Frecuencia</th>
            <th scope="col">Días específicos</th>
            <th scope="col">Fecha de creación</th>
            <th scope="col">Opciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Recorremos cada hábito y generamos una fila por cada uno -->
            <?php foreach ($habitos as $habito): ?>
                <tr>
                    <td><?= $habito['id'] ?></td>
                    <td><?= $habito['nombre_habito'] ?></td>
                    <td><?= $habito['descripcion'] ?></td>
                    <td>
                        <?= $habito['frecuencia'] ?>
                        <?php if ($habito['cada_cuantos_dias']): ?>
                            (cada <?= $habito['cada_cuantos_dias'] ?> días)
                        <?php endif; ?>
                    </td>
                    <td><?= $habito['dias_personalizados'] ?? 'N/A' ?></td>
                    <td><?= $habito['fecha_creacion'] ?></td>
                    <td>
                    <!-- Botón para editar, enviando el ID del hábito por GET -->
                    <a class="btn btn-warning text-white" href="editar_habito.php?id=<?= $habito['id'] ?>">
                        Editar
                    </a>
                    <!-- Botón para eliminar con confirmación -->
                    <button class="btn btn-danger" 
                        data-bs-toggle="modal" 
                        data-bs-target="#confirmarEliminarModal"
                        onclick="document.getElementById('btnEliminarConfirmado').href = 'eliminar_habito.php?id=<?= $habito['id'] ?>'">
                        Eliminar
                    </button>
                    </td>
                </tr>
                <?php endforeach; ?>
        </tbody>
        </table>
    <?php endif; ?>

    <!-- Botón flotante para agregar hábito -->
    <a href="agregar_habito.php" class="btn btn-success fab-button">
        Agregar hábito
    </a>

    <!-- Modal de eliminar hábito (confirmación con modal) -->
    <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">CONFIRMAR ELIMINACIÓN</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este hábito?</p>
                    <p class="fw-bolder">¡Se eliminará todo su historial de seguimiento y sus metas!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a id="btnEliminarConfirmado" class="btn btn-danger">Sí, Eliminar</a>
                </div>
            </div>
        </div>
    </div>

</main>

<?php
    require_once '../../includes/footer.php';
?>