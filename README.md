# Gestión de órdenes de trabajo

## Descripción

Mini-módulo de gestión de órdenes de trabajo con frontend en AngularJS 1.5+ y backend en PHP 8 orientado a objetos. Incluye CRUD completo, validaciones y paginación.

---

## Tecnologías utilizadas

* **Frontend:** AngularJS 1.5, Bootstrap 5
* **Backend:** PHP 8 orientado a objetos (sin frameworks)
* **Base de datos:** MySQL
* **API REST:** Rutas para gestión de órdenes (GET, POST, PUT, DELETE)

---

## Requisitos previos

* PHP 8 instalado
* Servidor web (Apache, Nginx, o similar) con soporte PHP
* MySQL o MariaDB 
* Cliente de mysql ej. phpmyadmin(opcional)
* Git (para clonar el repositorio)
* Navegador moderno(yo use firefox)

---

## Instalación y ejecución

1. Clona el repositorio:

   ```bash
   git clone https://github.com/JairoPreciado/pruebaTecnica.git
   cd pruebaTecnica/
   ```

2. Importa la base de datos:

   * Abre tu cliente MySQL (phpMyAdmin, MySQL Workbench, línea de comandos, etc.)
   * Importa el archivo `ordenes.sql` que está en la raíz del proyecto

3. Configura la conexión a la base de datos:

   * Abre `backend/config/Database.php`
   * Modifica las variables con tus credenciales de MySQL (host, usuario, contraseña, nombre de BD)

4. Levanta el servidor web apuntando al proyecto:

   * Si usas Apache, coloca la carpeta en tu `htdocs` o configura un virtual host apuntando a la raíz del proyecto.
   * Alternativamente, puedes usar PHP Built-in server para pruebas rápidas:

     ```bash
     cd backend
     php -S localhost:8000
     ```

5. Accede a la aplicación frontend:

   * Abre `frontend/index.html` directamente en el navegador
   * O si prefieres, podrias usar la alternativa previamente mencionada "PHP Built-in server" solo que con un numero de puerto distinto
   
     ```bash
     cd frontend
     php -S localhost:8001
     ```
---

## Uso

* En la página principal verás la lista de órdenes con paginación
* Puedes buscar órdenes con filtro avanzado (ejemplo: `cliente:jairo, id:1`)
* Crear, editar y eliminar órdenes mediante el formulario y los botones correspondientes

---

## Qué haría distinto en un entorno real

* Implementaría autenticación y permisos para que solo usuarios autorizados puedan acceder y modificar órdenes
* Usaria un framework mas a medida como laravel para favorecer a aspectos como la escalabilidad del proyecto y demas
* Usaria versiones mas actualizadas o nuevas para explotar nuevas funcionalidades de cada tecnologia
* Agregaria logs y depuracion para identificar posibles problemas de forma precisa
* Mejoraria el aspecto de las validaciones y manejo de errores tanto en el cliente(frontend) como en el servidor(backend)
