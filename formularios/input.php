<?php
// Función para generar un campo de texto con su etiqueta y validación
function generaTexto($nameHtml, $nombreCampo, $valor, $esValorCorrecto, $placeHolder){
    // Si el valor no es correcto, muestra un mensaje de error
    $mensaje = !$esValorCorrecto ? '<span style="color:red">Campo obligatorio</span>' : '';
    return '<div class="input-container">
        <label for="'.$nameHtml.'">'.ucfirst($nombreCampo).'</label> <!-- Etiqueta del campo -->
        <input type="text" name="'.$nameHtml.'" id="'.$nameHtml.'" value = "'.$valor.'" placeholder="'.$placeHolder.'"/> <!-- Campo de texto -->
        '.$mensaje.' <!-- Mensaje de error si el valor no es correcto -->
    </div>';
}

// Función para generar un campo de tipo password
function generaPassword($esValorCorrecto){
    // Si el valor no es correcto, muestra un mensaje de error
    $mensaje = !$esValorCorrecto ? '<span style="color:red">Contraseña de usuario obligatoria</span>' : '';
    return '<div class="input-container">
        <label for="clave">Clave</label> <!-- Etiqueta para la contraseña -->
        <input type="password" name="clave" id="nombre" placeholder="Inserte clave de usuario"/> <!-- Campo de contraseña -->
        '.$mensaje.' <!-- Mensaje de error si la contraseña no es correcta -->
    </div>';
}

// Función para generar un campo numérico con su etiqueta y validación
function generaCampoNumerico($nameHtml, $nombreCampo, $valor, $esValorCorrecto, $placeHolder, $mensaje){
    // Si el valor no es correcto, muestra un mensaje de error
    $mensaje = !$esValorCorrecto ? '<span style="color:red">Campo obligatorio, '.$mensaje.'</span>' : '';
    return '<div class="input-container">
        <label for="'.$nameHtml.'">'.ucfirst($nombreCampo).'</label> <!-- Etiqueta del campo numérico -->
        <input type="number" name="'.$nameHtml.'" id="'.$nameHtml.'" value = "'.$valor.'" placeholder="'.$placeHolder.'"/> <!-- Campo numérico -->
        '.$mensaje.' <!-- Mensaje de error si el valor no es correcto -->
    </div>';
}

// Función para generar un conjunto de botones de opción (radio buttons)
function generaRadio($nameHtml, $nombreCampo, $arrayValores, $esValorCorrecto, $valor){
    // Si el valor no es correcto, muestra un mensaje de error
    $mensaje = !$esValorCorrecto ? '<span style="color:red">Campo obligatorio</span>' : '';
    $html = '<div class="input-container">
        <label for="'.$nameHtml.'">'.ucfirst($nombreCampo).'</label>'; // Etiqueta para los botones de opción
    // Genera los radio buttons basados en el array de valores
    foreach ($arrayValores as $valorAux) {
        // Si el valor coincide, lo marca como seleccionado
        $html .= '<input type="radio" name="'.$nameHtml.'" value="' . $valorAux . '" ' . ($valor === $valorAux ? 'checked' : '') . '>' . ucfirst($valorAux) . '';
    }
    return $html . $mensaje . '</div>'; // Devuelve el conjunto de radio buttons con el mensaje de error
}

// Función para generar un botón de envío
function generaSubmit(){
    return '<button type="submit" class = "btnEnviar">Enviar</button>';  // Botón de envío con la clase CSS 'btnEnviar'
}

// Función para generar un botón de opción personalizado (como un botón de envío o similar)
function generaOpcion($nombre, $valor){
    return '<button name = "btnEnviar" type="submit" class = "btnEnviar" value = "'.$valor.'">'.$nombre.'</button>';
}

// Función para generar un campo oculto (hidden) en un formulario
function generaHidden($nombre, $valor){
    return '<input type="hidden" name="'.$nombre.'" value = "'.$valor.'">';  // Campo oculto con el valor proporcionado
}

// Función para generar un campo de tipo fecha con su validación y valor máximo
function generaDate($nameHtml, $nombreCampo, $esValorCorrecto, $valor, $fechaMax){
    // Si el valor no es correcto, muestra un mensaje de error
    $mensaje = !$esValorCorrecto ? '<span style="color:red">Campo obligatorio</span>' : '';
    return '<div class="input-container">
        <label for="'.$nameHtml.'">'.ucfirst($nombreCampo).'</label> <!-- Etiqueta del campo de fecha -->
        <input type="date" name="'.$nameHtml.'" id="'.$nameHtml.'" value = "'.$valor.'" max = "'.$fechaMax.'"> <!-- Campo de fecha -->
        '.$mensaje.'</div>'; // Devuelve el campo de fecha con el mensaje de error si es necesario
}

// Función para generar un párrafo con un enunciado y un valor específico
function generaParrafo($enunciado, $valor){
    return '<div class="input-container">
        <p class = "texto">'.$enunciado.': '.$valor.'</p> <!-- Párrafo con el enunciado y valor -->
        </div>';
}

// Función para generar un campo de selección (dropdown) con múltiples opciones
function generaSeleccion($nameHtml, $lista){
    $selectMultiple = '<div class="input-container">
    <label for="'.$nameHtml.'">OPcion</label> <!-- Etiqueta del campo de selección -->
    <select  name="'.$nameHtml.'" id="'.$nameHtml.'">';
    // Genera las opciones dentro del campo de selección
    foreach ($lista as $valorAux) {
        // Si el valor está en la lista, lo marca como seleccionado
        $selectMultiple .= '<option value="' . $valorAux . '" ' . (in_array($valorAux, $lista) ? 'selected' : '') . '>' . ucfirst($valorAux) . '</option>';
    }
    return $selectMultiple;  // Devuelve el campo de selección con las opciones
}
?>
