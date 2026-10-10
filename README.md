# Gestión de artículos — PHP + MySQL

Aplicación CRUD para registrar, listar, buscar, editar y eliminar artículos. Cada artículo tiene nombre, marca, unidades y ubicación.

## Requisitos
- PHP 8.0 o superior con extensiones PDO y pdo_mysql.
- MySQL/MariaDB.
- La base `webjobs_gestionarticulos` debe existir y el usuario MySQL debe tener permisos para crear tablas y leer/escribir registros.

## Configuración
Las credenciales están en `config.php`:
- Host: `mysql-webjobs.alwaysdata.net`
- Base de datos: `webjobs_gestionarticulos`
- Usuario: `webjobs`

Por seguridad, en un despliegue público conviene mover las credenciales a variables de entorno y no publicar el archivo.

## Instalación
1. Sube todos los archivos y carpetas a tu alojamiento PHP, conservando la carpeta `api`.
2. Verifica que `config.php` contenga las credenciales correctas.
3. Abre `https://TU-DOMINIO/index.php` (o la URL en la que hayas publicado el proyecto).
4. Al hacer la primera petición a la API, la aplicación crea la tabla `articulos` si no existe.

**Importante:** el código crea automáticamente la tabla, pero no crea la base de datos. `webjobs_gestionarticulos` debe estar creada previamente en el servidor MySQL.

## API REST
Ruta base: `https://TU-DOMINIO/api/articulos.php`

| Método | Ruta | Acción |
|---|---|---|
| GET | `/api/articulos.php` | Listar artículos |
| GET | `/api/articulos.php?id=1` | Consultar un artículo |
| GET | `/api/articulos.php?q=logitech` | Buscar por nombre, marca o ubicación |
| POST | `/api/articulos.php` | Crear artículo |
| PUT | `/api/articulos.php?id=1` | Actualizar artículo |
| DELETE | `/api/articulos.php?id=1` | Eliminar artículo |

### Ejemplo de JSON para POST y PUT
```json
{
  "nombre": "Teclado",
  "marca": "Logitech",
  "unidades": 12,
  "ubicacion": "Bodega Norte"
}
```

Las unidades deben ser un entero igual o mayor que cero. Las rutas completas se forman agregando la ruta base al dominio donde se publique el proyecto.

## Esquema de tabla
La tabla `articulos` contiene `id`, `nombre`, `marca`, `unidades`, `ubicacion`, `creado_en` y `actualizado_en`.
