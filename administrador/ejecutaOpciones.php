<?php
// Configura la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluye archivos necesarios para el funcionamiento de la página
include '../header.php';        // Cabecera del sitio web
include '../footer.php';        // Pie de página del sitio web
include '../formularios/input.php'; // Funciones para los formularios
include '../conexionBd.php';    // Conexión a la base de datos
include 'consultasAdmin.php';  // Funciones específicas para la administración

// Inicia la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica si el usuario no está logueado o si tiene rol de 'cliente' (no permitido en la página de administración)
if (!isset($_SESSION['nombre_usuario']) && $_SESSION['rol'] === 'cliente') {
    header('Location: ../index.php');  // Redirige al índice si no es un administrador
    exit();
}

// Variable booleana (se usa más tarde en el código)
$booleano;

// Si se recibe una solicitud POST desde un formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibe la opción de administración seleccionada
    $opcionAdmin = $_POST['opcionAdmin'] ?? '';
    // Obtiene el código del curso y su nombre mediante la función obtenerCodigoCurso
    $codigo = obtenerCodigoCurso($_POST['curso'] ?? '')[0];
    $curso = obtenerCodigoCurso($_POST['curso'] ?? '')[1];
    // Si la opción seleccionada es 'abrirCerrar', ejecuta el cambio de estado
    if($opcionAdmin === 'abrirCerrar'){
        // Recibe la acción de abrir o cerrar el curso
        $abrirCerrar = $_POST['abrirCerrar'] ?? '';
        ejecutarCambioDEstado($pdo, $codigo, $abrirCerrar);  // Llama a la función para ejecutar el cambio de estado
    }
    // Si la opción es 'obtenerListado', muestra el listado de los solicitantes admitidos
    elseif($opcionAdmin === 'obtenerListado'){
        echo mostrarListado($pdo, $codigo, $curso);  // Muestra el listado de admitidos en el curso
    }
}

// Función para ejecutar el cambio de estado del curso (abrir o cerrar)
function ejecutarCambioDEstado($pdo, $codigo, $abrirCerrar){
    // Establece el valor de 'abrir' (1) o 'cerrar' (0) según la acción seleccionada
    $valor = ($abrirCerrar === 'abrir') ? 1 : 0;
    $mensaje = '<p class=';
    // Si el cambio de estado fue exitoso, muestra un mensaje positivo, de lo contrario, muestra un mensaje de error
    $mensaje .= (activarDesactivarCurso($pdo, $valor, $codigo)) ? '"mensajeBueno">Se ha realizado la acción' : '"mensaje">No se ha podido realizar la acción, seleccione un curso válido';
    $mensaje .= '</p>';
    // Guarda el mensaje en la sesión para mostrarlo en la siguiente página
    $_SESSION['mensaje'] = $mensaje;
    // Redirige al usuario a la página principal
    header('Location: ../index.php');
    exit();
}

// Función que obtiene el código y nombre del curso desde el valor recibido
function obtenerCodigoCurso($codigoCurso){
    return explode("-", $codigoCurso);  // Divide el valor del curso (por ejemplo '123-curso') en dos partes
}

// Función que genera la página para mostrar el listado de solicitantes admitidos en el curso
function mostrarListado($pdo, $codigoCurso, $nombreCurso){
    // Genera la cabecera HTML
    $html = generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');
    // Agrega el título para el listado de admitidos
    $html .= '<br><h2>ADMITIDOS EN '.strtoupper($nombreCurso).'</h2>';
    // Llama a la función para obtener y mostrar el listado de admitidos en el curso
    $html .= obtenerListado($pdo, $codigoCurso);
    // Agrega el pie de página HTML
    $html .= generaFooter();
    return $html;
}
?>
