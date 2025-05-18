<?php

    session_start();
    require_once '../../includes/verificar_sesion.php';

    // Para que no puedan entrar con el enlace sin iniciar sesión
    verificarSesion();

    // Validamos que se haya enviado un parámetro id por la URL y que sea un número válido
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION["error"] = "ID de meta inválido";
        header("Location: index.php");
        exit();  
    }

    // Convertimos el valor recibido a entero por seguridad
    $id = intval($_GET['id']);
    $id_usuario = $_SESSION['usuario_id'];

    try {
        
        require_once '../../includes/conexion.php';

        // Verificamos que la meta exista y que pertenezca a un hábito del usuario actual
        $verificar = $pdo->prepare("SELECT m.id 
                                    FROM metas m
                                    JOIN habitos h ON m.id_habito = h.id
                                    WHERE m.id = :id AND h.id_usuario = :id_usuario");
        $verificar->execute([
            ':id' => $id,
            ':id_usuario' => $id_usuario
        ]);

        if ($verificar->rowCount() === 0) {
            $_SESSION["error"] = "Meta no encontrada o no tienes permiso para eliminarla.";
            header("Location: index.php");
            exit();  
        }

        
        // Si la verificación es exitosa, eliminamos la meta
        $eliminar = $pdo->prepare("DELETE FROM metas WHERE id = :id");
        $eliminar->execute([':id' => $id]);

        // Redirigimos a la lista de metas después de la eliminación
        $_SESSION["exito"] = "Meta eliminada correctamente.";
        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        // Capturamos y mostramos errores de base de datos si ocurren
        $_SESSION["error"] = "Error con la base de datos. Intentalo más tarde.";
        header("Location: index.php");
        exit();  
    }

?>