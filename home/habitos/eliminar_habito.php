<?php
    session_start();
    require_once '../../includes/verificar_sesion.php';

    // Para que no puedan entrar con el enlace sin iniciar sesión
    verificarSesion();

    // Validamos que se haya enviado un parámetro 'id' por la URL y que sea un número válido
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION["error"] = "ID de hábito inválido";
        header("Location: index.php");
        exit();  
    }

    // Convertimos el valor recibido a entero por seguridad
    $id = intval($_GET['id']);

    try {
        // Conectamos a la base de datos
        require_once '../../includes/conexion.php';

        /*
        * Antes de eliminar, verificamos que el hábito realmente exista y pertenezca al usuario actual.
        * Esto evita intentar eliminar un ID que no existe o que no pertenece al usuario.
        */
        $verificar = $pdo->prepare("SELECT id FROM habitos WHERE id = :id AND id_usuario = :id_usuario");
        $verificar->execute([
            ':id' => $id,
            ':id_usuario' => $_SESSION['usuario_id']
        ]);

        if ($verificar->rowCount() === 0) {
            $_SESSION["error"] = "Hábito no encontrado o no tienes permiso para eliminarlo";
            header("Location: index.php");
            exit();  
        }

        /*
        * Si el hábito existe y pertenece al usuario, lo eliminamos usando una consulta preparada.
        * Las relaciones ON DELETE CASCADE en la base de datos se encargarán de eliminar
        * los registros relacionados en dias_habito y seguimiento_habito automáticamente.
        */
        $eliminar = $pdo->prepare("DELETE FROM habitos WHERE id = :id");
        $eliminar->execute([':id' => $id]);

        // Redirigimos a la lista de hábitos después de la eliminación
        $_SESSION["exito"] = "Hábito eliminado correctamente";
        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        // Capturamos y mostramos errores de base de datos si ocurren
        $_SESSION["error"] = "Error al eliminar el hábito: " . $e->getMessage();
        header("Location: index.php");
        exit();  
    }
?>