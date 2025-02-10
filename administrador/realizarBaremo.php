<?php
// Configuración para mostrar errores en la pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclusión de archivos necesarios
include '../header.php';
include '../footer.php';
include '../formularios/input.php';
include '../conexionBd.php';
include 'consultasAdmin.php';

// Variable para almacenar el HTML generado
$html = '';

// Si el formulario es enviado con el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener el código del curso desde el formulario
    $codigo = obtenerCodigoCurso($_POST['codigoCurso'] ?? '')[0];
    // Obtener el número de plazas desde el formulario
    $nPlazas = $_POST['numeroPlazas'] ?? '';
    
    // Generar el encabezado de la página
    echo generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');
    
    // Llamar a la función que realiza la baremación, pasando el código del curso y el número de plazas
    if(obtenerPuntajes($pdo, $codigo, $nPlazas)){
        // Si la baremación se realiza con éxito, solo muestra el pie de página
        echo generaFooter();
    } else {
        // Si hay un error en la baremación, se muestra un mensaje de error y se redirige
        $_SESSION['mensaje'] = '<p class = "mensaje">La baremación no se ha podido realizar</p>';
        header('Location:../index.php');
        exit();
    }
} else {
    // Si no se ha enviado el formulario, generar la página inicial
    $html = generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');
    
    // Obtener los cursos cerrados desde la base de datos y mostrarlos
    $html .= obtenerCursosCerrados($pdo);
    
    // Agregar el pie de página
    $html .= generaFooter();
    
    // Mostrar el HTML generado
    echo $html;
}

// Función para obtener el código del curso desde su nombre (códigoCurso)
function obtenerCodigoCurso($codigoCurso){
    return explode("-", $codigoCurso); // Divide el código en partes usando el guion como separador
}
?>
