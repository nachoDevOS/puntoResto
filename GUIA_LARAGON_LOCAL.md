# Guia para ejecutar PuntoResto en local con Laragon

Esta guia es para instalar el sistema en una PC local de cliente y dejarlo listo para usarse cada vez que se encienda la computadora.

## Recomendacion importante

Para una PC de cliente, lo mejor es NO depender de `npm run dev`.

`npm run dev` levanta Vite y sirve los archivos frontend en modo desarrollo. Eso sirve mientras estas programando, pero para un cliente local conviene ejecutar una sola vez:

```bash
npm run build
```

Con eso se generan los archivos en `public/build` y Laragon puede servir el sistema sin tener abierto el servicio de Node/Vite.

## Requisitos

- Laragon instalado.
- PHP 8.3 o superior en Laragon.
- MySQL/MariaDB activo en Laragon.
- Composer instalado.
- Node.js instalado.

## Instalacion inicial

1. Copiar el proyecto dentro de Laragon:

```text
C:\laragon\www\puntoResto
```

2. Abrir una terminal en la carpeta del proyecto:

```bash
cd C:\laragon\www\puntoResto
```

3. Instalar dependencias PHP:

```bash
composer install
```

4. Instalar dependencias JavaScript:

```bash
npm install
```

5. Crear el archivo `.env` si no existe:

```bash
copy .env.example .env
```

6. Generar la clave de Laravel:

```bash
php artisan key:generate
```

7. Crear la base de datos en Laragon.

Nombre recomendado:

```text
puntoresto
```

8. Revisar el `.env`:

```env
APP_URL=http://puntoresto.test
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=puntoresto
DB_USERNAME=root
DB_PASSWORD=
```

9. Ejecutar migraciones:

```bash
php artisan migrate --force
```

10. Compilar el frontend:

```bash
npm run build
```

11. En Laragon, usar:

```text
Menu > www > puntoResto
```

O abrir en el navegador:

```text
http://puntoresto.test
```

## Como iniciar automaticamente al encender la PC

### Opcion recomendada: usar Laragon + frontend compilado

Si ya ejecutaste `npm run build`, no necesitas levantar `npm run dev`.

Solo configura Laragon para iniciar con Windows:

1. Abrir Laragon.
2. Ir a:

```text
Menu > Preferences
```

3. Activar:

```text
Start Laragon when Windows starts
Start all services automatically
```

Con eso, cuando la PC encienda, Laragon inicia Apache/Nginx y MySQL automaticamente. El sistema queda disponible en:

```text
http://puntoresto.test
```

## Si realmente quieres levantar npm run dev automaticamente

Usa esto solo si estas desarrollando en esa PC y necesitas cambios en vivo.

1. Crear un archivo llamado:

```text
C:\laragon\www\puntoResto\iniciar-vite.bat
```

2. Poner este contenido:

```bat
@echo off
cd /d C:\laragon\www\puntoResto
npm run dev -- --host 127.0.0.1
```

3. Abrir el Programador de tareas de Windows.

4. Crear una tarea basica:

```text
Nombre: PuntoResto Vite
Desencadenador: Al iniciar sesion
Accion: Iniciar un programa
Programa: C:\laragon\www\puntoResto\iniciar-vite.bat
```

5. Guardar la tarea.

Mientras esa tarea este activa, Windows levantara Vite al iniciar sesion.

## Ruta mas estable para cliente

Para una instalacion real en cliente:

1. Laragon inicia automaticamente.
2. MySQL inicia automaticamente.
3. El proyecto vive en `C:\laragon\www\puntoResto`.
4. Ejecutas `npm run build` cada vez que actualices el frontend.
5. El cliente abre:

```text
http://puntoresto.test
```

Asi no se necesita terminal abierta, ni `npm run dev`, ni usuario/clave para entrar al sistema.

## Cuando actualices el sistema

Despues de copiar nuevos cambios al cliente:

```bash
composer install
npm install
php artisan migrate --force
npm run build
php artisan optimize:clear
```

Luego reinicia Laragon si hace falta.

## Notas de impresion

El `.env` tiene estas variables para el servicio de ticket:

```env
PRINT_TICKET_SERVICE_URL=http://localhost:3051
PRINT_TICKET_TYPE=usb
PRINT_TICKET_TEMPLATE=ticket
PRINT_TICKET_BUSSINE="${APP_NAME}"
```

Si el sistema de impresion usa un servicio externo en `localhost:3051`, ese servicio tambien debe iniciar automaticamente en Windows.
