<?php
// Configuración para mostrar errores de PHP en el navegador durante el desarrollo.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Se incluyen archivos necesarios para el funcionamiento del sistema.
include '../header.php'; // Archivo con el encabezado del sitio web.
include '../footer.php'; // Archivo con el pie de página del sitio web.
include '../conexionBd.php'; // Archivo que maneja la conexión a la base de datos.
include 'input.php'; // Archivo que contiene funciones para manejar los datos de los formularios.
include 'consultasUsuario.php'; // Archivo que contiene funciones específicas para manejar las consultas de usuarios.

// Se inicializan los valores que vendrán del formulario de inscripción.
$valorCampos = [
    'fechaNacimiento' => $_POST['fechaNacimiento'] ?? '', // Si no existe el valor de fechaNacimiento, se deja vacío.
    'codigoCentro' => $_POST['codigoCentro'] ?? '', // Lo mismo para el código del centro.
    'coordinadorTIC' => $_POST['coordinadorTIC'] ?? '', // Si no existe, lo dejamos vacío.
    'grupoTIC' => $_POST['grupoTIC'] ?? '',
    'nombreGrupo' => $_POST['nombreGrupo'] ?? '',
    'programaBilingue' => $_POST['programaBilingue'] ?? '',
    'cargo' => $_POST['cargo'] ?? '',
    'nombreCargo' => $_POST['nombreCargo'] ?? '',
    'situacion' => $_POST['situacion'] ?? '',
    'especialidad' => $_POST['especialidad'] ?? ''
];

// Se definen los campos válidos, por defecto todos son válidos (true).
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

// Inicialización de variables para el curso y mensaje.
$mensaje = '';
$codigoCurso = '';
$nombreCurso = '';
$cargoJefe = 'jefe de departamento'; // Se define el cargo de jefe de departamento.
$cargos = ['secretario', 'director', 'jefe de estudios']; // Lista de cargos posibles.

// Verifica si la sesión no está iniciada y la inicia si es necesario.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica si el usuario no está autenticado, si no lo está redirige al login.
if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}

// Si el usuario es administrador, redirige con un mensaje indicando que no puede realizar solicitudes.
if ($_SESSION['rol'] === 'admin') {
    $_SESSION['mensaje'] = '<p class = "mensaje">El administrador no puede realizar solicitudes</p>';
    header('Location: ../index.php');
    exit();
}

// Si el método de la solicitud es POST (es decir, se envió el formulario).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoCurso = $_POST['codigoCurso'] ?? ''; // Se obtiene el código del curso del formulario.
    $nombreCurso = $_POST['nombreCurso'] ?? ''; // Se obtiene el nombre del curso.

    // Se actualiza la validez de los campos con los valores del formulario.
    $camposValidos = asignarValidez($camposValidos, $valorCampos);

    // Se comprueba si todos los campos son válidos.
    if (compruebaCampos($camposValidos)) {
        // Si los campos son válidos, intenta insertar el solicitante y guardar la solicitud.
        if (insertarSolicitante($pdo, $_SESSION['dni'], $_SESSION['nombre'], $_SESSION['apellidos'], $_SESSION['email'], $_SESSION['telefono'], $valorCampos)
            && guardarSolicitud($pdo, $_SESSION['dni'], $codigoCurso)) {
            // Si la inserción fue exitosa, se suman los puntos y se genera el resguardo.
            sumarPuntos($pdo, $cargos, $cargoJefe);
            echo generaResguardoHTML($valorCampos, $_SESSION['dni'], $_SESSION['nombre'], $_SESSION['apellidos'], $_SESSION['email'], $_SESSION['telefono']);
        } else {
            // Si no se pudo insertar, se muestra un mensaje de error.
            $mensaje = '<p class="mensaje">No se ha podido realizar la solicitud</p>';
            echo generaFormularioHTML($valorCampos, $camposValidos, $codigoCurso, $nombreCurso, $mensaje);
        }
    } else {
        // Si los campos no son válidos, muestra un mensaje pidiendo completar los campos.
        $mensaje = '<p class="mensaje">Por favor, completa todos los campos</p>';
        echo generaFormularioHTML($valorCampos, $camposValidos, $codigoCurso, $nombreCurso, $mensaje);
    }
} else {
    //Se obtiene el código y nombre del curso.
    $codigoNombreCurso = codigoNombreCurso($_GET['btnEnviar']);
    $codigoCurso = $codigoNombreCurso[0];
    $nombreCurso = $codigoNombreCurso[1];
    // Si el método no es POST, se verifica si la fecha de inscripción es válida.
    if (!fechaValida(date('Y-m-d'), $_GET['plazoInscripcion']) || ($_GET['numeroPlazas'] <= '0')) {
        // Si la fecha de inscripción ya ha pasado o no hay plazas, redirige con un mensaje.
        $_SESSION['mensaje'] = '<p class="mensaje">El plazo de inscripcion ha acabado</p>';
        header('Location: ../index.php');
        exit();
    }elseif(compruebaSolicitudYaRealizada($pdo, $_SESSION['dni'], $codigoCurso)){
        // Si el solicitante ya ha sido admitido, redirige con un mensaje.
        $_SESSION['mensaje'] = '<p class="mensaje">Ya ha solicitado este curso</p>';
        header('Location: ../index.php');
        exit();
    }elseif (buscarSolicitante($pdo, $_SESSION['dni'])) {
        // Si ya se ha enviado una solicitud, se obtiene el código y nombre del curso.
        $codigoNombreCurso = codigoNombreCurso($_GET['btnEnviar']);
        $codigoCurso = $codigoNombreCurso[0];

        // Se guarda la solicitud y redirige con un mensaje de éxito.
        guardarSolicitud($pdo, $_SESSION['dni'], $codigoCurso);
        $_SESSION['mensaje'] = '<p class="mensajeBueno">Se ha enviado la solicitud</p>';
        header('Location: ../index.php');
        exit();
    }
    // Se genera y muestra el formulario de inscripción.
    echo generaFormularioHTML($valorCampos, $camposValidos, $codigoCurso, $nombreCurso, $mensaje);
}

// Función que devuelve un array con el código y nombre del curso separados.
function codigoNombreCurso($codigoNombre) {
    return explode('-', $codigoNombre);
}

// Función que comprueba si todos los campos son válidos.
function compruebaCampos($camposValidos) {
    return !in_array(false, $camposValidos, true); // Devuelve true si no hay valores false en el array.
}

// Generación del formulario HTML.
function generaFormularioHTML($valorCampos, $camposValidos, $codigoCurso, $nombreCurso, $mensaje) {
    $html = generaHeader('logout.php', 'login.php', '../index.php', '../cursos.php', '../administrador/opcionesAdmin.php', '../css/style.css');
    $html .= $mensaje; // Muestra el mensaje (si hay alguno).
    $html .= generaFormulario($valorCampos, $camposValidos, $codigoCurso, $nombreCurso); // Llama a la función que genera el formulario HTML.
    $html .= generaFooter(); // Añade el pie de página.
    return $html; // Devuelve el HTML generado.
}

// Generación del resguardo HTML después de enviar la solicitud.
function generaResguardoHTML($valorCampos, $dni, $nombre, $apellidos, $correo, $telefono) {
    $html = generaHeader('logout.php', 'login.php', '../index.php', '../cursos.php', '../administrador/opcionesAdmin.php', '../css/style.css');
    $html .= '<p class="mensajeBueno">Solicitud enviada correctamente</p>'; // Muestra el mensaje de éxito.
    $html .= generaResguardo($valorCampos, $dni, $nombre, $apellidos, $correo, $telefono); // Genera los detalles del resguardo.
    $html .= generaFooter(); // Añade el pie de página.
    return $html; // Devuelve el HTML generado.
}

// Función para verificar si los campos son válidos.
function asignarValidez($camposValidos, $valorCampos) {
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
    return $camposValidos; // Devuelve el array de campos validados.
}

// Función para generar el formulario HTML.
function generaFormulario($valores, $validos, $codigoCurso, $nombreCurso) {
    // Construye un formulario HTML con todos los campos necesarios.
    $html = '<main><form action="InscribirseCurso.php" method="post" class="formulario">';
    $html .= '<h2>Inscripción a '.$nombreCurso.'</h2>';
    $html .= generaHidden('codigoCurso', $codigoCurso); // Campo oculto para el código del curso.
    $html .= generaHidden('nombreCurso', $nombreCurso); // Campo oculto para el nombre del curso.
    // Aquí se generan los campos del formulario (fecha de nacimiento, código centro, etc).
    $html .= generaDate('fechaNacimiento', 'Fecha de nacimiento', $validos['fechaNacimiento'], $valores['fechaNacimiento'], '2005-01-01');
    $html .= generaTexto('codigoCentro', 'Código centro', $valores['codigoCentro'], $validos['codigoCentro'], 'Inserte el código del centro');
    $html .= generaRadio('coordinadorTIC', '¿Es coordinador TIC?', ['si', 'no'], $validos['coordinadorTIC'], $valores['coordinadorTIC']);
    $html .= generaRadio('grupoTIC', '¿Pertenece a un grupo TIC?', ['si', 'no'], $validos['grupoTIC'], $valores['grupoTIC']);
    $html .= generaTexto('nombreGrupo', 'Nombre del grupo TIC', $valores['nombreGrupo'], $validos['nombreGrupo'], 'Inserte nombre del grupo');
    $html .= generaRadio('programaBilingue', '¿Sabe programación bilingüe?', ['si', 'no'], $validos['programaBilingue'], $valores['programaBilingue']);
    $html .= generaRadio('cargo', '¿Tiene algún cargo TIC?', ['si', 'no'], $validos['cargo'], $valores['cargo']);
    // Si el cargo es 'si', muestra un campo para el nombre del cargo.
    $html .= ($valores['cargo'] === 'si') ? generaTexto('nombreCargo', 'Nombre del cargo TIC', $valores['nombreCargo'], $validos['nombreCargo'], 'Inserte nombre del cargo') : '';
    $html .= generaRadio('situacion', '¿Se encuentra activo?', ['si', 'no'], $validos['situacion'], $valores['situacion']);
    $html .= generaTexto('especialidad', 'Especialidad', $valores['especialidad'], $validos['especialidad'], 'Inserte su especialidad');
    $html .= generaSubmit(); // Botón de envío del formulario.
    $html .= '</form></main>';
    return $html; // Devuelve el formulario generado.
}

// Función que genera el resguardo con los datos del solicitante.
function generaResguardo($valores, $dni, $nombre, $apellidos, $correo, $telefono) {
    $html = '<main><form action="InscribirseCurso.php" method="post" class="formulario">';
    $html .= '<h2>Datos del solicitante</h2>';
    // Muestra los detalles del solicitante en párrafos.
    $html .= generaParrafo('DNI', $dni);
    $html .= generaParrafo('Nombre', $nombre);
    $html .= generaParrafo('Apellidos', $apellidos);
    $html .= generaParrafo('Fecha de nacimiento', $valores['fechaNacimiento']);
    $html .= generaParrafo('Correo electrónico', $correo);
    $html .= generaParrafo('Teléfono', $telefono);
    $html .= generaParrafo('Código centro', $valores['codigoCentro']);
    $html .= generaParrafo('Coordinador TIC', $valores['coordinadorTIC']);
    $html .= generaParrafo('Grupo TIC', $valores['grupoTIC']);
    $html .= generaParrafo('Nombre del grupo', $valores['nombreGrupo']);
    $html .= generaParrafo('Programa bilingüe', $valores['programaBilingue']);
    $html .= generaParrafo('Cargo', $valores['cargo']);
    $html .= generaParrafo('Nombre del cargo', $valores['nombreCargo']);
    $html .= generaParrafo('Situación', ($valores['situacion'] === 'si') ? 'activo' : 'inactivo');
    $html .= generaParrafo('Especialidad', $valores['especialidad']);
    $html .= '<a href = "../index.php" class = "btnEnviar">Volver</a></div></form></main>';
    return $html; // Devuelve el HTML con los detalles del resguardo.
}

// Función que verifica si la fecha es válida (si no ha pasado).
function fechaValida($fechaHoy, $fechaLimite) {
    $fechaHoyConversion = strtotime($fechaHoy); // Convierte la fecha actual en timestamp.
    $fechaLimiteConversion = strtotime($fechaLimite); // Convierte la fecha límite en timestamp.
    return $fechaHoyConversion < $fechaLimiteConversion; // Compara las fechas.
}
?>
