<?php
// Se incluye la librería FPDF para crear PDFs
require('../fpdf186/fpdf.php');  // Asegúrate de que la ruta sea correcta

// Función para cargar clases de PHPMailer de manera automática
spl_autoload_register(function ($clase){
    // Se define la ruta completa de la clase a cargar
    $fullpath="../PHPMailer-master/src/".$clase.".php";
    
    // Si el archivo de la clase existe, se incluye
    if (file_exists($fullpath)){
      require_once $fullpath;
    } else {
      // Si no se encuentra la clase, se muestra un mensaje de error
      echo "<p>La clase $fullpath no se encuentra</p>";
    }
});

// Función para crear un PDF de verificación de inscripción
function crearPdfVerificacion($nombre, $dni, $codigoCurso, $nombreCurso, $fechaSolicitud, $puntos){
    // Crear una nueva instancia de la clase FPDF
    $pdf = new FPDF();
    // Agregar una página al documento PDF
    $pdf->AddPage();
    // Agregar una imagen (logo) en la parte superior izquierda del PDF
    $pdf->Image('imagenes/logoIes.jpeg', 10, 8, 30, 30);  // Ajusta la ruta y las dimensiones de la imagen
    // Establecer la fuente para el título (Arial, negrita, tamaño 18)
    $pdf->SetFont('Arial', 'B', 18);
    // Añadir un salto de línea de 60 unidades
    $pdf->Ln(60);
    // Agregar el título centrado
    $pdf->Cell(0, 10, 'Verificación de Inscripción', 0, 1, 'C');
    // Cambiar la fuente para el contenido (Arial, normal, tamaño 12)
    $pdf->SetFont('Arial', '', 12);
    // Añadir un espacio antes de comenzar con los datos
    $pdf->Ln(10);
    // Agregar la información del solicitante (nombre, DNI, código y nombre del curso, fecha de solicitud, puntos)
    $pdf->MultiCell(0, 10, 'Estimado/a '.$nombre);
    $pdf->Ln(10);
    $pdf->MultiCell(0, 10, 'Nos complace informarle su admision al cursco con codigo '.$codigoCurso.' y nombre '.$nombreCurso.' en la fecha de solicitud '.$fechaSolicitud.' con un total de puntos de '.$puntos);
    $pdf->MultiCell(0, 10, 'DNI: '.$dni);
    $pdf->MultiCell(0, 10, 'Código del Curso: '.$codigoCurso);
    $pdf->MultiCell(0, 10, 'Nombre del Curso: '.$nombreCurso);
    $pdf->MultiCell(0, 10, 'Estamos deseando colaborar con usted en su formación.');
    $pdf->MultiCell(0, 12, 'IES Domenico.');

    // Agregar un espacio extra al final del contenido
    $pdf->Ln(20);
    // Establecer el pie de página con una fuente más pequeña (Arial, cursiva, tamaño 8)
    $pdf->SetFont('Arial', 'I', 8);
    // Colocar el pie de página en la parte inferior del PDF
    $pdf->SetY(-15);
    // Escribir el texto del pie de página centrado
    $pdf->Cell(0, 10, 'Este documento es una verificación oficial de tu inscripción.', 0, 0, 'C');
    // Generar el nombre del archivo PDF usando el DNI y código del curso
    $nombreArchivo = $dni . $codigoCurso . '.pdf';
    // Guardar el PDF en la carpeta 'documentosVerificacion'
    $pdf->Output('F', 'documentosVerificacion/solicitud-' . date('Y-m-d') . $nombreArchivo);  // 'F' para guardar en una carpeta
}

// Función para enviar el correo con el PDF adjunto
function enviarCorreoVerificacion($admitidoCorreo, $dni, $codigoCurso) {
    // Ruta al archivo PDF que se va a enviar como adjunto
    $rutaPdf = "documentosVerificacion/solicitud-" . date('Y-m-d') . $dni . $codigoCurso . '.pdf';

    // Crear una nueva instancia de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuración para usar el servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'localhost';  // Servidor SMTP (en este caso, localhost)
        $mail->SMTPAuth = false;    // No se requiere autenticación SMTP
        $mail->Username = 'admin';  // Usuario (en este caso, no se usa porque SMTPAuth es false)
        $mail->Password = 'RK9xDp1L!Ih~';  // Contraseña (igual que arriba)
        $mail->Port = 587;          // Puerto del servidor SMTP
        // Dirección de quien envía el correo
        $mail->setFrom('admin@domenico.es', 'admin');
        // Dirección de destino (correo del admitido)
        $mail->addAddress($admitidoCorreo);
        // Asunto del correo
        $mail->Subject = 'Documento de verificación por admisión a curso';
        // Cuerpo del mensaje
        $mail->Body = 'Cualquier objeción contacte con nosotros: admin@domenico.es';
        // Si el archivo PDF existe, adjuntarlo al correo
        if (file_exists($rutaPdf)) {
            $mail->addAttachment($rutaPdf);
        }
        // Enviar el correo
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Si ocurre un error, mostrar el mensaje de error
        echo '<p class="mensaje">El mensaje no pudo ser enviado. Error de correo: ' . $mail->ErrorInfo . '</p>';
        return false;  // Si hubo un error, retornar falso
    }
}
?>
