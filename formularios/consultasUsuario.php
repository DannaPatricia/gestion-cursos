<?php
// Habilitar la visualización de errores para facilitar la depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para comprobar si un usuario existe en la base de datos con el nombre de usuario y clave proporcionados
function compruebaUsuario($pdo, $nombreUsuario, $clave) {
    try {
        // Consulta SQL para verificar el nombre de usuario y la clave
        $sql = "SELECT * FROM usuarios WHERE nombre_usuario = ? AND clave = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombreUsuario, $clave]);
        // Si se encuentra algún registro, devuelve los datos del usuario
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // Si ocurre un error, retorna null
        return null;
    }
    return null;
}

// Función para verificar si un DNI ya está registrado en la base de datos
function compruebaDni($pdo, $dni) {
    try {
        // Consulta SQL para verificar si el DNI existe en la base de datos
        $sql = "SELECT * FROM usuarios WHERE dni = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dni]);
        // Si se encuentra algún registro, devuelve los datos del usuario
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // Si ocurre un error, retorna null
        return null;
    }
    return null;
}

// Función para insertar un nuevo usuario en la base de datos si no existe con el mismo nombre de usuario ni DNI
function insertaUsuario($pdo, $nombre, $apellidos, $correo, $telefono, $nombreUsuario, $clave, $dni) {
    // Verifica si el nombre de usuario o el DNI ya existen
    if (!compruebaUsuario($pdo, $nombreUsuario, $clave) && !compruebaDni($pdo, $dni)) {
        try {
            // Inserta un nuevo usuario en la tabla de usuarios
            $sql = "INSERT INTO `usuarios`(`dni`,`nombre`, `apellidos`, `correo`, `telefono`, `nombre_usuario`, `clave`) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dni, $nombre, $apellidos, $correo, $telefono, $nombreUsuario, $clave]);
            // Si se inserta correctamente, devuelve los datos del nuevo usuario
            if ($stmt->rowCount() > 0) {
                return [
                    'id' => $pdo->lastInsertId(),  // Devuelve el último ID insertado
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'correo' => $correo,
                    'telefono' => $telefono,
                    'nombre_usuario' => $nombreUsuario,
                    'dni' => $dni,
                    'rol' => 'cliente'  // Asigna el rol 'cliente' al nuevo usuario
                ];
            }
        } catch (PDOException $e) {
            // Si ocurre un error, retorna null
            return null;
        }
    }
    return null;
}

// Función para buscar un solicitante por su DNI
function buscarSolicitante($pdo, $dni) {
    try {
        // Consulta SQL para buscar el solicitante por su DNI
        $sql = "SELECT * FROM solicitantes WHERE dni = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dni]);
        // Si se encuentra el solicitante, devuelve sus datos
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // Si ocurre un error, retorna false
        return false;
    }
    return false;
}

// Función para insertar un nuevo solicitante en la base de datos
function insertarSolicitante($pdo, $dni, $nombre, $apellidos, $correo, $telefono, $valorCampos) {
    // Si el campo 'nombreCargo' está vacío, se asigna 'vacio'
    $nombreCargo = empty($valorCampos['nombreCargo']) ? 'vacio' : $valorCampos['nombreCargo'];
    try {
        // Inserta un nuevo solicitante en la tabla de solicitantes
        $sql = "INSERT INTO `solicitantes`(`dni`, `apellidos`, `nombre`, `telefono`, `correo`, `codigocentro`, `coordinadortc`, `grupotc`, `nombregrupo`, `pbilin`, `cargo`, `nombrecargo`, `situacion`, `fechanac`, `especialidad`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        // Se ejecuta la consulta con los valores proporcionados
        $stmt->execute([
            $dni, $apellidos, $nombre, $telefono, $correo, $valorCampos['codigoCentro'],
            (int)siNo($valorCampos['coordinadorTIC']), (int)siNo($valorCampos['grupoTIC']),
            $valorCampos['nombreGrupo'], (int)siNo($valorCampos['programaBilingue']),
            (int)siNo($valorCampos['cargo']), $nombreCargo, activoNo($valorCampos['situacion']),
            $valorCampos['fechaNacimiento'], $valorCampos['especialidad']
        ]);
        // Si la inserción es exitosa, retorna true
        if ($stmt->rowCount() > 0) {
            return true;
        }
    } catch (PDOException $e) {
        // Si ocurre un error, muestra el mensaje de error
        echo $e->getMessage();
    }
    return false;
}

// Función para convertir los valores 'si'/'no' en 1/0
function siNo($valor) {
    return ($valor === 'si') ? '1' : '0';
}

// Función para convertir los valores 'si'/'no' en 'activo'/'inactivo'
function activoNo($valor) {
    return ($valor === 'si') ? 'activo' : 'inactivo';
}

// Función para guardar una solicitud de inscripción a un curso
function guardarSolicitud($pdo, $dni, $codigoCurso) {
    try {
        // Inserta una nueva solicitud en la tabla 'solicitudes'
        $sql = "INSERT INTO `solicitudes`(`dni`, `codigocurso`, `fechasolicitud`) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dni, $codigoCurso, date('Y-m-d')]);
        // Si la inserción es exitosa, retorna true
        if ($stmt->rowCount() > 0) {
            return true;
        }
    } catch (PDOException $e) {
        // Si ocurre un error, retorna false
        return false;
    }
    return false;
}

// Función para restar una plaza del curso especificado
function restarNPlazas($pdo, $codigoCurso) {
    try {
        // Actualiza la cantidad de plazas disponibles para el curso
        $sql = "UPDATE `cursos` SET `numeroplazas`= `numeroplazas` - 1 WHERE codigo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigoCurso]);
        // Si la actualización es exitosa, retorna true
        if ($stmt->rowCount() > 0) {
            return true;
        }
    } catch (PDOException $e) {
        // Si ocurre un error, retorna false
        return false;
    }
    return false;
}

// Función para sumar puntos a los solicitantes en función de sus características
function sumarPuntos($pdo, $cargos, $cargoJefe) {
    try {
        // Consulta para obtener todos los solicitantes
        $sql = "SELECT * FROM solicitantes";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        // Recorre cada solicitante y calcula los puntos
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $puntos = establecerPuntos($row, $cargos, $cargoJefe);
                // Actualiza los puntos del solicitante en la base de datos
                actualizarPuntos($pdo, $puntos, $row['dni']);
            }
            return true;
        }
    } catch (PDOException $e) {
        // Si ocurre un error, retorna null
        return null;
    }
    return null;
}

// Función para actualizar los puntos de un solicitante en la base de datos
function actualizarPuntos($pdo, $puntos, $dni) {
    try {
        // Actualiza los puntos del solicitante
        $sql = "UPDATE `solicitantes` SET `puntos`= ? WHERE dni = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$puntos, $dni]);
        // Si la actualización es exitosa, retorna true
        if ($stmt->rowCount() > 0) {
            return true;
        }
    } catch (PDOException $e) {
        // Si ocurre un error, retorna false
        return false;
    }
    return false;
}

// Función para establecer los puntos de un solicitante en función de su perfil
function establecerPuntos($row, $cargos, $cargoJefe) {
    $puntos = 0;
    $cargoTabla = strtolower(trim($row['nombrecargo']));
    // Asigna puntos basados en las características del solicitante
    $puntos += ($row['coordinadortc'] === 1) ? 4 : 0;
    $puntos += ($row['pbilin'] === 1) ? 3 : 0;
    $puntos += ($row['situacion'] === 'activo') ? 1 : 0;
    $puntos += (compruebaFechaNac($row['fechanac']) > 15) ? 1 : 0;
    $puntos += (in_array($cargoTabla, $cargos)) ? 2 : 0;
    $puntos += ($cargoTabla === $cargoJefe) ? 1 : 0;
    $puntos += ($row['grupotc'] === 1) ? 3 : 0;
    return $puntos;
}

// Función para comprobar la edad de un solicitante a partir de su fecha de nacimiento
function compruebaFechaNac($fechaNac) {
    $fecha = new DateTime($fechaNac);
    $hoy = new DateTime();
    $interval = $hoy->diff($fecha);
    // Retorna la edad en años
    return $interval->y;
}
?>
