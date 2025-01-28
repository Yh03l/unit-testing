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

Este proyecto implementa tests unitarios siguiendo las mejores prácticas de Domain-Driven Design y Clean Architecture.

### Estructura de Tests

### Cobertura Implementada
- Value Objects
  - Email (Validaciones, casos límite)
- Queries
  - GetUserByEmail (Con mocks y casos de error)

### Tecnologías de Testing
- PHPUnit para testing unitario
- XDebug para cobertura de código
- Mocks para aislar dependencias

### Ejecutar Tests

```bash
# Ejecutar tests
./vendor/bin/phpunit

# Generar reporte de cobertura
./vendor/bin/phpunit --coverage-html coverage
```
