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

    <!-- Solo mostrar la barra de busqueda si hay metas o si ya se realizo una busqueda -->
    <?php if (!empty($metas) || $busqueda !== ''): ?>
        <nav class="navbar navbar-white">
        <div class="container-fluid">
            <form class="row g-2 align-items-center w-100" method="GET" action="index.php">
                <div class="col">
                    <input class="form-control" type="search" placeholder="Buscar por nombre de hábito" aria-label="Buscar" name="busqueda" value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-success" type="submit">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
        </nav>
    <?php endif; ?>

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

    <!-- Alert si no hay metas (siempre y cuando no haya busqueda) -->
    <?php if (empty($metas) && $busqueda === ''): ?>
        <div class="alert alert-info">
            No tienes metas registradas aún. ¡Crea tu primera meta!
        </div>
    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-bordered">
            <caption>Lista de mis metas</caption>
            <thead class="table-dark">
                <tr>
                    <th scope="col" class="text-center col-1 d-none d-md-table-cell">ID</th>
                    <th scope="col" class="col-3">Hábito</th>
                    <th scope="col" class="col-2">Objetivo</th>
                    <th scope="col" class="col-2">Periodo</th>
                    <th scope="col" class="text-center col-1">Opciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($metas as $meta): ?>
                    <tr>
                        <td class="text-center d-none d-md-table-cell"><?= htmlspecialchars($meta['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($meta['nombre_habito'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($meta['cantidad_objetivo'], ENT_QUOTES, 'UTF-8'); ?> veces</td>
                        <td><?= htmlspecialchars(ucfirst($meta['periodo']), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="text-center">
                            <a class="text-warning me-3" href="editar_meta.php?id=<?= $meta['id'] ?>" aria-label="Editar">
                                <i class="bi bi-pencil-square fs-5"></i>
                            </a>
                            <a
                                href="#"
                                class="text-danger text-decoration-none"
                                data-bs-toggle="modal"
                                data-bs-target="#confirmarEliminarModal"
                                onclick="document.getElementById('btnEliminarConfirmado').href = 'eliminar_meta.php?id=<?= $meta['id'] ?>'"
                                aria-label="Eliminar">
                                    <i class="bi bi-trash-fill fs-5"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Botón flotante para agregar meta -->
    <a href="agregar_meta.php" class="btn btn-success rounded-circle fab-button">
        <i class="bi bi-plus-lg fs-4"></i>
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
                    <a id="btnEliminarConfirmado" class="btn btn-danger">Sí, eliminar</a>
                </div>
            </div>
        </div>
    </div>

</main>

<?php
    require_once '../../includes/footer.php';
?>