<?php
    // Función para generar un campo de texto con su correspondiente mensaje de error si no es correcto
    function generaTexto($nameHtml, $nombreCampo, $valor, $esValorCorrecto, $placeHolder){
        // Si el valor no es correcto, muestra un mensaje de error
        $mensaje = !$esValorCorrecto ? '<span style="color:red">Campo obligatorio</span>' : '';
        // Genera el campo de texto con el valor, el mensaje de error (si es necesario) y un placeholder
        return '<div class="input-container">
            <label for="'.$nameHtml.'">'.ucfirst($nombreCampo).'</label>
            <input type="text" name="'.$nameHtml.'" id="'.$nameHtml.'" value = "'.$valor.'" placeholder="'.$placeHolder.'"/>'.
            $mensaje.'  <!-- Muestra el mensaje de error, si es necesario -->
        </div>';
    }

    // Función para generar un campo de contraseña con su mensaje de error si no es correcto
    function generaPassword($esValorCorrecto){
        // Si el valor no es correcto, muestra un mensaje de error
        $mensaje = !$esValorCorrecto ? '<span style="color:red">Contraseña de usuario obligatoria</span>' : '';
        // Genera el campo de contraseña con el mensaje de error (si es necesario)
        return '<div class="input-container">
            <label for="clave">Clave</label>
            <input type="password" name="clave" id="nombre" placeholder="Inserte clave de usuario"/>'.
            $mensaje.' <!-- Muestra el mensaje de error si es necesario -->
        </div>';
    }

    // Función para generar un campo numérico con su mensaje de error si no es correcto
    function generaCampoNumerico($nameHtml, $nombreCampo, $valor, $esValorCorrecto, $placeHolder, $mensaje){
        // Si el valor no es correcto, muestra un mensaje de error
        $mensaje = !$esValorCorrecto ? '<span style="color:red">Campo obligatorio, '.$mensaje.'</span>' : '';
        // Genera el campo numérico con el valor, el mensaje de error y un placeholder
        return '<div class="input-container">
            <label for="'.$nameHtml.'">'.ucfirst($nombreCampo).'</label>
            <input type="number" name="'.$nameHtml.'" id="'.$nameHtml.'" value = "'.$valor.'" placeholder="'.$placeHolder.'"/>'.
            $mensaje.' <!-- Muestra el mensaje de error si es necesario -->
        </div>';
    }

    // Función para generar botones de opción (radio buttons) con su correspondiente mensaje de error
    function generaRadio($nameHtml, $nombreCampo, $arrayValores, $esValorCorrecto, $valor){
        // Si el valor no es correcto, muestra un mensaje de error
        $mensaje = !$esValorCorrecto ? '<span style="color:red">Campo obligatorio</span>' : '';
        $html = '<div class="input-container">
            <label for="'.$nameHtml.'">'.ucfirst($nombreCampo).'</label>';
        // Genera los botones de opción (radio buttons) con las opciones que vienen en el array
        foreach ($arrayValores as $valorAux) {
            $html .= '<input type="radio" name="'.$nameHtml.'" value="' . $valorAux . '" ' . ($valor === $valorAux ? 'checked' : '') . '>' . ucfirst($valorAux) . '';
        }
        // Devuelve el HTML generado con los radio buttons y el mensaje de error (si es necesario)
        return $html . $mensaje . '</div>';
    }

    // Función para generar el botón de envío del formulario
    function generaSubmit(){
        return '<button type="submit" class = "btnEnviar">Enviar</button>';
    }

    // Función para generar un botón con un nombre y valor específicos
    function generaOpcion($nombre, $valor){
        return '<button name = "btnEnviar" type="submit" class = "btnEnviar" value = "'.$valor.'">'.$nombre.'</button>';
    }

    // Función para generar un campo oculto (hidden) con su correspondiente nombre y valor
    function generaHidden($nombre, $valor){
        return '<input type="hidden" name="'.$nombre.'" value = "'.$valor.'">';
    }

    // Función para generar un campo de fecha con un valor máximo y mensaje de error si no es correcto
    function generaDate($nameHtml, $nombreCampo, $esValorCorrecto, $valor, $fechaMax){
        // Si el valor no es correcto, muestra un mensaje de error
        $mensaje = !$esValorCorrecto ? '<span style="color:red">Campo obligatorio</span>' : '';
        // Genera el campo de fecha con el valor, el mensaje de error y un valor máximo para la fecha
        return '<div class="input-container">
            <label for="'.$nameHtml.'">'.ucfirst($nombreCampo).'</label>
            <input type="date" name="'.$nameHtml.'" id="'.$nameHtml.'" value = "'.$valor.'" max = "'.$fechaMax.'">
            '.$mensaje.'</div>';
    }

    // Función para generar un párrafo con un enunciado y un valor (por ejemplo, mostrar un resumen)
    function generaParrafo($enunciado, $valor){
        return '<div class="input-container">
            <p class = "texto">'.$enunciado.': '.$valor.'</p>
            </div>';
    }

    // Función para generar un campo de selección (dropdown list) con las opciones que vienen en el array
    function generaSeleccion($nameHtml, $lista){
        // Inicia el contenedor del select
        $selectMultiple = '<div class="input-container">
        <label for="'.$nameHtml.'">OPcion</label>
        <select  name="'.$nameHtml.'" id="'.$nameHtml.'">';
        // Recorre la lista de opciones y crea las opciones dentro del select
        foreach ($lista as $valorAux) {
            // Verifica si el valor está seleccionado
            $selectMultiple .= '<option value="' . $valorAux . '" ' . (in_array($valorAux, $lista) ? 'selected' : '') . '>' . ucfirst($valorAux) . '</option>';
        }
        // Cierra el select y devuelve el HTML generado
        return $selectMultiple;
    }
?>
