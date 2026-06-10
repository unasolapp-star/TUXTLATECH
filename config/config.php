<?php

// Forzar la visualización de errores para depurar el HTTP 500
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Prevenir que fallos de MySQL rompan la app con una excepción fatal en PHP 8+
mysqli_report(MYSQLI_REPORT_OFF);

/* 1. CREDENCIALES DE BASE DE DATOS */

// Define la dirección del servidor o host donde se aloja el sistema gestor de base de datos
$db_hostname = getenv('MYSQLHOST') ?: "localhost";
// Define el nombre del usuario autorizado con privilegios para interactuar con la base de datos
$db_username = getenv('MYSQLUSER') ?: "root";
// Define la clave de acceso para el usuario especificado (vacía por defecto en entornos locales)
$db_password = getenv('MYSQLPASSWORD') ?: "";
// Define el nombre exacto de la base de datos a la cual el sistema realizará las consultas
$db_name     = getenv('MYSQLDATABASE') ?: "tienda_online";
$db_port     = getenv('MYSQLPORT') ?: "3306";

// Configura el puerto por defecto para las conexiones mysqli en todo el proyecto
ini_set('mysqli.default_port', $db_port);

?>