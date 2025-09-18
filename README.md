# Sistema de Control de Ventas

Sistema web para la gestión de ventas con **emisión de facturas y boletas electrónicas**, reportes dinámicos y gráficos.  
Actualmente está en proceso de desarrollo.  

## 🚀 Características principales
- Registro y gestión de clientes, productos, proveedores y usuarios.  
- Emisión de **facturas y boletas electrónicas en PDF**.  
- Reportes de ventas y productos con filtros por fecha.  
- Exportación de reportes a **Excel y PDF**.  
- Gráficos interactivos con **Chart.js**.  

## 📦 Requisitos
- PHP 7.4 o superior  
- MySQL 5.7 o superior  
- XAMPP/LAMP/WAMP (servidor local)  
- Composer (para gestionar dependencias de PHP)  

## ⚙️ Instalación
1. Clonar este repositorio:
   ```bash
   git clone https://github.com/CHANG15987/sistema-control-ventas.git

2. Importar la base de datos:

Dentro de la carpeta /bd se encuentra el archivo facturacion.sql.

Importarlo en MySQL usando phpMyAdmin o consola:
mysql -u root -p facturacion < bd/facturacion.sql

3. Configurar la conexión a la base de datos:

Editar el archivo conexion.php con los datos de tu servidor local:
$host = "localhost";
$user = "root";
$pass = "";
$db   = "facturacion";

4. Levantar el servidor local (ejemplo con XAMPP):

Colocar el proyecto en la carpeta htdocs.

Acceder desde el navegador:
http://localhost/facturacion

5. 👤 Acceso al sistema
Usuario: admin
Contraseña: admin123

6. 📌 Estado
Desarrollado por Chang Guevara Sapa
Este proyecto aún está en proceso de desarrollo y se irán añadiendo nuevas funcionalidades.