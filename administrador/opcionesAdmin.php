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

// Procesa el formulario cuando se envía con el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $btnEnviar = $_POST['btnEnviar'] ?? ''; // Obtiene el valor del botón que fue presionado
    $html = generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');
    $html .= $_SESSION['mensaje']; // Muestra el mensaje guardado en la sesión

    // Dependiendo del valor de 'btnEnviar', se generan diferentes formularios o acciones
    if ($btnEnviar === 'activarDesactivar') {
        $html .= generaRadioAbreCierra(); // Muestra opciones para abrir o cerrar un curso
    } elseif ($btnEnviar === 'listado') {
        $html .= generaOpcionesListado($pdo); // Muestra las opciones para ver el listado de admitidos
    } elseif ($btnEnviar === 'modificarCursos') {
        $html .= generaOpcionesModificar(); // Muestra opciones para insertar o eliminar un curso
    } elseif ($btnEnviar === 'baremo') {
        header('Location:realizarBaremo.php'); // Redirige a la página de baremación
        exit();
    } elseif ($btnEnviar === 'ejecutarAbrirCerrar') {
        $abrirCerrar = $_POST['abrirCerrar'] ?? ''; // Obtiene la acción de abrir o cerrar el curso
        $html .= generaSelectAbreCierra($pdo, $abrirCerrar); // Muestra un select para abrir o cerrar el curso
    } elseif ($btnEnviar === 'asignarNPlazas') {
        // Si se va a asignar el número de plazas
        $nPlazas = $_POST['nPlazas'] ?? '';
        $nPlazasValido = !empty($nPlazas) && validaPlazas((int)$nPlazas); // Verifica que el número de plazas es válido
        $html .= generaOpcionesNPlazas($pdo, $nPlazas); // Muestra el formulario para asignar número de plazas
        if ($nPlazasValido) {
            $codigo = obtenerCodigoCurso($_POST['curso'] ?? '')[0];
            if (asignarNPlazas($pdo, $nPlazas, $codigo)) {
                $_SESSION['mensaje'] = '<p class = "mensajeBueno">Operación realizada con éxito</p>';
            } else {
                $_SESSION['mensaje'] = '<p class = "mensaje">Operación no se ha podido realizar</p>';
            }
            header('Location: ../index.php');
            exit();
        } else {
            $_SESSION['mensaje'] = '<p class = "mensaje">Número de plazas no válido</p>';
        }
    }

    // Finaliza el HTML generando el pie de página
    $html .= generaFooter();
    echo $html;
} else {
    // Si el formulario no se ha enviado, muestra el menú de opciones
    echo generaMenu();
}

// Función para generar el menú de opciones del administrador
function generaMenu(){
    $html = generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');
    $html .= generaOpcionesMenu(); // Muestra las opciones disponibles para el administrador
    $html .= generaFooter();
    return $html;
}

// Función para generar las opciones del menú
function generaOpcionesMenu(){
    $html = '<main><form action="opcionesAdmin.php" method="post" class="formulario">';
    $html .= '<br><h2>Opciones de administrador</h2><br>';
    $html .= generaOpcion('Abrir/Cerrar curso', 'activarDesactivar'); // Opción para abrir o cerrar un curso
    $html .= generaOpcion('Mostrar listado de admitidos en un curso', 'listado'); // Opción para mostrar listado de admitidos
    $html .= generaOpcion('Insertar/Eliminar curso', 'modificarCursos'); // Opción para insertar o eliminar cursos
    $html .= generaOpcion('Baremación', 'baremo'); // Opción para realizar baremación
    $html .= generaOpcion('Insertar número de plazas', 'asignarNPlazas'); // Opción para asignar número de plazas
    $html .= '</form></main>';
    return $html;
}

// Función para mostrar las opciones de abrir o cerrar un curso
function generaRadioAbreCierra(){
    $html = '<main><form action="opcionesAdmin.php" method="post" class="formulario">';
    $html .= '<br><h2>ELIGE OPCIÓN</h2><br>';
    $html .= generaHidden('opcionAdmin', 'abrirCerrar');
    $html .= generaRadio('abrirCerrar', 'Abrir/Cerrar curso', ['abrir', 'cerrar'], true, 'abrir'); // Radio buttons para elegir entre abrir o cerrar
    $html .= generaOpcion("Enviar", "ejecutarAbrirCerrar");
    $html .= '</form></main>';
    return $html;
}

// Función para generar el formulario para abrir o cerrar un curso
function generaSelectAbreCierra($pdo, $abrirCerrar){
    $html = '<main><form action="ejecutaOpciones.php" method="post" class="formulario">';
    $html .= '<br><h2>'.strtoupper($abrirCerrar).' CURSO</h2><br>';
    $html .= generaHidden('opcionAdmin', 'abrirCerrar');
    $html .= generaHidden('abrirCerrar', $abrirCerrar);
    $html .= generaSelectBd($pdo, ($abrirCerrar === 'cerrar') ? true : false); // Selección de curso para abrir o cerrar
    $html .= generaSubmit();
    $html .= '</form></main>';
    return $html;
}

// Función para mostrar el listado de admitidos en un curso
function generaOpcionesListado($pdo){
    $html = '<main><form action="ejecutaOpciones.php" method="post" class="formulario">';
    $html .= '<br><h2>Elige curso</h2><br>';
    $html .= generaHidden('opcionAdmin', 'obtenerListado');
    $html .= generaSelectBd($pdo); // Muestra el listado de cursos para elegir
    $html .= generaSubmit();
    $html .= '</form></main>';
    return $html;
}

// Función para mostrar opciones para insertar o eliminar un curso
function generaOpcionesModificar(){
    $html = '<main><form action="insertarBorrarCurso.php" method="get" class="formulario">';
    $html .= '<br><h2>Elige Opcion</h2><br>';
    $html .= generaHidden('opcionAdmin', 'modificar');
    $html .= generaRadio('modificar', 'Insertar/Eliminar curso', ['insertar', 'eliminar'], true, 'insertar'); // Radio buttons para insertar o eliminar
    $html .= generaSubmit();
    $html .= '</form></main>';
    return $html;
}

// Función para asignar el número de plazas a un curso
function generaOpcionesNPlazas($pdo, $nPlazas){
    $html = '<main><form action="opcionesAdmin.php" method="post" class="formulario">';
    $html .= '<br><h2>ASIGNAR NÚMERO DE PLAZAS</h2><br>';
    $html .= generaHidden('opcionAdmin', 'asignarNPlazas');
    $html .= generaCampoNumerico('nPlazas', 'Inserta número de plazas', $nPlazas, true, 'Inserta número de plazas, mínimo 10 y máximo 30', 'Minimo 10 y máximo 30'); // Campo para asignar número de plazas
    $html .= generaSelectBd($pdo, true); // Selección de curso para asignar plazas
    $html .= generaOpcion("Enviar", "asignarNPlazas");
    $html .= '</form></main>';
    return $html;
}

// Función para validar que el número de plazas está entre 10 y 30
function validaPlazas($plazas){
    return $plazas > 10 && $plazas <= 30;
}

// Función para obtener el código de un curso a partir de su nombre
function obtenerCodigoCurso($codigoCurso){
    return explode("-", $codigoCurso); // Divide el código del curso en partes
}
?>
