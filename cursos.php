<?php
// Incluye archivos externos para usar funciones o configuraciones comunes
include 'header.php'; // Archivo que genera el encabezado de la página
include 'footer.php'; // Archivo que genera el pie de página
include 'conexionBd.php'; // Archivo para conectarse a la base de datos

// Verifica si la solicitud se realiza con el método GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Genera el encabezado HTML utilizando una función personalizada
    $html = generaHeader(
        'formularios/logout.php',   // Ruta para cerrar sesión
        'formularios/login.php',    // Ruta para iniciar sesión
        'index.php',                // Ruta de inicio
        'cursos.php',               // Ruta para la página de cursos
        'administrador/opcionesAdmin.php', // Ruta para opciones del administrador
        'css/style.css'             // Ruta para el archivo CSS
    );

    // Agrega el mensaje almacenado en la sesión al contenido
    $html .= $_SESSION['mensaje'];

    // Genera y agrega la lista de cursos activos al contenido
    $html .= obtenerCursosActivos($pdo);

    // Cierra la etiqueta <main> y agrega el pie de página
    $html .= '</main>' . generaFooter();

    // Imprime el contenido final generado
    echo $html;
}

// Limpia el mensaje almacenado en la sesión después de mostrarlo
$_SESSION['mensaje'] = '';

// Función para obtener los cursos activos desde la base de datos
function obtenerCursosActivos($pdo) {
    $html = ''; // Variable que almacenará el contenido HTML generado

    // Consulta SQL para seleccionar cursos abiertos
    $sql = "SELECT * FROM cursos WHERE abierto = 1";
    $stmt = $pdo->query($sql); // Ejecuta la consulta

    // Verifica si la consulta devuelve resultados
    if ($stmt->rowCount() > 0) {
        $html .= '<main>'; // Abre el contenedor principal para los cursos

        // Recorre los resultados obtenidos
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Genera los datos de cada curso utilizando otra función
            $html .= generaDatosCurso($fila);
        }
    } else {
        // Mensaje si no hay cursos activos
        $html .= "<p class='mensaje'>No hay cursos activos en este momento.</p>";
    }
    return $html; // Retorna el HTML generado
}

// Función para generar la estructura HTML de cada curso
function generaDatosCurso($fila) {
    // Crea un formulario con los datos del curso, utilizando valores del array asociativo $fila
    return "<form class='formulario' action='formularios/InscribirseCurso.php' method='get'>
    <div class='containerInfoCurso'>
        <input type='hidden' name='plazoInscripcion' value='{$fila['plazoinscripcion']}'> <!-- Plazo de inscripción -->
        <input type='hidden' name='numeroPlazas' value='{$fila['numeroplazas']}'> <!-- Número de plazas -->
        <h2>{$fila['nombre']}</h2> <!-- Nombre del curso -->
        <p>Plazo inscripción: {$fila['plazoinscripcion']}</p> <!-- Muestra el plazo -->
        <button type='submit' class='btnEnviar' name='btnEnviar' value='{$fila['codigo']}-{$fila['nombre']}'>Inscribirse</button> <!-- Botón de inscripción -->
    </div>
    </form>";
}

?>
