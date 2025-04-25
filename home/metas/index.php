<?php

    session_start();
    require_once '../../includes/verificar_sesion.php';
    
    // Para que no puedan entrar con el enlace sin iniciar sesión
    verificarSesion();

    $seccionActual = 'metas';
    $pageTitle = "Mis metas";
    require_once '../../includes/header.php';
    require_once '../../includes/conexion.php';

    $id_usuario = $_SESSION['usuario_id'];

    // Búsqueda
    $busqueda = $_GET['busqueda'] ?? '';

    // Busqueda por nombre de hábito
    if ($busqueda !== '') {
        $sql = "SELECT m.id, h.nombre AS nombre_habito, m.cantidad_objetivo, p.nombre AS periodo 
                FROM metas m
                JOIN habitos h ON m.id_habito = h.id
                JOIN periodos p ON m.id_periodo = p.id
                WHERE h.id_usuario = :id_usuario AND h.nombre LIKE :busqueda";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':busqueda' => "%$busqueda%"
        ]);
    } else {
        // Si no hay busqueda se seleccionan todas las metas del usuario
        $sql = "SELECT m.id, h.nombre AS nombre_habito, m.cantidad_objetivo, p.nombre AS periodo 
                FROM metas m
                JOIN habitos h ON m.id_habito = h.id
                JOIN periodos p ON m.id_periodo = p.id
                WHERE h.id_usuario = :id_usuario";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
    }

    $metas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<main class="content container mt-4">

    <h1 class="text-center">Mis metas</h1>

    <nav class="navbar navbar-whitw bg-white">
    <div class="container-fluid">
        <form class="row g-2 align-items-center w-100" method="GET" action="index.php">
            <div class="col-auto">
                <label class="col-form-label">Buscar por nombre de hábito:</label>
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

    <!-- Alert si no hay metas -->
    <?php if (empty($metas)): ?>
        <div class="alert alert-info">
            No tienes metas registradas aún. ¡Crea tu primera meta!
        </div>
    <?php else: ?>

        <table class="table">
        <caption>Lista de mis metas</caption>
        <thead class="table-dark">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Hábito</th>
                <th scope="col">Objetivo</th>
                <th scope="col">Periodo</th>
                <th scope="col">Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($metas as $meta): ?>
                <tr>
                    <td class="w-1"><?= $meta['id'] ?></td>
                    <td><?= $meta['nombre_habito'] ?></td>
                    <td><?= $meta['cantidad_objetivo'] ?> veces</td>
                    <td><?= ucfirst($meta['periodo']) ?></td>
                    <td>
                        <a class="btn btn-warning text-white" href="editar_meta.php?id=<?= $meta['id'] ?>">
                            Editar
                        </a>
                        <button class="btn btn-danger" 
                            data-bs-toggle="modal" 
                            data-bs-target="#confirmarEliminarModal"
                            onclick="document.getElementById('btnEliminarConfirmado').href = 'eliminar_meta.php?id=<?= $meta['id'] ?>'">
                            Eliminar
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    <?php endif; ?>

    <!-- Botón flotante para agregar meta -->
    <a href="agregar_meta.php" class="btn btn-success fab-button">
        Agregar meta
    </a>

    <!-- Modal de eliminar meta -->
    <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">CONFIRMAR ELIMINACIÓN</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar esta meta?</p>
                    <p class="fw-bolder">¡Esta acción no se puede deshacer!</p>
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