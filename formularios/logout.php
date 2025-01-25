<?php
session_start(); // Inicia la sesión para poder acceder a las variables de sesión

// Comprueba si la sesión del usuario está activa (es decir, si la variable de sesión 'id_usuario' está establecida)
if (isset($_SESSION['id_usuario'])) {
    // Si la sesión está activa, se desactiva:
    session_unset();   // Elimina todas las variables de sesión
    session_destroy(); // Destruye la sesión

    // Redirige al usuario a la página principal (index.php)
    header('Location: ../index.php');
    exit(); // Termina la ejecución del script después de la redirección
} else {
    // Si no hay una sesión activa (no está configurada 'id_usuario'), redirige al usuario a la página de cursos
    header('Location: ../cursos.php');
    exit(); // Termina la ejecución del script después de la redirección
}
?>
