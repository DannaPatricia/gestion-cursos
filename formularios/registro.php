<?php
ini_set('display_errors', 1); // Habilita la visualización de errores.
ini_set('display_startup_errors', 1); // Habilita los errores durante el arranque.
error_reporting(E_ALL); // Muestra todos los errores.

include '../header.php'; // Incluye el archivo de cabecera (header.php).
include '../footer.php'; // Incluye el archivo de pie de página (footer.php).
include '../conexionBd.php'; // Incluye la conexión a la base de datos (conexionBd.php).
include 'input.php'; // Incluye funciones para generar los inputs del formulario (input.php).
include 'consultasUsuario.php'; // Incluye funciones de consulta relacionadas con el usuario (consultasUsuario.php).

// Se inicializa un array para almacenar los valores de los campos del formulario.
$valorCampos = [
    'dni' => $_POST['dni'] ?? '',
    'nombre' => $_POST['nombre'] ?? '',
    'apellidos' => $_POST['apellidos'] ?? '',
    'email' => $_POST['email'] ?? '',
    'telefono' => $_POST['telefono'] ?? '',
    'usuario' => $_POST['usuario'] ?? '',
    'clave' => $_POST['clave'] ?? '',
];

// Array que define la validez de cada campo (todos inicialmente válidos).
$camposValidos = [
    'dni' => true,
    'nombre' => true,
    'apellidos' => true,
    'email' => true,
    'telefono' => true,
    'usuario' => true,
    'clave' => true,
];

$mensaje = ''; // Variable para almacenar mensajes de error o éxito.

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Si el formulario se envía mediante POST:
    // Se verifica la validez de cada campo con la función asignarValidez.
    $camposValidos = asignarValidez($camposValidos, $valorCampos);
    
    // Si todos los campos son válidos:
    if (compruebaCampos($camposValidos)) {
        // Intenta insertar al usuario en la base de datos, pasando los valores del formulario.
        $datosUsuario = insertaUsuario($pdo, $valorCampos['nombre'], $valorCampos['apellidos'], $valorCampos['email'], $valorCampos['telefono'], $valorCampos['usuario'], $valorCampos['clave'], $valorCampos['dni']);
        
        if ($datosUsuario === null) {
            // Si no se puede registrar (por problemas como DNI ya registrado), muestra un mensaje de error.
            $mensaje = '<p class="mensaje">No se ha podido registrar, DNI con cuenta ya registrada o inserte correctamente los datos</p>';
        } else {
            // Si el registro es exitoso, se inicia una sesión y se redirige al usuario al login.
            session_start();
            darDatosSesion($datosUsuario);
            header('Location: login.php');
            exit();
        }
    } else {
        // Si algún campo no es válido, muestra un mensaje de error pidiendo completar los campos.
        $mensaje = '<p class="mensaje">Por favor, completa todos los campos</p>';
    }
}

// Genera el HTML de la página, incluyendo el encabezado, formulario, mensaje y pie de página.
$html = generaHeader('login.php', 'logout.php', '../index.php', '../cursos.php', '../administrador/opcionesAdmin.php', '../css/style.css');
$html .= $mensaje; // Agrega el mensaje (si existe).
$html .= generaFormulario($valorCampos, $camposValidos); // Genera el formulario de registro.
$html .= generaFooter(); // Agrega el pie de página.
echo $html; // Muestra el HTML final.

function compruebaCampos($camposValidos) {
    // Devuelve true si todos los campos son válidos (no contienen 'false').
    return !in_array(false, $camposValidos, true);
}

function validaNumero($numero){
    // Verifica que el número de teléfono tenga exactamente 9 caracteres.
    return strlen($numero) == 9;
}

function validaMail($email){
    // Verifica que el correo electrónico tenga un formato válido.
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function darDatosSesion($datosUsuario){
    // Inicializa variables de sesión con los datos del usuario.
    $_SESSION['id_usuario'] = $datosUsuario['id'];
    $_SESSION['nombre'] = $datosUsuario['nombre'];
    $_SESSION['apellidos'] = $datosUsuario['apellidos'];
    $_SESSION['email'] = $datosUsuario['email'];
    $_SESSION['telefono'] = $datosUsuario['telefono'];
    $_SESSION['nombre_usuario'] = $datosUsuario['nombre_usuario'];
    $_SESSION['rol'] = $datosUsuario['rol'];
    $_SESSION['dni'] = $datosUsuario['dni'];
}

function generaFormulario($valores, $validos) {
    // Genera el formulario HTML con los valores y la validez de cada campo.
    $html = '<main><form action="registro.php" method="post" class="formulario">';
    $html .= '<h2>REGISTRO</h2>';
    // Se generan los campos de texto y otros tipos de inputs.
    $html .= generaTexto('dni', 'DNI', $valores['dni'], $validos['dni'], 'Inserte dni');
    $html .= generaTexto('nombre', 'Nombre', $valores['nombre'], $validos['nombre'], 'Inserte nombre');
    $html .= generaTexto('apellidos','Apellidos',  $valores['apellidos'], $validos['apellidos'], 'Inserte apellidos');
    $html .= generaTexto('email', 'Correo electrónico', $valores['email'], $validos['email'], 'Inserte email, ej: usuario@gmail.com');
    $html .= generaCampoNumerico('telefono', 'Número de teléfono', $valores['telefono'], $validos['telefono'], 'Inserte número de teléfono', ' deben ser 9 números');
    $html .= generaTexto('usuario', 'Nombre de usuario', $valores['usuario'], $validos['usuario'], 'Inserte nombre de usuario');
    $html .= generaPassword($validos['clave']); // Campo de contraseña.
    $html .= generaSubmit(); // Botón de envío.
    $html .= '</form></main>';
    return $html;
}

function asignarValidez($camposValidos, $valorCampos){
    // Asigna la validez de cada campo según su contenido y formato.
    $camposValidos['dni'] = !empty($valorCampos['dni']);
    $camposValidos['nombre'] = !empty($valorCampos['nombre']);
    $camposValidos['apellidos'] = !empty($valorCampos['apellidos']);
    $camposValidos['email'] = !empty($valorCampos['email']) && validaMail($valorCampos['email']);
    $camposValidos['telefono'] = !empty($valorCampos['telefono']) && validaNumero($valorCampos['telefono']);
    $camposValidos['usuario'] = !empty($valorCampos['usuario']);
    $camposValidos['clave'] = !empty($valorCampos['clave']);
    return $camposValidos;
}
?>
