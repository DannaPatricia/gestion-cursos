<?php
// Configura la visualización de errores para facilitar la depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Obtiene los puntajes más altos y gestiona la admisión de solicitantes.
 * @param PDO $pdo Objeto PDO para conexión a la base de datos.
 * @param string $codigoCurso Código del curso.
 * @param int $nPlazas Número de plazas disponibles.
 * @return bool True si se admitieron solicitantes, False si no.
 */
function obtenerPuntajes($pdo, $codigoCurso, $nPlazas) {
    try {
        $sql = "SELECT * FROM solicitantes INNER JOIN solicitudes ON solicitantes.dni = solicitudes.dni
        WHERE solicitudes.codigocurso = :codigo ORDER BY solicitantes.puntos DESC LIMIT :nPlazas";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':codigo', $codigoCurso, PDO::PARAM_STR);
        $stmt->bindParam(':nPlazas', $nPlazas, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Itera sobre los solicitantes admitidos y actualiza la base de datos
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                admitirSolicitantes($pdo, $codigoCurso, $fila['dni']);
                mostrarAdmitidos($pdo, $codigoCurso, $fila['nombre'], $fila['dni']);
            }
            return true;
        } else {
            echo "<p class='mensaje'>No hay admitidos</p>";
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
        return false;
    }
    return false;
}

/**
 * Marca un solicitante como admitido en un curso.
 * @param PDO $pdo Objeto PDO para conexión a la base de datos.
 * @param string $codigoCurso Código del curso.
 * @param string $dni DNI del solicitante.
 * @return bool True si se actualizó correctamente, False si no.
 */
function admitirSolicitantes($pdo, $codigoCurso, $dni) {
    try {
        $sql = "UPDATE `solicitudes` SET `admitido`= 1 WHERE codigocurso = ? AND DNI = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigoCurso, $dni]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        echo $e->getMessage();
        return false;
    }
}

/**
 * Muestra los datos de los solicitantes admitidos.
 * @param PDO $pdo Objeto PDO para conexión a la base de datos.
 * @param string $codigoCurso Código del curso.
 * @param string $nombre Nombre del solicitante.
 * @param string $dni DNI del solicitante.
 * @return bool True si hay datos que mostrar, False si no.
 */
function mostrarAdmitidos($pdo, $codigoCurso, $nombre, $dni) {
    try {
        $sql = "SELECT * FROM solicitudes WHERE codigocurso = :codigo AND admitido = 1 AND dni = :dni";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':codigo', $codigoCurso, PDO::PARAM_STR);
        $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo generaDatosAdmitido($fila, $nombre);
            }
            return true;
        } else {
            echo "<p class='mensaje'>No hay admitidos</p>";
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
        return false;
    }
}

/**
 * Genera el HTML para mostrar los datos de un solicitante admitido.
 * @param array $fila Datos del solicitante.
 * @param string $nombre Nombre del solicitante.
 * @return string HTML con los datos del admitido.
 */
function generaDatosAdmitido($fila, $nombre) {
    return "<div class='formulario'>
    <div class='containerInfoCurso'>
        <h2>".ucfirst($nombre)."</h2>
        <p>Fecha solicitud: {$fila['fechasolicitud']}</p>
    </div>
</div>";
}

/**
 * Genera un desplegable con los cursos disponibles, opcionalmente filtrando por fecha.
 * @param PDO $pdo Objeto PDO para conexión a la base de datos.
 * @param bool $conRestriccion Si es true, aplica restricción de fecha.
 * @return string|null HTML del desplegable o null si no hay resultados.
 */
function generaSelectBd($pdo, $conRestriccion = false) {
    try {
        $sql = "SELECT * FROM cursos";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $html = '<select name="curso" id="curso">';
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!$conRestriccion || fechaValida(date('Y-m-d'), $row['plazoinscripcion'])) {
                    $valor = $row['codigo'] . '-' . $row['nombre'];
                    $html .= '<option value="' . htmlspecialchars($valor) . '">' . htmlspecialchars($row['nombre']) . '</option>';
                }
            }
            $html .= '</select>';
            return $html;
        }
    } catch (PDOException $e) {
        return null;
    }
    return null;
}

/**
 * Verifica si una fecha es válida comparando la fecha actual con una fecha límite.
 * @param string $fechaHoy Fecha actual en formato 'Y-m-d'.
 * @param string $fechaLimite Fecha límite en formato 'Y-m-d'.
 * @return bool True si la fecha actual es posterior a la fecha límite, False si no.
 */
function fechaValida($fechaHoy, $fechaLimite) {
    $fechaHoyConversion = strtotime($fechaHoy);
    $fechaLimiteConversion = strtotime($fechaLimite);
    return $fechaHoyConversion > $fechaLimiteConversion;
}

// Otras funciones importantes, como insertarCurso, activarDesactivarCurso, asignarNPlazas, eliminarCurso y obtenerListado, están estructuradas de manera similar: manejan la lógica del curso, realizan consultas a la base de datos y generan el HTML dinámicamente.
