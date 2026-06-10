<?php
/* 
 * EL ELEMENTO HEAD EN HTML:
 * El apartado <head> (cabecera) de un documento HTML funciona como un contenedor 
 * oculto al usuario donde se declaran los metadatos de la página. Sirve para 
 * configurar la codificación de texto, ajustar la vista en dispositivos móviles, 
 * y enlazar recursos externos fundamentales como hojas de estilo (CSS), fuentes, 
 * y bibliotecas de programación (JavaScript) que dictarán el comportamiento visual 
 * y lógico de todo el sitio web.
 */

// Define la codificación de caracteres a UTF-8 para interpretar correctamente tildes y caracteres especiales
echo "<meta charset=\"UTF-8\">";
// Instruye a Internet Explorer y Edge a utilizar su motor de renderizado más reciente disponible
echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">";
// Configura la escala inicial y la adaptabilidad de la pantalla para dispositivos móviles
echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">";

// Importa la hoja de estilos principal del framework Bootstrap 3 desde su red de entrega de contenidos (CDN)
echo "<link rel=\"stylesheet\" href=\"http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css\">";
// Importa la librería jQuery necesaria para el funcionamiento de los componentes interactivos de Bootstrap
echo "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js\"></script>";
// Importa el archivo de comportamientos interactivos (JavaScript) de Bootstrap 3
echo "<script src=\"http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js\"></script>";

// Integra la biblioteca de íconos vectoriales FontAwesome versión 4 para añadir gráficos a la interfaz
echo "<link href=\"//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css\" rel=\"stylesheet\">";

// Incorpora la librería SweetAlert2 mediante CDN para generar ventanas de alerta dinámicas y modernas
echo "<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script>";
// Abre la etiqueta para declarar una función de JavaScript personalizada que será accesible globalmente
echo "<script>
// Define la función para interceptar clics e invocar cuadros de confirmación antes de redireccionar
function confirmarAccion(event, url, titulo, texto) {
    // Detiene el comportamiento de navegación inmediato que tienen los enlaces por defecto
    event.preventDefault();
    // Construye y despliega la ventana modal de alerta utilizando el componente SweetAlert
    Swal.fire({
        // Asigna el encabezado principal de la ventana
        title: titulo,
        // Asigna el cuerpo descriptivo de la advertencia
        text: texto,
        // Establece un ícono visual de precaución
        icon: 'warning',
        // Habilita el botón secundario para que el usuario pueda declinar la acción
        showCancelButton: true,
        // Pinta el botón de confirmación con un tono rojizo alertante
        confirmButtonColor: '#d9534f',
        // Pinta el botón de cancelación con un tono azul neutral
        cancelButtonColor: '#337ab7',
        // Inyecta el texto personalizado para el botón que autoriza la orden
        confirmButtonText: 'Sí, continuar',
        // Inyecta el texto para el botón que cierra la modal sin cambios
        cancelButtonText: 'Cancelar',
        // Aplica una máscara opaca oscura al fondo de la página para enfocar la alerta
        backdrop: 'rgba(0,0,0,0.8)'
    // Maneja la resolución de la promesa una vez que el usuario elige una opción
    }).then((result) => {
        // Evalúa si el evento resultante corresponde a un clic afirmativo de confirmación
        if (result.isConfirmed) {
            // Ejecuta la redirección final del navegador hacia la ruta destino original
            window.location.href = url;
        }
    });
}
// Cierra la etiqueta de programación JavaScript
</script>";
?>