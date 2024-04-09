# Aplicación QUESTIONS

## Requisitos

1. PHP 8.2+
2. MariaDB 10+
3. Composer 2.2.6+

## 1. Pasos para la instalación de la apliación
### 1.1. Crear base de datos
Es necesario crear una base de datos para la puesta en marcha del API REST.

### 1.2. Configurar la conexión
Para configurar la conexión de la aplicación con la base de datos es necesario editar el archivo *.env* y modificar las siguientes propiedades con las de la conexión de base de datos:

      DB_CONNECTION=mysql
      DB_HOST=localhost
      DB_PORT=3306
      DB_DATABASE=
      DB_USERNAME=
      DB_PASSWORD=

### 1.3. Crear la migración
Para crear las tablas iniciales que necesita la aplicación sólo hace falta ejecutar la migración de php con el siguiente comando

       $ php artisan migrate

### 1.4. Importar datos iniciales
Para importar los datos iniciales de la base de preguntas se puede utilizar el archivo 20240329_question_data_v1.sql dentre de la carpeta database/data del proyecto. 

También se pueden importar las preguntas directamente de los archivos de la CAIB desde la página [Comisión de seguimiento para la reducción de la temporalidad de la ocupació pública](https://intranet.caib.es/sites/mesatemporalitat/ca/inici/) con el script en Python 3 que se encuentra en la carpeta database/scripts del proyecto.

### 1.5. Iniciar el servidor
Para iniciar el servidor utilizamos el siguiente comando

      $ php artisan serve


# HOWTO PHP
## Start php server
     php artisan serve

## Create new model
      php artisan make:model Name
      php artisan model:show Name

## Migrations
      php artisan migrate
      php artisan migrate:status
      php artisan migrate:rollback --step=1

## Routes
### Clear Route Cache
      php artisan route:clear

### Route List
      php artisan route:list
     
# Resources
https://laravel.com/docs/10.x/sanctum

https://medium.com/@abdelra7manabdullah/api-authentication-using-laravel-sanctum-v10-x-21dfe130cda

