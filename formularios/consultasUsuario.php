<?php
// Inicia la sesión para poder utilizar datos de sesión más adelante
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para comprobar si un usuario con el nombre y clave especificados existe en la base de datos.
function compruebaUsuario($pdo, $nombreUsuario, $clave) {
    try {
        // SQL para buscar el usuario por nombre y clave
        $sql = "SELECT * FROM usuarios WHERE nombre_usuario = ? AND clave = ?";
        $stmt = $pdo->prepare($sql); // Preparar la consulta para evitar inyecciones SQL
        $stmt->execute([$nombreUsuario, $clave]); // Ejecutar la consulta con los parámetros proporcionados
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC); // Si existe el usuario, devuelve sus datos en formato asociativo
        }
    } catch (PDOException $e) {
        // Si hay un error en la consulta, devuelve null
        return null;
    }
    return null;
}

// Función para comprobar si ya existe un usuario con el DNI proporcionado
function compruebaDni($pdo, $dni) {
    try {
        // SQL para buscar el usuario por DNI
        $sql = "SELECT * FROM usuarios WHERE dni = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dni]);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC); // Si existe, devuelve los datos del usuario
        }
    } catch (PDOException $e) {
        // Si ocurre un error en la consulta, retorna null
        return null;
    }
    return null;
}

// Función para insertar un nuevo usuario en la base de datos
function insertaUsuario($pdo, $nombre, $apellidos, $correo, $telefono, $nombreUsuario, $clave, $dni){
    // Comprobamos si ya existe un usuario con el nombre de usuario o el DNI
    if(!compruebaUsuario($pdo, $nombreUsuario, $clave) && !compruebaDni($pdo, $dni)){
        try {
            // SQL para insertar un nuevo usuario
            $sql = "INSERT INTO `usuarios`(`dni`,`nombre`, `apellidos`, `correo`, `telefono`, `nombre_usuario`, `clave`) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dni, $nombre, $apellidos, $correo, $telefono, $nombreUsuario, $clave]); // Ejecutar la consulta con los valores
            if ($stmt->rowCount() > 0) {
                // Si la inserción es exitosa, retorna los datos del nuevo usuario
                return [
                    'id' => $pdo->lastInsertId(),  // Devuelve el ID del último registro insertado
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'correo' => $correo,
                    'telefono' => $telefono,
                    'nombre_usuario' => $nombreUsuario,
                    'dni' => $dni,
                    'rol' => 'cliente'  // Por defecto, el rol del usuario es 'cliente'
                ];
            }
        } catch (PDOException $e) {
            // Si hay un error en la inserción, retorna null
            return null;
        }
        return null;
    }
}

// Función para buscar un solicitante por DNI
function buscarSolicitante($pdo, $dni){
    try {
        // SQL para buscar un solicitante por su DNI
        $sql = "SELECT * FROM solicitantes WHERE dni = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dni]);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC); // Devuelve los datos del solicitante
        }
    } catch (PDOException $e) {
        // Si ocurre un error, devuelve false
        return false;
    }
    return false;
}

// Función para comprobar si un usuario ya ha realizado una solicitud para un curso
function compruebaSolicitudYaRealizada($pdo, $dni, $codigoCurso){
    try {
        // SQL para buscar si ya existe una solicitud para el curso
        $sql = "SELECT * FROM solicitudes WHERE dni = ? AND codigocurso = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dni, $codigoCurso]);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC); // Si ya existe, devuelve los datos de la solicitud
        }
    } catch (PDOException $e) {
        // Si ocurre un error, devuelve false
        return false;
    }
    return false;
}

// Función para insertar un solicitante en la base de datos
function insertarSolicitante($pdo, $dni, $nombre, $apellidos, $correo, $telefono, $valorCampos){
    $nombreCargo = empty($valorCampos['nombreCargo']) ? 'vacio' : $valorCampos['nombreCargo'];  // Si no hay nombre de cargo, se asigna 'vacio'
    try {
        // SQL para insertar un solicitante
        $sql = "INSERT INTO `solicitantes`(`dni`, `apellidos`, `nombre`, `telefono`, `correo`, `codigocentro`, `coordinadortc`, `grupotc`, `nombregrupo`, `pbilin`, `cargo`, `nombrecargo`, `situacion`, `fechanac`, `especialidad`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        // Ejecutar la consulta con los datos proporcionados
        $stmt->execute([$dni, $apellidos, $nombre, $telefono, $correo, $valorCampos['codigoCentro'], (int)siNo($valorCampos['coordinadorTIC']), (int)siNo($valorCampos['grupoTIC']), $valorCampos['nombreGrupo'], (int)siNo($valorCampos['programaBilingue']), (int)siNo($valorCampos['cargo']), $nombreCargo, activoNo($valorCampos['situacion']), $valorCampos['fechaNacimiento'], $valorCampos['especialidad']]);
        if ($stmt->rowCount() > 0) {
            return true;  // Si la inserción es exitosa, devuelve true
        }
    } catch (PDOException $e) {
        // Si ocurre un error, muestra el mensaje de error
        echo $e->getMessage();
    }
    return false; // Si no se pudo insertar, retorna false
}

// Función auxiliar que convierte 'si' o 'no' en 1 o 0
function siNo($valor){
    return ($valor === 'si') ? '1' : '0';
}

// Función auxiliar para convertir 'activo' o 'inactivo'
function activoNo($valor){
    return ($valor === 'si') ? 'activo' : 'inactivo';
}

// Función para guardar la solicitud de inscripción a un curso
function guardarSolicitud($pdo, $dni, $codigoCurso){
    try {
        // SQL para insertar una solicitud
        $sql = "INSERT INTO `solicitudes`(`dni`, `codigocurso`, `fechasolicitud`) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dni, $codigoCurso, date('Y-m-d')]);  // La fecha de solicitud es la fecha actual
        if ($stmt->rowCount() > 0) {
            return true; // Si la solicitud es guardada correctamente, retorna true
        }
    } catch (PDOException $e) {
        // Si ocurre un error, devuelve false
        return false;
    }
    return false;
}

// Función para restar el número de plazas disponibles en un curso
function restarNPlazas($pdo, $codigoCurso){
    try {
        // SQL para actualizar el número de plazas de un curso
        $sql = "UPDATE `cursos` SET `numeroplazas`= `numeroplazas` - 1 WHERE codigo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigoCurso]);  // Ejecuta la consulta
        if ($stmt->rowCount() > 0) {
            return true; // Si se actualiza el número de plazas, retorna true
        }
    } catch (PDOException $e) {
        // Si ocurre un error, devuelve false
        return false;
    }
    return false;
}

// Función para sumar puntos a los solicitantes según ciertos criterios
function sumarPuntos($pdo, $cargos, $cargoJefe){
    try {
        // SQL para obtener todos los solicitantes
        $sql = "SELECT * FROM solicitantes";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            // Para cada solicitante, sumamos los puntos según ciertos criterios
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $puntos = establecerPuntos($row, $cargos, $cargoJefe);  // Calcula los puntos para cada solicitante
                actualizarPuntos($pdo, $puntos, $row['dni']);  // Actualiza los puntos en la base de datos
            }
            return true;
        }
    } catch (PDOException $e) {
        // Si ocurre un error, devuelve null
        return null;
    }
    return null;
}

// Función para actualizar los puntos de un solicitante
function actualizarPuntos($pdo, $puntos, $dni){
    try {
        // SQL para actualizar los puntos de un solicitante
        $sql = "UPDATE `solicitantes` SET `puntos`= ? WHERE dni = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$puntos, $dni]);  // Ejecutar la consulta
        if ($stmt->rowCount() > 0) {
            return true;  // Si los puntos se actualizan correctamente, devuelve true
        }
    } catch (PDOException $e) {
        return false;  // Si ocurre un error, devuelve false
    }
    return false;
}

// Función para establecer los puntos de un solicitante basado en sus atributos
function establecerPuntos($row, $cargos, $cargoJefe){
    $puntos = 0;
    $cargoTabla = strtolower(trim($row['nombrecargo']));
    $puntos += ($row['coordinadortc'] === 1) ? 4 : 0;  // Añade puntos si el solicitante es coordinador
    $puntos += ($row['pbilin'] === 1) ? 3 : 0;  // Añade puntos si el solicitante es bilingüe
    $puntos += ($row['situacion'] === 'activo') ? 1 : 0;  // Añade puntos si el solicitante está activo
    $puntos += (compruebaFechaNac($row['fechanac']) > 15) ? 1 : 0;  // Añade puntos si tiene más de 15 años
    $puntos += (in_array($cargoTabla, $cargos)) ? 2 : 0;  // Añade puntos si su cargo está en la lista de cargos
    $puntos += ($cargoTabla === $cargoJefe) ? 1 : 0;  // Añade puntos si es el cargo jefe
    $puntos += ($row['grupotc'] === 1) ? 3 : 0;  // Añade puntos si pertenece al grupo TIC
    return $puntos;
}

// Función para calcular la edad de una persona a partir de su fecha de nacimiento
function compruebaFechaNac($fechaNac){
    $fecha = new DateTime($fechaNac);  // Convierte la fecha de nacimiento a un objeto DateTime
    $hoy = new DateTime();  // Fecha actual
    $interval = $hoy->diff($fecha);  // Calcula la diferencia en años entre la fecha de nacimiento y la fecha actual
    return $interval->y;  // Retorna la cantidad de años
}
?>
