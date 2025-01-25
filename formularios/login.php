<?php
// Configuración para mostrar errores y advertencias
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclusión de archivos necesarios para el funcionamiento de la página
include '../header.php';           // Archivo para la cabecera de la página
include '../footer.php';           // Archivo para el pie de página
include '../conexionBd.php';       // Archivo para la conexión a la base de datos
include 'input.php';               // Archivo con funciones para generar formularios
include 'consultasUsuario.php';    // Archivo con funciones para manejar usuarios en la base de datos

// Variables para almacenar los valores de los campos del formulario
$valorCampos = [
    'nombre' => $_POST['nombre'] ?? '', // Recibe el valor del campo 'nombre' del formulario, si existe
    'clave' => $_POST['clave'] ?? '',   // Recibe el valor del campo 'clave' del formulario, si existe
];

// Variables para validar los campos del formulario
$camposValidos = [
    'nombre' => true,  // Valor por defecto, se asume que el campo 'nombre' es válido
    'clave' => true,   // Valor por defecto, se asume que el campo 'clave' es válido
];

// Mensaje que se mostrará si ocurre algún error
$mensaje = '';

// Procesamiento del formulario cuando se realiza el POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validación de los campos, comprueba si los campos 'nombre' y 'clave' no están vacíos
    $camposValidos['nombre'] = !empty($valorCampos['nombre']);
    $camposValidos['clave'] = !empty($valorCampos['clave']);
    
    // Si todos los campos son válidos
    if (compruebaCampos($camposValidos)) {
        // Comprobación de las credenciales del usuario en la base de datos
        $datosUsuario = compruebaUsuario($pdo, $valorCampos['nombre'], $valorCampos['clave']);
        
        // Si no se encuentra el usuario, muestra un mensaje de error
        if ($datosUsuario === null) {
            $mensaje = '<p class="mensaje">Credenciales incorrectas o usuario no existe</p>';
        } else {
            // Si el usuario es válido, inicia la sesión y redirige al usuario a la página de cursos
            session_start();                // Inicia la sesión
            darDatosSesion($datosUsuario); // Almacena los datos del usuario en la sesión
            header('Location: ../cursos.php'); // Redirige a la página de cursos
            exit(); // Termina la ejecución del script después de redirigir
        }
    } else {
        // Si alguno de los campos no es válido, muestra un mensaje de error
        $mensaje = '<p class="mensaje">Por favor, completa todos los campos</p>';
    }
}

// Generación de la página HTML con el formulario, el mensaje (si hay), y el pie de página
$html = generaHeader('login.php', 'logout.php', '../index.php', '../cursos.php', '../administrador/opcionesAdmin.php', '../css/style.css');
$html .= $mensaje; // Agrega el mensaje de error (si existe)
$html .= generaFormulario($valorCampos, $camposValidos); // Agrega el formulario
$html .= generaFooter(); // Agrega el pie de página
echo $html; // Muestra la página generada

// Función que genera el formulario de login
function generaFormulario($valores, $validos) {
    $html = '<main><form action="login.php" method="post" class="formulario">';
    $html .= '<h2>LOGIN</h2>';
    // Genera los campos del formulario con las validaciones correspondientes
    $html .= generaTexto('nombre', 'Nombre de usuario', $valores['nombre'], $validos['nombre'], 'Inserte nombre de usuario');
    $html .= generaPassword($validos['clave']);
    // Enlace para registrarse si aún no tienen cuenta
    $html .= '<p>¿Aún no tienes una cuenta? <a href="registro.php">Registrarse</a></p>';
    $html .= generaSubmit(); // Genera el botón de envío
    $html .= '</form></main>';
    return $html; // Devuelve el HTML del formulario generado
}

// Función para comprobar si todos los campos son válidos
function compruebaCampos($camposValidos) {
    return !in_array(false, $camposValidos, true); // Devuelve 'true' si no hay campos inválidos
}

// Función para almacenar los datos del usuario en la sesión
function darDatosSesion($datosUsuario){
    $_SESSION['id_usuario'] = $datosUsuario['id'];        // Almacena el ID del usuario
    $_SESSION['nombre'] = $datosUsuario['nombre'];        // Almacena el nombre del usuario
    $_SESSION['apellidos'] = $datosUsuario['apellidos'];  // Almacena los apellidos del usuario
    $_SESSION['email'] = $datosUsuario['correo'];         // Almacena el correo del usuario
    $_SESSION['telefono'] = $datosUsuario['telefono'];    // Almacena el teléfono del usuario
    $_SESSION['nombre_usuario'] = $datosUsuario['nombre_usuario']; // Almacena el nombre de usuario
    $_SESSION['rol'] = $datosUsuario['rol'];              // Almacena el rol del usuario (por ejemplo, admin)
    $_SESSION['dni'] = $datosUsuario['dni'];              // Almacena el DNI del usuario
}
?>
