<?php

    session_start();
    require_once '../../includes/verificar_sesion.php';

    //Para que no puedan entrar con el enlace sin iniciar sesión o sin ser admins
    verificarSesion();
    verificarSesionAdmin();

    // Validamos que se haya enviado un parámetro 'id' por la URL y que sea un número válido
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION["error"] = "ID de usuario invalido";
        header("Location: index.php");
        exit();  
    }

    // Convertimos el valor recibido a entero por seguridad
    $id = intval($_GET['id']);

    try {

        require_once '../../includes/conexion.php';

        /*
        * Antes de eliminar, verificamos que el usuario realmente exista.
        * Esto evita intentar eliminar un ID que no existe y mejora la experiencia del usuario.
        */
        $verificar = $pdo->prepare("SELECT id FROM usuarios WHERE id = :id");
        $verificar->execute([':id' => $id]);

        if ($verificar->rowCount() === 0) {
            $_SESSION["error"] = "Usuario no encontrado.";
            header("Location: index.php");
            exit();  
        }

        /*
        * Si el usuario existe, lo eliminamos usando una consulta preparada.
        * Esto también protege contra inyecciones SQL.
        */
        $eliminar = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $eliminar->execute([':id' => $id]);

        // Redirigimos a la lista de usuarios después de la eliminación
        $_SESSION["exito"] = "Usuario eliminado.";
        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        // Capturamos y mostramos errores de base de datos si ocurren
        $_SESSION["error"] = "Error PDO: " . $e->getMessage();
        header("Location: index.php");
        exit();  
    }
?>
