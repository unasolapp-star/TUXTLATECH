// Función encargada de confirmar y procesar la compra final recibiendo un arreglo de artículos y una bandera
function comprar(arreglo,v){ // arreglo de [arreglos de (cantidad, id) en string]
    // Dispara una alerta modal (SweetAlert) para pedir confirmación al usuario
    Swal.fire({
        // Establece el título principal de la ventana modal
        title: '¿Confirmar compra?',
        // Establece el texto descriptivo o pregunta para el usuario
        text: '¿Estás seguro de que deseas finalizar la compra?',
        // Define el icono visual de la alerta como una interrogación
        icon: 'question',
        // Habilita el botón secundario para permitir cancelar la acción
        showCancelButton: true,
        // Asigna un color rojo al botón de confirmación
        confirmButtonColor: '#d9534f',
        // Asigna un color azul al botón de cancelación
        cancelButtonColor: '#337ab7',
        // Personaliza el texto del botón que confirma la operación
        confirmButtonText: 'Sí, comprar',
        // Personaliza el texto del botón que cancela la operación
        cancelButtonText: 'Cancelar',
        // Oscurece el fondo detrás de la alerta modal para resaltarla
        backdrop: 'rgba(0,0,0,0.8)'
    // Define una promesa que se ejecuta una vez que el usuario interactúa con la alerta
    }).then((result) => {
        // Verifica si el usuario hizo clic en el botón de confirmación
        if (result.isConfirmed) {
            // Declara una variable local vacía para construir la URL de redirección
            var url="";
            // Concatena la ruta base del script PHP que procesará la compra
            url+="../php/comprar.php?";
            // Inicia un bucle para recorrer todos los elementos del arreglo recibido
            for(var i=0; i<arreglo.length; i++){
                // alert(arreglo[i]);
                // Agrega la clave del arreglo GET a la cadena de la URL
                url+="datos[";
                // Agrega el índice actual del bucle a la clave
                url+=i;
                // Cierra el corchete y añade el signo de igual para la asignación
                url+="]=";
                // Concatena el valor del producto (cantidad e id) en esa posición
                url+=arreglo[i];
                // Añade el separador de parámetros para preparar el siguiente dato
                url+="&";
            }
            // Agrega el parámetro 'v' (bandera para vaciar el carrito) a la URL
            url+="v="
            // Concatena el valor de la bandera recibido por la función
            url+=v;
            // Redirige el navegador del usuario hacia la URL construida
            window.location.replace(url);
        }
    });
}
// Función dedicada a insertar un producto específico al carrito temporal del usuario
function agregarAlCarrito(id){
    // Obtiene el valor numérico ingresado por el usuario en el campo de cantidad seleccionada
    var cantidad_seleccionada=Number(document.getElementById("cantidad_seleccionada").value);
    // Inicializa la variable que contendrá la ruta de destino
    var url="";
    // Empieza a construir la URL apuntando al script PHP de inserción junto con el primer parámetro
    url+="../php/agregar_al_carrito.php?cantidad=";
    // Agrega a la URL el valor numérico de las piezas que se desean comprar
    url+=cantidad_seleccionada;
    // Agrega el separador y la declaración de la clave para el identificador del producto
    url+="\&id_producto=";
    // Concatena a la cadena final el ID del artículo que entra por parámetro
    url+=id;
    
    // Muestra una ventana de notificación emergente visual indicando un resultado exitoso
    Swal.fire({
        // Define el título afirmativo de la alerta
        title: '¡Éxito!',
        // Define el mensaje confirmando la acción de guardado
        text: 'El producto se agregó a tu carrito.',
        // Define un ícono de verificación o palomita
        icon: 'success',
        // Oculta el botón de confirmación ya que la alerta desaparecerá sola
        showConfirmButton: false,
        // Configura un temporizador para que la alerta se cierre en 1500 milisegundos
        timer: 1500,
        // Agrega el fondo opaco detrás de la ventana
        backdrop: 'rgba(0,0,0,0.8)'
    // Ejecuta un bloque de instrucciones una vez terminada la visualización
    }).then(() => {
        // Ejecuta el redireccionamiento para procesar la información en el backend
        window.location.replace(url);
    });
}
// Función responsable de mandar un solo artículo a la ventana general de compras
function enviarAPantallaDeCompraUno(id){
    // Captura e interpreta como número lo que el cliente tecleó en el input de existencias
    var cantidad_seleccionada=Number(document.getElementById("cantidad_seleccionada").value);
    // Prepara un contenedor de texto vacío para la estructura de la dirección web
    var url="";
    // Da inicio a la dirección referenciando a la interfaz de pago y prepara la casilla cero
    url+="../php/pantalla_de_compra.php?datos[0]=";
    // Ingresa la cantidad del producto dentro de la posición inicial
    url+=cantidad_seleccionada;
    // Intercala una coma que servirá en PHP para particionar el texto recibido
    url+=",";
    // Pega de igual forma la ID que corresponde a esta mercancía individual
    url+=id;
    // Concluye la URL añadiendo un cero al parámetro "v" evitando que borre carritos ajenos a la compra
    url+="&v=0";
    // Provoca el reemplazo de la vista actual en la ventana de navegación
    window.location.replace(url);
}
// Función que toma toda una colección del carrito y los transmite hacia la pre-visualización de compra
function enviarAPantallaDeCompraMuchos(arreglo_local){
    // Inicia y vacía el contenedor literal de la URL a procesar
    var url="";
    // Escribe el punto de partida que enviará a la pantalla general con el símbolo de apertura GET
    url+="../php/pantalla_de_compra.php?";
    // Recorre todos los sub-elementos combinados empacados en la matriz local
    for(var i=0; i<arreglo_local.length; i++){
        // alert(arreglo_local[i]);
        // Define el nombre del segmento de la matriz global a rellenar vía GET
        url+="datos[";
        // Especifica numéricamente la posición que se está enviando en esa iteración
        url+=i;
        // Cierra el corchete matricial e iguala para asignarle sus valores
        url+="]=";
        // Inserta la cadena que engloba cantidad e id del material respectivo al ciclo en curso
        url+=arreglo_local[i];
        // Enlaza un '&' por cada ciclo para permitir un ensamble secuencial ininterrumpido en la query string
        url+="&";
    }
    // Culmina la cadena adjuntando la variable 'v' valorada en uno para indicar vaciado del carrito tras confirmarse
    url+="v=1";
    // alert(url);
    // Fuerza el direccionamiento del cliente a la pantalla recaudadora final con todos sus productos listados
    window.location.replace(url);
}
