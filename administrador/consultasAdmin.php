<?php
// Incluye el archivo para enviar correos de verificación
include 'enviarVerificacion.php';

// Configura la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función que obtiene los puntajes de los solicitantes, los clasifica y gestiona los admitidos
function obtenerPuntajes($pdo, $codigoCurso, $nPlazas)
{
    $yaAdmitidos  = [];  // Array para almacenar los solicitantes ya admitidos
    $solicitantes = [];  // Array para almacenar los solicitantes que aún no están admitidos
    try {
        // Consulta SQL para obtener los solicitantes ordenados por puntaje
        $sql = "SELECT * FROM solicitantes INNER JOIN solicitudes ON solicitantes.dni = solicitudes.dni
        WHERE solicitudes.codigocurso = :codigo ORDER BY solicitantes.puntos DESC";
        
        // Prepara y ejecuta la consulta
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':codigo', $codigoCurso, PDO::PARAM_STR);
        $stmt->execute();
        
        // Verifica si hay resultados
        if ($stmt->rowCount() > 0) {
            // Procesa cada solicitante
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $datos = [
                    'nombre'         => $fila['nombre'],
                    'dni'            => $fila['dni'],
                    'puntos'         => $fila['puntos'],
                    'fechasolicitud' => $fila['fechasolicitud'],
                    'correo'         => $fila['correo'],
                ];

                // Verifica si el solicitante ya está admitido
                if (yaEsAdmitido($pdo, $fila['dni'])) {
                    $yaAdmitidos[] = $datos;  // Si ya está admitido, lo agrega al array correspondiente
                } else {
                    $solicitantes[] = $datos;  // Si no está admitido, lo agrega al array de solicitantes
                }
            }

            // Ordena a los solicitantes según los puntajes y los limita al número de plazas
            $solicitantesAdmitir = ordenarSolicitantes($nPlazas, $solicitantes, $yaAdmitidos);
            // foreach($solicitantesAdmitir as $solicitante){
            //     echo "<p class = 'mensaje'>{$solicitante['nombre']} - {$solicitante['puntos']}</p>";
            // }
            // Gestiona el proceso de admisión y envío de correos
            gestionarSolicitantes($pdo, $codigoCurso, $solicitantesAdmitir);
            enviarCorreos($solicitantesAdmitir, $codigoCurso);
            
            return true;
        } else {
            echo "<p class = 'mensaje'>No hay admitidos</p>";  // Si no hay solicitantes, muestra un mensaje
        }
    } catch (PDOException $e) {
        echo $e->getMessage();  // Captura y muestra cualquier error de la base de datos
        return false;
    }
    return false;
}

// Función que gestiona los solicitantes admitidos
function gestionarSolicitantes($pdo, $codigoCurso, $admitidos){
    $nombreCurso = obtenerNombreCurso($pdo, $codigoCurso);  // Obtiene el nombre del curso
    foreach ($admitidos as $admitido) {
        // Admite a cada solicitante y genera el PDF de verificación
        admitirSolicitantes($pdo, $codigoCurso, $admitido['dni']);
        mostrarAdmitidos($pdo, $codigoCurso, $admitido['nombre'], $admitido['dni']);
        crearPdfVerificacion($admitido['nombre'], $admitido['dni'], $codigoCurso, $nombreCurso, $admitido['fechasolicitud'], $admitido['puntos']);
    }
}

// Función que ordena a los solicitantes según los puntajes y limita el número de plazas disponibles
function ordenarSolicitantes($nPlazas, $solicitantes, $yaAdmitidos){
    $mergeSolicitudes = array_merge($solicitantes, $yaAdmitidos);  // Une los solicitantes con los admitidos previamente
    return array_slice($mergeSolicitudes, 0, $nPlazas);  // Limita el número de solicitantes según las plazas
}

// Función que envía los correos de verificación a los solicitantes admitidos
function enviarCorreos($solicitantes, $codigoCurso){
    foreach ($solicitantes as $solicitante) {
        enviarCorreoVerificacion($solicitante['correo'], $solicitante['dni'], $codigoCurso);  // Envía el correo de verificación
    }
}

// Función que verifica si un solicitante ya ha sido admitido
function yaEsAdmitido($pdo, $dni){
    try {
        $sql  = "SELECT * FROM `solicitudes` WHERE `dni` LIKE ? AND `admitido` = 1";  // Consulta si el DNI está admitido
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dni]);
        return $stmt->rowCount() > 0;  // Devuelve true si ya está admitido
    } catch (PDOException $e) {
        return null;  // Si ocurre un error, devuelve null
    }
    return null;
}

// Función que obtiene el nombre del curso según su código
function obtenerNombreCurso($pdo, $codigoCurso){
    try {
        $sql  = "SELECT nombre FROM cursos WHERE codigo = ?";  // Consulta el nombre del curso
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigoCurso]);
        if ($stmt->rowCount() > 0) {
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila['nombre'];  // Devuelve el nombre del curso
        }
    } catch (PDOException $e) {
        return null;  // Si ocurre un error, devuelve null
    }
    return null;
}

// Función que actualiza el estado de un solicitante a "admitido"
function admitirSolicitantes($pdo, $codigoCurso, $dni){
    try {
        $sql  = "UPDATE `solicitudes` SET `admitido`= 1 WHERE codigocurso = ? AND DNI = ?";  // Actualiza el estado de la solicitud
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigoCurso, $dni]);
        return $stmt->rowCount() > 0;  // Devuelve true si la actualización fue exitosa
    } catch (PDOException $e) {
        echo $e->getMessage();  // Muestra cualquier error
        return false;
    }
    return false;
}

// Función que muestra los solicitantes admitidos para un curso
function mostrarAdmitidos($pdo, $codigoCurso, $nombre, $dni){
    try {
        $sql  = "SELECT * FROM solicitudes WHERE codigocurso = :codigo AND admitido = 1 AND dni = :dni";  // Consulta los admitidos
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':codigo', $codigoCurso, PDO::PARAM_STR);
        $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo generaDatosAdmitido($fila, $nombre);  // Genera los datos del solicitante admitido
            }
            return true;
        } else {
            echo "<p class='mensaje'>No hay admitidos</p>";  // Si no hay admitidos, muestra un mensaje
        }
    } catch (PDOException $e) {
        echo $e->getMessage();  // Muestra cualquier error
        return false;
    }
    return false;
}

// Función que genera los datos del solicitante admitido para mostrar
function generaDatosAdmitido($fila, $nombre)
{
    return "<div class='formulario'>
    <div class = 'containerInfoCurso'>
                <h2>" . ucfirst($nombre) . "</h2>
                <p>Fecha solicitud: {$fila['fechasolicitud']}</p>
              </div>
    </div>";
}

// Función que genera un select HTML con los cursos disponibles en la base de datos
function generaSelectBd($pdo, $conRestriccion = false)
{
    try {
        $sql  = "SELECT * FROM cursos";  // Consulta todos los cursos
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $html = '<select name="curso" id="curso">';
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (! $conRestriccion || fechaValida(date('Y-m-d'), $row['plazoinscripcion'])) {
                    $valor = $row['codigo'] . '-' . $row['nombre'];
                    $html .= '<option value="' . htmlspecialchars($valor) . '">' . htmlspecialchars($row['nombre']) . '</option>';
                }
            }
            $html .= '</select>';
            return $html;  // Devuelve el select con los cursos
        }
    } catch (PDOException $e) {
        return null;  // Si ocurre un error, devuelve null
    }
    return null;
}

// Función que obtiene los cursos que están cerrados
function obtenerCursosCerrados($pdo)
{
    $html = '';
    $sql  = "SELECT * FROM cursos WHERE abierto = 0";  // Consulta los cursos cerrados
    $stmt = $pdo->query($sql);
    if ($stmt->rowCount() > 0) {
        $html .= '<main>';
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $html .= generaDatosCurso($fila);  // Genera los datos de cada curso cerrado
        }
    } else {
        $html .= "<p class='mensaje'>No hay cursos cerrados en este momento.</p>";  // Si no hay cursos cerrados, muestra un mensaje
    }
    return $html;
}

// Función que genera los datos de un curso
function generaDatosCurso($fila)
{
    return "<form class='formulario' action = 'realizarBaremo.php' method = 'post'>
    <div class = 'containerInfoCurso'>
                <input type='hidden' name='plazoInscripcion' value = '{$fila['plazoinscripcion']}'>
                <input type='hidden' name='numeroPlazas' value = '{$fila['numeroplazas']}'>
                <h2>{$fila['nombre']}</h2>
                <p>Plazo inscripción: {$fila['plazoinscripcion']}</p>
                <p>Número plazas: {$fila['numeroplazas']}</p>
                <button type = 'submit' class = 'btnEnviar' name = 'codigoCurso' value = '{$fila['codigo']}-{$fila['nombre']}'>Realizar baremo</button>
              </div></form>";
}

// Función que valida si una fecha es posterior a otra
function fechaValida($fechaHoy, $fechaLimite)
{
    $fechaHoyConversion    = strtotime($fechaHoy);
    $fechaLimiteConversion = strtotime($fechaLimite);
    return $fechaHoyConversion > $fechaLimiteConversion;  // Devuelve true si la fecha actual es posterior a la fecha límite
}

// Función que inserta un nuevo curso en la base de datos
function insertarCurso($pdo, $valorCampos)
{
    $estado = ($valorCampos['estado'] === 'abrir') ? 1 : 0;  // Establece el estado del curso según el valor recibido
    try {
        $sql  = "INSERT INTO `cursos`(`nombre`, `abierto`, `numeroplazas`, `plazoinscripcion`) VALUES (?, ?, ?, ?)";  // Inserta el nuevo curso
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$valorCampos['nombre'], $estado, $valorCampos['nPlazas'], $valorCampos['fechaLimite']]);
        return $stmt->rowCount() > 0;  // Devuelve true si la inserción fue exitosa
    } catch (PDOException $e) {
        return false;  // Si ocurre un error, devuelve false
    }
    return false;
}

// Función que activa o desactiva un curso
function activarDesactivarCurso($pdo, $booleano, $codigoCurso)
{
    try {
        $sql  = "UPDATE `cursos` SET `abierto`= ? WHERE codigo = ?";  // Actualiza el estado del curso
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booleano, $codigoCurso]);
        return $stmt->rowCount() > 0;  // Devuelve true si la actualización fue exitosa
    } catch (PDOException $e) {
        return false;  // Si ocurre un error, devuelve false
    }
    return false;
}

// Función que asigna un número de plazas a un curso
function asignarNPlazas($pdo, $nPlazas, $codigoCurso)
{
    try {
        $sql  = "UPDATE `cursos` SET `numeroplazas`= ? WHERE codigo = ?";  // Actualiza el número de plazas
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nPlazas, $codigoCurso]);
        return $stmt->rowCount() > 0;  // Devuelve true si la actualización fue exitosa
    } catch (PDOException $e) {
        return false;  // Si ocurre un error, devuelve false
    }
    return false;
}

// Función que elimina un curso de la base de datos
function eliminarCurso($pdo, $codigo)
{
    try {
        $sql  = "DELETE FROM `cursos` WHERE codigo = ?";  // Elimina el curso
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo]);
        return $stmt->rowCount() > 0;  // Devuelve true si la eliminación fue exitosa
    } catch (PDOException $e) {
        return false;  // Si ocurre un error, devuelve false
    }
    return false;
}

// Función que obtiene el listado de los solicitantes admitidos de un curso
function obtenerListado($pdo, $codigoCurso)
{
    $html = '';
    $sql  = "SELECT * FROM solicitudes INNER JOIN usuarios ON solicitudes.dni = usuarios.dni WHERE solicitudes.admitido = 1 AND solicitudes.codigocurso = ?";  // Consulta los admitidos
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$codigoCurso]);
    if ($stmt->rowCount() > 0) {
        $html .= '<main>';
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $html .= generaDatoSolicitante($fila);  // Genera los datos de cada solicitante admitido
        }
    } else {
        $html .= "<p class='mensaje'>No hay admitidos en este momento.</p>";  // Si no hay admitidos, muestra un mensaje
    }
    return $html . '<main>';
}

// Función que genera los datos de un solicitante para mostrarlos
function generaDatoSolicitante($fila)
{
    return "<form class='formulario' action = '' method = 'get'>
    <div class = 'container'>
                <p class = 'textoDestacado'>DNI: {$fila['dni']}</p>
                <p>Nombre: {$fila['nombre']}</p>
                <p>Apellido: {$fila['apellidos']}</p>
                <p>Fecha de solicitud: {$fila['fechasolicitud']}</p>
              </div></form>";
}
?>
