<?php
// Habilita la visualización de errores para facilitar la depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclusión de archivos necesarios para la ejecución del script
include '../header.php';
include '../footer.php';
include '../formularios/input.php';
include '../conexionBd.php';
include 'consultasAdmin.php';

// Inicia la sesión si aún no ha sido iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica si el usuario tiene una sesión activa y es cliente; de lo contrario, redirige al inicio
if (!isset($_SESSION['nombre_usuario']) && $_SESSION['rol'] === 'cliente') {
    header('Location: ../index.php');
    exit();
}

// Variable utilizada para guardar un valor booleano en el contexto del script
$booleano;

// Manejo de solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera la opción seleccionada por el administrador
    $opcionAdmin = $_POST['opcionAdmin'] ?? '';
    // Obtiene el código y nombre del curso a partir de la entrada del formulario
    $codigo = obtenerCodigoCurso($_POST['curso'] ?? '')[0];
    $curso = obtenerCodigoCurso($_POST['curso'] ?? '')[1];

    // Lógica para abrir o cerrar un curso según la opción seleccionada
    if ($opcionAdmin === 'abrirCerrar') {
        $abrirCerrar = $_POST['abrirCerrar'] ?? '';
        ejecutarCambioDEstado($pdo, $codigo, $abrirCerrar);
    } 
    // Lógica para mostrar el listado de admitidos en un curso
    elseif ($opcionAdmin === 'obtenerListado') {
        echo mostrarListado($pdo, $codigo, $curso);
    }
}

/**
 * Cambia el estado de un curso a abierto o cerrado.
 * @param PDO $pdo Objeto PDO para conexión a la base de datos.
 * @param string $codigo Código del curso.
 * @param string $abrirCerrar Acción solicitada ('abrir' o 'cerrar').
 * @return void Redirige a la página principal después de ejecutar la acción.
 */
function ejecutarCambioDEstado($pdo, $codigo, $abrirCerrar) {
    $valor = ($abrirCerrar === 'abrir') ? 1 : 0; // Determina el estado según la acción
    $mensaje = '<p class=';
    $mensaje .= (activarDesactivarCurso($pdo, $valor, $codigo)) 
        ? '"mensajeBueno">Se ha realizado la acción' 
        : '"mensaje">No se ha podido realizar la acción, seleccione un curso válido';
    $mensaje .= '</p>';
    $_SESSION['mensaje'] = $mensaje; // Guarda el mensaje en la sesión
    header('Location: ../index.php'); // Redirige al inicio
    exit();
}

/**
 * Obtiene el código y el nombre de un curso a partir de una cadena de texto.
 * @param string $codigoCurso Cadena que incluye el código y nombre del curso separados por un guion.
 * @return array Array con el código en la posición 0 y el nombre en la posición 1.
 */
function obtenerCodigoCurso($codigoCurso) {
    return explode("-", $codigoCurso);
}

/**
 * Genera el HTML con el listado de admitidos en un curso.
 * @param PDO $pdo Objeto PDO para conexión a la base de datos.
 * @param string $codigoCurso Código del curso.
 * @param string $nombreCurso Nombre del curso.
 * @return string HTML con el encabezado, listado de admitidos y pie de página.
 */
function mostrarListado($pdo, $codigoCurso, $nombreCurso) {
    // Genera el encabezado del HTML con los enlaces necesarios
    $html = generaHeader(
        '../formularios/logout.php', 
        '../formularios/login.php', 
        '../index.php', 
        '../cursos.php', 
        'opcionesAdmin.php', 
        '../css/style.css'
    );

    // Título para la sección del listado de admitidos
    $html .= '<br><h2>ADMITIDOS EN ' . strtoupper($nombreCurso) . '</h2>';

    // Obtiene el listado de admitidos en el curso
    $html .= obtenerListado($pdo, $codigoCurso);

    // Genera el pie de página
    $html .= generaFooter();

    return $html;
}
?>
