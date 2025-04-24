<?php

    session_start();
    require_once '../../includes/verificar_sesion.php';

    //Para que no puedan entrar con el enlace sin iniciar sesión o sin ser admins
    verificarSesion();
    verificarSesionAdmin();

    $seccionActual = 'usuarios';
    $pageTitle = "Usuarios"; 

    require_once '../../includes/header.php';
    require_once '../../includes/conexion.php';

    // Verificamos si hay una búsqueda (GET), si no, se deja como cadena vacía
    $busqueda = $_GET['busqueda'] ?? '';

    if ($busqueda !== '') {
        // Si hay un valor de búsqueda, preparamos una consulta que lo utilice
        // Se utiliza LIKE con % para encontrar coincidencias parciales en nombres o correos        
        $sql = "SELECT 
                    u.id,
                    u.nombre AS nombre_usuario,
                    u.correo,
                    r.nombre AS nombre_rol,
                    u.fecha_registro
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id
                WHERE u.nombre LIKE :busqueda OR u.correo LIKE :busqueda";
    
        // Preparamos y ejecutamos la consulta con un valor escapado de forma segura    
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':busqueda' => "%$busqueda%"]);
    } else {

        // Si no hay búsqueda, simplemente seleccionamos todos los usuarios        
        $sql = "SELECT 
                    u.id,
                    u.nombre AS nombre_usuario,
                    u.correo,
                    r.nombre AS nombre_rol,
                    u.fecha_registro
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id";
    
        // Ejecutamos la consulta directamente (sin parámetros)
        $stmt = $pdo->query($sql);
    }

    // Obtenemos todos los resultados en un arreglo asociativo    
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="content container mt-4">

    <h1 class="text-center">Lista de usuarios registrados</h1>

    <nav class="navbar navbar-whitw bg-white">
    <div class="container-fluid">
        <form class="row g-2 align-items-center w-100" method="GET" action="index.php">
            <div class="col-auto">
                <label class="col-form-label">Buscar por nombre o correo:</label>
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

    <table class="table">
    <caption>Lista de usuarios</caption>
    <thead class="table-dark">
        <tr>
        <th scope="col">ID</th>
        <th scope="col">Nombre</th>
        <th scope="col">Correo</th>
        <th scope="col">Rol</th>
        <th scope="col">Fecha de registro</th>
        <th scope="col">Opciones</th>
        </tr>
    </thead>
    <tbody>
        <!-- Recorremos cada usuario y generamos una fila por cada uno -->
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= $usuario['id'] ?></td>
                <td><?= $usuario['nombre_usuario'] ?></td>
                <td><?= $usuario['correo'] ?></td>
                <td><?= $usuario['nombre_rol'] ?></td>
                <td><?= $usuario['fecha_registro'] ?></td>
                <td>
                <!-- Botón para editar, enviando el ID del usuario por GET -->
                <a class="btn btn-warning text-white" href="editar_usuario.php?id=<?= $usuario['id'] ?>">
                    Editar
                </a>
                <!-- Botón para eliminar con confirmación -->
                <button class="btn btn-danger" 
                    data-bs-toggle="modal" 
                    data-bs-target="#confirmarEliminarModal"
                    onclick="document.getElementById('btnEliminarConfirmado').href = 'eliminar_usuario.php?id=<?= $usuario['id'] ?>'">
                    Eliminar
                </button>
                </td>
            </tr>
            <?php endforeach; ?>
    </tbody>
    </table>

    <!-- Botón flotante para agregar usuario -->
    <a href="agregar_usuario.php" class="btn btn-success fab-button">
        Agregar usuario
    </a>

    <!-- Modal de eliminar usuario (confirmación con modal) -->
    <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">CONFIRMAR ELIMINACIÓN</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este usuario?</p>
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