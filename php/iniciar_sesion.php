<?php
/* 1. CONFIGURACIÓN Y SESIÓN */

// Importa el archivo de configuración con las credenciales de la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para el manejo de variables globales
session_start();

/* 2. PROCESAR LOGIN (Lógica superior separada del HTML) */

// Incorpora el script que procesa la validación de las credenciales de acceso
include "./procesar_login.php";

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Carga modular base de componentes compartidos como meta etiquetas -->
    <?php include "./head_html.php"; ?>
    <!-- Dictamina el nombre visible en la pestaña o ventana del explorador -->
    <title>Inicio de sesión</title>
    <!-- Enlace referenciando a la figura icono adjunta al título local de la pestaña -->
    <link rel="shortcut icon" href="./../img/logo.jpg">
    <!-- Estructuras de optimización asíncrona y emparejamiento predefinido entre navegadores web (Normalize) -->
    <link rel="preload" href="./../css/normalize.css" as="style">
    <link rel="stylesheet" href="./../css/normalize.css">
    <!-- Declaración anticipada e inserción de la configuración gráfica nativa del proyecto -->
    <link rel="preload" href="./../css/estilo_generico.css" as="style">
    <link rel="stylesheet" href="./../css/estilo_generico.css">
    <link rel="preload" href="./../css/styles-iniciosesion-registro.css" as="style">
    <link rel="stylesheet" href="./../css/styles-iniciosesion-registro.css">
</head>

<body class="container">
    <!-- Elemento del bloque superior abarcando y controlando todo el menú directivo del sistema -->
    <header>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container-fluid">
                <!-- Contenedor adaptativo cabecera principal reservado a la portabilidad móvil y comercial -->
                <div class="navbar-header">
                    <!-- Botón reactivo destinado a contraer las funcionalidades secundarias en espacio reducido -->
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <!-- Referencia identitaria superior a la propia denominación de la tienda -->
                    <a class="navbar-brand" href="./../index.php">TuxtlaTech</a>
                </div>

                <div class="collapse navbar-collapse" id="myNavbar">
                    <!-- Disposición en lista de viñetas alineada hacia el extremo occidental del menú -->
                    <ul class="nav navbar-nav">
                        <li><a href="./../index.php">Lista de productos</a></li>
                    </ul>
                    <!-- Envoltura aglutinante oriental con enlaces para la gestión de acceso de usuarios -->
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a href="./registro.php"><span class="glyphicon glyphicon-user"></span>Registrarse
                            </a>
                        </li>
                        <li class="active">
                            <a href="#"><span class="glyphicon glyphicon-log-in">
                                </span>Ingresar</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenedor matriz para dar estructura y centralizar visualmente al formulario responsivo -->
    <div class="centrar">
        <h1 style="text-align:center; margin:0">Iniciar sesión</h1>
        <!-- Define el formulario que enviará los datos a esta misma página mediante el método POST -->
        <form class="form form-horizontal" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="form-group">
                <!-- Etiqueta descriptiva para el campo de nombre de usuario con contenedor de errores -->
                <label for="nombre" class="control-label">Nombre de usuario: <span class="error"><?php echo $nombreErr?></span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                    </div>
                    <!-- Campo de texto para que el usuario ingrese su nombre o identificador -->
                    <input type="text" name="nombre" class="form-control" autocomplete="username" value="<?= $nombre?>">
                </div>
            </div>
            <div class="form-group">
                <!-- Etiqueta descriptiva para el campo de contraseña con contenedor de errores -->
                <label for="contrasena" class="control-label">Contraseña <span class="error"><?php echo $contraErr?></span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></div>
                    <!-- Campo de texto ofuscado para que el usuario ingrese su clave secreta -->
                    <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Password" autocomplete="password" value="<?php echo $contra?>">
                    <!-- Icono interactivo que permite alternar la visibilidad de los caracteres de la contraseña -->
                    <div class="input-group-addon" style="cursor: pointer;" onclick="togglePassword('contrasena', 'toggleIcon')">
                        <span class="glyphicon glyphicon-eye-open" id="toggleIcon" aria-hidden="true"></span>
                    </div>
                </div>
            </div>
            <!-- Enlace de redirección rápida para usuarios que aún no poseen una cuenta -->
            <p class="no-registrado">¿No tienes cuenta? <a class="btn-link" href="./registro.php">Registrarse</a></p>
            <div class="form-group boton">
                <!-- Botón de envío que desencadena la validación de los datos capturados en el formulario -->
                <input type="submit" class="btn btn-default comprar" value="Entrar"></input>
            </div>
        </form>
    </div>

    <script>
    // Función JavaScript encargada de mostrar u ocultar el texto introducido en el campo de contraseña
    function togglePassword(inputId, iconId) {
        // Recupera el elemento de entrada (input) mediante su identificador
        var input = document.getElementById(inputId);
        // Recupera el elemento del ícono mediante su identificador
        var icon = document.getElementById(iconId);
        // Evalúa si el campo se encuentra en estado oculto (password)
        if (input.type === "password") {
            // Cambia el tipo de campo a texto legible
            input.type = "text";
            // Remueve la clase del ícono de ojo abierto
            icon.classList.remove("glyphicon-eye-open");
            // Añade la clase del ícono de ojo cerrado
            icon.classList.add("glyphicon-eye-close");
        } else {
            // Restaura el tipo de campo a formato ofuscado (password)
            input.type = "password";
            // Remueve la clase del ícono de ojo cerrado
            icon.classList.remove("glyphicon-eye-close");
            // Añade la clase del ícono de ojo abierto
            icon.classList.add("glyphicon-eye-open");
        }
    }
    </script>
</body>

</html>