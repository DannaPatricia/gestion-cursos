<?php
// Configuración para mostrar errores en el entorno de desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclusión de dependencias necesarias para el funcionamiento del script
include '../header.php';
include '../footer.php';
include '../formularios/input.php';
include '../conexionBd.php';
include 'consultasAdmin.php';

// Inicio de sesión, si aún no se ha iniciado
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificación de sesión activa y permisos de rol
if (!isset($_SESSION['nombre_usuario']) || $_SESSION['rol'] === 'cliente') {
    header('Location: ../index.php');
    exit();
}

// Inicialización de valores para los campos del formulario
$valorCampos = [
    'nombre' => $_POST['nombre'] ?? '',
    'nPlazas' => $_POST['nPlazas'] ?? '',
    'fechaLimite' => $_POST['fechaLimite'] ?? '',
];

// Validación inicial de los campos del formulario
$camposValidos = [
    'nombre' => true,
    'nPlazas' => true,
    'fechaLimite' => true,
];

// Variable para generar la salida HTML dinámica
$html;

// Manejo de solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opcionAdmin = $_POST['opcionAdmin']; // Obtener la acción seleccionada por el administrador
    $codigo = obtenerCodigoCurso($_POST['curso'] ?? '')[0]; // Obtener código del curso

    // Insertar un nuevo curso
    if ($opcionAdmin === 'insertar') {
        $camposValidos = asignarValidez($camposValidos, $valorCampos, $valorCampos['nPlazas']);
        if (compruebaCampos($camposValidos)) {
            ejecutarInserccion($pdo, $valorCampos);
        }
        echo mostrarFormularioInsertar($valorCampos, $camposValidos);
    } 
    // Eliminar un curso
    else {
        ejecutarEliminar($pdo, $codigo);
    }
} else {
    // Determina si se muestra el formulario de inserción o eliminación
    $opcionSeleccionada = $_GET['modificar'];
    $html = ($opcionSeleccionada === 'insertar') 
        ? mostrarFormularioInsertar($valorCampos, $camposValidos) 
        : mostrarFormularioEliminar($pdo);
    echo $html;
}

/**
 * Ejecuta la lógica para insertar un nuevo curso.
 * @param PDO $pdo Conexión a la base de datos.
 * @param array $valorCampos Valores del formulario.
 */
function ejecutarInserccion($pdo, $valorCampos) {
    $mensaje = '<p class=';
    $mensaje .= insertarCurso($pdo, $valorCampos) 
        ? '"mensajeBueno">Inserción realizada con éxito' 
        : '"mensaje">No se ha podido realizar la inserción';
    $mensaje .= '</p>';
    $_SESSION['mensaje'] = $mensaje;
    header('Location: ../index.php');
    exit();
}

/**
 * Ejecuta la lógica para eliminar un curso.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string $codigo Código del curso a eliminar.
 */
function ejecutarEliminar($pdo, $codigo) {
    $mensaje = '<p class=';
    $mensaje .= eliminarCurso($pdo, $codigo) 
        ? '"mensajeBueno">Eliminación realizada con éxito' 
        : '"mensaje">No se ha podido realizar la eliminación';
    $mensaje .= '</p>';
    $_SESSION['mensaje'] = $mensaje;
    header('Location: ../index.php');
    exit();
}

/**
 * Muestra el formulario para insertar un nuevo curso.
 * @param array $valorCampos Valores actuales del formulario.
 * @param array $camposValidos Estados de validez de los campos.
 * @return string HTML del formulario.
 */
function mostrarFormularioInsertar($valorCampos, $camposValidos) {
    $html = generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');
    $html .= '<main><form action="insertarBorrarCurso.php" method="post" class="formulario">';
    $html .= '<br><h2>DATOS NUEVO CURSO</h2><br>';
    $html .= generaHidden('opcionAdmin', 'insertar');
    $html .= generaTexto('nombre', 'Nombre del curso', $valorCampos['nombre'], $camposValidos['nombre'], 'Inserte nombre del curso');
    $html .= generaCampoNumerico('nPlazas', 'Número de plazas', $valorCampos['nPlazas'], $camposValidos['nPlazas'], 'Inserte número de plazas', ' máx plazas 25');
    $html .= generaDate('fechaLimite', 'Fecha límite inscripción', $camposValidos['fechaLimite'], $valorCampos['fechaLimite'], '');
    $html .= generaSubmit();
    $html .= '</form></main>';
    $html .= generaFooter();
    return $html;
}

/**
 * Muestra el formulario para eliminar un curso.
 * @param PDO $pdo Conexión a la base de datos.
 * @return string HTML del formulario.
 */
function mostrarFormularioEliminar($pdo) {
    $html = generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');
    $html .= '<main><form action="insertarBorrarCurso.php" method="post" class="formulario">';
    $html .= '<br><h2>ELIMINAR CURSO</h2><br>';
    $html .= generaHidden('opcionAdmin', 'eliminar');
    $html .= generaSelectBd($pdo);
    $html .= generaSubmit();
    $html .= '</form></main>';
    $html .= generaFooter();
    return $html;
}

/**
 * Comprueba si todos los campos son válidos.
 * @param array $camposValidos Estados de validez de los campos.
 * @return bool Resultado de la validación.
 */
function compruebaCampos($camposValidos) {
    return !in_array(false, $camposValidos, true);
}

/**
 * Obtiene el código y el nombre de un curso.
 * @param string $codigoCurso Cadena que contiene el código y nombre del curso.
 * @return array Código y nombre del curso.
 */
function obtenerCodigoCurso($codigoCurso) {
    return explode("-", $codigoCurso);
}

/**
 * Asigna validez a los campos basándose en las reglas definidas.
 * @param array $valoresValidos Estados actuales de los campos.
 * @param array $valorCampos Valores de los campos.
 * @param int $plazas Número de plazas ingresado.
 * @return array Estados de validez actualizados.
 */
function asignarValidez($valoresValidos, $valorCampos, $plazas) {
    $valoresValidos['nombre'] = !empty($valorCampos['nombre']);
    $valoresValidos['estado'] = !empty($valorCampos['estado']);
    $valoresValidos['nPlazas'] = !empty($valorCampos['nPlazas']) && compruebaPlazas((int)$plazas);
    $valoresValidos['fechaLimite'] = !empty($valorCampos['fechaLimite']);
    return $valoresValidos;
}

/**
 * Verifica si el número de plazas está dentro del rango permitido.
 * @param int $plazas Número de plazas.
 * @return bool Resultado de la verificación.
 */
function compruebaPlazas($plazas) {
    return 0 < $plazas && $plazas <= 25;
}
?>
