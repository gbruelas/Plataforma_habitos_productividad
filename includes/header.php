<?php

    require_once 'verificar_sesion.php';

    //Para que no puedan entrar con el enlace sin iniciar sesión
    verificarSesion();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Hábitos y productividad'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="/proyecto_integrador/assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="full">
        <header class="p-3 bg-white border-bottom box-shadow sticky-top" id="header-blur">
            <div class="container">
                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">

                    <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                        <li><a href="/proyecto_integrador/home/" class="nav-link px-3 text-dark <?= ($seccionActual == 'inicio') ? 'fw-bolder' : '' ?>">Inicio</a></li>
                        <li><a href="/proyecto_integrador/home/habitos" class="nav-link px-3 text-dark <?= ($seccionActual == 'habitos') ? 'fw-bolder' : '' ?>">Hábitos</a></li>
                        <li><a href="/proyecto_integrador/home/metas" class="nav-link px-3 text-dark <?= ($seccionActual == 'metas') ? 'fw-bolder' : '' ?>">Metas</a></li>
                        <li><a href="/proyecto_integrador/home/seguimiento" class="nav-link px-3 text-dark <?= ($seccionActual == 'seguimiento') ? 'fw-bolder' : '' ?>">Seguimiento</a></li>
                        <?php if ($_SESSION['rol'] == '1'): ?>
                            <li><a href="/proyecto_integrador/home/usuarios" class="nav-link px-3 text-dark <?= ($seccionActual == 'usuarios') ? 'fw-bolder' : '' ?>">Usuarios</a></li>
                        <?php endif; ?>
                    </ul>

                    <div class="text-end">
                        <a href="/proyecto_integrador/logout.php" class="btn btn-outline-danger me-2">
                            <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                        </a>
                    </div>
                </div>
            </div>
    </header>
