<?php
/* 1. GESTIÓN DE SESIÓN */

// Inicia o reanuda la sesión actual para tener acceso a las variables globales vinculadas al usuario
session_start();
// Elimina específicamente el arreglo de datos personales del usuario de la sesión activa
unset($_SESSION['sesion_personal']);

/* 2. DESTRUCCIÓN Y REDIRECCIÓN */

// Evalúa si la destrucción completa de la sesión y todos sus datos asociados en el servidor fue exitosa
if(session_destroy()){
    // Redirige el flujo de navegación del usuario de vuelta a la página principal de la tienda
    header("Location: ../index.php");
    // Finaliza la ejecución del script para asegurar una redirección limpia y detener el procesamiento
    exit();
}
