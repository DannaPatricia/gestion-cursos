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
    'dni' => $_POST['dni'] ?? '',  // Obtiene el DNI del POST, si no existe deja vacío
    'nombre' => $_POST['nombre'] ?? '',  // Obtiene el nombre del POST
    'apellidos' => $_POST['apellidos'] ?? '',  // Obtiene los apellidos del POST
    'email' => $_POST['email'] ?? '',  // Obtiene el email del POST
    'telefono' => $_POST['telefono'] ?? '',  // Obtiene el teléfono del POST
    'usuario' => $_POST['usuario'] ?? '',  // Obtiene el nombre de usuario del POST
    'clave' => $_POST['clave'] ?? '',  // Obtiene la clave del POST
];

// Inicializa la validez de los campos (todos válidos inicialmente)
$camposValidos = [
    'dni' => true,
    'nombre' => true,
    'apellidos' => true,
    'email' => true,
    'telefono' => true,
    'usuario' => true,
    'clave' => true,
];

// Mensaje que se mostrará si ocurre algún error
$mensaje = '';

// Verifica si la solicitud es un POST (envío de formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Asigna la validez de los campos basados en los valores ingresados
    $camposValidos = asignarValidez($camposValidos, $valorCampos);

    // Verifica si todos los campos son válidos
    if (compruebaCampos($camposValidos)) {
        // Intenta insertar al nuevo usuario
        $datosUsuario = insertaUsuario($pdo, $valorCampos['nombre'], $valorCampos['apellidos'], $valorCampos['email'], $valorCampos['telefono'], $valorCampos['usuario'], $valorCampos['clave'], $valorCampos['dni']);
        
        // Si no se pudo registrar al usuario, muestra un mensaje de error
        if ($datosUsuario === null) {
            $mensaje = '<p class="mensaje">No se ha podido registrar, DNI con cuenta ya registrada o inserte correctamente los datos</p>';
        } else {
            // Si el registro es exitoso, inicia sesión y redirige al login
            session_start();
            darDatosSesion($datosUsuario);  // Guarda los datos del usuario en la sesión
            header('Location: login.php');  // Redirige al login
            exit();  // Asegura que no se ejecute más código después de la redirección
        }
    } else {
        // Si algún campo no es válido, muestra un mensaje de error
        $mensaje = '<p class="mensaje">Por favor, completa todos los campos</p>';
    }
}

// Genera la estructura HTML del encabezado, formulario, y pie de página
$html = generaHeader('login.php', 'logout.php', '../index.php', '../cursos.php', '../administrador/opcionesAdmin.php', '../css/style.css');
$html .= $mensaje;  // Muestra el mensaje (si hay uno)
$html .= generaFormulario($valorCampos, $camposValidos);  // Genera el formulario de registro
$html .= generaFooter();  // Genera el pie de página

// Imprime el HTML completo
echo $html;

// Función que verifica si todos los campos son válidos
function compruebaCampos($camposValidos) {
    return !in_array(false, $camposValidos, true);  // Devuelve true si todos los campos son válidos
}

// Función que valida si un número tiene 9 dígitos (para el teléfono)
function validaNumero($numero){
    return strlen($numero) == 9;  // Verifica si la longitud del número es 9
}

// Función que valida si un correo electrónico tiene el formato correcto
function validaMail($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);  // Utiliza una función de PHP para validar el email
}

// Función que guarda los datos del usuario en la sesión
function darDatosSesion($datosUsuario){
    $_SESSION['id_usuario'] = $datosUsuario['id'];  // Guarda el ID de usuario
    $_SESSION['nombre'] = $datosUsuario['nombre'];  // Guarda el nombre
    $_SESSION['apellidos'] = $datosUsuario['apellidos'];  // Guarda los apellidos
    $_SESSION['email'] = $datosUsuario['email'];  // Guarda el correo electrónico
    $_SESSION['telefono'] = $datosUsuario['telefono'];  // Guarda el teléfono
    $_SESSION['nombre_usuario'] = $datosUsuario['nombre_usuario'];  // Guarda el nombre de usuario
    $_SESSION['rol'] = $datosUsuario['rol'];  // Guarda el rol (por ejemplo, admin, cliente)
    $_SESSION['dni'] = $datosUsuario['dni'];  // Guarda el DNI
}

// Función que genera el formulario de registro
function generaFormulario($valores, $validos) {
    $html = '<main><form action="registro.php" method="post" class="formulario">';
    $html .= '<h2>REGISTRO</h2>';
    $html .= generaTexto('dni', 'DNI', $valores['dni'], $validos['dni'], 'Inserte dni');  // Campo para el DNI
    $html .= generaTexto('nombre', 'Nombre', $valores['nombre'], $validos['nombre'], 'Inserte nombre');  // Campo para el nombre
    $html .= generaTexto('apellidos','Apellidos',  $valores['apellidos'], $validos['apellidos'], 'Inserte apellidos');  // Campo para los apellidos
    $html .= generaTexto('email', 'Correo electrónico', $valores['email'], $validos['email'], 'Inserte email, ej: usuario@gmail.com');  // Campo para el correo electrónico
    $html .= generaCampoNumerico('telefono', 'Número de teléfono', $valores['telefono'], $validos['telefono'], 'Inserte número de teléfono', ' deben ser 9 números');  // Campo para el teléfono
    $html .= generaTexto('usuario', 'Nombre de usuario', $valores['usuario'], $validos['usuario'], 'Inserte nombre de usuario');  // Campo para el nombre de usuario
    $html .= generaPassword($validos['clave']);  // Campo para la clave
    $html .= generaSubmit();  // Botón de envío
    $html .= '</form></main>';
    return $html;
}

// Función que asigna la validez de cada campo dependiendo de su valor
function asignarValidez($camposValidos, $valorCampos){
    $camposValidos['dni'] = !empty($valorCampos['dni']);  // El DNI no puede estar vacío
    $camposValidos['nombre'] = !empty($valorCampos['nombre']);  // El nombre no puede estar vacío
    $camposValidos['apellidos'] = !empty($valorCampos['apellidos']);  // Los apellidos no pueden estar vacíos
    $camposValidos['email'] = !empty($valorCampos['email']) && validaMail($valorCampos['email']);  // El email debe ser válido
    $camposValidos['telefono'] = !empty($valorCampos['telefono']) && validaNumero($valorCampos['telefono']);  // El teléfono debe tener 9 dígitos
    $camposValidos['usuario'] = !empty($valorCampos['usuario']);  // El nombre de usuario no puede estar vacío
    $camposValidos['clave'] = !empty($valorCampos['clave']);  // La clave no puede estar vacía
    return $camposValidos;  // Devuelve los campos con su validez actualizada
}
?>
