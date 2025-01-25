<?php
// Inicia una sesión para gestionar las variables de sesión
session_start();

/**
 * Función que genera la estructura básica del HTML (doctype, head y apertura del body).
 * @param string $style Ruta al archivo CSS que se enlazará.
 * @return string HTML básico con la inclusión del archivo de estilos.
 */
function generaHtml($style)
{
    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Codificación de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para diseño responsivo -->
    <title>Document</title> <!-- Título del documento -->
    <link rel="stylesheet" href="'.$style.'" media="all"> <!-- Enlace al archivo CSS -->
</head>
<body>';
}

/**
 * Función que genera el encabezado de la página con opciones de navegación y gestión de usuarios.
 * @param string $logout Ruta al archivo para cerrar sesión.
 * @param string $login Ruta al archivo para iniciar sesión.
 * @param string $index Ruta al archivo de inicio.
 * @param string $cursos Ruta al archivo de cursos activos.
 * @param string $opcionesAdministrador Ruta al panel de administración (solo para admins).
 * @param string $style Ruta al archivo CSS (se pasa a generaHtml).
 * @return string HTML que incluye el encabezado completo con opciones de navegación.
 */
function generaHeader($logout, $login, $index, $cursos, $opcionesAdministrador, $style)
{
    // Llama a generaHtml para construir la estructura inicial del HTML
    $html = generaHtml($style);

    // Inicia la construcción del encabezado
    $html .= '<header>
    <nav aria-label="gestiona-opciones-usuarios"> <!-- Navegación para gestión de usuario -->
        <h1 class="titulo">Encuentra Cursos</h1> <!-- Título de la página -->
        <ul>';

    // Si el usuario está autenticado (tiene un id en la sesión), muestra su nombre y un enlace para cerrar sesión
    $html .= isset($_SESSION['id_usuario'])
        ? '<li>'.$_SESSION['nombre_usuario'].'</li><li><a href="'.$logout.'">Cerrar sesión</a></li>'
        // Si no está autenticado, muestra un enlace para iniciar sesión
        : '<li><a href="'.$login.'">Iniciar sesión</a></li>';

    // Si el usuario es administrador, añade un enlace al panel de administración
    $html .= isset($_SESSION['id_usuario']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'
        ? '<li><a href="'.$opcionesAdministrador.'">Panel de administración</a></li>'
        : '';

    // Cierra la primera navegación
    $html .= '</ul>
    </nav>
    
    <!-- Segunda navegación para las páginas principales -->
    <nav aria-label="gestiona-paginas">
        <ul>
            <li><a href="'.$index.'">Inicio</a></li> <!-- Enlace a la página de inicio -->
            <li><a href="'.$cursos.'">Cursos activos</a></li> <!-- Enlace a la lista de cursos -->
            <li><a href="'.$cursos.'">Inscripción</a></li> <!-- Enlace a la inscripción (se repite el mismo enlace) -->
        </ul>
    </nav>
</header>';

    // Retorna el HTML del encabezado completo
    return $html;
}
