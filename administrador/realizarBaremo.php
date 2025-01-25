<?php
// Activar la visualización de errores para el desarrollo.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivos externos para la cabecera, pie de página, formularios y conexión a base de datos.
include '../header.php';
include '../footer.php';
include '../formularios/input.php';
include '../conexionBd.php';
include 'consultasAdmin.php';

// Variable para almacenar el contenido HTML que se generará.
$html = '';

// Verificar si el formulario se envió mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener el código del curso (separado por un guion) y el número de plazas.
    $codigo = obtenerCodigoCurso($_POST['codigoCurso'] ?? '')[0];
    $nPlazas = $_POST['numeroPlazas'] ?? '';
    
    // Generar el encabezado de la página con los enlaces proporcionados (logout, login, cursos, etc.).
    echo generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');

    // Realizar la baremación de los cursos, si es exitosa, se muestra el pie de página.
    if(obtenerPuntajes($pdo, $codigo, $nPlazas)){
        echo generaFooter(); // Mostrar pie de página si la operación fue exitosa.
    } else {
        // Si la baremación no pudo realizarse, guardar el mensaje de error en la sesión y redirigir al índice.
        $_SESSION['mensaje'] = '<p class = "mensaje">La baremación no se ha podido realizar</p>';
        header('Location:../index.php');
        exit(); // Terminar la ejecución del script después de la redirección.
    }
} else {
    // Si no se envió el formulario mediante POST, mostrar la vista inicial.
    $html = generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');
    // Obtener los cursos cerrados desde la base de datos.
    $html .= obtenerCursosCerrados($pdo);
    $html .= generaFooter(); // Agregar el pie de página.
    echo $html; // Mostrar todo el contenido generado.
}

// Función para obtener el código del curso, separando los componentes del código con un guion.
function obtenerCodigoCurso($codigoCurso){
    return explode("-", $codigoCurso); // Divide el código en partes separadas por el guion y devuelve un arreglo.
}
?>
