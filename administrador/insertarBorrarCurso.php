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

// Inicia la sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirige al usuario si no está logueado o si tiene el rol de 'cliente'
if (!isset($_SESSION['nombre_usuario']) || $_SESSION['rol'] === 'cliente') {
    header('Location: ../index.php');
    exit();
}

// Variables para almacenar los valores de los campos del formulario
$valorCampos = [
    'nombre' => $_POST['nombre'] ?? '',
    'nPlazas' => $_POST['nPlazas'] ?? '',
    'fechaLimite' => $_POST['fechaLimite'] ?? '',
];

// Variables para validar los campos
$camposValidos = [
    'nombre' => true,
    'nPlazas' => true,
    'fechaLimite' => true,
];

// Lógica para procesar el formulario basado en el método POST o GET
$html;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opcionAdmin = $_POST['opcionAdmin'];
    $codigo = obtenerCodigoCurso($_POST['curso'] ?? '')[0];
    
    if ($opcionAdmin === 'insertar') {
        // Si la opción seleccionada es 'insertar', validar los campos y realizar la inserción
        $camposValidos = asignarValidez($camposValidos, $valorCampos, $valorCampos['nPlazas']);
        if (compruebaCampos($camposValidos)) {
            ejecutarInserccion($pdo, $valorCampos);
        }
        // Mostrar el formulario de inserción
        echo mostrarFormularioInsertar($valorCampos, $camposValidos);
    } else {
        // Si la opción es 'eliminar', ejecutar la eliminación del curso
        ejecutarEliminar($pdo, $codigo);
    }
} else {
    // Si la solicitud es GET, mostrar el formulario adecuado
    $opcionSeleccionada = $_GET['modificar'];
    $html = ($opcionSeleccionada === 'insertar') ? mostrarFormularioInsertar($valorCampos, $camposValidos) : mostrarFormularioEliminar($pdo);
    echo $html;
}

// Función para insertar un curso en la base de datos
function ejecutarInserccion($pdo, $valorCampos){
    $mensaje = '<p class=';
    $mensaje .= insertarCurso($pdo, $valorCampos) ? '"mensajeBueno">Inserción realizada con éxito' : '"mensaje">No se ha podido realizar la inserción';
    $mensaje .= '</p>';
    $_SESSION['mensaje'] = $mensaje;
    header('Location: ../index.php');
    exit();
}

// Función para eliminar un curso de la base de datos
function ejecutarEliminar($pdo, $codigo){
    $mensaje = '<p class=';
    $mensaje .= eliminarCurso($pdo, $codigo) ? '"mensajeBueno">Eliminación realizada con éxito' : '"mensaje">No se ha podido realizar la eliminación';
    $mensaje .= '</p>';
    $_SESSION['mensaje'] = $mensaje;
    header('Location: ../index.php');
    exit();
}
 
// Función para mostrar el formulario de inserción de curso
function mostrarFormularioInsertar($valorCampos, $camposValidos){
    $html = generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');
    $html .= '<main><form action="insertarBorrarCurso.php" method="post" class="formulario">';
    $html .= '<br><h2>DATOS NUEVO CURSO</h2><br>';
    $html .= generaHidden('opcionAdmin', 'insertar');  // Campo oculto para indicar la opción de acción
    $html .= generaTexto('nombre', 'Nombre del curso', $valorCampos['nombre'], $camposValidos['nombre'], 'Inserte nombre del curso');
    $html .= generaCampoNumerico('nPlazas', 'Número de plazas', $valorCampos['nPlazas'], $camposValidos['nPlazas'], 'Inserte número de plazas', ' máx plazas 25');
    $html .= generaDate('fechaLimite', 'Fecha límite inscripción',  $camposValidos['fechaLimite'], $valorCampos['fechaLimite'], '');
    $html .= generaSubmit();
    $html .= '</form></main>';
    $html .= generaFooter();
    return $html;
}

// Función para mostrar el formulario de eliminación de curso
function mostrarFormularioEliminar($pdo){
    $html = generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');
    $html .= '<main><form action="insertarBorrarCurso.php" method="post" class="formulario">';
    $html .= '<br><h2>ELIMINAR CURSO</h2><br>';
    $html .= generaHidden('opcionAdmin', 'eliminar');  // Campo oculto para indicar la opción de eliminar
    $html .= generaSelectBd($pdo);  // Función para generar un select con los cursos de la base de datos
    $html .= generaSubmit();
    $html .= '</form></main>';
    $html .= generaFooter();
    return $html;
}

// Función para comprobar que todos los campos son válidos
function compruebaCampos($camposValidos) {
    return !in_array(false, $camposValidos, true);
}

// Función para obtener el código del curso desde el formato 'codigo-nombre'
function obtenerCodigoCurso($codigoCurso){
    return explode("-", $codigoCurso);
}

// Función para asignar la validez de cada campo en el formulario
function asignarValidez($valoresValidos, $valorCampos, $plazas){
    $valoresValidos['nombre'] = !empty($valorCampos['nombre']);  // Verificar que el nombre no esté vacío
    $valoresValidos['nPlazas'] = !empty($valorCampos['nPlazas']) && compruebaPlazas((int)$plazas);  // Verificar que el número de plazas sea válido
    $valoresValidos['fechaLimite'] = !empty($valorCampos['fechaLimite']);  // Verificar que la fecha límite no esté vacía
    return $valoresValidos;
}

// Función para comprobar que el número de plazas sea válido
function compruebaPlazas($plazas){
    return 0 < $plazas && $plazas <= 25;  // Verificar que las plazas estén en el rango de 1 a 25
}
?>
