# Microservicio Comercial - Nur-tricenter

## Descripción

El Microservicio Comercial es un componente central del sistema Nur-tricenter que gestiona todos los aspectos comerciales y contractuales de los servicios nutricionales. Este microservicio es responsable de la gestión del catálogo de servicios, la administración de contratos y la facturación de los servicios ofrecidos por la empresa.

## Propósito

El propósito principal es proporcionar una plataforma robusta y escalable que permita:
- Gestionar el catálogo de servicios nutricionales
- Administrar los contratos de los pacientes
- Manejar el proceso de facturación
- Proporcionar una API para la integración con otros microservicios

## Funcionalidades

### 1. Gestión de Catálogo
- Definición y mantenimiento del catálogo de servicios
- Administración de precios y costos de servicios
- Actualización de estados de servicios
- Consulta de servicios disponibles

### 2. Gestión de Contratos
- Creación de contratos para servicios nutricionales
- Activación y cancelación de contratos
- Seguimiento del estado de contratos
- Consulta de historial de contratos por paciente

### 3. Facturación
- Generación automática de facturas
- Emisión de facturas por servicios contratados
- Gestión del estado de facturas
- Consulta de historial de facturación

### 4. Administración de Usuarios
- Gestión de perfiles de usuarios (Administradores y Pacientes)
- Validación de credenciales
- Actualización de información de usuarios
- Control de permisos y accesos

## Diagrama de Clases del Dominio

<p align="center">
  <img src="public/img/Diagrama MSComercial.jpg" alt="Diagrama de Dominio" width="800"/>
</p>

## Base de Datos

<p align="center">
  <img src="public/img/Diagrama_bd.jpg" alt="Diagrama de Base de Datos" width="800"/>
</p>

El diagrama muestra la estructura central del dominio, incluyendo:
- Agregados principales (Usuario, Catálogo, Contrato)
- Entidades (Administrador, Paciente, Factura)
- Objetos de valor (Email, CostoServicio, FechaContrato)
- Relaciones y comportamientos clave del dominio

## Tecnologías Utilizadas
- Laravel 11
- PHP 8.2+
- MySQL/PostgreSQL
- Laravel Modules

## Testing

Este proyecto implementa diferentes tipos de pruebas siguiendo las mejores prácticas de Domain-Driven Design y Clean Architecture:

### Tipos de Pruebas Implementadas
- Tests Unitarios
- Tests de Contrato (Contract Testing con Pact)
- Tests de Integración

### Documentación Detallada
Para una guía completa sobre la estrategia de testing, incluyendo:
- Estructura y organización de tests
- Convenciones y buenas prácticas
- Ejemplos de implementación
- Guía de Contract Testing con Pact
- Configuración y ejecución

👉 Ver la [Documentación Completa de Testing](tests/README.md)

### Ejecutar Tests

```bash
# Ejecutar todos los tests
./vendor/bin/phpunit

# Generar reporte de cobertura
./vendor/bin/phpunit --coverage-html coverage
```

# Unit Testing Project

Este es un proyecto de Laravel para practicar pruebas unitarias y de integración.

## Requisitos Previos

- PHP 8.2 o superior
- Composer
- Node.js (versión LTS recomendada)
- Git

## Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/Yh03l/unit-testing.git
cd unit-testing
```

2. Instalar dependencias de PHP:
```bash
composer install
```

3. Instalar dependencias de Node.js:
```bash
npm install
```

4. Configurar el entorno:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configurar la base de datos:
- El proyecto está configurado para usar SQLite por defecto
- Se creará automáticamente el archivo de base de datos al ejecutar:
```bash
php artisan migrate
```

## Ejecutar el Proyecto

1. Iniciar el servidor de desarrollo:
```bash
php artisan serve
```

El proyecto estará disponible en `http://localhost:8000`

## Ejecutar Pruebas

Para ejecutar las pruebas unitarias:
```bash
php artisan test
```

Este comando iniciará:
- Servidor Laravel
- Cola de trabajos
- Logs en tiempo real
- Servidor de Vite

## Estructura del Proyecto

- `/app` - Código principal de la aplicación
- `/tests` - Pruebas unitarias y de integración
- `/src/Commercial` - Módulo de lógica comercial y código principal del microservicio
- `/database` - Migraciones y seeders
