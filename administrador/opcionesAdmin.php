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

// Comprobar si la sesión no está iniciada y, si no, iniciarla.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado y si es un administrador. Si no, redirigir a la página principal.
if (!isset($_SESSION['nombre_usuario']) || $_SESSION['rol'] === 'cliente') {
    header('Location: ../index.php');
    exit();
}

// Comprobar si el formulario se envió mediante POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperar el valor de btnEnviar enviado en el formulario (determina la acción a ejecutar).
    $btnEnviar = $_POST['btnEnviar'] ?? '';
    
    // Generar el encabezado de la página con los enlaces proporcionados.
    $html = generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');

    // Dependiendo del valor de btnEnviar, realizar diferentes acciones:
    if($btnEnviar === 'activarDesactivar'){
        $html .= generaRadioAbreCierra(); // Generar formulario para activar o desactivar curso.
    }elseif($btnEnviar === 'listado'){
        $html .= generaOpcionesListado($pdo); // Generar formulario para mostrar listado de admitidos.
    }elseif($btnEnviar === 'modificarCursos'){
        $html .= generaOpcionesModificar(); // Generar formulario para insertar o eliminar curso.
    }elseif($btnEnviar === 'baremo'){
        header('Location:realizarBaremo.php'); // Redirigir a la página de baremación.
        exit();
    }elseif($btnEnviar === 'ejecutarAbrirCerrar'){
        // Si se elige ejecutar abrir/cerrar curso, obtener la acción (abrir o cerrar).
        $abrirCerrar = $_POST['abrirCerrar'] ?? '';
        $html .= generaSelectAbreCierra($pdo, $abrirCerrar); // Generar formulario para seleccionar un curso a abrir o cerrar.
    }elseif($btnEnviar === 'asignarNPlazas'){
        // Si se elige asignar plazas, obtener el número de plazas.
        $nPlazas = $_POST['nPlazas'] ?? '';
        // Validar que el número de plazas esté entre 10 y 30.
        $nPlazasValido = !empty($nPlazas) && validaPlazas((int)$nPlazas);
        
        // Generar formulario para asignar el número de plazas a un curso.
        $html .= generaOpcionesNPlazas($pdo, $nPlazas);

        if($nPlazasValido){
            // Obtener el código del curso y asignar el número de plazas.
            $codigo = obtenerCodigoCurso($_POST['curso'] ?? '')[0];
            if(asignarNPlazas($pdo, $nPlazas, $codigo)){
                // Si la operación es exitosa, mostrar mensaje de éxito.
                $_SESSION['mensaje'] = '<p class = "mensajeBueno">Operación realizada con éxito</p>';
            }else{
                // Si la operación falla, mostrar mensaje de error.
                $_SESSION['mensaje'] = '<p class = "mensaje">Operación no se ha podido realizar</p>';
            }
            // Redirigir al índice después de realizar la operación.
            header('Location: ../index.php');
            exit();
        }
    }

    // Agregar el pie de página al HTML.
    $html .= generaFooter();
    echo $html;
}else{
    // Si no se ha enviado un formulario POST, mostrar el menú principal.
    echo generaMenu();
}

// Función para generar el menú principal con las opciones de administrador.
function generaMenu(){
    // Generar el encabezado y el pie de página.
    $html = generaHeader('../formularios/logout.php', '../formularios/login.php', '../index.php', '../cursos.php', 'opcionesAdmin.php', '../css/style.css');
    // Generar el formulario con las opciones del menú.
    $html .= generaOpcionesMenu();
    $html .= generaFooter();
    return $html;
}

// Función para generar las opciones del menú del administrador.
function generaOpcionesMenu(){
    $html = '<main><form action="opcionesAdmin.php" method="post" class="formulario">';
    $html .= '<br><h2>Opciones de administrador</h2><br>';
    $html .= generaOpcion('Abrir/Cerrar curso', 'activarDesactivar');
    $html .= generaOpcion('Mostrar listado de admitidos en un curso', 'listado');
    $html .= generaOpcion('Insertar/Eliminar curso', 'modificarCursos');
    $html .= generaOpcion('Baremación', 'baremo');
    $html .= generaOpcion('Insertar número de plazas', 'asignarNPlazas');
    $html .= '</form></main>';
    return $html;
}

// Función para generar el formulario para abrir o cerrar un curso.
function generaRadioAbreCierra(){
    $html = '<main><form action="opcionesAdmin.php" method="post" class="formulario">';
    $html .= '<br><h2>ELIGE OPCIÓN</h2><br>';
    // Campo oculto para indicar que se trata de la opción de abrir o cerrar curso.
    $html .= generaHidden('opcionAdmin', 'abrirCerrar');
    // Botones de radio para elegir entre abrir o cerrar un curso.
    $html .= generaRadio('abrirCerrar', 'Abrir/Cerrar curso', ['abrir', 'cerrar'], true, 'abrir');
    // Opción para enviar el formulario.
    $html .= generaOpcion("Enviar", "ejecutarAbrirCerrar");
    $html .= '</form></main>';
    return $html;
}

// Función para generar el formulario para seleccionar un curso a abrir o cerrar.
function generaSelectAbreCierra($pdo, $abrirCerrar){
    $html = '<main><form action="ejecutaOpciones.php" method="post" class="formulario">';
    $html .= '<br><h2>'.strtoupper($abrirCerrar).' CURSO</h2><br>';
    // Campos ocultos para enviar los datos de la opción y la acción (abrir o cerrar).
    $html .= generaHidden('opcionAdmin', 'abrirCerrar');
    $html .= generaHidden('abrirCerrar', $abrirCerrar);
    // Selección de cursos desde la base de datos.
    $html .= generaSelectBd($pdo, ($abrirCerrar === 'cerrar') ? true : false);
    $html .= generaSubmit();
    $html .= '</form></main>';
    return $html;
}

// Función para generar el formulario para mostrar listado de estudiantes admitidos.
function generaOpcionesListado($pdo){
    $html = '<main><form action="ejecutaOpciones.php" method="post" class="formulario">';
    $html .= '<br><h2>Elige curso</h2><br>';
    // Campo oculto para indicar que se trata de la opción de obtener el listado de estudiantes.
    $html .= generaHidden('opcionAdmin', 'obtenerListado');
    // Selección de curso desde la base de datos.
    $html .= generaSelectBd($pdo);
    $html .= generaSubmit();
    $html .= '</form></main>';
    return $html;
}

// Función para generar el formulario para insertar o eliminar un curso.
function generaOpcionesModificar(){
    $html = '<main><form action="insertarBorrarCurso.php" method="get" class="formulario">';
    $html .= '<br><h2>Elige Opcion</h2><br>';
    // Campo oculto para indicar que se trata de la opción de modificar un curso.
    $html .= generaHidden('opcionAdmin', 'modificar');
    // Radio para elegir entre insertar o eliminar un curso.
    $html .= generaRadio('modificar', 'Insertar/Eliminar curso', ['insertar', 'eliminar'], true, 'insertar');
    $html .= generaSubmit();
    $html .= '</form></main>';
    return $html;
}

// Función para generar el formulario para asignar el número de plazas en un curso.
function generaOpcionesNPlazas($pdo, $nPlazas){
    $html = '<main><form action="opcionesAdmin.php" method="post" class="formulario">';
    $html .= '<br><h2>ASIGNAR NÚMERO DE PLAZAS</h2><br>';
    $html .= generaHidden('opcionAdmin', 'asignarNPlazas');
    // Campo para ingresar el número de plazas.
    $html .= generaCampoNumerico('nPlazas', 'Inserta número de plazas', $nPlazas, true, 'Inserta número de plazas, mínimo 10 y máximo 30', 'Minimo 10 y máximo 30');
    // Selección de curso desde la base de datos.
    $html .= generaSelectBd($pdo, true);
    // Opción para enviar el formulario.
    $html .= generaOpcion("Enviar", "asignarNPlazas");
    $html .= '</form></main>';
    return $html;
}

// Función para validar que el número de plazas esté entre 10 y 30.
function validaPlazas($plazas){
    return $plazas > 10 && $plazas <= 30;
}

// Función para obtener el código de un curso a partir de su identificador.
function obtenerCodigoCurso($codigoCurso){
    return explode("-", $codigoCurso);
}
?>
