# evg-reservas-libros-back

Este es el backend para la aplicación de reservas de libros.

## Primeros Pasos

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/joseluisdelvall/evg-reservas-libros-back.git
    ```

2. **Instalar composer:**
    Instala composer en el siguiente enlace y sigue los pasos
    [https://getcomposer.org/download/](https://getcomposer.org/download/)

2.  **Instalar las dependencias de PHP (con Composer):**
    Asegúrate de tener Composer instalado en tu sistema. Si no lo tienes, puedes descargarlo desde [https://getcomposer.org/](https://getcomposer.org/). Luego, ejecuta el siguiente comando en la raíz del proyecto:
    ```bash
    composer install
    ```

3.  **(Opcional) Verificar la versión de Composer:**
    ```bash
    composer --version
    ```

4.  **Posible problema con la extensión ZIP:**
    Si tienes problemas al instalar las dependencias con Composer, es posible que necesites activar la extensión `zip` en tu archivo `php.ini`. Busca la línea `;extension=zip` y quita el punto y coma al principio. Después de modificar el archivo, reinicia tu servidor web (por ejemplo, Apache si estás usando XAMPP).

¡Con estos pasos, cualquier persona debería poder configurar tu proyecto fácilmente! ¿Qué te parece?
