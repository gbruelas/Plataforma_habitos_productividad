<?php

    session_start();
    require_once '../includes/verificar_sesion.php';

    // Verificar que el usuario haya iniciado sesión
    verificarSesion();

    require_once '../includes/conexion.php';

    // Procesar formulario POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $usuario_id = $_SESSION['usuario_id'];
            $id_habito = $_POST['id_habito'] ?? null;
            $hoy = date('Y-m-d');

            // Validación básica
            if (empty($id_habito)) {
                $_SESSION['error'] = "ID de hábito no proporcionado.";
                header("Location: index.php");
                exit();
            }

            // Verificar que el hábito pertenezca al usuario
            $sqlVerificar = "SELECT id FROM habitos WHERE id = :id AND id_usuario = :usuario_id";
            $stmtVerificar = $pdo->prepare($sqlVerificar);
            $stmtVerificar->execute([
                ':id' => $id_habito,
                ':usuario_id' => $usuario_id
            ]);

            if ($stmtVerificar->rowCount() === 0) {
                $_SESSION['error'] = "No tienes permiso para este hábito o no existe.";
                header("Location: index.php");
                exit();
            }

            // Actualizar el seguimiento
            $sqlActualizar = "UPDATE seguimiento_habito 
                                SET cumplido = 1 
                                WHERE id_habito = :id 
                                AND fecha = :fecha";
            
            $stmtActualizar = $pdo->prepare($sqlActualizar);
            $stmtActualizar->execute([
                ':id' => $id_habito,
                ':fecha' => $hoy
            ]);

            // Redirección con mensaje de éxito 
            $_SESSION['exito'] = "¡Hábito completado con éxito!";
            header("Location: index.php");
            exit();

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header("Location: index.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Error:" . $e->getMessage();
            header("Location: index.php");
            exit();
        }
    } else {
        // Si no es POST, redirigir
        $_SESSION['error'] = "Método no permitido";
        header("Location: index.php");
        exit();
    }

?>