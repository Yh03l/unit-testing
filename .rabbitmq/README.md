# Configuración de RabbitMQ para NurtriCenter

Este documento explica la configuración de RabbitMQ utilizada en el sistema NurtriCenter.

## ¿Qué es el archivo definitions.json?

El archivo `definitions.json` es una configuración predefinida para RabbitMQ que permite establecer automáticamente:
- Usuarios y sus permisos
- Intercambios (exchanges)
- Colas (queues)
- Enlaces entre intercambios y colas (bindings)

Esto nos permite tener una configuración consistente cada vez que iniciamos RabbitMQ.

## Estructura del Sistema de Mensajería

### Usuarios del Sistema
Tenemos diferentes usuarios configurados para cada servicio:
- `cateringUser`: Para el servicio de catering
- `comercialUser`: Para el servicio comercial
- `logisticaUser`: Para el servicio de logística
- `nutricionUser`: Para el servicio de nutrición
- `admin`: Usuario administrador general

Cada usuario tiene permisos completos en su ámbito de trabajo.

### Intercambios (Exchanges)
Los intercambios son puntos donde los mensajes son publicados. Utilizamos el tipo "fanout" que distribuye los mensajes a todas las colas vinculadas. Algunos ejemplos son:

- `cliente-creado`: Notifica cuando se crea un nuevo cliente
- `plan-alimentario-creado`: Notifica cuando se crea un nuevo plan alimentario
- `catering-contratado`: Notifica cuando se contrata un servicio de catering
- `entrega-cancelada`: Notifica cuando se cancela una entrega

### Colas (Queues)
Las colas almacenan los mensajes para cada servicio. El nombre de la cola indica qué servicio la utiliza y qué tipo de mensajes recibe. Por ejemplo:

- `catering.cliente-creado`: Cola del servicio de catering para nuevos clientes
- `logistica.cliente-creado`: Cola del servicio de logística para nuevos clientes
- `nutricion.evaluacion-nutricional-contratado`: Cola del servicio de nutrición para evaluaciones contratadas

### Enlaces (Bindings)
Los enlaces conectan los intercambios con las colas. Por ejemplo:
- Cuando se publica un mensaje en el intercambio `cliente-creado`, se distribuye a las colas:
  - `catering.cliente-creado`
  - `logistica.cliente-creado`
  - `nutricion.cliente-creado`

## Flujo de Mensajes

1. Un servicio publica un mensaje en un intercambio (ejemplo: `cliente-creado`)
2. El intercambio distribuye el mensaje a todas las colas vinculadas
3. Cada servicio procesa los mensajes de sus colas correspondientes

## Ejemplo de Flujo

Cuando se crea un nuevo cliente:
1. El servicio que crea el cliente publica un mensaje en el intercambio `cliente-creado`
2. Este mensaje se distribuye automáticamente a:
   - La cola de catering para gestionar servicios de alimentación
   - La cola de logística para preparar entregas
   - La cola de nutrición para evaluaciones nutricionales

## Conceptos Técnicos Importantes

### Virtual Hosts (vhost)
- Un vhost es como un "mini-broker" dentro de RabbitMQ
- Similar a tener diferentes bases de datos en un servidor
- En nuestra configuración usamos el vhost por defecto "/"
- Todos los servicios comparten este mismo espacio virtual

### Routing Keys
- En nuestra configuración las routing keys están vacías (`""`)
- Esto es porque usamos exchanges tipo "fanout"
- Los exchanges fanout ignoran la routing key y envían a todas las colas vinculadas
- Otros tipos de exchanges usan routing keys de manera diferente:
  - Direct: para enrutamiento directo
  - Topic: para enrutamiento basado en patrones
  - Headers: para enrutamiento basado en headers

### Configuraciones de Durabilidad
- Exchanges y colas están marcados como `durable: true`
- Esto significa que sobreviven a reinicios del broker
- `auto_delete: false` en exchanges evita que se eliminen automáticamente

### Permisos
Los usuarios tienen permisos definidos por tres patrones:
- `configure: ".*"`: Permite crear/eliminar recursos
- `write: ".*"`: Permite publicar mensajes
- `read: ".*"`: Permite consumir mensajes
El patrón `".*"` significa acceso completo al recurso correspondiente.

## Estructura de Mensajes

### Eventos del Sistema

1. **Gestión de Clientes**
   - Exchange: `cliente-creado`
   - Consumidores: Catering, Logística, Nutrición
   - Uso: Notifica la creación de nuevos clientes

2. **Gestión Nutricional**
   - Exchange: `evaluacion-nutricional-contratado`
   - Consumidores: Nutrición
   - Uso: Notifica cuando se contrata una evaluación nutricional

   - Exchange: `plan-alimentario-creado`
   - Consumidores: Catering
   - Uso: Notifica la creación de un nuevo plan alimentario

   - Exchange: `plan-alimentario-asignado`
   - Consumidores: Comercial
   - Uso: Notifica cuando se asigna un plan alimentario

3. **Gestión de Catering**
   - Exchange: `catering-contratado`
   - Consumidores: Catering
   - Uso: Notifica la contratación de servicios de catering

4. **Gestión de Logística**
   - Exchange: `orden-trabajo-finalizado`
   - Consumidores: Logística
   - Uso: Notifica la finalización de órdenes de trabajo

   - Exchange: `entrega-cancelada`
   - Consumidores: Catering
   - Uso: Notifica la cancelación de entregas

### Convención de Nombres

- Exchanges: Representan eventos en pasado (ej: `cliente-creado`)
- Colas: Formato `[servicio].[evento]` (ej: `catering.cliente-creado`)
- Todos los nombres usan kebab-case para mejor legibilidad

## Notas Importantes

- Todas las colas son duraderas (`durable: true`), lo que significa que sobreviven a reinicios del broker
- Los intercambios son de tipo "fanout", lo que significa que distribuyen los mensajes a todas las colas vinculadas
- Cada servicio tiene su propio usuario con credenciales específicas
- La configuración se carga automáticamente al iniciar el contenedor de RabbitMQ 