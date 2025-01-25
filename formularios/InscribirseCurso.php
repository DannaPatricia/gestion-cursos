<?php
// Activar la visualización de errores para el desarrollo.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivos necesarios para cabecera, pie de página, conexión a base de datos y formularios.
include '../header.php';
include '../footer.php';
include '../conexionBd.php';
include 'input.php';
include 'consultasUsuario.php';

// Inicializar las variables con los valores recibidos del formulario (si existen).
$valorCampos = [
    'fechaNacimiento' => $_POST['fechaNacimiento'] ?? '',
    'codigoCentro' => $_POST['codigoCentro'] ?? '',
    'coordinadorTIC' => $_POST['coordinadorTIC'] ?? '',
    'grupoTIC' => $_POST['grupoTIC'] ?? '',
    'nombreGrupo' => $_POST['nombreGrupo'] ?? '',
    'programaBilingue' => $_POST['programaBilingue'] ?? '',
    'cargo' => $_POST['cargo'] ?? '',
    'nombreCargo' => $_POST['nombreCargo'] ?? '',
    'situacion' => $_POST['situacion'] ?? '',
    'especialidad' => $_POST['especialidad'] ?? '',
];

// Inicialización de un array para controlar si los campos son válidos.
$camposValidos = [
    'fechaNacimiento' => true,
    'codigoCentro' => true,
    'coordinadorTIC' => true,
    'grupoTIC' => true,
    'nombreGrupo' => true,
    'programaBilingue' => true,
    'cargo' => true,
    'nombreCargo' => true,
    'situacion' => true,
    'especialidad' => true,
];

// Inicialización de variables de mensaje y datos del curso.
$mensaje = '';
$codigoCurso = '';
$nombreCurso = '';
$cargoJefe = 'jefe de departamento'; // El cargo del jefe
$cargos = ['secretario', 'director', 'jefe de estudios']; // Posibles cargos

// Verificar si no se ha iniciado la sesión, y si es así, iniciar una nueva sesión.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirigir a la página de login si no está logueado.
if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}

// Si el rol del usuario es 'admin', se bloquea la acción.
if ($_SESSION['rol'] === 'admin') {
    $_SESSION['mensaje'] = '<p class = "mensaje">El administrador no puede realizar solicitudes</p>';
    header('Location: ../index.php');
    exit();
}

// Si el método de solicitud es POST, se procesa la solicitud del formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoCurso = $_POST['codigoCurso'] ?? '';
    $nombreCurso = $_POST['nombreCurso'] ?? '';
    // Asignar validez a los campos según los valores recibidos en el formulario.
    $camposValidos = asignarValidez($camposValidos, $valorCampos);

    // Si todos los campos son válidos, se intenta insertar el solicitante y la solicitud.
    if (compruebaCampos($camposValidos)) {
        if (insertarSolicitante($pdo, $_SESSION['dni'], $_SESSION['nombre'], $_SESSION['apellidos'], $_SESSION['email'], $_SESSION['telefono'], $valorCampos)
            && guardarSolicitud($pdo, $_SESSION['dni'], $codigoCurso)) {
            // Si la inserción es exitosa, sumar los puntos correspondientes y generar el resguardo HTML.
            sumarPuntos($pdo, $cargos, $cargoJefe);
            echo generaResguardoHTML($valorCampos, $_SESSION['dni'], $_SESSION['nombre'], $_SESSION['apellidos'], $_SESSION['email'], $_SESSION['telefono']);
        } else {
            // Si algo falla, se muestra un mensaje de error.
            $mensaje = '<p class="mensaje">No se ha podido realizar la solicitud</p>';
            echo generaFormularioHTML($valorCampos, $camposValidos, $codigoCurso, $nombreCurso, $mensaje);
        }
    } else {
        // Si los campos no son válidos, pedir que se completen todos los campos.
        $mensaje = '<p class="mensaje">Por favor, completa todos los campos</p>';
        echo generaFormularioHTML($valorCampos, $camposValidos, $codigoCurso, $nombreCurso, $mensaje);
    }
} else {
    // Si no es una solicitud POST, procesar otros casos:
    if (!fechaValida(date('Y-m-d'), $_GET['plazoInscripcion']) || ($_GET['numeroPlazas'] <= '0')) {
        // Verificar si el plazo de inscripción ha expirado o si no hay plazas disponibles.
        $_SESSION['mensaje'] = '<p class="mensaje">El plazo de inscripcion ha acabado</p>';
        header('Location: ../index.php');
        exit();
    } elseif (buscarSolicitante($pdo, $_SESSION['dni'])) {
        // Si el solicitante ya ha hecho una solicitud, procesar la solicitud y restar una plaza.
        $codigoNombreCurso = codigoNombreCurso($_GET['btnEnviar']);
        $codigoCurso = $codigoNombreCurso[0];
        guardarSolicitud($pdo, $_SESSION['dni'], $codigoCurso);
        restarNPlazas($pdo, $codigoCurso);
        $_SESSION['mensaje'] = '<p class="mensajeBueno">Se ha enviado la solicitud</p>';
        header('Location: ../index.php');
        exit();
    }
    // Generar el formulario HTML para la inscripción si no se cumple ninguna de las condiciones anteriores.
    $codigoNombreCurso = codigoNombreCurso($_GET['btnEnviar']);
    $codigoCurso = $codigoNombreCurso[0];
    $nombreCurso = $codigoNombreCurso[1];
    echo generaFormularioHTML($valorCampos, $camposValidos, $codigoCurso, $nombreCurso, $mensaje);
}

// Función para separar el código del curso y su nombre.
function codigoNombreCurso($codigoNombre){
    return explode('-', $codigoNombre); // Divide el código completo en dos partes.
}

// Función para comprobar si todos los campos son válidos.
function compruebaCampos($camposValidos) {
    return !in_array(false, $camposValidos, true); // Retorna true si todos los campos son válidos.
}

// Función para generar el formulario HTML.
function generaFormularioHTML($valorCampos, $camposValidos, $codigoCurso, $nombreCurso, $mensaje){
    $html = generaHeader('logout.php', 'login.php', '../index.php', '../cursos.php', '../administrador/opcionesAdmin.php', '../css/style.css');
    $html .= $mensaje;
    $html .= generaFormulario($valorCampos, $camposValidos, $codigoCurso, $nombreCurso); // Genera los campos del formulario.
    $html .= generaFooter(); // Añadir el pie de página.
    return $html;
}

// Función para generar el resguardo de la solicitud.
function generaResguardoHTML($valorCampos, $dni, $nombre, $apellidos, $correo, $telefono){
    $html = generaHeader('logout.php', 'login.php', '../index.php', '../cursos.php', '../administrador/opcionesAdmin.php', '../css/style.css');
    $html .= '<p class="mensajeBueno">Solicitud enviada correctamente</p>';
    $html .= generaResguardo($valorCampos, $dni, $nombre, $apellidos, $correo, $telefono); // Genera el resguardo con los datos del solicitante.
    $html .= generaFooter();
    return $html;
}

// Función para asignar validez a cada campo según su valor.
function asignarValidez($camposValidos, $valorCampos){
    $camposValidos['fechaNacimiento'] = !empty($valorCampos['fechaNacimiento']);
    $camposValidos['codigoCentro'] = !empty($valorCampos['codigoCentro']);
    $camposValidos['coordinadorTIC'] = !empty($valorCampos['coordinadorTIC']);
    $camposValidos['grupoTIC'] = !empty($valorCampos['grupoTIC']);
    $camposValidos['nombreGrupo'] = !empty($valorCampos['nombreGrupo']);
    $camposValidos['programaBilingue'] = !empty($valorCampos['programaBilingue']);
    $camposValidos['cargo'] = !empty($valorCampos['cargo']);
    $camposValidos['nombreCargo'] = ($valorCampos['cargo'] === 'si') ? !empty($valorCampos['nombreCargo']) : true;
    $camposValidos['situacion'] = !empty($valorCampos['situacion']);
    $camposValidos['especialidad'] = !empty($valorCampos['especialidad']);
    return $camposValidos;
}

// Función para generar el formulario con los campos necesarios.
function generaFormulario($valores, $validos, $codigoCurso, $nombreCurso) {
    $html = '<main><form action="InscribirseCurso.php" method="post" class="formulario">';
    $html .= '<h2>Inscripción a '.$nombreCurso.'</h2>';
    $html .= generaHidden('codigoCurso', $codigoCurso);
    $html .= generaHidden('nombreCurso', $nombreCurso);
    // Generación de los diferentes campos del formulario (fecha de nacimiento, código del centro, etc.)
    $html .= generaDate('fechaNacimiento', 'Fecha de nacimiento', $validos['fechaNacimiento'], $valores['fechaNacimiento'], '2005-01-01');
    $html .= generaTexto('codigoCentro', 'Código centro', $valores['codigoCentro'], $validos['codigoCentro'], 'Inserte el código del centro');
    $html .= generaRadio('coordinadorTIC', '¿Es coordinador TIC?', ['si', 'no'], $validos['coordinadorTIC'], $valores['coordinadorTIC']);
    $html .= generaRadio('grupoTIC', '¿Pertenece a un grupo TIC?', ['si', 'no'], $validos['grupoTIC'], $valores['grupoTIC']);
    $html .= generaTexto('nombreGrupo', 'Nombre del grupo TIC', $valores['nombreGrupo'], $validos['nombreGrupo'], 'Inserte nombre del grupo');
    $html .= generaRadio('programaBilingue', '¿Sabe programación bilingüe?', ['si', 'no'], $validos['programaBilingue'], $valores['programaBilingue']);
    $html .= generaRadio('cargo', '¿Tiene algún cargo TIC?', ['si', 'no'], $validos['cargo'], $valores['cargo']);
    $html .= ($valores['cargo'] === 'si') ? generaTexto('nombreCargo', 'Nombre del cargo TIC', $valores['nombreCargo'], $validos['nombreCargo'], 'Inserte nombre del cargo') : '';
    $html .= generaRadio('situacion', '¿Se encuentra activo?', ['si', 'no'], $validos['situacion'], $valores['situacion']);
    $html .= generaTexto('especialidad', 'Especialidad', $valores['especialidad'], $validos['especialidad'], 'Inserte su especialidad');
    $html .= generaSubmit(); // Botón de enviar.
    $html .= '</form></main>';
    return $html;
}

// Función para generar el resguardo de la solicitud con los datos del solicitante.
function generaResguardo($valores, $dni, $nombre, $apellidos, $correo, $telefono){
    $html = '<main><form action="InscribirseCurso.php" method="post" class="formulario">';
    $html .= '<h2>Datos del solicitante</h2>';
    $html .= generaParrafo('DNI', $dni);
    $html .= generaParrafo('Nombre', $nombre);
    $html .= generaParrafo('Apellidos', $apellidos);
    $html .= generaParrafo('Fecha de nacimiento', $valores['fechaNacimiento']);
    $html .= generaParrafo('Correo electrónico', $correo);
    $html .= generaParrafo('Teléfono', $telefono);
    $html .= generaParrafo('Código Centro', $valores['codigoCentro']);
    $html .= generaParrafo('Coordinador TIC', $valores['coordinadorTIC']);
    $html .= generaParrafo('Grupo TIC', $valores['grupoTIC']);
    $html .= generaParrafo('Nombre Grupo', $valores['nombreGrupo']);
    $html .= generaParrafo('Programa Bilingüe', $valores['programaBilingue']);
    $html .= generaParrafo('Cargo', $valores['cargo']);
    $html .= generaParrafo('Nombre Cargo', $valores['nombreCargo']);
    $html .= generaParrafo('Situación', ($valores['situacion'] === 'si') ? 'activo' : 'inactivo');
    $html .= generaParrafo('Especialidad', $valores['especialidad']);
    $html .= '<a href="../index.php" class="btnEnviar">Volver</a>';
    $html .= '</form></main>';
    return $html;
}

// Función para validar la fecha (comparando la fecha actual con el límite de inscripción).
function fechaValida($fechaHoy, $fechaLimite) {
    $fechaHoyConversion = strtotime($fechaHoy); 
    $fechaLimiteConversion = strtotime($fechaLimite);
    return $fechaHoyConversion < $fechaLimiteConversion; // Retorna true si la fecha actual es menor que la fecha límite.
}
?>
