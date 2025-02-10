<?php
// Configura la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluye archivos necesarios
include '../header.php';  // Encabezado de la página
include '../footer.php';  // Pie de página
include '../conexionBd.php';  // Conexión a la base de datos
include 'input.php';  // Funciones para generar inputs de formularios
include 'consultasUsuario.php';  // Funciones para interactuar con la base de datos relacionadas a usuarios

// Inicializa los valores de los campos del formulario (si están en POST)
$valorCampos = [
    'nombre' => $_POST['nombre'] ?? '',  // Obtiene el nombre de usuario del POST, si no existe deja vacío
    'clave' => $_POST['clave'] ?? '',    // Obtiene la clave de usuario del POST, si no existe deja vacío
];

// Define si los campos son válidos inicialmente (todos válidos)
$camposValidos = [
    'nombre' => true,
    'clave' => true,
];

// Inicializa el mensaje que se mostrará en el formulario
$mensaje = '';

// Verifica si la solicitud es un POST (envío de formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validación de campos (si no están vacíos)
    $camposValidos['nombre'] = !empty($valorCampos['nombre']);
    $camposValidos['clave'] = !empty($valorCampos['clave']);

    // Verifica si todos los campos son válidos
    if (compruebaCampos($camposValidos)) {
        // Intenta verificar las credenciales del usuario
        $datosUsuario = compruebaUsuario($pdo, $valorCampos['nombre'], $valorCampos['clave']);
        
        // Si las credenciales son incorrectas, muestra un mensaje
        if ($datosUsuario === null) {
            $mensaje = '<p class="mensaje">Credenciales incorrectas o usuario no existe</p>';
        } else {
            // Si las credenciales son correctas, inicia sesión
            session_start();
            darDatosSesion($datosUsuario);  // Guarda los datos del usuario en la sesión
            header('Location: ../cursos.php');  // Redirige al usuario a la página de cursos
            exit();  // Asegura que no se ejecute más código después de la redirección
        }
    } else {
        // Si algún campo no es válido, muestra un mensaje
        $mensaje = '<p class="mensaje">Por favor, completa todos los campos</p>';
    }
}

// Genera la estructura HTML del encabezado, formulario, y pie de página
$html = generaHeader('login.php', 'logout.php', '../index.php', '../cursos.php', '../administrador/opcionesAdmin.php', '../css/style.css');
$html .= $mensaje;  // Muestra el mensaje (si hay uno)
$html .= generaFormulario($valorCampos, $camposValidos);  // Genera el formulario de login
$html .= generaFooter();  // Genera el pie de página

// Imprime el HTML completo
echo $html;

// Función que genera el formulario de login
function generaFormulario($valores, $validos) {
    $html = '<main><form action="login.php" method="post" class="formulario">';
    $html .= '<h2>LOGIN</h2>';
    $html .= generaTexto('nombre', 'Nombre de usuario', $valores['nombre'], $validos['nombre'], 'Inserte nombre de usuario');  // Campo de nombre de usuario
    $html .= generaPassword($validos['clave']);  // Campo de contraseña
    $html .= '<p>¿Aún no tienes una cuenta? <a href="registro.php">Registrarse</a></p>';  // Enlace a la página de registro
    $html .= generaSubmit();  // Botón de envío
    $html .= '</form></main>';
    return $html;
}

// Función que comprueba si todos los campos son válidos
function compruebaCampos($camposValidos) {
    return !in_array(false, $camposValidos, true);  // Devuelve true si todos los campos son válidos
}

// Función que guarda los datos del usuario en la sesión
function darDatosSesion($datosUsuario){
    $_SESSION['id_usuario'] = $datosUsuario['id'];  // Guarda el ID de usuario
    $_SESSION['nombre'] = $datosUsuario['nombre'];  // Guarda el nombre
    $_SESSION['apellidos'] = $datosUsuario['apellidos'];  // Guarda los apellidos
    $_SESSION['email'] = $datosUsuario['correo'];  // Guarda el correo electrónico
    $_SESSION['telefono'] = $datosUsuario['telefono'];  // Guarda el teléfono
    $_SESSION['nombre_usuario'] = $datosUsuario['nombre_usuario'];  // Guarda el nombre de usuario
    $_SESSION['rol'] = $datosUsuario['rol'];  // Guarda el rol (por ejemplo, admin, cliente)
    $_SESSION['dni'] = $datosUsuario['dni'];  // Guarda el DNI
}
?>
